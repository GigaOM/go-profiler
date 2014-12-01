<?php
/**
* Adds a new panel for hook info.
*/
class GO_Profiler_Debug_Bar_Panel extends Debug_Bar_Panel
{
	/**
	* Initializes debug-bar panel.
	*/
	public function init()
	{
		$this->title( 'Gigaom Profiler: WordPress Hooks' );
	}//end init

	/**
	* Renders the panel in the debug bar overlay
	*/
	public function render()
	{
		require_once __DIR__ . '/templates/go-profiler-debugbarpanel-template.html';

		?>
		<h1>Gigaom Profiler: WordPress Hooks</h1>
		<div id="go-profiler-panel">
			<ul>
			<?php
			foreach ( go_profiler()->epochs as $epoch )
			{
				?>
				<li><a href="#go-profiler-debugbar-tab-<?php echo esc_attr( $epoch ); ?>"><?php echo esc_html( $epoch ); ?> &rarr;</a></li>
				<?php
			}
			?>
			</ul>
			<?php

			foreach ( go_profiler()->epochs as $epoch )
			{
				?>
				<div id="go-profiler-debugbar-tab-<?php echo esc_attr( $epoch ); ?>">
					<h2><?php echo esc_js( $epoch ); ?> hooks</h2>
					<ul>
						<li><a href="#go-profiler-debugbar-summary-tab-<?php echo esc_attr( $epoch ); ?>">Summary</a></li>
						<li><a href="#go-profiler-debugbar-aggregate-tab-<?php echo esc_attr( $epoch ); ?>">Aggregate</a></li>
						<li><a href="#go-profiler-debugbar-transcript-tab-<?php echo esc_attr( $epoch ); ?>">Transcript</a></li>
					</ul>
					<?php
					$this->summary_table( $epoch );
					$this->aggregate_table( $epoch );
					$this->transcript_table( $epoch );
					?>

					<script>
						jQuery(function() {
							jQuery( "go-profiler-debugbar-tab-<?php echo esc_attr( $epoch ); ?>" ).tabs();
						});
					</script>
				</div>
				<?php
			}
			?>
		</div>

		<script>
			jQuery(function() {
				jQuery( "go-profiler-panel" ).tabs();
			});
		</script>
		<?php
	}//end render

	/**
	* Renders summary table for go-profiler.js to fill.
	*/
	public function summary_table( $epoch )
	{
		?>
		<div id="go-profiler-debugbar-summary-tab-<?php echo esc_attr( $epoch ); ?>">
			&nbsp;
		</div>
		<?php
	}//end summary_table

	/**
	* Renders aggregate table for go-profiler.js to fill.
	*/
	public function aggregate_table( $epoch )
	{
		?>
		<div id="go-profiler-debugbar-aggregate-tab-<?php echo esc_attr( $epoch ); ?>">
			<table id="go-profiler-debugbar-aggregate-table-<?php echo esc_attr( $epoch ); ?>">
				<tr>
					<th>Hook</th>
					<th>Calls</th>
					<th>Memory</th>
					<th>Time</th>
					<th>Query time</th>
				</tr>
			</table>
		</div>
		<?php
	}//end aggregate_table

	/**
	* Renders transcript table for go-profiler.js to fill.
	*/
	public function transcript_table( $epoch )
	{
		?>
		<div id="go-profiler-debugbar-transcript-tab-<?php echo esc_attr( $epoch ); ?>">
			<table id="go-profiler-debugbar-transcript-table-<?php echo esc_attr( $epoch ); ?>">
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
		</div>
		<?php
	}//end transcript_table
}//end class