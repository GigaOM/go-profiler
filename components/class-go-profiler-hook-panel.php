<?php

/**
* adds a new panel for hook info
*/

class go_profiler_hook_panel extends Debug_Bar_Panel {
	
	public function init() {
		$this->title( __( 'Hook call transcript', 'debug-bar' ) );
	}

	public function render(){
		?>
			<h2>Hook call transcript</h2>
			<table id='debug_hook_table'>
				<tr>
					<td>Hook</td>
					<td>Memory (MB)</td>
					<td>&Delta;&nbsp;memory</td>
					<td>Run time (sec)</td>
					<td>&Delta; Run time</td>
					<td>DB query run time (sec)</td>
					<td>&Delta; DB query run time</td>
					<td>Query count</td>
					<td>Queries</td>
					<td>Backtrace</td>
				</tr>
			</table>
		<?php
	}

}
