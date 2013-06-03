( function( $ ) {
var GO_Profiler_Debug = function GO_Profiler_Debug() {
  $( document ).on( 'go-profiler-data-loaded', function( event, go_profiler_data ) {
		if( go_profiler_data ) {
			var profiler_data = $.parseJSON( go_profiler_data );
			//@TODO: move the templates to appropriate php files
			var go_profiler_summary_template = Mustache.compile("{{#summary}}<h2><span>TOTAL HOOKS</span>{{total_hooks}}</h2><h2><span>MOST MEMORY INTENSIVE</span>{{max_mem}} MB</h2><h2><span>LONGEST RUNNING</span>{{longest_hook}} seconds </h2><h2><span>USED MOST OFTEN</span>{{most_often}}</h2>{{/summary}}");
			var summary_row = go_profiler_summary_template(profiler_data);
			$( "#debug-menu-target-go-profiler-hook-panel" ).prepend( summary_row );
			$( "#debug-menu-target-go-profiler-aggregate-panel" ).prepend( summary_row );
	
			var $go_profiler_hook_rows = Mustache.render( $( '#go-profiler-hook-tpl' ).html(), profiler_data ); 
			$( "#debug-hook-table > tbody:last" ).append( $go_profiler_hook_rows );
			
			var $go_profiler_aggregate_rows = Mustache.render( $( '#go-profiler-aggregate-tpl' ).html(), profiler_data );
			$( "#debug-aggregate-table > tbody:last" ).append( $go_profiler_aggregate_rows );
		}
		
		// cache rows to reduce filter overhead
		var $hook_cached_rows = $( "#debug-hook-table" ).find( 'tr:gt(2)' );
		var $aggregate_cached_rows = $( "#debug-aggregate-table" ).find( 'tr:gt(2)' );
		$( ".go-profiler-search" ).keyup(function() {

				var $go_profiler_cached_rows = ( $( this ).closest( "table" ).attr( 'id' ) == 'debug-hook-table' ) ? $hook_cached_rows : $aggregate_cached_rows ;
        var term = $( this ).val().split( ' ' );
				if( "" != term )
				{
					$go_profiler_cached_rows.hide();
					$.each( term, function( i, v ) {
						$go_profiler_shown_rows = $go_profiler_cached_rows.filter( "*:contains('"+v+"')" );
          });
					$go_profiler_shown_rows.show();
        } else {
					$go_profiler_cached_rows.show();
        }
    });
		
	});
}
	$(document).ready(function(){
		var go_profiler_filler = new GO_Profiler_Debug();
	}
})( jQuery );

