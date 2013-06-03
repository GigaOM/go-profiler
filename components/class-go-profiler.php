<?php

class GO_Profiler
{

	public $hooks = array();
	private $_queries_at_last_call = 0;
	private $_query_running_time = 0;

	public function __construct()
	{
		add_action( 'all', array( $this, 'hook' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enq_scripts' ) );
		add_filter( 'debug_bar_panels', array( $this, 'add_profiler_panels' ) );
		register_shutdown_function( array( $this, 'shutdown' ) );
	}

	public function init()
	{
		wp_register_script( 'mustache', plugins_url( 'js/external/jquery.mustache.js', __FILE__ ), false, false, true );
    		wp_register_script( 'go-profiler', plugins_url( 'js/go-profiler.js', __FILE__ ), array( 'mustache' ), false, true );
	}

	public function enq_scripts()
	{
		wp_enqueue_script( 'mustache');
    wp_enqueue_script( 'go-profiler' );
	}

	public function add_profiler_panels($panels)
	{
		
		if ( ! class_exists( 'GO_Profiler_Hook_Panel' ) )
		{
			include 'class-go-profiler-hook-panel.php';
			$panels[] = new GO_Profiler_Hook_Panel();
		}
		
		if ( ! class_exists( 'GO_Profiler_Aggregate_Panel' ) )
		{
			include 'class-go-profiler-aggregate-panel.php';
			$panels[] = new GO_Profiler_Aggregate_Panel();
		}
		
		return $panels;
	}

	public function hook()
	{

		global $wpdb, $timestart;
		$timenow = microtime( TRUE );

		// capture the db query info
		$queries = array();
		if ( is_array( $wpdb->queries ) )
		{
			foreach( array_slice( $wpdb->queries, $this->_queries_at_last_call ) as $query )
			{
				$queries[] = 'yes';
				$this->_query_running_time += $query[1];
			}
		}else{
			//adds none as visible test of is_array($wpdb->queries)
			$queries[] = 'none';
		}

		// get a subset of the backtrace and format it into text
		$backtrace = array();
		foreach ( array_slice( debug_backtrace(), 4 , 2 ) as $temp )
		{
			//had to change these to test, as WP_DEBUG sets error_reporting to E_ALL - page fills with warnings for functions w/o files
			$backtrace_function = ( isset( $temp['function'] ) ) ? $temp['function'] : ' ';
			$backtrace_file = ( isset( $temp['file'] ) ) ? sprintf(' in %1$s()',$temp['file']) : ' ';
			$backtrace_line = ( isset( $temp['line'] ) ) ? sprintf(' at %1$s()',$temp['line']) : ' ';
			$backtrace[] = $backtrace_function.$backtrace_line.$backtrace_file;
		}

		// capture the remaining data
		$this->hooks[] = (object) array(
			'hook' => func_get_arg( 0 ), // the name of the current hook
			'memory' => memory_get_usage( FALSE ), // total script memory usage, in bytes
			'runtime' => $timenow - $timestart, // the total execution time, in seconds, at the start of the hook
			'query_runtime' => $this->_query_running_time,
			'query_count' => $wpdb->num_queries,
			'queries' => count( $queries ) ? $queries : NULL,
			'backtrace' => $backtrace,
		);

		$this->_queries_at_last_call = absint( $wpdb->num_queries );
	}

	public function shutdown()
	{
		$delta_m = $delta_t = $delta_q = $hook = $hook_m = $hook_t = array();
		foreach( $this->hooks as $k => $v )
		{
			$delta_m[ $k ] = $v->memory - $this->hooks[ absint( $k - 1 ) ]->memory;
			$delta_t[ $k ] = $v->runtime - $this->hooks[ absint( $k - 1 ) ]->runtime;
			$delta_q[ $k ] = $v->query_runtime - $this->hooks[ absint( $k - 1 ) ]->query_runtime;

			if( ! isset( $hook[ $v->hook ] ))
			{
				$hook[ $v->hook ] = 0;
			}
			$hook[ $v->hook ]++; 

			if( ! isset( $hook_m[ $v->hook ] ))
			{
				$hook_m[ $v->hook ] = 0;
			}
			$hook_m[ $v->hook ] += $delta_m[ $k ]; 

			if( ! isset( $hook_t[ $v->hook ] ))
			{
				$hook_t[ $v->hook ] = 0;
			}
			$hook_t[ $v->hook ] += $delta_t[ $k ]; 
		}

		foreach( $this->hooks as $k => $v )
		{
			$go_profile_hook_info[] = array(
				'hook' => $v->hook,
				'memory' => number_format( $v->memory / 1024 / 1024, 3 ),
        'delta-m' => number_format( $delta_m[ $k ] / 1024 / 1024, 3 ),
        'runtime' => number_format( $v->runtime, 4 ),
        'delta-r' => number_format( $delta_t[ $k ], 4 ),
        'q-runtime' => number_format( $v->query_runtime, 4 ),
        'delta-q' => number_format( $delta_q[ $k ], 4 ),
        'q-count' => $v->query_count,
        'queries' => $v->queries,
        'backtrace' => $v->backtrace,
			); 
		}
		$go_profile_total = $go_profile_max_mem = $go_profile_longest = $go_profile_popular = 0;
		foreach( $hook as $k => $v )
		{	
			$hook_mem = ( $hook_m[ $k ] / 1024 ) / 1024;
			$go_profile_agg_hook[] = array(
				'hook' => $k,
        'calls' => number_format( $v ),
        'memory' => number_format( $hook_mem, 3 ),
        'time' => number_format( $hook_t[ $k ], 4 ),
			);
			$go_profile_total += $v;
			$go_profile_max_mem = ( $hook_mem > $go_profile_max_mem ) ? $hook_mem : $go_profile_max_mem;
			$go_profile_longest = ( $hook_t[ $k ] > $go_profile_longest ) ? $hook_t[ $k ] : $go_profile_longest;
			$go_profile_popular = ( $v > $go_profile_popular ) ? $v : $go_profile_popular;
		}
		
		$go_profile_summary = array( 'total_hooks' => number_format( $go_profile_total ), 'max_mem' => number_format( $go_profile_max_mem, 3 ), 'longest_hook' => number_format( $go_profile_longest, 4 ), 'most_often' => number_format( $go_profile_popular ) );
	
		$go_profile_ret_json = "<script> var go_profiler_data = '" 
			. json_encode( array( 'summary' => $go_profile_summary, 'hooks' => $go_profile_hook_info, 'aggregate' => $go_profile_agg_hook ) ) 
			. "'; jQuery(document).trigger('go-profiler-data-loaded', [ go_profiler_data ] );"
			." </script>";
		
		echo $go_profile_ret_json;
	}
}

function go_profiler()
{
	global $go_profiler;

	if( ! $go_profiler )
	{
		$go_profiler = new GO_Profiler();
	}

	return $go_profiler;
}
