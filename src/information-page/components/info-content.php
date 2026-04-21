<?php
/**
 * Information page — content body component.
 *
 * Renders the legal/informational content: optional intro paragraph,
 * then sections with h5 headings and paragraphs.
 * Styled after Contrive .privacy-policy-con.
 *
 * @param array $data Legal page data from etheme_get_legal_page_data().
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_info_content( $data ) {
	$title    = isset( $data['title'] )    ? $data['title']    : '';
	$sections = isset( $data['sections'] ) && is_array( $data['sections'] ) ? $data['sections'] : array();
	$intro    = isset( $data['intro'] )    ? $data['intro']    : '';
	?>
	<section class="info-content">
		<div class="container mx-auto px-4">
			<div class="info-content__inner" data-aos="fade-up">
				<?php if ( '' !== $title ) : ?>
					<h4 class="info-content__page-title"><?php echo esc_html( $title ); ?></h4>
				<?php endif; ?>

				<?php if ( '' !== $intro ) : ?>
					<p class="info-content__intro"><?php echo esc_html( $intro ); ?></p>
				<?php endif; ?>

				<?php foreach ( $sections as $section ) : ?>
					<?php
					$heading = isset( $section['heading'] ) ? $section['heading'] : '';
					$body    = isset( $section['body'] )    ? $section['body']    : '';
					if ( '' === $heading && '' === $body ) {
						continue;
					}
					?>
					<div class="info-content__section">
						<?php if ( '' !== $heading ) : ?>
							<h5 class="info-content__section-title"><?php echo esc_html( $heading ); ?></h5>
						<?php endif; ?>
						<?php if ( '' !== $body ) : ?>
							<p class="info-content__section-body"><?php echo esc_html( $body ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}
