( function( $ ) {
	var GO_Profiler_Debug = function GO_Profiler_Debug() {
		$( document ).on( 'go-profiler-data-loaded', function( event, go_profiler_data ) {

			for ( var k in go_profiler_data ){
				if ( ! go_profiler_data.hasOwnProperty( k ) ){
					continue;
				}

				var go_profiler_summary_row = Mustache.render( $( '#go-profiler-summary-tpl' ).html(), go_profiler_data[ k ] );
				$( '#go-profiler-debugbar-summary-tab-' + k ).append( go_profiler_summary_row );

//				var go_profiler_aggregate_rows = Mustache.render( $( '#go-profiler-aggregate-tpl' ).html(), go_profiler_data[ k ] );
//				$( '#go-profiler-debugbar-aggregate-table-' + k + ' > tbody:last' ).append( go_profiler_aggregate_rows );

//				var go_profiler_hook_rows = Mustache.render( $( '#go-profiler-transcript-tpl' ).html(), go_profiler_data[ k ] );
//				$( '#go-profiler-debugbar-transcript-table-' + k + ' > tbody:last' ).append( go_profiler_hook_rows );
			}
		});
	}

	// We'd like to use $(document).ready() here but if we do we miss the event trigger.
	var go_profiler_filler = new GO_Profiler_Debug();
})( jQuery );

