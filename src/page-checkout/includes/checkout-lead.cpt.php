<?php
/**
 * Checkout leads CPT — abandoned-cart registry.
 *
 * Stores every shopper who reached "Continuar al pago" (email + cart snapshot),
 * independent of whether the order completes. Admin UI: list columns, status
 * filter, read-only detail metabox, bulk "contacted" action and CSV export.
 *
 * @package Etheme
 * @see specs/23.checkout-leads.md
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lead statuses with Spanish labels.
 *
 * @return array<string,string>
 */
function etheme_lead_get_statuses() {
	return array(
		'interested' => __( 'Interesado', 'etheme' ),
		'contacted'  => __( 'Contactado', 'etheme' ),
		'recovered'  => __( 'Recuperado', 'etheme' ),
		'purchased'  => __( 'Compró', 'etheme' ),
	);
}

/**
 * Numeric rank of a status. Higher rank never gets downgraded.
 *
 * @param string $status Status slug.
 * @return int
 */
function etheme_lead_status_rank( $status ) {
	$ranks = array(
		'interested' => 0,
		'contacted'  => 1,
		'recovered'  => 2,
		'purchased'  => 3,
	);
	return isset( $ranks[ $status ] ) ? $ranks[ $status ] : 0;
}

/**
 * Register the CPT. Private, admin UI only, no manual creation.
 */
function etheme_register_checkout_lead_cpt() {
	$labels = array(
		'name'               => __( 'Carritos abandonados', 'etheme' ),
		'singular_name'      => __( 'Carrito abandonado', 'etheme' ),
		'edit_item'          => __( 'Detalle del lead', 'etheme' ),
		'view_item'          => __( 'Ver lead', 'etheme' ),
		'search_items'       => __( 'Buscar leads', 'etheme' ),
		'not_found'          => __( 'No hay leads capturados todavía', 'etheme' ),
		'not_found_in_trash' => __( 'No hay leads en la papelera', 'etheme' ),
		'menu_name'          => __( 'Carritos abandonados', 'etheme' ),
	);

	register_post_type( 'etheme_checkout_lead', array(
		'labels'          => $labels,
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_icon'       => 'dashicons-cart',
		'supports'        => array( 'title' ),
		'has_archive'     => false,
		'rewrite'         => false,
		'show_in_rest'    => false,
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
	) );
}
add_action( 'init', 'etheme_register_checkout_lead_cpt' );

/**
 * Register lead meta (no REST exposure).
 */
function etheme_register_checkout_lead_meta() {
	$fields = array(
		'_lead_email'           => 'string',
		'_lead_name'            => 'string',
		'_lead_items'           => 'array',
		'_lead_total'           => 'number',
		'_lead_currency'        => 'string',
		'_lead_captured_at'     => 'string',
		'_lead_updated_at'      => 'string',
		'_lead_status'          => 'string',
		'_lead_recovery_coupon' => 'string',
		'_lead_recovery_url'    => 'string',
		'_lead_source'          => 'string',
		'_lead_unsubscribed'    => 'boolean',
	);

	foreach ( $fields as $key => $type ) {
		register_post_meta( 'etheme_checkout_lead', $key, array(
			'show_in_rest' => false,
			'single'       => true,
			'type'         => $type,
		) );
	}
}
add_action( 'init', 'etheme_register_checkout_lead_meta' );

/* -------------------------------------------------------------------------
 * Admin list table: columns, sorting, status filter
 * ---------------------------------------------------------------------- */

/**
 * Replace list table columns.
 *
 * @param array $columns Default columns.
 * @return array
 */
function etheme_lead_admin_columns( $columns ) {
	return array(
		'cb'            => isset( $columns['cb'] ) ? $columns['cb'] : '<input type="checkbox" />',
		'title'         => __( 'Email', 'etheme' ),
		'lead_items'    => __( 'Ítems', 'etheme' ),
		'lead_total'    => __( 'Total', 'etheme' ),
		'lead_status'   => __( 'Estado', 'etheme' ),
		'lead_captured' => __( 'Capturado', 'etheme' ),
		'lead_updated'  => __( 'Actualizado', 'etheme' ),
	);
}
add_filter( 'manage_etheme_checkout_lead_posts_columns', 'etheme_lead_admin_columns' );

