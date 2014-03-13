<?php
/**
* Adds a new panel for aggregate hook info.
*/
class GO_Profiler_Aggregate_Panel extends Debug_Bar_Panel
{
	/**
	 * Initializes debug-bar tab for aggregate hook usage
	 */
	public function init()
	{
		$this->title( __( 'Aggregated Hook Usage', 'debug-bar' ) );
	}//end init

	/**
	 *	Renders base table for go-profiler.js to fill
	 */
	public function render()
	{
		include_once __DIR__ . '/templates/go-profiler-mustache-template.php';
		?>
		<table id='debug-aggregate-table'>
			<tr>
				<td colspan="3"> Filter: <input type='text' class='go-profiler-search'/></td>
			</tr>
			<tr>
				<th>hook</th>
				<th>calls</th>
				<th>memory usage</th>
				<th>time</th>
			</tr>
		</table>
		<?php
	}//end render
}//end class