<?php
// home-faqs (sección Update / newsletter — contenido reemplazado por FAQs).
/**
 * Home FAQs Component
 *
 * Mantiene el diseño de la sección Update/newsletter (fondo accent, imágenes decorativas,
 * grilla de dos columnas imagen + contenido, animaciones). El formulario de suscripción
 * fue reemplazado por un acordeón de Preguntas Frecuentes.
 *
 * Layout:
 *   Columna izquierda (5/12) — imagen principal (configurable desde el editor o fallback update-image.png).
 *   Columna derecha  (7/12) — eyebrow h6 + título h2 + acordeón FAQ.
 *
 * Datos: src/core/config/config.json → homeFaqs (eyebrow, title, items[]).
 * Acordeón: <details>/<summary> nativo (accesible, sin dependencias JS).
 *
 * @param array $attributes Block attributes.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_home_newsletter( $attributes ) {
	$config    = etheme_get_core_config();
	$faqs_data = isset( $config['homeFaqs'] ) && is_array( $config['homeFaqs'] ) ? $config['homeFaqs'] : array();

	$eyebrow_attr = isset( $attributes['faqsEyebrow'] ) ? trim( (string) $attributes['faqsEyebrow'] ) : '';
	$eyebrow_cfg  = isset( $faqs_data['eyebrow'] ) ? trim( (string) $faqs_data['eyebrow'] ) : '';
	$eyebrow_raw  = '' !== $eyebrow_attr ? $eyebrow_attr : $eyebrow_cfg;

	$title_attr = isset( $attributes['faqsTitle'] ) ? trim( (string) $attributes['faqsTitle'] ) : '';
	$title_cfg  = isset( $faqs_data['title'] ) ? trim( (string) $faqs_data['title'] ) : '';
	$title_raw  = '' !== $title_attr ? $title_attr : $title_cfg;
	if ( '' === $title_raw ) {
		$title_raw = __( 'Preguntas frecuentes', 'etheme' );
	}

	$eyebrow = esc_html( $eyebrow_raw );
	$title   = esc_html( $title_raw );
	$items    = isset( $faqs_data['items'] ) && is_array( $faqs_data['items'] ) ? $faqs_data['items'] : array();
	$image_id = absint( $attributes['faqsImageId'] ?? 0 );

	$theme_uri = get_template_directory_uri();
	$main_img  = $image_id > 0
		? wp_get_attachment_image_url( $image_id, 'large' )
		: $theme_uri . '/assets/images/update-image.png';

	$left_items  = array_filter( $items, fn( $k ) => 0 === $k % 2, ARRAY_FILTER_USE_KEY );
	$right_items = array_filter( $items, fn( $k ) => 1 === $k % 2, ARRAY_FILTER_USE_KEY );
	?>

	<section class="newsletter-section relative" aria-labelledby="faqs-heading">
		<figure class="newsletter-deco newsletter-deco--left" aria-hidden="true">
			<img src="<?php echo esc_url( $theme_uri . '/assets/images/update-leftimage.png' ); ?>" alt="" class="newsletter-deco__img">
		</figure>
		<figure class="newsletter-deco newsletter-deco--right" aria-hidden="true">
			<img src="<?php echo esc_url( $theme_uri . '/assets/images/update-rightimage.png' ); ?>" alt="" class="newsletter-deco__img">
		</figure>

		<div class="container mx-auto px-4">
			<div class="grid grid-cols-1 lg:grid-cols-12 items-start">

				<!-- Columna imagen -->
				<div class="lg:col-span-5 order-first lg:order-none">
					<div class="newsletter-image-wrapper" data-aos="fade-up">
						<?php if ( $image_id > 0 ) : ?>
							<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'newsletter-image', 'alt' => '' ) ); ?>
						<?php else : ?>
							<img src="<?php echo esc_url( $main_img ); ?>" alt="" class="newsletter-image">
						<?php endif; ?>
					</div>
				</div>

				<!-- Columna contenido: título + acordeón -->
				<div class="lg:col-span-7">
					<div class="newsletter-content newsletter-content--with-image" data-aos="fade-up">

						<?php if ( '' !== $eyebrow ) : ?>
							<h6><?php echo $eyebrow; ?></h6>
						<?php endif; ?>

						<?php if ( '' !== $title ) : ?>
							<h2 id="faqs-heading"><?php echo $title; ?></h2>
						<?php endif; ?>

						<?php if ( ! empty( $items ) ) : ?>
							<div class="faq-accordion-grid">
								<div class="faq-accordion-col">
									<?php foreach ( $left_items as $item ) : ?>
										<?php etheme_render_faq_item( $item ); ?>
									<?php endforeach; ?>
								</div>
								<div class="faq-accordion-col">
									<?php foreach ( $right_items as $item ) : ?>
										<?php etheme_render_faq_item( $item ); ?>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

					</div>
				</div>

			</div>
		</div>
	</section>

	<?php
}

/**
 * Render a single FAQ accordion item using <details>/<summary>.
 *
 * @param array $item Array with 'question' and 'answer' keys.
 * @return void
 */
function etheme_render_faq_item( $item ) {
	$question = isset( $item['question'] ) ? esc_html( $item['question'] ) : '';
	$answer   = isset( $item['answer'] )   ? esc_html( $item['answer'] )   : '';

	if ( '' === $question && '' === $answer ) {
		return;
	}
	?>
	<details class="faq-accordion__item">
		<summary class="faq-accordion__question">
			<?php echo $question; ?>
			<span class="faq-accordion__icon" aria-hidden="true"></span>
		</summary>
		<div class="faq-accordion__answer">
			<p><?php echo $answer; ?></p>
		</div>
	</details>
	<?php
}
