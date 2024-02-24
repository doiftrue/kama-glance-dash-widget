<?php

namespace KamaGlanceDashboardWidget;

trait Data {

	private function get_content_data(): array {

		// posts
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		foreach( $post_types as $ptype ){
			$pcounts = wp_count_posts( $ptype->name );

			$amount = ( $ptype->name === 'attachment' ) ? $pcounts->inherit ?? 0 : $pcounts->publish ?? 0;

			$data["post_type:$ptype->name"] = [
				'class'       => 'post',
				'cap'         => $ptype->cap->edit_posts,
				'link'        => ( $ptype->name === 'attachment' ) ? 'upload.php' : 'edit.php?post_type=' . $ptype->name,
				'amount'      => $amount,
				'amount_text' => ( $amount === 1 ) ? $ptype->labels->singular_name : $ptype->label,
				/** @see Display::extra_td() */
				'extra'       => [
					[
						'link'        => "edit.php?post_type=$ptype->name&post_status=draft",
						'amount'      => ( $pcounts->draft ?? 0 ),
						'amount_text' => _n( 'draft', 'drafts', ( $pcounts->draft ?? 0 ), 'kgdw' ),
						'class'       => 'draft',
					],
					[
						'link'        => "edit.php?post_type=$ptype->name&post_status=pending",
						'amount'      => ( $pcounts->pending ?? 0 ),
						'amount_text' => _n( 'pending', 'pending', ( $pcounts->pending ?? 0 ), 'kgdw' ),
						'class'       => 'pending',
					],
				],
			];
		}

		// comments
		$ccounts = wp_count_comments();
		$data['comments'] = [
			'class'       => 'comment',
			'cap'         => 'moderate_comments',
			'link'        => 'edit-comments.php',
			'amount'      => ( $ccounts->approved ?? 0 ),
			'amount_text' => _n( 'Comment', 'Comments', ( $ccounts->approved ?? 0 ), 'kgdw' ),
			/** @see Display::extra_td() */
			'extra'       => [
				[
					'link'        => "edit-comments.php?comment_status=moderated",
					'amount'      => ( $ccounts->moderated ?? 0 ),
					'amount_text' => _n( 'pending', 'pending', ( $ccounts->moderated ?? 0 ), 'kgdw' ),
					'class'       => 'pending',
				],
			],
		];

		/**
		 * Allows to modify the data for "Content" widget block.
		 */
		return apply_filters( 'kama_glance_dash_widget__content_data', $data );
	}

	private function get_taxonomies_data(): array {

		$data = [
			[
				'cap'         => 'manage_links',
				'class'       => 'links_cat',
				'link'        => 'edit-tags.php?taxonomy=link_category',
				'amount'      => $amount = (int) wp_count_terms( 'link_category' ),
				'amount_text' => _n( 'Link Category', 'Link Categories', $amount, 'kgdw' ),
			],
			[
				'cap'         => 'manage_links',
				'class'       => 'links',
				'link'        => 'link-manager.php',
				'amount'      => $amount = count( get_bookmarks() ),
				'amount_text' => _n( 'Link', 'Links', $amount, 'kgdw' ),
			],
		];

		$taxonomies = get_taxonomies( [ 'show_ui' => true ], 'objects' );

		foreach( $taxonomies as $tax ){
			$data["tax:$tax->name"] = [
				'cap'         => $tax->cap->manage_terms,
				'class'       => $tax->name,
				'link'        => 'edit-tags.php?taxonomy=' . $tax->name,
				'amount'      => $amount = (int) wp_count_terms( $tax->name ),
				'amount_text' => $amount === 1 ? $tax->labels->singular_name : $tax->label,
			];
		}

		/**
		 * Allows to modify the data for "Taxonomies" widget block.
		 */
		return apply_filters( 'kama_glance_dash_widget__taxonomies_data', $data );
	}

	private function get_info_data(): array {

		list( $active_plugin_amount, $inactive_plugin_amount ) = $this->count_plugins();

		$data = [
			'plugins' => [
				'class'       => 'plugins',
				'cap'         => 'activate_plugins',
				'link'        => 'plugins.php',
				'amount'      => $active_plugin_amount,
				'amount_text' => _n( 'Plugin', 'Plugins', $active_plugin_amount, 'kgdw' ),
				/** @see Display::extra_td() */
				'extra'       => [
					[
						'link'        => 'plugins.php?plugin_status=inactive',
						'class'       => 'inactive',
						'amount'      => $inactive_plugin_amount,
						'amount_text' => _n( 'inactive', 'inactive', $inactive_plugin_amount, 'kgdw' ),
					],
				],
			],
			'users'   => [
				'class'       => 'users',
				'cap'         => 'list_users',
				'link'        => 'users.php',
				'amount'      => $amount = count_users()['total_users'],
				'amount_text' => _n( 'User', 'Users', $amount, 'kgdw' ),
			],
			'widgets' => [
				'class'       => 'widgets',
				'cap'         => 'edit_theme_options',
				'link'        => 'widgets.php',
				'amount'      => $amount = $this->count_active_widgets(),
				'amount_text' => _n( 'Widget', 'Widgets', $amount, 'kgdw' ),
			],
			'menus'   => [
				'class'       => 'menus',
				'cap'         => 'edit_theme_options',
				'link'        => 'nav-menus.php',
				'amount'      => $amount = $this->count_active_menus(),
				'amount_text' => _n( 'Menu', 'Menus', $amount, 'kgdw' ),
			],
		];

		/**
		 * Allows to modify the data for "Info" widget block.
		 */
		return apply_filters( 'kama_glance_dash_widget__info_data', $data );
	}

	private function count_active_widgets(): int {
		global $wp_registered_sidebars;
		if( ! empty( $wp_registered_sidebars ) ){
			$sidebars_widgets = wp_get_sidebars_widgets();
			$widget_amount = 0;
			foreach( $sidebars_widgets as $key => $value ){
				if( 'wp_inactive_widgets' === $key ){
					continue;
				}
				if( is_array( $value ) ){
					$widget_amount += count( $value );
				}
			}
		}

		return $widget_amount;
	}

	private function count_active_menus(): int {
		$locations = get_registered_nav_menus();
		$active_menu_amount = 0;
		foreach( $locations as $slug => $description ){
			if( has_nav_menu( $slug ) ){
				$active_menu_amount++;
			}
		}

		return $active_menu_amount;
	}

	private function count_plugins(): array {
		/** This filter is documented in wp-admin/includes/class-wp-plugins-list-table.php */
		$all_plugins = apply_filters( 'all_plugins', get_plugins() );

		$active_plugin_amount = 0;
		$inactive_plugin_amount = 0;

		foreach( $all_plugins as $plugin_file => $plugin_data ){
			if( is_plugin_active( $plugin_file ) ){
				$active_plugin_amount++;
			}
			else {
				$inactive_plugin_amount++;
			}
		}

		return [ $active_plugin_amount, $inactive_plugin_amount ];
	}

}
