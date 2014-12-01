<?php
/**
* Adds a new panel for hook info.
*/
class GO_Profiler_Debug_Bar_Panel extends Debug_Bar_Panel
{
	/**
	* Initializes debug-bar tab for hook transcript.
	*/
	public function init( $epoch )
	{
		$this->title( 'Hooks at ' . esc_html( $epoch ) );
	}//end init

	/**
	* Renders base table for go-profiler.js to fill.
	*/
	public function render()
	{
		include_once __DIR__ . '/templates/go-profiler-debug-bar-panel.js';
		?>
		<table id='go-profiler-aggregate-table'>
			<tr>
				<td colspan="4">Filter: <input type='text' class='go-profiler-search'/></td>
			</tr>
			<tr>
				<th>Hook</th>
				<th>Calls</th>
				<th>Memory</th>
				<th>Time</th>
				<th>Query time</th>
			</tr>
		</table>

		<table id='go-profiler-transcript-table'>
			<tr>
				<td colspan="3"> Filter: <input type='text' class='go-profiler-search'/></td>
			</tr>
			<tr>
				<th rowspan="2">Hook</th>
				<th colspan="2">Memory</th>
				<th colspan="2">Run time</th>
				<th colspan="2">DB query runtime</th>
				<th            >Query count</th>
				<th            >Queries</th>
				<th            >Backtrace</th>
			</tr>
			<tr>
				<!-- Hook is rowspanned into here -->

				<!-- Memory -->
				<th>(MB)</th>
				<th>&Delta;</th>

				<!-- Run time -->
				<th>(sec)</th>
				<th>&Delta;</th>

				<!-- DB query runtime -->
				<th>(sec)</th>
				<th>&Delta;</th>

				<!-- Query count -->
				<th>&nbsp;</th>

				<!-- Queries -->
				<th>&nbsp;</th>

				<!-- Backtrace -->
				<th>&nbsp;</th>
			</tr>
		</table>
		<?php
	}//end render
}//end class