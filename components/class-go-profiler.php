<?php

class GO_Profiler
{
	public $hooks = array();
	private $_queries_at_last_call = 0;
	private $_query_running_time = 0;

	/**
	 * constructor
	 */
	public function __construct()
	{
		// behind the curtain: how we hook to every hook
		// priority is a fairly large prime, Mersenne at that
		// it really should be called at the very last thing for every hook
		add_action( 'all', array( $this, 'hook' ), 2147483647 );
		register_shutdown_function( array( $this, 'shutdown' ) );

		// these display the profile info in the Debug bar
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'debug_bar_panels', array( $this, 'debug_bar_panels' ) );
	}//end __construct

	/**
	 * enqueue scripts
	 */
	public function wp_enqueue_scripts()
	{
		// only continue if we're in a context where the debug bar is showing
		// and if not, then remove our tracking hook for performance
		if (
			! is_super_admin() ||
			! is_admin_bar_showing() ||
			! is_object( $GLOBALS['debug_bar'] ) ||
			$GLOBALS['debug_bar']->is_wp_login()
		)
		{
			remove_action( 'all', array( $this, 'hook' ), 2147483647 );
			return;
		}

		// @TODO: is either mustache or handlebars provided elsewhere in WP? ...perhaps not.
		// @TODO: mustache is definitely provided in https://github.com/GigaOM/go-ui/tree/master/components/js/lib/external
		wp_enqueue_script( 'mustache', plugins_url( 'js/external/mustache.min.js', __FILE__ ), FALSE, FALSE, TRUE );
		wp_enqueue_script( 'go-profiler', plugins_url( 'js/go-profiler.js', __FILE__ ), array( 'mustache', 'jquery' ), FALSE, TRUE );
		wp_enqueue_style( 'go-profiler', plugins_url( 'css/go-profiler.css', __FILE__ ), FALSE, FALSE, 'all' );
	}//end wp_enqueue_scripts

	/**
	 * add profiler panels
	 *
	 * @param array $panels to add
	 * @return $panels[] go_profiler_panel
	 */
	public function debug_bar_panels( $panels )
	{
		if ( ! class_exists( 'GO_Profiler_Hook_Panel' ) )
		{
			include __DIR__ . '/class-go-profiler-hook-panel.php';
			$panels[] = new GO_Profiler_Hook_Panel();
		}//end if

		if ( ! class_exists( 'GO_Profiler_Aggregate_Panel' ) )
		{
			include __DIR__ . '/class-go-profiler-aggregate-panel.php';
			$panels[] = new GO_Profiler_Aggregate_Panel();
		}//end if

		return $panels;
	}//end debug_bar_panels

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
	 * shutdown hooks
	 */
	public function summarize( $transcript )
	{
		//asdasdasd
		// asdasd
	}

	/**
	 * shutdown hooks
	 */
	public function shutdown()
	{
		// we'll have to iterate the hook log a few times
		// the first is to initialize the metrics
		$delta_m = $delta_t = $delta_q = $hook = $hook_m = $hook_t = array();
		foreach ( $this->hooks as $k => $v )
		{
			$delta_m[ $k ] = $v->memory - $this->hooks[ absint( $k - 1 ) ]->memory;
			$delta_t[ $k ] = $v->runtime - $this->hooks[ absint( $k - 1 ) ]->runtime;
			$delta_q[ $k ] = $v->query_runtime - $this->hooks[ absint( $k - 1 ) ]->query_runtime;

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
		}//end foreach

		// we're going to split the hook call transcript into epochs
		// this just preps the vars, the splitting is done on the next iteration
		// - startup to init
		// - init to template_redirect
		// - template_redirect to shutdown
		$epoch = (object) array(
			'startup' => array(),
		);
		$epoch_current = 'startup';
		$next_epoch = array(
			'init',
			'template_redirect',
		);

		// now iterate to get the play-by-play hook transcript with metrics
		// ...and the transcript by epoch
		foreach ( $hook as $k => $v )
		{
			// is it time to shift epochs? 
			if ( current( $next_epoch ) == $v->hook )
			{
				$epoch_current = array_shift( $next_epoch );
				$epoch->$epoch_current = array();
			}

			$transcript[] = $epoch->{$epoch_current}[] = array(
				'hook'      => $v->hook,
				'memory'    => number_format( $v->memory / 1024 / 1024, 3 ),
				'delta-m'   => number_format( $delta_m[ $k ] / 1024 / 1024, 3 ),
				'runtime'   => number_format( $v->runtime, 4 ),
				'delta-r'   => number_format( $delta_t[ $k ], 4 ),
				'q-runtime' => number_format( $v->query_runtime, 4 ),
				'delta-q'   => number_format( $delta_q[ $k ], 4 ),
				'q-count'   => $v->query_count,
				'queries'   => $v->queries,
				'backtrace' => $v->backtrace,
			);
		}//end foreach

		// and a final iteration to summarize it all
		$total = $max_mem = $longest = $popular = 0;
		foreach ( $hook as $k => $v )
		{
			$hook_mem = ( $hook_m[ $k ] / 1024 ) / 1024;
			$agg_hook[] = array(
				'hook'   => $k,
				'calls'  => number_format( $v ),
				'memory' => number_format( $hook_mem, 3 ),
				'time'   => number_format( $hook_t[ $k ], 4 ),
			);
			$total += $v;

			if ( $hook_mem > $max_mem )
			{
				$max_mem = $hook_mem;
				$max_mem_name = $k;
			}//end if

			if ( $hook_t[ $k ] > $longest )
			{
				$longest = $hook_t[ $k ];
				$longest_name = $k;
			}//end if

			if ( $v > $popular )
			{
				$popular = $v;
				$popular_name = $k;
			}//end if
		}//end foreach

		$summary = array(
			'total_hooks'       => number_format( $total ),
			'max_mem'           => number_format( $max_mem, 3 ),
			'max_mem_name'      => $max_mem_name,
			'longest_hook'      => number_format( $longest, 4 ),
			'longest_hook_name' => $longest_name,
			'most_often'        => number_format( $popular ),
			'most_often_name'   => $popular_name,
		);

		$go_profiler_json = json_encode( array(
			'summary'     => $summary,
			'aggregate'   => $agg_hook,
			'transcript'  => $transcript,
		) );

		// @TODO: this should maybe be moved to a localize_script() call and the JS refactored to not require the $(document).trigger(...)
		// $go_profiler_json is escaped with json_encode() just above
		echo "<script> ( function( $ ) { var go_profiler_data = '$go_profiler_json'; $(document).trigger( 'go-profiler-data-loaded', [ go_profiler_data ] ); })( jQuery ); </script>";
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