<?php

class GO_Profiler
{
	public $hooks = array();
	private $_queries_at_last_call = 0;
	private $_query_running_time = 0;
	public $epochs = array(
		'startup',
		'init',
		'template_redirect',
	);

	/**
	 * constructor
	 */
	public function __construct()
	{
		if ( ! $this->active() )
		{
			return;
		}

		// behind the curtain: how we hook to every hook
		// priority is a fairly large prime, Mersenne at that
		// it really should be called at the very last thing for every hook
		add_action( 'all', array( $this, 'hook' ), 2147483647 );
		register_shutdown_function( array( $this, 'shutdown' ) );
	}//end __construct

	/**
	 * Are we active for this page load?
	 */
	public function active()
	{
		if ( defined( 'WP_CLI' ) && WP_CLI )
		{
			return FALSE;
		}

		return TRUE;
	}//end active

	/**
	 * hook
	 *
	 * @global wpdb $wpdb
	 * @global int $timestart
	 */
	public function hook()
	{
		global $wpdb, $timestart;
		$timenow = microtime( TRUE );

		// capture the db query info
		$queries = array();
		if ( is_array( $wpdb->queries ) && ( $wpdb->num_queries > $this->_queries_at_last_call ) )
		{
			foreach ( array_slice( $wpdb->queries, ( 0 - ( $wpdb->num_queries - $this->_queries_at_last_call ) ) ) as $query )
			{
				$queries[] = $wpdb->num_queries - $this->_queries_at_last_call;
				$this->_query_running_time += $query[1];
			}//end foreach
		}//end if
		else
		{
			//adds none as visible test of is_array($wpdb->queries)
			$queries[] = 'none';
		}//end else

		// get a subset of the backtrace and format it into text
		$backtrace = array();
		foreach ( array_slice( debug_backtrace(), 4, 2 ) as $temp )
		{
			//had to change these to test, as WP_DEBUG sets error_reporting to E_ALL - page fills with warnings for functions w/o files
			$backtrace_function = isset( $temp['function'] ) ? $temp['function'] : ' ';
			$backtrace_file = isset( $temp['file'] ) ? sprintf( ' in %1$s()', $temp['file'] ) : ' ';
			$backtrace_line = isset( $temp['line'] ) ? sprintf( ' at %1$s()', $temp['line'] ) : ' ';
			$backtrace[] = $backtrace_function . $backtrace_line . $backtrace_file;
		}//end foreach

		// capture the remaining data
		$this->hooks[] = ( object ) array(
			'hook'          => func_get_arg( 0 ), // the name of the current hook
			'memory'        => memory_get_usage( FALSE ), // total script memory usage, in bytes
			'runtime'       => $timenow - $timestart, // the total execution time, in seconds, to the start of the hook
			'query_runtime' => $this->_query_running_time,
			'query_count'   => $wpdb->num_queries,
			'queries'       => (boolean) $queries ? $queries : NULL,
			'backtrace'     => $backtrace,
		);

		$this->_queries_at_last_call = absint( $wpdb->num_queries );
	}//end hook

