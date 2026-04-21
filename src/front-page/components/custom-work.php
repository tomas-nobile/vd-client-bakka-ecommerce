<?php
// home-custom-work — diseño Contrive choose2-con.
/**
 * Home Custom Work Component
 *
 * Sección de muebles a medida con fondo parallax (carpenter.png),
 * overlay oscuro, columna izquierda con eyebrow/título/descripción/CTA
 * a WhatsApp, y columna derecha con KPIs.
 * Fade-up: data-aos="fade-up" manejado por fp-fade-up.js.
 * Parallax: home-custom-work-parallax.js via rAF.
 *
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_custom_work_get_whatsapp_url() {
	$config_path = get_template_directory() . '/src/core/config/config.json';
	if ( ! file_exists( $config_path ) ) {
		return '#';
	}
	$config = json_decode( file_get_contents( $config_path ), true );
	$url    = $config['social']['whatsapp']['url'] ?? '#';
	return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '#';
}

function etheme_custom_work_get_kpis() {
	return array(
		array( 'number' => '8',  'expression' => '+',  'label' => __( 'Años de experiencia', 'etheme' ) ),
		array( 'number' => '500', 'expression' => '+',  'label' => __( 'Proyectos realizados', 'etheme' ) ),
		array( 'number' => '100', 'expression' => '%',  'label' => __( 'Clientes satisfechos', 'etheme' ) ),
	);
}

function etheme_custom_work_render_kpi( $kpi ) {
	?>
	<li>
		<div class="cw-kpi__value">
			<span class="cw-kpi__number"><?php echo esc_html( $kpi['number'] ); ?></span>
			<span class="cw-kpi__expression"><?php echo esc_html( $kpi['expression'] ); ?></span>
		</div>
		<span class="cw-kpi__label"><?php echo esc_html( $kpi['label'] ); ?></span>
	</li>
	<?php
}

function etheme_custom_work_render_left( $whatsapp_url ) {
	?>
	<div class="w-full lg:w-5/12">
		<div class="cw-content" data-aos="fade-up">
			<h6 class="cw-content__eyebrow"><?php esc_html_e( 'Diseño Exclusivo', 'etheme' ); ?></h6>
			<h2 class="cw-content__title" id="custom-work-heading">
				<?php esc_html_e( 'Muebles a Medida para Tu Espacio', 'etheme' ); ?>
			</h2>
			<p class="cw-content__desc">
				<?php esc_html_e( 'Creamos piezas únicas adaptadas a cada ambiente y necesidad. Desde el diseño hasta la entrega, trabajamos con vos en cada detalle.', 'etheme' ); ?>
			</p>
			<a href="<?php echo esc_url( $whatsapp_url ); ?>"
			   class="cw-content__cta"
			   target="_blank"
			   rel="noopener noreferrer">
				<?php esc_html_e( 'Consultanos', 'etheme' ); ?>
			</a>
		</div>
	</div>
	<?php
}

function etheme_custom_work_render_right() {
	$kpis = etheme_custom_work_get_kpis();
	?>
	<div class="w-full lg:w-7/12">
		<div class="cw-kpis" data-aos="fade-up" data-aos-delay="150">
			<ul class="cw-kpis__list list-none p-0 m-0">
				<?php foreach ( $kpis as $kpi ) :
					etheme_custom_work_render_kpi( $kpi );
				endforeach; ?>
			</ul>
		</div>
	</div>
	<?php
}

function etheme_render_home_custom_work() {
	$image_url    = esc_url( get_template_directory_uri() . '/assets/images/carpenter.png' );
	$whatsapp_url = etheme_custom_work_get_whatsapp_url();
	?>
	<section
		class="custom-work-con"
		aria-labelledby="custom-work-heading"
		data-parallax-bg="true"
		style="--cw-bg-image: url('<?php echo $image_url; ?>');">
		<div class="cw-overlay" aria-hidden="true"></div>
		<div class="container mx-auto px-6 md:px-12 lg:px-20">
			<div class="flex flex-wrap lg:items-center">
				<?php
				etheme_custom_work_render_left( $whatsapp_url );
				etheme_custom_work_render_right();
				?>
			</div>
		</div>
	</section>
	<?php
}
