<?php

/**
* Adds a new panel for hook info.
*/

class GO_profiler_hook_panel extends Debug_Bar_Panel 
{

	/**
	*	Initializes debug-bar tab for hook transcript.
	*/

	public function init() 
	{
		$this->title( __( 'Hook Call Transcript', 'debug-bar' ) );
	}

	/**
	*	Renders base table for go-profiler.js to fill. 	
	*/	
	
	public function render()
	{
		include_once 'templates/go-profiler-mustache-template.php';
		?>
			<table id='debug-hook-table' style='font-size:12px;'>
				<tr>
					<td colspan="3"> Filter: <input type='text' class='go-profiler-search'/></td>
				</tr>
				<tr>
					<th rowspan="2">Hook</th>
					<th colspan="2">Memory</th>
					<th colspan="2">Run time</th>
					<th colspan="2">DB query runtime</th>
					<th rowspan="2">Query count</th>
					<th rowspan="2">Queries</th>
					<th rowspan="2">Backtrace</th>
				</tr>
				<tr>
					<th>(MB)</th>
					<th>&Delta;</th>
					<th>(sec)</th>
          <th>&Delta;</th>
					<th>(sec)</th>
          <th>&Delta;</th>
				</tr>
			</table>
		<?php
	}

}