	/**
	 * get metrics for the hook transcript
	 */
	public function get_metrics( $transcript )
	{
		// we'll have to iterate the hook log a few times
		// the first is to initialize the metrics
		$delta_m = $delta_t = $delta_q = $hook = $hook_m = $hook_t = $hook_q = array();
		foreach ( $transcript as $k => $v )
		{
			if ( 0 === $k )
			{
				$delta_m[ $k ] = 0;
				$overhead_m = $transcript[0]->memory;

				$delta_t[ $k ] = 0;
				$overhead_t = $transcript[0]->runtime;

				$delta_q[ $k ] = 0;
				$overhead_q = $transcript[0]->query_runtime;
			}
			else
			{
				$delta_m[ $k ] = $v->memory        - $transcript[ $k - 1 ]->memory;
				$delta_t[ $k ] = $v->runtime       - $transcript[ $k - 1 ]->runtime;
				$delta_q[ $k ] = $v->query_runtime - $transcript[ $k - 1 ]->query_runtime;
			}

			if ( ! isset( $hook[ $v->hook ] ) )
			{
				$hook[ $v->hook ] = 0;
			}//end if
			$hook[ $v->hook ]++;

			if ( ! isset( $hook_m[ $v->hook ] ) )
			{
				$hook_m[ $v->hook ] = 0;
			}//end if
			$hook_m[ $v->hook ] += $delta_m[ $k ];

			if ( ! isset( $hook_t[ $v->hook ] ) )
			{
				$hook_t[ $v->hook ] = 0;
			}//end if
			$hook_t[ $v->hook ] += $delta_t[ $k ];

			if ( ! isset( $hook_q[ $v->hook ] ) )
			{
				$hook_q[ $v->hook ] = 0;
			}//end if
			$hook_q[ $v->hook ] += $delta_q[ $k ];
		}//end foreach

		// now iterate to get the play-by-play hook transcript with metrics
		foreach ( $transcript as $k => $v )
		{
			$transcript[ $k ] = (object) array(
				'hook'          => $v->hook,
//				'memory'        => number_format( $v->memory / 1024 / 1024, 3 ),
				'memory'        => $v->memory,
//				'memory_delta'  => number_format( $delta_m[ $k ] / 1024 / 1024, 3 ),
				'memory_delta'  => $delta_m[ $k ],
//				'runtime'       =>  number_format( $v->runtime, 4 ),
				'runtime'       =>  $v->runtime,
//				'runtime_delta' => number_format( $delta_t[ $k ], 4 ),
				'runtime_delta' => $delta_t[ $k ],
//				'query_runtime' => number_format( $v->query_runtime, 4 ),
				'query_runtime' => $v->query_runtime,
//				'query_delta'   => number_format( $delta_q[ $k ], 4 ),
				'query_delta'   => $delta_q[ $k ],
//				'query_count'   => $v->query_count,
				'queries'       => $v->queries,
				'backtrace'     => $v->backtrace,
			);
		}//end foreach

		// and a final iteration over the list of hooks to summarize them
		$summary = $this->summarize_and_aggregate( $hook, $hook_m, $hook_t, $hook_q );

		$return = (object) array(
			'summary'    => $summary->summary,
			'aggregate'  => $summary->aggregate,
			'transcript' => $transcript,
			'overhead'   => (object) array(
				'memory'        => $overhead_m,
				'runtime'       => $overhead_t,
				'query_runtime' => $overhead_q,
			),
		);

//		$return->summary->total_memory = number_format( array_sum( $delta_m ) / 1024 / 1024, 3 );
		$return->summary->total_memory = array_sum( $delta_m );
//		$return->summary->total_time = number_format( array_sum( $delta_t ), 4 );
		$return->summary->total_time = array_sum( $delta_t );
//		$return->summary->total_querytime = number_format( array_sum( $delta_q ), 4 );
		$return->summary->total_querytime = array_sum( $delta_q );

		return $return;
	}//end get_metrics

