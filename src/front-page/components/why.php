<?php
// home-why — diseño Contrive.
/**
 * Home Why (Choose Us) Component
 *
 * Displays the "Why You Should Choose Us" section with 3 benefit items.
 * Layout: Tailwind flex. Visual styling: why.scss.
 * Fade-up: data-aos="fade-up" handled by fp-fade-up.js (no external libs).
 *
 * @param array $attributes Block attributes.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_why_get_items() {
	return array(
		array(
			'icon'        => 'choose-icon1.png',
			'title'       => __( 'Envíos a CABA<br> y GBA', 'etheme' ),
			'description' => __( 'Nosotros realizamos el envío de tus muebles para garantizar que lleguen en perfecto estado, evitando inconvenientes en el transporte.', 'etheme' ),
		),
		array(
			'icon'        => 'choose-icon2.png',
			'title'       => __( 'Promociones y<br> descuentos', 'etheme' ),
			'description' => __( 'Accede a ofertas exclusivas y descuentos especiales en cada una de tus compras.', 'etheme' ),
		),
		array(
			'icon'        => 'choose-icon3.png',
			'title'       => __( 'Pagos 100%<br> Seguros', 'etheme' ),
			'description' => __( 'Protegemos tus datos con tecnología de encriptación de alta seguridad en todas las transacciones.', 'etheme' ),
		),
	);
}

function etheme_why_render_item( $item, $theme_uri ) {
	$icon_url = esc_url( $theme_uri . '/assets/images/' . $item['icon'] );
	$title    = wp_kses( $item['title'], array( 'br' => array() ) );
	?>
	<li class="beneft-box">
		<figure class="icon mb-0">
			<img src="<?php echo $icon_url; ?>" alt="" class="img-fluid">
		</figure>
		<h5><?php echo $title; ?></h5>
		<p class="mb-0"><?php echo esc_html( $item['description'] ); ?></p>
	</li>
	<?php
}

function etheme_why_render_content( $eyebrow, $title, $desc ) {
	?>
	<div class="w-full lg:w-5/12 lg:pr-10 xl:pr-16">
		<div class="why_content" data-aos="fade-up">
			<h6><?php echo $eyebrow; ?></h6>
			<h2 id="why-heading"><?php echo $title; ?></h2>
			<p class="mb-0"><?php echo $desc; ?></p>
		</div>
	</div>
	<?php
}

function etheme_why_render_items( $theme_uri ) {
	$items = etheme_why_get_items();
	?>
	<div class="w-full lg:w-7/12">
		<div class="why_wrapper" data-aos="fade-up">
			<ul class="list-none p-0 m-0">
				<?php foreach ( $items as $item ) :
					etheme_why_render_item( $item, $theme_uri );
				endforeach; ?>
			</ul>
		</div>
	</div>
	<?php
}

function etheme_render_home_why( $attributes ) {
	$theme_uri = get_template_directory_uri();
	$eyebrow   = esc_html( $attributes['whyEyebrow'] );
	$title     = esc_html( $attributes['whyTitle'] );
	$desc      = esc_html( $attributes['whyDescription'] );
	?>
	<section class="why-con" aria-labelledby="why-heading">
		<div class="w-full px-6 md:px-12 lg:px-20 2xl:max-w-screen-2xl 2xl:mx-auto">
			<div class="flex flex-wrap lg:items-center">
				<?php
				etheme_why_render_content( $eyebrow, $title, $desc );
				etheme_why_render_items( $theme_uri );
				?>
			</div>
		</div>
	</section>
	<?php
}
