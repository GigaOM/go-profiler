<?php
/*
Plugin Name: Gigaom Execution Profiler
Plugin URI: http://kitchen.gigaom.com/
Description: Profiles the exection flow within WordPress
Version: a
Original Author: Casey Bisson
Author URI: http://maisonbisson.com/blog/
Contributors: Stephen Page, Stephen Glauser
*/

// only loads if WP_DEBUG is defined and TRUE
if ( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	//need this set to gather query data
	if ( ! defined( 'SAVEQUERIES' ) )
	{
		define( 'SAVEQUERIES', TRUE );
	}//end if

	require __DIR__ . '/components/class-go-profiler.php';
	go_profiler();
}