/**
 * Render custom column values.
 *
 * @param string $column  Column key.
 * @param int    $post_id Lead ID.
 */
function etheme_lead_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'lead_items':
			$items = get_post_meta( $post_id, '_lead_items', true );
			if ( ! is_array( $items ) || empty( $items ) ) {
				echo '—';
				break;
			}
			$units = 0;
			foreach ( $items as $item ) {
				$units += isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
			}
			printf(
				/* translators: 1: product count, 2: unit count */
				esc_html__( '%1$d productos / %2$d unidades', 'etheme' ),
				count( $items ),
				(int) $units
			);
			break;

		case 'lead_total':
			$total    = (float) get_post_meta( $post_id, '_lead_total', true );
			$currency = (string) get_post_meta( $post_id, '_lead_currency', true );
			if ( function_exists( 'wc_price' ) ) {
				echo wp_kses_post( wc_price( $total, $currency ? array( 'currency' => $currency ) : array() ) );
			} else {
				echo esc_html( number_format_i18n( $total, 2 ) );
			}
			break;

		case 'lead_status':
			$status   = (string) get_post_meta( $post_id, '_lead_status', true );
			$statuses = etheme_lead_get_statuses();
			$label    = isset( $statuses[ $status ] ) ? $statuses[ $status ] : $statuses['interested'];
			$colors   = array(
				'interested' => '#996800',
				'contacted'  => '#1d4ed8',
				'recovered'  => '#7c3aed',
				'purchased'  => '#1a7a3c',
			);
			$color    = isset( $colors[ $status ] ) ? $colors[ $status ] : $colors['interested'];
			printf(
				'<span style="display:inline-block;padding:2px 8px;border:1px solid %1$s;color:%1$s;font-size:11px;font-weight:600;">%2$s</span>',
				esc_attr( $color ),
				esc_html( $label )
			);
			if ( get_post_meta( $post_id, '_lead_unsubscribed', true ) ) {
				echo ' <span style="font-size:11px;color:#b32d2e;">' . esc_html__( '(baja)', 'etheme' ) . '</span>';
			}
			break;

		case 'lead_captured':
			echo esc_html( get_the_date( 'd/m/Y H:i', $post_id ) );
			break;

		case 'lead_updated':
			$updated = (string) get_post_meta( $post_id, '_lead_updated_at', true );
			echo $updated ? esc_html( mysql2date( 'd/m/Y H:i', $updated ) ) : '—';
			break;
	}
}
add_action( 'manage_etheme_checkout_lead_posts_custom_column', 'etheme_lead_admin_column_content', 10, 2 );

/**
 * Sortable columns: total and dates.
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function etheme_lead_sortable_columns( $columns ) {
	$columns['lead_total']    = 'lead_total';
	$columns['lead_captured'] = 'date';
	$columns['lead_updated']  = 'lead_updated';
	return $columns;
}
add_filter( 'manage_edit-etheme_checkout_lead_sortable_columns', 'etheme_lead_sortable_columns' );

/**
 * Handle custom orderby + status filter on the admin list query.
 *
 * @param WP_Query $query Main admin query.
 */
