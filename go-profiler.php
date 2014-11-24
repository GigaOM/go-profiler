<?php
/*
Plugin Name: Gigaom Execution Profiler
Plugin URI: http://kitchen.gigaom.com/
Description: Profiles the exection flow within WordPress
Version: 1.0
Author: Casey Bisson
Author URI: http://maisonbisson.com/
*/

// only load if WP_DEBUG is defined and TRUE
// add a define( 'WP_DEBUG', TRUE ); to your wp-config.php or similar
if (
	! defined( 'WP_DEBUG' ) ||
	! WP_DEBUG
)
{
	return;
}

//need this set to gather query data
if ( ! defined( 'SAVEQUERIES' ) )
{
	define( 'SAVEQUERIES', TRUE );
}//end if

require __DIR__ . '/components/class-go-profiler.php';
go_profiler();