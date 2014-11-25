<script id='go-profiler-summary-tpl' type='text/mustache'>
{{#summary}}
	<h2>
		<span>Total hooks</span>
		{{total_hooks}}
	</h2>
	<h2>
		<span>Total memory</span>
		<span>{{total_memory}}</span>
	</h2>
	<h2>
		<span>Total time</span>
		<span>{{total_time}}</span>
	</h2>
	<h2>
		<span>Total query time</span>
		<span>{{total_querytime}}</span>
	</h2>

	<h2>
		<span>Used most often</span>
		{{most_popular_name}}
		<span>{{most_popular}}</span>
	</h2>
	<h2>
		<span>Most memory intensive</span>
		{{most_memory_name}}
		<span>{{most_memory}} MB</span>
	</h2>
	<h2>
		<span>Longest running</span>
		{{most_time_name}}
		<span>{{most_time}} seconds</span>
	</h2>
	<h2>
		<span>Longest queries</span>
		{{lmost_querytime_name}}
		<span>{{most_querytime}} seconds</span>
	</h2>
{{/summary}}
</script>

<script id='go-profiler-transcript-tpl' type='text/mustache' >
	{{#transcript}}
		<tr>
			<td>
				{{hook}}
			</td>
			<td>
				{{memory}}
			</td>
			<td>
				{{memory_delta}}
			</td>
			<td>
				{{runtime}}
			</td>
			<td>
				{{runtime_delta}}
			</td>
			<td>
				{{query_runtime}}
			</td>
			<td>
				{{query_delta}}
			</td>
			<td>
				{{query_count}}
			</td>
			<td>
				{{#queries}}
				{{.}}
				{{/queries}}
				</ul>
			</td>
			<td>
				{{#backtrace}} {{.}} <br/> {{/backtrace}}
			</td>
		</tr>
	{{/transcript}}
</script>

<script id='go-profiler-aggregate-tpl' type='text/mustache' >
	{{#aggregate}}
		<tr>
			<td>
				{{hook}}
			</td>
			<td>
				{{calls}}
			</td>
			<td>
				{{memory}}
			</td>
			<td>
				{{time}}
			</td>
			<td>
				{{querytime}}
			</td>
		</tr>
	{{/aggregate}}
</script>
