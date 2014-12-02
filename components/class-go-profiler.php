<?php

class GO_Profiler
{
	public $config = NULL;
	public $metrics = NULL;
	public $wpcli = NULL;
	public $is_wpcli = FALSE;
	public $hooks = array();
	private $_queries_at_last_call = 0;
	private $_query_running_time = 0;
	public $apc_start = NULL;

	/**
	 * constructor
	 */
	public function __construct()
	{
		// behind the curtain: how we hook to every hook
		// priority is a fairly large prime, Mersenne at that
		// it really should be called at the very last thing for every hook
		add_action( 'all', array( $this, 'hook' ), 2147483647 );

		if ( defined( 'WP_CLI' ) && WP_CLI )
		{
			$this->is_wpcli = TRUE;
			$this->wpcli();
		}

		if ( ! $this->active() )
		{
			// remove the hook to "all" we added earler
			// doing it in this order so we can start tracking as soon as possible
			remove_action( 'all', array( $this, 'hook' ), 2147483647 );
			return;
		}

		// if we're here, we're active and running
		// this shutdown function will output our tracking data
		register_shutdown_function( array( $this, 'shutdown' ) );

		$this->apc_start = $this->raw_apc_data();
	}//end __construct

	/**
	 * A loader for the WP:CLI class
	 */
	public function wpcli()
	{
		if ( ! $this->wpcli )
		{
			require_once __DIR__ . '/class-go-profiler-wpcli.php';

			// declare the class to WP:CLI
			WP_CLI::add_command( 'go-profiler', 'GO_Profiler_Wpcli' );

			$this->wpcli = TRUE;
		}
	}//end wpcli

	/**
	 * An accessor for the metrics object
	 */
	public function metrics()
	{
		if ( ! $this->metrics )
		{
			require_once __DIR__ . '/class-go-profiler-metrics.php';
			$this->metrics = new GO_Profiler_Metrics();
		}

		return $this->metrics;
	}//end metrics

	/**
	 * Are we active for this page load?
	 */
	public function active()
	{
		// don't run on WP:CLI requests
		if ( $this->is_wpcli )
		{
			return FALSE;
		}

		// Don't run if the secret isn't set as a $_GET var
		if ( ! isset( $_GET[ $this->config( 'secret' ) ] ) )
		{
			return FALSE;
		}

		// looks like we're active and running
		return TRUE;
	}//end active

	/**
	 * Get config settings
	 */
	public function config( $key = NULL )
	{
		if ( ! $this->config )
		{
			$this->config = apply_filters(
				'go_config',
				array(
					'secret' => 'abracadabra',
				),
				'go-profiler'
			);
		}//END if

		if ( ! empty( $key ) )
		{
			return isset( $this->config[ $key ] ) ? $this->config[ $key ] : NULL ;
		}

		return $this->config;
	}//end config

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

	private function apc_data()
	{
		if ( ! $this->apc_start )
		{
			return $this->raw_apc_data();
		}

		$now = $this->raw_apc_data();

		return (object) array(
			'hits' => (real) $now->hits - $this->apc_start->hits,
			'misses' => (real) $now->misses - $this->apc_start->misses,
			'inserts' => (real) $now->inserts - $this->apc_start->inserts,
			'fragmentation' => $now->fragmentation,
		);
	}

	/**
	 * get raw(-ish) apc data
	 */
	private function raw_apc_data()
	{
		if( ! function_exists('apc_cache_info' ) )
		{
			return (object) array(
				'hits' => NULL,
				'misses' => NULL,
				'inserts' => NULL,
				'fragmentation' => NULL,
			);
		}

		// get basic apc cache status
		$cache = apc_cache_info( 'opcode' );

		// get and calculate fragmentation status
		$mem = apc_sma_info();
		$nseg = $freeseg = $fragsize = $freetotal = 0;
		for( $i=0; $i < $mem['num_seg']; $i++ )
		{
			$ptr = 0;
			foreach( $mem['block_lists'][$i] as $block )
			{
				if ( $block['offset'] != $ptr )
				{
					++$nseg;
				}
				$ptr = $block['offset'] + $block['size'];

				/* Only consider blocks <5M for the fragmentation % */
				if ( $block['size'] < ( 5 * 1024 * 1024 ) )
				{
					$fragsize += $block['size'];
				}
				$freetotal += $block['size'];
			}
			$freeseg += count( $mem['block_lists'][ $i ] );
		}

		if ( $freeseg > 1 )
		{
			$frag = sprintf("%.2f%% (%sM out of %sM in %d fragments)", ( $fragsize / $freetotal ) * 100, round( $fragsize / 1024 / 1024, 2 ), round( $freetotal / 1024 / 1024, 2 ), $freeseg );
		}
		else
		{
			$frag = "0%";
		}

		return (object) array(
			'hits' => $cache['num_hits'],
			'misses' => $cache['num_misses'],
			'inserts' => $cache['num_inserts'],
			'fragmentation' => $frag,
		);
	}//end get_apc_data

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

		if ( function_exists( 'sys_getloadavg' ) )
		{
			$load = sys_getloadavg();
			$load = $load[0];
		}
		else
		{
			$load = NULL;
		}

		echo '<script id="go-profiler-data">' . $this->json_encode( (object) array(
			'hooks' => $this->hooks,
			'queries' => $wpdb->queries,
			'cache' => array(
				'hits' => (int) $wp_object_cache->cache_hits,
				'misses' => (int) $wp_object_cache->cache_misses,
			),
			'load' => $load,
			'apc' => $this->apc_data(),
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