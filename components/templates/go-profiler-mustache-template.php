<script id='go-profiler-summary-tpl' type='text/mustache'>
{{#summary}}
	<h2>
		<span>Total hooks</span>
		{{total_hooks}}
	</h2>
	<h2>
		<span>Most memory intensive</span>
		{{max_mem_name}}
		<span>{{max_mem}} MB</span>
	</h2>
	<h2>
		<span>Longest running</span>
		{{longest_hook_name}}
		<span>{{longest_hook}} seconds</span>
	</h2>
	<h2>
		<span>Used most often</span>
		{{most_often_name}}
		<span>{{most_often}}</span>
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
				{{delta-m}}
			</td>
			<td>
				{{runtime}}
			</td>
			<td>
				{{delta-r}}
			</td>
			<td>
				{{q-runtime}}
			</td>
			<td>
				{{delta-q}}
			</td>
			<td>
				{{q-count}}
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
