<?php

/**
* adds a new panel for hook info
*/

class go_profiler_hook_panel extends Debug_Bar_Panel {
	
	public function init() {
		$this->title( __( 'Hook Call Transcript', 'debug-bar' ) );
	}
	/*
		I'm going to use some inline styles here...
		trying to make the table more legible. Since this is for dev only,
		I'm not sure if we should bother with a style sheet?
	*/
	public function render(){
		?>
			<table id='debug_hook_table' style='font-size:12px;'>
				<tr>
					<td colspan="3"> Filter: <input type='text' id='hook_search'/></td>
				</tr>
				<tr>
					<th rowspan="2">Hook</th>
					<th colspan="2" style='text-align:center'>Memory</th>
					<th colspan="2" style='text-align:center'>Run time</th>
					<th colspan="2" style='text-align:center'>DB query runtime</th>
					<th rowspan="2">Query count</th>
					<th rowspan="2" style='padding:6px'>Queries</th>
					<th rowspan="2">Backtrace</th>
				</tr>
				<tr>
					<th style='text-align:center'>(MB)</th>
					<th style='text-align:center'>&Delta;</th>
					<th style='text-align:center'>(sec)</th>
          <th style='text-align:center'>&Delta;</th>
					<th style='text-align:center'>(sec)</th>
          <th style='text-align:center'>&Delta;</th>
				</tr>
			</table>
		<?php
	}

}
