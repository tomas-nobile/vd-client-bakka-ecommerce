<?php
/**
 * Navbar helpers — product_cat tree with transient cache and invalidation hooks.
 *
 * Loaded early via functions.php so invalidation hooks are always registered.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ETHEME_NAVBAR_CATS_TRANSIENT', 'etheme_navbar_product_cats' );

/**
 * Get flat product categories and group them into a parent → children tree.
 * Uses a 12-hour transient; invalidated by any product_cat term change.
 *
 * @return array[] Top-level WP_Term objects, each with a `children` property.
 */
function etheme_navbar_get_product_cats() {
	$cached = get_transient( ETHEME_NAVBAR_CATS_TRANSIENT );
	if ( false !== $cached ) {
		return $cached;
	}

	$all_terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $all_terms ) || empty( $all_terms ) ) {
		return array();
	}

	$top_level = array();
	$children  = array();

	foreach ( $all_terms as $term ) {
		if ( 0 === (int) $term->parent ) {
			$top_level[ $term->term_id ] = $term;
		} else {
			$children[ (int) $term->parent ][] = $term;
		}
	}

	$result = array();
	foreach ( $top_level as $term_id => $term ) {
		$term->children = $children[ $term_id ] ?? array();
		$result[]       = $term;
	}

	set_transient( ETHEME_NAVBAR_CATS_TRANSIENT, $result, 12 * HOUR_IN_SECONDS );

	return $result;
}

/**
 * Delete the product categories transient when any term in product_cat changes.
 *
 * @param int    $term_id  Term ID.
 * @param int    $tt_id    Term taxonomy ID.
 * @param string $taxonomy Taxonomy slug.
 */
function etheme_navbar_invalidate_cats_cache( $term_id, $tt_id, $taxonomy ) {
	if ( 'product_cat' === $taxonomy ) {
		delete_transient( ETHEME_NAVBAR_CATS_TRANSIENT );
	}
}
/**
 * AJAX endpoint: return the current WooCommerce cart item count.
 * Called by navbar-cart-sync.js on bfcache page restores.
 */
add_action( 'wp_ajax_etheme_get_cart_count',        'etheme_ajax_get_cart_count' );
add_action( 'wp_ajax_nopriv_etheme_get_cart_count', 'etheme_ajax_get_cart_count' );
function etheme_ajax_get_cart_count() {
	$count = ( function_exists( 'WC' ) && WC()->cart )
		? (int) WC()->cart->get_cart_contents_count()
		: 0;
	wp_send_json_success( array( 'count' => $count ) );
}

add_action( 'created_term', 'etheme_navbar_invalidate_cats_cache', 10, 3 );
add_action( 'edited_term',  'etheme_navbar_invalidate_cats_cache', 10, 3 );
add_action( 'delete_term',  'etheme_navbar_invalidate_cats_cache', 10, 3 );

/**
 * Build and return the HTML for the shop dropdown (product_cat tree).
 *
 * @param bool $is_mobile Whether the dropdown is inside the mobile panel.
 * @return string HTML string.
 */
function etheme_navbar_render_shop_dropdown( $is_mobile = false ) {
	$categories = etheme_navbar_get_product_cats();

	$extra_cls = $is_mobile ? ' etheme-dropdown--mobile' : '';

	ob_start();
	?>
	<div class="etheme-dropdown etheme-dropdown--shop<?php echo esc_attr( $extra_cls ); ?>">
		<ul class="etheme-dropdown__list" role="list">
			<?php if ( empty( $categories ) ) : ?>
				<li class="etheme-dropdown__item">
					<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>" class="etheme-dropdown__link">
						<?php esc_html_e( 'Sin categorías disponibles', 'etheme' ); ?>
					</a>
				</li>
			<?php else : ?>
				<?php foreach ( $categories as $cat ) : ?>
					<?php
					$term_link = get_term_link( $cat );
					if ( is_wp_error( $term_link ) ) {
						continue;
					}
					?>
					<li class="etheme-dropdown__item<?php echo ! empty( $cat->children ) ? ' etheme-dropdown__item--has-sub' : ''; ?>">
						<?php if ( ! empty( $cat->children ) ) : ?>
							<?php $submenu_id = 'etheme-shop-subcat-' . (int) $cat->term_id; ?>
							<div class="etheme-dropdown__row">
								<a href="<?php echo esc_url( $term_link ); ?>" class="etheme-dropdown__link">
									<?php echo esc_html( $cat->name ); ?>
								</a>
								<button
									type="button"
									class="etheme-dropdown__toggle"
									aria-expanded="false"
									aria-controls="<?php echo esc_attr( $submenu_id ); ?>"
									aria-label="<?php echo esc_attr( sprintf( __( 'Abrir subcategorías de %s', 'etheme' ), $cat->name ) ); ?>"
								>
									<svg class="etheme-dropdown__chevron" aria-hidden="true" width="8" height="5" viewBox="0 0 8 5">
										<path d="M1 1l3 3 3-3" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</button>
							</div>
						<?php else : ?>
							<a href="<?php echo esc_url( $term_link ); ?>" class="etheme-dropdown__link">
								<?php echo esc_html( $cat->name ); ?>
							</a>
						<?php endif; ?>
						<?php if ( ! empty( $cat->children ) ) : ?>
							<ul id="<?php echo esc_attr( $submenu_id ); ?>" class="etheme-dropdown__sub" role="list">
								<?php foreach ( $cat->children as $child ) : ?>
									<?php
									$child_link = get_term_link( $child );
									if ( is_wp_error( $child_link ) ) {
										continue;
									}
									?>
									<li class="etheme-dropdown__item">
										<a href="<?php echo esc_url( $child_link ); ?>" class="etheme-dropdown__link">
											<?php echo esc_html( $child->name ); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
		<div class="etheme-dropdown--shop__footer">
			<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>" class="etheme-dropdown--shop__cta">
				<?php esc_html_e( 'Ver todos los productos', 'etheme' ); ?>
				<svg aria-hidden="true" width="14" height="14" viewBox="0 0 14 14" fill="none">
					<path d="M3 7h8M7 3l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
