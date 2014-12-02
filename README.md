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

### Requires ###

Set `define( 'WP_DEBUG', TRUE );` somewhere, perhaps in your `wp-config.php`. It also works better if you set `define( 'SAVEQUERIES', TRUE );` as well. The plugin tries setting it, but if you've got it set `FALSE` somewhere, that will fail.

Profile information is shown inside the [Debug Bar](https://wordpress.org/plugins/debug-bar/). This plugin runs without that one, but it just increases memory consumption and reduces performance without showing anything.

### Fork me! ###

This plugin is on [Github](https://github.com/GigaOM/go-profiler): https://github.com/GigaOM/go-profiler