# GO Profiler #
Contributors: misterbisson, camwyn, vaci101
Tags: tags, custom taxonomies, term authority, tag management, 
Requires: WP Debug Bar
Tested up to: 3.9
Stable tag: trunk

Hooking up the Debug bar!

## Description ##

GO Profiler Profiles the exection flow within WordPress. It adds four new panels to the WordPress Debug Bar:

1. Queries
	Shows total queries, total query time, and each query individually and it's source.
2. WP Queries
	Summary shows queried object id, query type, query template, show on front, post type.
	Also shows query arguments, actual SQL, and the entire Query object.
3. Hook Call Transcript
	Summary shows total hooks, most memory intensive, longest running, and most used.
	Also lists each hook with info: cumulative memory, cumulative run time, query runtime, query count, queries and backtrace.
4. Aggregate Hook Usage
	Summary shows total hooks, most memory intensive, longest running, and most used.
	Also shows aggregated hook info by hook: calls, memory usage, and time.

Note that on large sites with complex themes and/or a ton of plugins, the hook panels can be very slow...


###### Fork me! ######

This plugin is on [Github](https://github.com/GigaOM/go-profiler): https://github.com/GigaOM/go-profiler