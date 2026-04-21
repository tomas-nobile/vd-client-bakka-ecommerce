<?php
/**
 * Etheme_Navbar_Walker — custom Walker_Nav_Menu.
 *
 * Detects the "Shop" menu item (by WooCommerce shop page ID or CSS class
 * `etheme-navbar-shop`) and replaces its dropdown with the product_cat tree
 * built by etheme_navbar_render_shop_dropdown().
 *
 * For all other items, delegates to the standard Walker_Nav_Menu logic
 * but applies etheme-* CSS classes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Etheme_Navbar_Walker extends Walker_Nav_Menu {

	/** @var int WooCommerce shop page ID (0 if WC not active). */
	private $shop_page_id = 0;

	/** @var bool Whether rendering inside the mobile panel. */
	private $is_mobile = false;

	/**
	 * @param bool $is_mobile Pass true when rendering inside the mobile panel.
	 */
	public function __construct( $is_mobile = false ) {
		$this->is_mobile = (bool) $is_mobile;

		if ( function_exists( 'wc_get_page_id' ) ) {
			$this->shop_page_id = (int) wc_get_page_id( 'shop' );
		}
	}

	/**
	 * Detect whether a nav item represents the shop.
	 *
	 * Priority:
	 *  1. Item is a "page" type whose object_id matches the WC shop page.
	 *  2. Item has the CSS class `etheme-navbar-shop`.
	 *
	 * @param WP_Post $item Nav menu item.
	 * @return bool
	 */
	private function is_shop_item( $item ) {
		if (
			$this->shop_page_id > 0
			&& 'page' === $item->object
			&& (int) $item->object_id === $this->shop_page_id
		) {
			return true;
		}

		return is_array( $item->classes )
			&& in_array( 'etheme-navbar-shop', $item->classes, true );
	}

	/**
	 * Override display_element so that the shop item's WP menu children are
	 * never iterated — the product_cat tree is injected directly instead.
	 *
	 * @param WP_Post $element           Nav menu item.
	 * @param array   $children_elements Keyed array of children.
	 * @param int     $max_depth         Maximum depth.
	 * @param int     $depth             Current depth.
	 * @param array   $args              Walker args.
	 * @param string  $output            Output string (by reference).
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( 0 === $depth && $this->is_shop_item( $element ) ) {
			$this->render_shop_item( $output, $element, $depth, $args );
			return;
		}

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

	/**
	 * Render the shop item with the product_cat tree dropdown.
	 *
	 * @param string  $output Output string (by reference).
	 * @param WP_Post $item   Nav menu item.
	 * @param int     $depth  Current depth.
	 * @param array   $args   Walker args.
	 */
	private function render_shop_item( &$output, $item, $depth, $args ) {
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes = array_filter( $classes, fn( $c ) => 'etheme-navbar-shop' !== $c );
		$classes[] = 'etheme-nav-item';
		$classes[] = 'etheme-nav-item--has-dropdown';
		$classes[] = 'etheme-nav-item--shop';

		$output .= '<li class="' . esc_attr( implode( ' ', array_filter( $classes ) ) ) . '">';

		$output .= '<a href="' . esc_url( $item->url ) . '" class="etheme-nav-link etheme-nav-link--dropdown" aria-haspopup="true" aria-expanded="false">';
		$output .= esc_html( $item->title );
		$output .= '<svg class="etheme-nav-link__chevron" aria-hidden="true" width="10" height="6" viewBox="0 0 10 6">';
		$output .= '<path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>';
		$output .= '</svg>';
		$output .= '</a>';

		$output .= etheme_navbar_render_shop_dropdown( $this->is_mobile );

		$output .= '</li>';
	}

	/**
	 * Start a menu item element.
	 *
	 * @param string  $output Output string (by reference).
	 * @param WP_Post $item   Nav menu item.
	 * @param int     $depth  Current depth.
	 * @param object  $args   Walker args.
	 * @param int     $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes      = empty( $item->classes ) ? array() : (array) $item->classes;
		$has_children = ! empty( $args->has_children );

		$classes[] = 'etheme-nav-item';
		if ( $has_children ) {
			$classes[] = 'etheme-nav-item--has-dropdown';
		}
		if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-ancestor', $classes, true ) ) {
			$classes[] = 'is-active';
		}

		$output .= '<li class="' . esc_attr( implode( ' ', array_filter( $classes ) ) ) . '">';

		$link_cls = 'etheme-nav-link' . ( $has_children ? ' etheme-nav-link--dropdown' : '' );

		$output .= '<a href="' . esc_url( $item->url ) . '" class="' . esc_attr( $link_cls ) . '"';
		if ( $has_children ) {
			$output .= ' aria-haspopup="true" aria-expanded="false"';
		}
		if ( $item->target ) {
			$output .= ' target="' . esc_attr( $item->target ) . '"';
		}
		if ( $item->xfn ) {
			$output .= ' rel="' . esc_attr( $item->xfn ) . '"';
		}
		$output .= '>';
		$output .= esc_html( $item->title );
		if ( $has_children ) {
			$output .= '<svg class="etheme-nav-link__chevron" aria-hidden="true" width="10" height="6" viewBox="0 0 10 6">';
			$output .= '<path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>';
			$output .= '</svg>';
		}
		$output .= '</a>';
	}

	/**
	 * Open a submenu level — wraps in etheme-dropdown.
	 *
	 * @param string $output Output string (by reference).
	 * @param int    $depth  Current depth.
	 * @param object $args   Walker args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<div class="etheme-dropdown"><ul class="etheme-dropdown__list" role="list">';
	}

	/**
	 * Close a submenu level.
	 *
	 * @param string $output Output string (by reference).
	 * @param int    $depth  Current depth.
	 * @param object $args   Walker args.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul></div>';
	}
}
