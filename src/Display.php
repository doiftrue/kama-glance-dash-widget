<?php

namespace KamaGlanceDashboardWidget;

trait Display {

	/**
	 * @see wp_dashboard_right_now()
	 */
	public function display() {

		update_right_now_message();

		$this->echo_search_engines_discourage();

		//if( current_user_can( 'view_site_health_checks' ) ){
		//	echo sprintf( '<p class="kgdwidget__site-health">'. __( 'Check:', '' ) . ' ' . '<a href="%s">%s</a></p>', admin_url('site-health.php') , __( 'Site Health', 'kgdw' ) );
		//}

		do_action( 'kama_glance_dash_widget__before_show_blocks' );
		?>

		<div id="kgdwidget__block kgdwidget__block-info"><?php $this->echo_section( 'info' ); ?></div>
		<div id="kgdwidget__block kgdwidget__block-content"><?php $this->echo_section( 'content' ); ?></div>
		<div id="kgdwidget__block kgdwidget__block-taxonomies"><?php $this->echo_section( 'taxonomies' ); ?></div>

		<div id="dashboard_right_now">
			<?php
			ob_start();
			// Preserve WP actions hooked
			/** This action is documented in wp-admin/includes/dashboard.php */
			do_action( 'rightnow_end' );
			/** This action is documented in wp-admin/includes/dashboard.php */
			do_action( 'activity_box_end' );
			$actions = ob_get_clean();

			if ( $actions ) {
				?>
				<div class="sub">
					<?php echo $actions // phpcs:ignore ?>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}

	/**
	 * @see wp_dashboard_right_now()
	 */
	private function echo_search_engines_discourage() {
		// Check if search engines are asked not to index this site.
		if(
			! is_network_admin() && ! is_user_admin()
			&& current_user_can( 'manage_options' )
			&& ! get_option( 'blog_public' )
		){
			/** This filter is documented in wp-admin/includes/dashboard.php */
			$title = apply_filters( 'privacy_on_link_title', '' );
			/** This filter is documented in wp-admin/includes/dashboard.php */
			$content = apply_filters( 'privacy_on_link_text', __( 'Search engine visibility is OFF', 'kgdw' ) );
			?>
			<p class="search-engines-info">
				<a href='options-reading.php' title="<?php echo esc_attr( $title ) ?>"><?php echo esc_html( $content ) ?></a>
			</p>
			<?php
		}
	}

	private function echo_section( string $section_type ) {

		list( $section_data, $section_title ) = $this->get_section_data( $section_type );

		$rows_output = [];
		foreach( $section_data as $row ){
			$rows_output = $this->row( $row, $rows_output );
		}

		if( isset( $rows_output[0] ) ){
			unset( $rows_output[0] );
		}

		ksort( $rows_output );
		$rows_output = array_reverse( $rows_output, true );

		?>
		<div class="kgdwidget__block-section">
			<h5><?php echo esc_html( $section_title ) ?></h5>
			<table>
				<?php
				foreach( $rows_output as $row ){
					echo $row; // phpcs:ignore
				}
				do_action( 'kama_glance_dash_widget__table_end', $rows_output, $section_type );
				?>
			</table>
		</div>
		<?php
	}

	private function row( Section_Row $row, array $rows_output ): array {

		if( ! current_user_can( $row->cap ) ){
			return [];
		}

		$extra_td = $row->extra ? $this->extra_td( $row->extra ) : '';
		$rows_index = $row->amount;
		if( $extra_td && ! $rows_index ){
			$rows_index = uniqid( '', true );
		}

		if( ! isset( $rows_output[ $row->amount ] ) ){
			$rows_output[ $row->amount ] = '';
		}

		// Usually we hide rows with 0 entries, but we want to show rows with drafts or pending even
		// if they have 0 published - sticking these cases in $rows_output[-1] solves this
		if( ! isset( $rows_output[ -1 ] ) ){
			$rows_output[ -1 ] = '';
		}

		$rows_output[ $rows_index ] .= strtr(
			'
			<tr class="kgdwidget__tr kgdwidget__tr-{class}">
				<td class="kgdw-td-number kgdw-number">{number}</td>
				<td class="kgdw-td-label">{anchor}</td>
				<td class="kgdw-td-extra">{extra}</td>
			</tr>
			',
			[
				'{class}'  => esc_attr( $row->class ),
				'{number}' => number_format_i18n( $row->amount ),
				'{anchor}' => sprintf( '<a href="%s">%s</a>', esc_attr( $row->link ), esc_html( $row->amount_text ) ),
				'{extra}'  => $extra_td,
			]
		);

		return $rows_output;
	}

	private function extra_td( array $extra ): string {
		$extra_links = [];

		foreach( $extra as $item ){

			if( ! $item->amount ){
				continue;
			}

			$link = strtr( '<span class="kgdw-number">{amount}</span> <a class="kgdw-td-extra-link {css_class}" href="{url}">{anchor}</a>', [
				'{css_class}' => "kgdw-td-extra-link--$item->class",
				'{url}'       => esc_attr( $item->link ),
				'{amount}'    => number_format_i18n( $item->amount ),
				'{anchor}'    => esc_html( $item->amount_text ),
			] );

			$extra_links[] = $link;
		}

		return implode( ' ', $extra_links );
	}

}
