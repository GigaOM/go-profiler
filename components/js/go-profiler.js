( function( $ ) {
var GO_Profiler_Debug = function GO_Profiler_Debug() {
  $( document ).on( 'go-profiler-data-loaded', function( event, go_profiler_data ) {
		if( go_profiler_data ){

			var profiler_data = jQuery.parseJSON( go_profiler_data );
			
			var go_profiler_summary_template = Mustache.compile("{{#summary}}<h2><span>TOTAL HOOKS</span>{{total_hooks}}</h2><h2><span>MOST MEMORY INTENSIVE</span>{{max_mem}} MB</h2><h2><span>LONGEST RUNNING</span>{{longest_hook}} seconds </h2><h2><span>USED MOST OFTEN</span>{{most_often}}</h2>{{/summary}}");
			var summary_row = go_profiler_summary_template(profiler_data);
			$( "#debug-menu-target-go_profiler_hook_panel" ).prepend( summary_row );
			$( "#debug-menu-target-go_profiler_aggregate_panel" ).prepend( summary_row );
	
			var hook_template = Mustache.compile( " {{#hooks}} <tr> <td> {{hook}} </td> <td> {{memory}} </td> <td> {{delta-m}} </td> <td> {{runtime}} </td> <td> {{delta-r}} </td> <td> {{q-runtime}} </td> <td> {{delta-q}} </td> <td> {{q-count}} </td> <td> {{queries}} </td> <td style='white-space:nowrap'> {{#backtrace}} {{.}} <br/> {{/backtrace}}</td </tr> {{/hooks}}" );
			var hook_rows = hook_template(  profiler_data );
			$( "#debug_hook_table > tbody:last" ).append( hook_rows );
			
			var aggregate_template = Mustache.compile( " {{#aggregate}} <tr> <td>{{hook}} </td> <td style='white-space:nowrap'> {{calls}} </td> <td style='white-space:nowrap'> {{memory}} </td> <td style='white-space:nowrap'> {{time}} </td> </tr> {{/aggregate}} " );
			var aggregate_rows = aggregate_template( profiler_data );
			$( "#debug_aggregate_table > tbody:last" ).append( aggregate_rows );
		}
		
		// cache rows to reduce filter overhead
		var hook_cached_rows = $( "#debug_hook_table" ).find( 'tr:gt(2)' );
		var aggregate_cached_rows = $( "#debug_aggregate_table" ).find( 'tr:gt(2)' );
		$( "#hook_search" ).keyup(function(){
        var term = $( this ).val().split( ' ' );
				if( term != "" )
				{
					hook_cached_rows.hide();
					$.each( term, function( i, v ){
              shown_rows = hook_cached_rows.filter("*:contains('"+v+"')");
          });
					shown_rows.show();
        } else {
					hook_cached_rows.show();
        }
    });
		
		$( "#aggregate_search" ).keyup( function(){
			var term = $( this ).val().split( ' ' );
        if( term != "" )
        {
          aggregate_cached_rows.hide();
          $.each( term, function( i, v ){
              shown_rows = aggregate_cached_rows.filter( "*:contains('"+v+"')" );
          });
          shown_rows.show();
        } else {
          aggregate_cached_rows.show();
        }
		});

	});
}
	var go_profiler_filler = new GO_Profiler_Debug();
})( jQuery );

