<?php

/**
* adds a new panel for aggregate hook info
*/

class go_profiler_aggregate_panel extends Debug_Bar_Panel {
	
	public function init() {
		$this->title( __( 'Aggregated Hook Usage', 'debug-bar' ) );
	}

	public function render(){
		?>
			<table id='debug_aggregate_table' style='font-size:12px'>
				<tr>
          <td colspan="3"> Filter: <input type='text' id='aggregate_search'/></td>
        </tr>
				<tr>
					<th>Hook</th>
					<th>Calls</th>
					<th>Memory usage</th>
					<th>Time</th>
				</tr>
			</table>
		<?php
	}

}
