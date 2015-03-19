<?php
/*
Name: Gigaom Execution Profiler
Plugin URI: http://kitchen.gigaom.com/
Description: Profiles the exection flow within WordPress
Version: 1.0
Author: Gigaom
Author URI: http://gigaom.com
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
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
