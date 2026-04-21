<?php
/**
 * Navbar — orchestrator block.
 *
 * Loads component renderers and outputs the full navbar markup:
 * brand · desktop nav · action icons · mobile panel · search modal.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content (unused — SSR).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$navbar_dir = get_template_directory() . '/src/core/navbar/';

require_once $navbar_dir . 'includes/navbar-walker.php';

foreach ( array( 'navbar-brand', 'navbar-menu', 'navbar-actions' ) as $component ) {
	require_once $navbar_dir . 'components/' . $component . '.php';
}

$defaults = array(
	'menuLocation' => 'etheme-primary',
	'showSearch'   => true,
	'showCart'     => true,
);

$attributes = wp_parse_args( $attributes, $defaults );
?>

<header <?php echo get_block_wrapper_attributes( array( 'class' => 'etheme-navbar-header' ) ); ?>>
	<div class="etheme-navbar-container">
		<nav class="etheme-navbar" role="navigation" aria-label="<?php esc_attr_e( 'Navegación principal', 'etheme' ); ?>">

			<?php etheme_render_navbar_brand(); ?>

			<div class="etheme-navbar-collapse">
				<?php etheme_render_navbar_menu( $attributes ); ?>
			</div>

			<div class="etheme-navbar-right">
				<?php etheme_render_navbar_actions( $attributes ); ?>

				<button
					class="etheme-navbar-toggler"
					type="button"
					aria-controls="etheme-mobile-menu"
					aria-expanded="false"
					aria-label="<?php esc_attr_e( 'Abrir menú', 'etheme' ); ?>"
				>
					<span class="etheme-navbar-toggler__bar"></span>
					<span class="etheme-navbar-toggler__bar"></span>
					<span class="etheme-navbar-toggler__bar"></span>
				</button>
			</div>

		</nav>
	</div>

	<!-- Mobile fullscreen panel -->
	<div
		id="etheme-mobile-menu"
		class="etheme-mobile-menu"
		role="dialog"
		aria-modal="true"
		aria-label="<?php esc_attr_e( 'Menú de navegación', 'etheme' ); ?>"
		aria-hidden="true"
	>
		<div class="etheme-mobile-menu__inner">
			<div class="etheme-mobile-menu__header">
				<?php etheme_render_navbar_brand(); ?>
				<button
					class="etheme-mobile-menu__close"
					type="button"
					aria-label="<?php esc_attr_e( 'Cerrar menú', 'etheme' ); ?>"
				>
					<svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
						<path d="M3 3l16 16M19 3L3 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</button>
			</div>
			<?php etheme_render_navbar_menu( $attributes, true ); ?>
		</div>
	</div>

	<!-- Search modal overlay -->
	<?php
	$etheme_search_action = function_exists( 'wc_get_page_permalink' )
		? wc_get_page_permalink( 'shop' )
		: home_url( '/shop/' );
	?>
	<div
		id="etheme-search-modal"
		class="etheme-search-modal"
		role="dialog"
		aria-modal="true"
		aria-labelledby="etheme-search-modal-title"
		aria-hidden="true"
	>
		<div class="etheme-search-modal__backdrop" aria-hidden="true"></div>
		<div class="etheme-search-modal__panel">
			<h2 id="etheme-search-modal-title" class="screen-reader-text">
				<?php esc_html_e( 'Buscar en el sitio', 'etheme' ); ?>
			</h2>
			<button
				class="etheme-search-modal__close"
				type="button"
				aria-label="<?php esc_attr_e( 'Cerrar búsqueda', 'etheme' ); ?>"
			>
				<svg width="16" height="16" viewBox="0 0 22 22" fill="none" aria-hidden="true" class='mb-2'>
					<path d="M3 3l16 16M19 3L3 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
			</button>
			<form
				role="search"
				method="get"
				action="<?php echo esc_url( $etheme_search_action ); ?>"
				class="etheme-search-modal__form"
			>
				<input type="hidden" name="post_type" value="product" />
				<label for="etheme-search-input" class="screen-reader-text">
					<?php esc_html_e( 'Buscar', 'etheme' ); ?>
				</label>
				<input
					type="search"
					id="etheme-search-input"
					name="s"
					class="etheme-search-modal__input"
					placeholder="<?php esc_attr_e( 'Escribe para buscar…', 'etheme' ); ?>"
					autocomplete="off"
				/>
				<button
					type="submit"
					class="etheme-search-modal__submit"
					aria-label="<?php esc_attr_e( 'Buscar', 'etheme' ); ?>"
				>
					<svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
						<circle cx="9" cy="9" r="6.5" stroke="currentColor" stroke-width="2"/>
						<path d="M14 14l5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</button>
			</form>
		</div>
	</div>
</header>