function etheme_lead_admin_list_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'etheme_checkout_lead' !== $query->get( 'post_type' ) ) {
		return;
	}

	$orderby = $query->get( 'orderby' );
	if ( 'lead_total' === $orderby ) {
		$query->set( 'meta_key', '_lead_total' );
		$query->set( 'orderby', 'meta_value_num' );
	} elseif ( 'lead_updated' === $orderby ) {
		$query->set( 'meta_key', '_lead_updated_at' );
		$query->set( 'orderby', 'meta_value' );
	}

	// Status dropdown filter (admin list only, value whitelisted against known statuses).
	$status = isset( $_GET['lead_status'] ) ? sanitize_key( wp_unslash( $_GET['lead_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $status && array_key_exists( $status, etheme_lead_get_statuses() ) ) {
		$meta_query   = (array) $query->get( 'meta_query' );
		$meta_query[] = array(
			'key'   => '_lead_status',
			'value' => $status,
		);
		$query->set( 'meta_query', $meta_query );
	}
}
add_action( 'pre_get_posts', 'etheme_lead_admin_list_query' );

/**
 * Status filter dropdown above the list table.
 *
 * @param string $post_type Current post type.
 */
function etheme_lead_status_filter_dropdown( $post_type ) {
	if ( 'etheme_checkout_lead' !== $post_type ) {
		return;
	}
	$current = isset( $_GET['lead_status'] ) ? sanitize_key( wp_unslash( $_GET['lead_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<select name="lead_status">
		<option value=""><?php esc_html_e( 'Todos los estados', 'etheme' ); ?></option>
		<?php foreach ( etheme_lead_get_statuses() as $slug => $label ) : ?>
			<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current, $slug ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}
add_action( 'restrict_manage_posts', 'etheme_lead_status_filter_dropdown' );

/* -------------------------------------------------------------------------
 * Bulk action: mark as contacted
 * ---------------------------------------------------------------------- */

/**
 * Register the bulk action.
 *
 * @param array $actions Bulk actions.
 * @return array
 */
function etheme_lead_register_bulk_actions( $actions ) {
	$actions['etheme_mark_contacted'] = __( 'Marcar como contactado', 'etheme' );
	return $actions;
}
add_filter( 'bulk_actions-edit-etheme_checkout_lead', 'etheme_lead_register_bulk_actions' );

/**
 * Handle the bulk action. Never downgrades a higher status.
 *
 * @param string $redirect_to Redirect URL.
 * @param string $action      Action slug.
 * @param array  $post_ids    Selected lead IDs.
 * @return string
 */
function etheme_lead_handle_bulk_contacted( $redirect_to, $action, $post_ids ) {
	if ( 'etheme_mark_contacted' !== $action ) {
		return $redirect_to;
	}
	$updated = 0;
	foreach ( $post_ids as $post_id ) {
		if ( etheme_lead_set_status( (int) $post_id, 'contacted' ) ) {
			$updated++;
		}
	}
	return add_query_arg( 'etheme_leads_contacted', $updated, $redirect_to );
}
add_filter( 'handle_bulk_actions-edit-etheme_checkout_lead', 'etheme_lead_handle_bulk_contacted', 10, 3 );

/**
 * Admin notice after the bulk action.
 */
function etheme_lead_bulk_contacted_notice() {
	if ( ! isset( $_GET['etheme_leads_contacted'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$count = absint( wp_unslash( $_GET['etheme_leads_contacted'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html( sprintf(
			/* translators: %d: number of leads marked as contacted */
			_n( '%d lead marcado como contactado.', '%d leads marcados como contactados.', $count, 'etheme' ),
			$count
		) )
	);
}
add_action( 'admin_notices', 'etheme_lead_bulk_contacted_notice' );

/* -------------------------------------------------------------------------
 * Read-only detail metabox
 * ---------------------------------------------------------------------- */

/**
 * Register the detail metabox.
 */
function etheme_lead_register_metabox() {
	add_meta_box(
		'etheme-lead-detail',
		__( 'Detalle del carrito abandonado', 'etheme' ),
		'etheme_lead_render_metabox',
		'etheme_checkout_lead',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'etheme_lead_register_metabox' );

/**
 * Render the read-only detail metabox.
 *
 * @param WP_Post $post Lead post.
 */
function etheme_lead_render_metabox( $post ) {
	$items        = get_post_meta( $post->ID, '_lead_items', true );
	$total        = (float) get_post_meta( $post->ID, '_lead_total', true );
	$currency     = (string) get_post_meta( $post->ID, '_lead_currency', true );
	$status       = (string) get_post_meta( $post->ID, '_lead_status', true );
	$name         = (string) get_post_meta( $post->ID, '_lead_name', true );
	$coupon       = (string) get_post_meta( $post->ID, '_lead_recovery_coupon', true );
	$recovery_url = function_exists( 'etheme_lead_get_recovery_url' ) ? etheme_lead_get_recovery_url( $post->ID ) : '';
	$unsub        = (bool) get_post_meta( $post->ID, '_lead_unsubscribed', true );
	$statuses     = etheme_lead_get_statuses();
	?>
	<table class="widefat striped" style="margin-bottom:16px;">
		<tbody>
			<tr>
				<td style="width:180px;"><strong><?php esc_html_e( 'Email', 'etheme' ); ?></strong></td>
				<td><?php echo esc_html( get_post_meta( $post->ID, '_lead_email', true ) ); ?></td>
			</tr>
			<?php if ( $name ) : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Nombre', 'etheme' ); ?></strong></td>
				<td><?php echo esc_html( $name ); ?></td>
			</tr>
			<?php endif; ?>
			<tr>
				<td><strong><?php esc_html_e( 'Estado', 'etheme' ); ?></strong></td>
				<td>
					<?php echo esc_html( isset( $statuses[ $status ] ) ? $statuses[ $status ] : $statuses['interested'] ); ?>
					<?php if ( $unsub ) : ?>
						— <span style="color:#b32d2e;"><?php esc_html_e( 'Se dio de baja de los emails', 'etheme' ); ?></span>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Total del carrito', 'etheme' ); ?></strong></td>
				<td>
					<?php
					if ( function_exists( 'wc_price' ) ) {
						echo wp_kses_post( wc_price( $total, $currency ? array( 'currency' => $currency ) : array() ) );
					} else {
						echo esc_html( number_format_i18n( $total, 2 ) );
					}
					?>
				</td>
			</tr>
			<?php if ( $coupon ) : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Cupón de campaña', 'etheme' ); ?></strong></td>
				<td><code><?php echo esc_html( $coupon ); ?></code></td>
			</tr>
			<?php endif; ?>
			<?php if ( $recovery_url ) : ?>
			<tr>
				<td><strong><?php esc_html_e( 'Link de recuperación', 'etheme' ); ?></strong></td>
				<td><a href="<?php echo esc_url( $recovery_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $recovery_url ); ?></a></td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ( is_array( $items ) && ! empty( $items ) ) : ?>
	<table class="widefat striped">
		<thead>
			<tr>
				<th style="width:60px;"></th>
				<th><?php esc_html_e( 'Producto', 'etheme' ); ?></th>
				<th style="width:90px;"><?php esc_html_e( 'Cantidad', 'etheme' ); ?></th>
				<th style="width:120px;"><?php esc_html_e( 'Subtotal', 'etheme' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $items as $item ) : ?>
			<tr>
				<td>
					<?php if ( ! empty( $item['thumbnail'] ) ) : ?>
						<img src="<?php echo esc_url( $item['thumbnail'] ); ?>" alt="" style="width:48px;height:48px;object-fit:cover;" />
					<?php endif; ?>
				</td>
				<td>
					<?php if ( ! empty( $item['permalink'] ) ) : ?>
						<a href="<?php echo esc_url( $item['permalink'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( isset( $item['name'] ) ? $item['name'] : '' ); ?></a>
					<?php else : ?>
						<?php echo esc_html( isset( $item['name'] ) ? $item['name'] : '' ); ?>
					<?php endif; ?>
					<?php if ( ! empty( $item['sku'] ) ) : ?>
						<br /><small>SKU: <?php echo esc_html( $item['sku'] ); ?></small>
					<?php endif; ?>
				</td>
				<td><?php echo esc_html( isset( $item['quantity'] ) ? (int) $item['quantity'] : 0 ); ?></td>
				<td>
					<?php
					$line = isset( $item['line_subtotal'] ) ? (float) $item['line_subtotal'] : 0;
					if ( function_exists( 'wc_price' ) ) {
						echo wp_kses_post( wc_price( $line, $currency ? array( 'currency' => $currency ) : array() ) );
					} else {
						echo esc_html( number_format_i18n( $line, 2 ) );
					}
					?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php else : ?>
		<p><?php esc_html_e( 'Sin ítems registrados.', 'etheme' ); ?></p>
	<?php endif; ?>
	<?php
}

/* -------------------------------------------------------------------------
 * CSV export
 * ---------------------------------------------------------------------- */

/**
 * Export button next to the list table filters.
 *
 * @param string $which Table nav position.
 */
function etheme_lead_export_button( $which ) {
	global $typenow;
	if ( 'etheme_checkout_lead' !== $typenow || 'top' !== $which ) {
		return;
	}

	$args = array( 'action' => 'etheme_export_leads' );
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filters, the export URL itself is nonced.
	if ( ! empty( $_GET['lead_status'] ) ) {
		$args['lead_status'] = sanitize_key( wp_unslash( $_GET['lead_status'] ) );
	}
	if ( ! empty( $_GET['m'] ) ) {
		$args['m'] = absint( wp_unslash( $_GET['m'] ) );
	}
	// phpcs:enable

	$url = wp_nonce_url( add_query_arg( $args, admin_url( 'admin-post.php' ) ), 'etheme-export-leads' );
	printf(
		'<a href="%s" class="button" style="margin-left:8px;">%s</a>',
		esc_url( $url ),
		esc_html__( 'Exportar CSV', 'etheme' )
	);
}
add_action( 'manage_posts_extra_tablenav', 'etheme_lead_export_button' );

/**
 * Neutralize CSV formula injection: Excel executes cells starting with
 * = + - @, so prefix them with a single quote.
 *
 * @param string $value Raw cell value.
 * @return string
 */
function etheme_lead_csv_safe( $value ) {
	$value = (string) $value;
	if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@' ), true ) ) {
		return "'" . $value;
	}
	return $value;
}

/**
 * CSV export handler (admin-post). Respects the active status/month filter.
 * Without an explicit status filter it excludes "purchased"; unsubscribed
 * leads are always excluded (they must never reach a campaign list).
 */
function etheme_lead_export_csv() {
	if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'No tenés permisos para exportar leads.', 'etheme' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'etheme-export-leads' );

	$status = isset( $_GET['lead_status'] ) ? sanitize_key( wp_unslash( $_GET['lead_status'] ) ) : '';
	if ( $status && ! array_key_exists( $status, etheme_lead_get_statuses() ) ) {
		$status = '';
	}
	$month = isset( $_GET['m'] ) ? absint( wp_unslash( $_GET['m'] ) ) : 0;

	$meta_query = array(
		array(
			'relation' => 'OR',
			array(
				'key'     => '_lead_unsubscribed',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_lead_unsubscribed',
				'value'   => '1',
				'compare' => '!=',
			),
		),
	);

	if ( $status ) {
		$meta_query[] = array(
			'key'   => '_lead_status',
			'value' => $status,
		);
	} else {
		$meta_query[] = array(
			'key'     => '_lead_status',
			'value'   => 'purchased',
			'compare' => '!=',
		);
	}

	$args = array(
		'post_type'      => 'etheme_checkout_lead',
		'post_status'    => 'publish',
		'posts_per_page' => 5000,
		'fields'         => 'ids',
		'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'no_found_rows'  => true,
	);
	if ( $month ) {
		$args['m'] = $month;
	}

	$lead_ids = get_posts( $args );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="leads-carritos-' . gmdate( 'Ymd-His' ) . '.csv"' );

	$out = fopen( 'php://output', 'w' );
	// UTF-8 BOM so Excel renders tildes/ñ correctly.
	fwrite( $out, "\xEF\xBB\xBF" );
	fputcsv( $out, array( 'email', 'nombre', 'estado', 'items_resumen', 'total', 'cupon', 'recovery_url', 'capturado' ) );

	foreach ( $lead_ids as $lead_id ) {
		$items   = get_post_meta( $lead_id, '_lead_items', true );
		$summary = '';
		if ( is_array( $items ) ) {
			$parts = array();
			foreach ( $items as $item ) {
				$qty     = isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
				$iname   = isset( $item['name'] ) ? (string) $item['name'] : '';
				$parts[] = $qty . ' × ' . $iname;
			}
			$summary = implode( '; ', $parts );
		}

		fputcsv( $out, array(
			etheme_lead_csv_safe( get_post_meta( $lead_id, '_lead_email', true ) ),
			etheme_lead_csv_safe( get_post_meta( $lead_id, '_lead_name', true ) ),
			(string) get_post_meta( $lead_id, '_lead_status', true ),
			etheme_lead_csv_safe( $summary ),
			(string) get_post_meta( $lead_id, '_lead_total', true ),
			etheme_lead_csv_safe( get_post_meta( $lead_id, '_lead_recovery_coupon', true ) ),
			function_exists( 'etheme_lead_get_recovery_url' ) ? etheme_lead_get_recovery_url( $lead_id ) : '',
			get_the_date( 'Y-m-d H:i', $lead_id ),
		) );
	}

	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	exit;
}
add_action( 'admin_post_etheme_export_leads', 'etheme_lead_export_csv' );
