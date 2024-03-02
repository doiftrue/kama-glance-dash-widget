<?php
/**
 * Plugin Name: Kama Glance Dashboard Widget
 * Description: A better version of the "At a Glance" dashboard widget. Shows all registered post type, comments, etc. Has no settings and works in admin only.
 *
 * Author: Kama
 * Author URI: http://wp-kama.com
 *
 * Text Domain: kgdw
 * Domain Path: languages
 * License: GPLv3
 *
 * Version: 1.3.2
 */

namespace KamaGlanceDashboardWidget;

add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

function init(){
	global $pagenow;

	$is_dashboard_page = ( 'index.php' === $pagenow && is_admin() );

	// This plugin works on admin dashboard page only
	if( ! $is_dashboard_page || defined( 'DOING_AJAX' ) || defined( 'WP_CLI' ) ){
		return;
	}

	require_once __DIR__ . '/src/Display.php';
	require_once __DIR__ . '/src/Data.php';
	require_once __DIR__ . '/src/Main.php';
	require_once __DIR__ . '/src/Section_Row.php';

	$main = new Main( __FILE__ );
	$main->init();
}



