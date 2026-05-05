<?php
/**
 * WooCommerce attribute seed.
 *
 * Reads every JSON file in src/core/config/attributes/ and creates the
 * corresponding global attribute + terms if they don't exist yet.
 *
 * Bump ETHEME_ATTR_SEED_VERSION when the JSON config changes so the seed
 * re-runs automatically on existing installations.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ETHEME_ATTR_SEED_VERSION', '4' );

function etheme_seed_wc_attributes() {
	if ( ! function_exists( 'wc_create_attribute' ) ) {
		return;
	}

	if ( get_transient( 'etheme_wc_attributes_seeded' ) === ETHEME_ATTR_SEED_VERSION ) {
		return;
	}

	$config_dir = get_template_directory() . '/src/core/config/attributes/';
	$files      = glob( $config_dir . '*.json' );

	if ( empty( $files ) ) {
		return;
	}

	foreach ( $files as $file ) {
		$json = file_get_contents( $file );
		$def  = json_decode( $json, true );

		if ( ! is_array( $def ) || empty( $def['slug'] ) || empty( $def['name'] ) ) {
			continue;
		}

		// Create the attribute if it doesn't exist.
		$attribute_id = wc_attribute_taxonomy_id_by_name( $def['slug'] );
		if ( ! $attribute_id ) {
			$attribute_id = wc_create_attribute( array(
				'name'         => $def['name'],
				'slug'         => $def['slug'],
				'type'         => isset( $def['type'] ) ? $def['type'] : 'select',
				'order_by'     => isset( $def['order_by'] ) ? $def['order_by'] : 'menu_order',
				'has_archives' => ! empty( $def['has_archives'] ),
			) );

			if ( is_wp_error( $attribute_id ) ) {
				continue;
			}

			delete_transient( 'wc_attribute_taxonomies' );
			WC_Cache_Helper::invalidate_cache_group( 'woocommerce-attributes' );
		}

		// Register the taxonomy so wp_insert_term works in the same request.
		$taxonomy = wc_attribute_taxonomy_name( $def['slug'] );
		if ( ! taxonomy_exists( $taxonomy ) ) {
			register_taxonomy( $taxonomy, 'product' );
		}

		etheme_seed_attribute_terms( $taxonomy, $def['terms'] ?? array() );

		// Aliases share the same terms under a different attribute name/slug.
		if ( ! empty( $def['aliases'] ) && is_array( $def['aliases'] ) ) {
			foreach ( $def['aliases'] as $alias ) {
				if ( empty( $alias['slug'] ) || empty( $alias['name'] ) ) {
					continue;
				}
				$alias_id = wc_attribute_taxonomy_id_by_name( $alias['slug'] );
				if ( ! $alias_id ) {
					$alias_id = wc_create_attribute( array(
						'name'         => $alias['name'],
						'slug'         => $alias['slug'],
						'type'         => isset( $def['type'] ) ? $def['type'] : 'select',
						'order_by'     => isset( $def['order_by'] ) ? $def['order_by'] : 'menu_order',
						'has_archives' => false,
					) );
					if ( is_wp_error( $alias_id ) ) {
						continue;
					}
					delete_transient( 'wc_attribute_taxonomies' );
					WC_Cache_Helper::invalidate_cache_group( 'woocommerce-attributes' );
				}
				$alias_taxonomy = wc_attribute_taxonomy_name( $alias['slug'] );
				if ( ! taxonomy_exists( $alias_taxonomy ) ) {
					register_taxonomy( $alias_taxonomy, 'product' );
				}
				etheme_seed_attribute_terms( $alias_taxonomy, $def['terms'] ?? array() );
			}
		}
	}

	set_transient( 'etheme_wc_attributes_seeded', ETHEME_ATTR_SEED_VERSION, YEAR_IN_SECONDS );
}
add_action( 'init', 'etheme_seed_wc_attributes', 20 );

function etheme_seed_attribute_terms( $taxonomy, $terms ) {
	if ( empty( $terms ) || ! is_array( $terms ) ) {
		return;
	}
	foreach ( $terms as $term_def ) {
		if ( empty( $term_def['name'] ) ) {
			continue;
		}
		$slug         = ! empty( $term_def['slug'] ) ? $term_def['slug'] : sanitize_title( $term_def['name'] );
		$parent_label = isset( $term_def['parent_label'] ) ? $term_def['parent_label'] : '';
		$existing     = get_term_by( 'slug', $slug, $taxonomy );
		if ( ! $existing ) {
			$result  = wp_insert_term( $term_def['name'], $taxonomy, array( 'slug' => $slug ) );
			$term_id = ! is_wp_error( $result ) ? $result['term_id'] : 0;
		} else {
			$term_id = $existing->term_id;
		}
		if ( $term_id ) {
			update_term_meta( $term_id, 'etheme_parent_color', $parent_label );
		}
	}
}

// ── Admin column: shows parent label for secondary colors ─────────────────────

add_filter( 'manage_edit-pa_color_columns', 'etheme_pa_color_admin_columns' );
add_action( 'manage_pa_color_custom_column', 'etheme_pa_color_admin_column_content', 10, 3 );

function etheme_pa_color_admin_columns( $columns ) {
	$result = array();
	foreach ( $columns as $key => $label ) {
		$result[ $key ] = $label;
		if ( 'name' === $key ) {
			$result['etheme_parent_color'] = __( 'Variante de', 'etheme' );
		}
	}
	return $result;
}

function etheme_pa_color_admin_column_content( $content, $column, $term_id ) {
	if ( 'etheme_parent_color' !== $column ) {
		return;
	}
	$parent = get_term_meta( $term_id, 'etheme_parent_color', true );
	echo $parent ? esc_html( $parent ) : '—';
}
