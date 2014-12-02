<?php

class GO_Profiler
{
	public $hooks = array();
	public $wpcli = NULL;
	public $is_wpcli = FALSE;
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

		if ( defined( 'WP_CLI' ) && WP_CLI )
		{
			$this->is_wpcli = TRUE;
			$this->wpcli();
		}

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
		if ( $this->is_wpcli )
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