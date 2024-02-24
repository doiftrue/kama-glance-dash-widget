<?php
/**
 * Plugin Name: Kama Glance Dashboard Widget
 * Description: A better version of the "At a Glance" dashboard widget. Shows all registered post type, comments, etc.
 * Version: 1.1
 * Author: Kama
 * Author URI: http://wp-kama.com
 * License: GPLv3
 */

namespace KamaGlanceDashboardWidget;

if( defined( 'DOING_AJAX' ) || ! is_admin() ){
	return;
}

require_once __DIR__ . '/src/Display.php';
require_once __DIR__ . '/src/Data.php';
require_once __DIR__ . '/src/Main.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

function init(){
	$main = new Main( __FILE__ );
	$main->init();
}