	/**
	 * summarize the hook metrics
	 */
	public function summarize_and_aggregate( $hook, $hook_m, $hook_t, $hook_q )
	{
		// and a final iteration to summarize it all
		$return = (object) array(
			'summary' => (object) array(),
			'aggregate' => array(),
		);
		$return->summary->total_hooks = $return->summary->most_popular = $return->summary->most_memory = $return->summary->most_time = $return->summary->most_querytime = 0;
		foreach ( $hook as $k => $v )
		{
			$hook_mem = ( $hook_m[ $k ] / 1024 ) / 1024;
			$return->aggregate[] = array(
				'hook'      => $k,
//				'calls'     => number_format( $v ),
				'calls'     => $v,
//				'memory'    => number_format( $hook_mem, 3 ),
				'memory'    => $hook_mem,
//				'time'      => number_format( $hook_t[ $k ], 4 ),
				'time'      => $hook_t[ $k ],
//				'querytime' => number_format( $hook_q[ $k ], 4 ),
				'querytime' => $hook_q[ $k ],
			);
			$return->summary->total_hooks += $v;

			if ( $v > $return->summary->most_popular )
			{
				$return->summary->most_popular = $v;
				$return->summary->most_popular_name = $k;
			}//end if

			if ( $hook_mem > $return->summary->most_memory )
			{
				$return->summary->most_memory = $hook_mem;
				$return->summary->most_memory_name = $k;
			}//end if

			if ( $hook_t[ $k ] > $return->summary->most_time )
			{
				$return->summary->most_time = $hook_t[ $k ];
				$return->summary->most_time_name = $k;
			}//end if

			if ( $hook_q[ $k ] > $return->summary->most_querytime )
			{
				$return->summary->most_querytime = $v;
				$return->summary->most_querytime_name = $k;
			}//end if
		}//end foreach

		// format the numbers
		// @TODO: should we format the numbers in JS rather than here?
//		$return->summary->total_hooks   = number_format( $return->summary->total_hooks );
		$return->summary->total_hooks   = $return->summary->total_hooks;
//		$return->summary->most_popular = number_format( $return->summary->most_popular );
		$return->summary->most_popular = $return->summary->most_popular;
//		$return->summary->most_memory = number_format( $return->summary->most_memory, 3 );
		$return->summary->most_memory = $return->summary->most_memory;
//		$return->summary->most_time = number_format( $return->summary->most_time, 4 );
		$return->summary->most_time = $return->summary->most_time;
//		$return->summary->most_querytime = number_format( $return->summary->most_querytime, 4 );
		$return->summary->most_querytime = $return->summary->most_querytime;

		return $return;
	}//end summarize_and_aggregate

	/**
	 * blah hooks
	 */
	public function blah( $transcript )
	{
		// we're going to split the hook call transcript into epochs
		// this just preps the vars, the splitting is done on the next iteration
		// - startup to init
		// - init to template_redirect
		// - template_redirect to shutdown
		$epoch = (object) array();
		$next_epoch = $this->epochs;
		$epoch_current = array_shift( $next_epoch );

		// we'll have to iterate the hook log a few times
		// start by iteratating to get the transcript by epoch
		foreach ( $transcript as $v )
		{
			// is it time to shift epochs?
			if ( current( $next_epoch ) == $v->hook )
			{
				$epoch_current = array_shift( $next_epoch );
				$epoch->$epoch_current = array();
			}

			$epoch->{$epoch_current}[ ] = (object) $v;
		}//end foreach

		// now iterate through the epochs to get details
		foreach ( (array) $epoch as $k => $v )
		{
			$epoch->$k = $this->get_metrics( $v );
		}

		return $epoch;
	}//end blah

	/**
	 * prettyprint the json
	 */
	private function json_encode( $src )
	{
		return str_ireplace(
			array(
				'},{',
				'],[',
			),
			array(
				"},\n{",
				"],\n[",
			),
			json_encode( $src )
		);
	}//end json_encode

	/**
	 * shutdown hooks
	 */
	public function shutdown()
	{
		global $wpdb;
		global $wp_object_cache;

		echo '<script id="go-profiler-data">' . $this->json_encode( (object) array(
			'transcript' => $this->hooks,
//			'queries' => $wpdb->queries,
			'cache' => array(
				'hits' => (int) $wp_object_cache->cache_hits,
				'misses' => (int) $wp_object_cache->cache_misses,
			),
		) ) . ';</script>';
	}//end shutdown
}//end class

/**
 * Singleton
 *
 * @global GO_Profiler $go_profiler
 */
function go_profiler()
{
	global $go_profiler;

	if ( ! $go_profiler )
	{
		$go_profiler = new GO_Profiler();
	}//end if

	return $go_profiler;
}//end go_profiler