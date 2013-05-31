jQuery( function( $ ) {
var GO_Profiler_Debug = function GO_Profiler_Debug() {
  jQuery( document ).on( 'go-profiler-data-loaded', function( go_profiler_data ) {
		var hook = go_profiler_data.hook, aggregate = go_profiler_data.aggregate;
		jQuery("#debug_hook_table").html(hook);
		jQuery("#debug_hook_aggregate").html(aggregate);	
} );
}

	var go_profiler_filler = new GO_Profiler_Debug();
});

