<?php

namespace KamaGlanceDashboardWidget;

class Main {

	use Display;
	use Data;

	private $main_file_path;

	public function __construct( string $main_file ){
	    $this->main_file_path = $main_file;
	}

	public function init() {
		load_plugin_textdomain( 'kgdw', false, 'kama-glance-dash-widget/languages' );
		add_action( 'load-index.php', [ $this, 'dashboard_init' ] );
	}

	public function dashboard_init() {

		if( ! current_user_can( 'edit_posts' ) ){
			return;
		}

		add_action( 'admin_print_styles', [ $this, 'enqueue_style' ] );
		add_action( 'wp_dashboard_setup', [ $this, 'replace_widget' ] );
	}

	public function enqueue_style() {
		wp_enqueue_style( 'kama-glance-dash-widget', plugins_url( 'assets/styles.css', $this->main_file_path ) );
	}

	public function replace_widget() {
		global $wp_meta_boxes;

		$core = & $wp_meta_boxes['dashboard']['normal']['core'];

		unset( $core['dashboard_right_now'] ); // remove wp widget

		wp_add_dashboard_widget( 'kgdwidget', 'At a Glance', [ $this, 'display' ] );

		// place to top
		$save = $core['kgdwidget'];
		unset( $core['kgdwidget'] );
		$core = array_merge( [ 'kgdwidget' => $save ], $core );
	}

}
