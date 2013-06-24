<script id='go-profiler-summary-tpl' type='text/mustache'>
{{#summary}}
	<h2>
		<span>TOTAL HOOKS</span>
		{{total_hooks}}
	</h2>
	<h2>
		<span>MOST MEMORY INTENSIVE</span>
		{{max_mem_name}}
		<span>{{max_mem}} MB</span>
	</h2>
	<h2>
		<span>LONGEST RUNNING</span>
		{{longest_hook_name}}
		<span>{{longest_hook}} seconds</span>
	</h2>
	<h2>
		<span>USED MOST OFTEN</span>
		{{most_often_name}}
		<span>{{most_often}}</span>
	</h2>
{{/summary}}
</script>

<script id='go-profiler-hook-tpl' type='text/mustache' >
	{{#hooks}}
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
				{{queries}}
			</td>
			<td>
				{{#backtrace}} {{.}} <br/> {{/backtrace}}
			</td>
		</tr>
	{{/hooks}}
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
