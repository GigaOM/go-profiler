<?php
// wp --url=bgeo.me --require=./test.php go-profiler test
class GO_Profiler_Wpcli extends WP_CLI_Command
{

	public function test( $args, $assoc_args )
	{
		if ( empty( $args ) )
		{
			WP_CLI::error( 'Please specify a URL to test.' );
			return;
		}

		if ( ! is_array( $assoc_args ) )
		{
			$assoc_args = array();
		}

		$assoc_args['url'] = $args[0];
		$args = (object) array_intersect_key( $assoc_args, array(
			'url' => TRUE,
			'count' => TRUE,
		) );

		if ( ! isset( $args->count ) )
		{
			$args->count = 17;
		}

		$runs = array();
		for ( $i = 1; $i <= $args->count; $i++ )
		{
			$test_url = add_query_arg( array( go_profiler()->config( 'secret' ) => rand() ), $args->url );
			WP_CLI::line( $test_url );
			$start_time = microtime( TRUE );
			$fetch_raw = wp_remote_get( $test_url, array(
				'timeout'    => 90,
				'user-agent' => 'Microsoft Internet Explorer or something',
			) );

			// time the request
			$runs[ $i ]->response_time = microtime( TRUE ) - $start_time;

			// get the response code
			$runs[ $i ]->response_code = wp_remote_retrieve_response_code( $fetch_raw );

			// headers
			// - last-modified
			// - cache-control
			// - set-cookie

			// system load (unix-only)
			// DB queries
	
			$fetch_body = wp_remote_retrieve_body( $fetch_raw );
			preg_match( '|<script id="go-profiler-data">(.*);</script>|is', $fetch_body, $matches );

			if ( empty( $matches[1] ) )
			{
				sleep( 2 );
				continue;
			}

			$transcript = json_decode( $matches[1] );

			if ( ! is_object( $transcript ) )
			{
				sleep( 2 );
				continue;
			}

			$hook_metrics = go_profiler()->metrics()->hook_metrics( $transcript->hooks );
			$query_metrics = go_profiler()->metrics()->query_metrics( $transcript->queries );

// print_r( $metrics->summary );
// print_r( $transcript->cache );

// max, min, mode
// time, queries, query time, cache keys
// startup, init, template_redirect

			$runs[ $i ]->system_load    = $transcript->load;
			$runs[ $i ]->query_count    = $query_metrics->query_count;
			$runs[ $i ]->query_time     = $query_metrics->query_time;
			$runs[ $i ]->hook_count     = $hook_metrics->summary->total_hooks;
			$runs[ $i ]->hook_memory    = $hook_metrics->summary->total_memory;
			$runs[ $i ]->hook_time      = $hook_metrics->summary->total_time;
		}

		print_r( $runs );
	}
}
WP_CLI::add_command( 'go-profiler', 'GO_Profiler_WPCLI' );