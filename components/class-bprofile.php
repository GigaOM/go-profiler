<?php

class bProfile
{

	public $hooks = array();
	private $_queries_at_last_call = 0;
	private $_query_running_time = 0;

	public function __construct()
	{
		add_action( 'all', array( $this, 'hook' ) );
		register_shutdown_function( array( $this, 'shutdown' ) );
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
				$queries[] = $query[0];
				$this->_query_running_time += $query[1];
			}
		}

		// get a subset of the backtrace and format it into text
		$backtrace = array();
		foreach ( array_slice( debug_backtrace(), 4 , 2 ) as $temp )
		{
			$backtrace[] = sprintf( '%1$s() in %2$s#%3$s',
				$temp['function'],
				$temp['file'],
				$temp['line']
			);
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

		?>
		<h2>bProfile hook call transcript</h2>
		<table>
			<tr>
				<td>Hook</td>
				<td>Memory<br />(megabytes)</td>
				<td>Memory delta</td>
				<td>Running time<br />(seconds)</td>
				<td>Running time delta</td>
				<td>DB query running time<br />(seconds)</td>
				<td>DB query running time delta</td>
				<td>Query running count</td>
				<td>Queries</td>
				<td>Backtrace</td>
			</tr>
		<?php
		foreach( $this->hooks as $k => $v )
		{
			printf('<tr>
					<td>%1$s</td>
					<td>%2$s</td>
					<td>%3$s</td>
					<td>%4$s</td>
					<td>%5$s</td>
					<td>%6$s</td>
					<td>%7$s</td>
					<td>%8$s</td>
					<td>%9$s</td>
					<td>%10$s</td>
				</tr>',
				$v->hook,
				number_format( $v->memory / 1024 / 1024, 3 ),
				number_format( $delta_m[ $k ] / 1024 / 1024, 3 ),
				number_format( $v->runtime, 4 ),
				number_format( $delta_t[ $k ], 4 ),
				number_format( $v->query_runtime, 4 ),
				number_format( $delta_q[ $k ], 4 ),
				$v->query_count,
				print_r( $v->queries, TRUE ),
				print_r( $v->backtrace, TRUE )
			);
		}
		echo '</table>';

		?>
		<h2>bProfile aggregated hook usage</h2>
		<table>
			<tr>
				<td>Hook</td>
				<td>Calls</td>
				<td>Memory usage</td>
				<td>Time</td>
			</tr>
		<?php
		foreach( $hook as $k => $v )
		{
			printf('<tr>
					<td>%1$s</td>
					<td>%2$s</td>
					<td>%3$s</td>
					<td>%4$s</td>
				</tr>',
				$k,
				number_format( $v ),
				number_format( $hook_m[ $k ] / 1024 / 1024, 3 ),
				number_format( $hook_t[ $k ], 4 )
			);
		}
		echo '</table>';

	}
}