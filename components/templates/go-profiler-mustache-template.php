<script id='go-profiler-summary-tpl' type='text/mustache'>
{{#summary}}
	<h2>
		<span>Total hooks</span>
		{{total}}
	</h2>
	<h2>
		<span>Total memory</span>
		{{popular_name}}
		<span>{{popular}}</span>
	</h2>
	<h2>
		<span>Total time</span>
		{{popular_name}}
		<span>{{popular}}</span>
	</h2>
	<h2>
		<span>Total queries</span>
		{{popular_name}}
		<span>{{popular}}</span>
	</h2>

	<h2>
		<span>Used most often</span>
		{{popular_name}}
		<span>{{popular}}</span>
	</h2>
	<h2>
		<span>Most memory intensive</span>
		{{max_mem_name}}
		<span>{{max_mem}} MB</span>
	</h2>
	<h2>
		<span>Longest running</span>
		{{longest_name}}
		<span>{{longest}} seconds</span>
	</h2>
	<h2>
		<span>Most queries</span>
		{{longest_name}}
		<span>{{longest}} seconds</span>
	</h2>
{{/summary}}
</script>

<script id='go-profiler-hook-tpl' type='text/mustache' >
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
		</tr>
	{{/aggregate}}
</script>
