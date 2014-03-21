# GO Profiler #
Contributors: misterbisson, camwyn, vaci101  
Tags: performance, execution flow, hooks, queries  
Requires: WP Debug Bar  
Tested up to: 3.9  
Requires at least: 3.1

"Hook"ing up the Debug bar!

## Description ##

GO Profiler Profiles the exection flow within WordPress. It adds two new panels to the WordPress Debug Bar:

1. Hook Call Transcript
    * Summary shows total hooks, most memory intensive, longest running, and most used.
    * Also lists each hook with info: cumulative memory, cumulative run time, query runtime, query count, queries and backtrace.
2. Aggregate Hook Usage
    * Summary shows total hooks, most memory intensive, longest running, and most used.
    * Also shows aggregated hook info by hook: calls, memory usage, and time.

Note that on large sites with complex themes and/or a ton of plugins, the hook panels can be very slow...


### Fork me! ###

This plugin is on [Github](https://github.com/GigaOM/go-profiler): https://github.com/GigaOM/go-profiler