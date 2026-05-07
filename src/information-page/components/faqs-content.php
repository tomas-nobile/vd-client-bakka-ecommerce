<?php
/**
 * FAQ page content component.
 *
 * Renders FAQs grouped by category as accessible accordion.
 * Outputs JSON-LD FAQPage schema markup for SEO.
 *
 * @param array $data FAQ page data from etheme_get_faqs_data().
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_faqs_content( $data ) {
	$categories = isset( $data['categories'] ) && is_array( $data['categories'] ) ? $data['categories'] : array();

	if ( empty( $categories ) ) {
		return;
	}

	$schema_entities = array();
	$item_counter    = 0;

	foreach ( $categories as $category ) {
		$items = isset( $category['items'] ) && is_array( $category['items'] ) ? $category['items'] : array();
		foreach ( $items as $item ) {
			$q = isset( $item['question'] ) ? $item['question'] : '';
			$a = isset( $item['answer'] )   ? $item['answer']   : '';
			if ( '' !== $q && '' !== $a ) {
				$schema_entities[] = array(
					'@type'          => 'Question',
					'name'           => $q,
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $a,
					),
				);
			}
		}
	}
	?>
	<section class="faqs-content">
		<div class="container mx-auto px-4">
			<div class="faqs-content__inner" data-aos="fade-up">

				<?php foreach ( $categories as $cat_index => $category ) :
					$cat_title = isset( $category['title'] ) ? $category['title'] : '';
					$items     = isset( $category['items'] ) && is_array( $category['items'] ) ? $category['items'] : array();
					if ( empty( $items ) ) {
						continue;
					}
					?>
					<div class="faqs-content__category">
						<?php if ( '' !== $cat_title ) : ?>
							<h2 class="faqs-content__category-title"><?php echo esc_html( $cat_title ); ?></h2>
						<?php endif; ?>

						<dl class="faqs-content__list">
							<?php foreach ( $items as $item ) :
								$item_counter++;
								$question = isset( $item['question'] ) ? $item['question'] : '';
								$answer   = isset( $item['answer'] )   ? $item['answer']   : '';
								if ( '' === $question || '' === $answer ) {
									continue;
								}
								$item_id = 'faq-item-' . $item_counter;
								?>
								<div class="faqs-content__item">
									<dt class="faqs-content__question">
										<button
											class="faqs-content__toggle"
											aria-expanded="false"
											aria-controls="<?php echo esc_attr( $item_id ); ?>"
											type="button"
										>
											<span><?php echo esc_html( $question ); ?></span>
											<span class="faqs-content__icon" aria-hidden="true"></span>
										</button>
									</dt>
									<dd class="faqs-content__answer" id="<?php echo esc_attr( $item_id ); ?>" hidden>
										<p><?php echo esc_html( $answer ); ?></p>
									</dd>
								</div>
							<?php endforeach; ?>
						</dl>
					</div>
				<?php endforeach; ?>

			</div>
		</div>
	</section>

	<?php if ( ! empty( $schema_entities ) ) : ?>
	<script type="application/ld+json">
	<?php echo wp_json_encode(
		array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $schema_entities,
		),
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
	); ?>
	</script>
	<?php endif; ?>

	<script>
	(function () {
		document.addEventListener('DOMContentLoaded', function () {
			var inner = document.querySelector('.faqs-content__inner');
			if (!inner) return;

			inner.addEventListener('click', function (e) {
				var btn = e.target.closest('.faqs-content__toggle');
				if (!btn) return;

				var item   = btn.closest('.faqs-content__item');
				var answer = document.getElementById(btn.getAttribute('aria-controls'));
				var open   = btn.getAttribute('aria-expanded') === 'true';

				inner.querySelectorAll('.faqs-content__item').forEach(function (other) {
					if (other === item) return;
					var otherBtn    = other.querySelector('.faqs-content__toggle');
					var otherAnswer = document.getElementById(otherBtn.getAttribute('aria-controls'));
					otherBtn.setAttribute('aria-expanded', 'false');
					otherAnswer.hidden = true;
					other.classList.remove('faqs-content__item--open');
				});

				btn.setAttribute('aria-expanded', String(!open));
				answer.hidden = open;
				item.classList.toggle('faqs-content__item--open', !open);
			});
		});
	}());
	</script>
	<?php
}
