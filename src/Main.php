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
		load_plugin_textdomain( 'kgdw', false, basename( dirname( $this->main_file_path ) ) . '/languages' );
		add_action( 'load-index.php', [ $this, 'dashboard_init' ] );
	}

	public function dashboard_init() {

		// regular site dashboard
		if( current_user_can( 'edit_posts' ) ){
			add_action( 'admin_print_styles', [ $this, 'enqueue_style' ] );
			add_action( 'wp_dashboard_setup', [ $this, 'replace_widget' ] );
		}

		// multisite site dashboard
		if( is_multisite() ){
			// TODO: replace network widget: on hook `wp_network_dashboard_setup`
		}
	}

	public function enqueue_style() {
		wp_enqueue_style( 'kama-glance-dash-widget', plugins_url( 'assets/styles.css', $this->main_file_path ), [], '1.3.2' );
	}

	public function replace_widget() {
		global $wp_meta_boxes;

		$wp_widget_key = 'dashboard_right_now';

		$core = &$wp_meta_boxes['dashboard']['normal']['core'];
		$wp_widget = $core[ $wp_widget_key ];

		// place wp widget
		unset( $core[ $wp_widget_key ] ); // remove

		wp_add_dashboard_widget( $wp_widget_key, $wp_widget['title'], [ $this, 'display' ] );

		$save = $core[ $wp_widget_key ];
		unset( $core[ $wp_widget_key ] );
		$core = array_merge( [ $wp_widget_key => $save ], $core );
	}

}
