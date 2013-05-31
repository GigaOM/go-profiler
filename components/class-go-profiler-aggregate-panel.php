<?php

/**
* adds a new panel for aggregate hook info
*/

class go_profiler_aggregate_panel extends Debug_Bar_Panel {
	
	public function init() {
		$this->title( __( 'Aggregated hook usage', 'debug-bar' ) );
	}

	public function render(){
		?>
			<h2>Aggregated hook usage</h2>
			<table id='debug_aggregate_table'>
				<tr>
					<td>Hook</td>
					<td>Calls</td>
					<td>Memory usage</td>
					<td>Time</td>
				</tr>
			</table>
		<?php
	}

}
