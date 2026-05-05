<?php

function etheme_hero_build_slides( $attributes, $theme_uri ) {
	$base_title = isset( $attributes['heroTitle'] ) ? $attributes['heroTitle'] : '';
	$titles     = array(
		$base_title,
		$attributes['heroTitle2'] ?? $base_title,
		$attributes['heroTitle3'] ?? $base_title,
	);

	$base_id = intval( $attributes['heroImageId'] ?? 0 );
	$ids     = array(
		$base_id,
		intval( $attributes['heroImageId2'] ?? 0 ),
		intval( $attributes['heroImageId3'] ?? 0 ),
	);

	$default_image = $theme_uri . '/assets/images/banner-image.png';
	$slides        = array();

	foreach ( $titles as $index => $raw_title ) {
		$id       = $ids[ $index ] ?: $base_id;
		$img_url  = $id ? wp_get_attachment_image_url( $id, 'large' ) : '';
		$slides[] = array(
			'title'      => esc_html( $raw_title ),
			'hero_image' => $img_url ?: $default_image,
		);
	}

	return $slides;
}

function etheme_hero_parse_attributes( $attributes ) {
	$theme_uri = get_template_directory_uri();
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

	return array(
		'subtitle'        => esc_html( $attributes['heroSubtitle'] ),
		'description'     => esc_html( $attributes['heroDescription'] ),
		'cta_text'        => esc_html( $attributes['heroCtaText'] ),
		'cta_url'         => esc_url( $shop_url ),
		'discount_number' => esc_html( $attributes['heroDiscountNumber'] ),
		'discount_label'  => esc_html( $attributes['heroDiscountLabel'] ),
		'discount_sub'    => esc_html( $attributes['heroDiscountSublabel'] ),
		'slides'          => etheme_hero_build_slides( $attributes, $theme_uri ),
		'theme_uri'       => $theme_uri,
	);
}

function etheme_hero_side_images( $theme_uri ) {
	?>
	<figure class="banner-leftimage image mb-0">
		<img src="<?php echo esc_url( $theme_uri . '/assets/images/update-leftimage.png' ); ?>" class="img-fluid" alt="">
	</figure>
	<figure class="banner-rightimage image mb-0">
		<img src="<?php echo esc_url( $theme_uri . '/assets/images/about-rightimage.png' ); ?>" class="img-fluid" alt="">
	</figure>
	<?php
}

function etheme_hero_slide_content( $data ) {
	?>
	<div class="col-lg-6 col-md-12 col-12">
		<div class="banner_content" data-aos="fade-up">
			<h6><?php echo $data['subtitle']; ?></h6>
			<h1><?php echo $data['title']; ?></h1>
			<p class="text"><?php echo $data['description']; ?></p>
			<a href="<?php echo $data['cta_url']; ?>" class="text-decoration-none primary_btn">
				<?php echo $data['cta_text']; ?>
			</a>
		</div>
	</div>
	<?php
}

function etheme_hero_slide_image( $data ) {
	$theme_uri = $data['theme_uri'];
	?>
	<div class="col-lg-6 col-md-12 col-12 text-center">
		<div class="banner_wrapper position-relative" data-aos="fade-up">
			<figure class="banner-image mb-0">
				<img src="<?php echo esc_url( $data['hero_image'] ); ?>" alt="">
			</figure>
		</div>
	</div>
	<?php
}

function etheme_hero_carousel_slides( $data ) {
	$slides = isset( $data['slides'] ) ? $data['slides'] : array();
	if ( empty( $slides ) ) {
		return;
	}

	$common = $data;
	unset( $common['slides'] );

	foreach ( $slides as $index => $slide ) {
		$active = 0 === $index ? ' active' : '';
		$slide_data = array_merge(
			$common,
			array(
				'title'      => $slide['title'],
				'hero_image' => $slide['hero_image'],
			)
		);
		?>
		<div class="carousel-item<?php echo $active; ?>">
			<div class="row">
				<?php
				etheme_hero_slide_content( $slide_data );
				etheme_hero_slide_image( $slide_data );
				?>
			</div>
		</div>
		<?php
	}
}

function etheme_hero_indicators( $count ) {
	?>
	<ul class="carousel-indicators">
		<?php for ( $i = 0; $i < $count; $i++ ) : ?>
			<li data-target="#bannerCarouselControls"
				data-slide-to="<?php echo $i; ?>"
				<?php echo 0 === $i ? 'class="active" aria-current="true"' : ''; ?>></li>
		<?php endfor; ?>
	</ul>
	<?php
}

function etheme_hero_nav_arrows( $theme_uri ) {
	?>
	<div class="pagination-outer">
		<a class="carousel-control-prev" href="#bannerCarouselControls" role="button" data-slide="prev">
			<img src="<?php echo esc_url( $theme_uri . '/assets/images/banner-leftarrow.png' ); ?>"
				alt="<?php esc_attr_e( 'Previous', 'etheme' ); ?>" class="next-arrow img-fluid">
			<span class="sr-only"><?php esc_html_e( 'Previous', 'etheme' ); ?></span>
		</a>
		<a class="carousel-control-next" href="#bannerCarouselControls" role="button" data-slide="next">
			<img src="<?php echo esc_url( $theme_uri . '/assets/images/banner-rightarrow.png' ); ?>"
				alt="<?php esc_attr_e( 'Next', 'etheme' ); ?>" class="next-arrow img-fluid">
			<span class="sr-only"><?php esc_html_e( 'Next', 'etheme' ); ?></span>
		</a>
	</div>
	<?php
}

function etheme_hero_carousel( $data ) {
	$slides = isset( $data['slides'] ) ? $data['slides'] : array();
	$count  = count( $slides );
	if ( $count < 1 ) {
		return;
	}
	?>
	<div id="bannerCarouselControls" class="carousel slide" data-ride="carousel">
		<?php etheme_hero_indicators( $count ); ?>
		<div class="carousel-inner">
			<?php etheme_hero_carousel_slides( $data ); ?>
		</div>
		<?php etheme_hero_nav_arrows( $data['theme_uri'] ); ?>
	</div>
	<?php
}

function etheme_hero_carousel_js() {
	?>
	<script>
	(function () {
		document.addEventListener('DOMContentLoaded', function () {
			var el = document.getElementById('bannerCarouselControls');
			if (!el) return;
			var items = el.querySelectorAll('.carousel-item');
			var dots  = el.querySelectorAll('.carousel-indicators li');
			var cur   = 0;
			var timer = null;
			var transitioning = false;

			function clearDirectionClasses() {
				items.forEach(function (item) {
					item.classList.remove('carousel-item-next', 'carousel-item-prev', 'carousel-item-left', 'carousel-item-right');
				});
			}

			function go(index) {
				if (transitioning) return;
				var next = (index + items.length) % items.length;
				if (next === cur) return;

				transitioning = true;
				clearDirectionClasses();

				var dir = next > cur || (cur === items.length - 1 && next === 0) ? 'next' : 'prev';

				if (dir === 'next') {
					items[cur].classList.add('carousel-item-right');
					items[next].classList.add('carousel-item-next');
					items[next].offsetHeight;
					items[next].classList.add('carousel-item-left');
				} else {
					items[cur].classList.add('carousel-item-left');
					items[next].classList.add('carousel-item-prev');
					items[next].offsetHeight;
					items[next].classList.add('carousel-item-right');
				}

				function onTransitionEnd(e) {
					if (e.target !== items[cur] && e.target !== items[next]) return;
					items[cur].removeEventListener('transitionend', onTransitionEnd);
					items[next].removeEventListener('transitionend', onTransitionEnd);
					items[cur].classList.remove('active', 'carousel-item-left', 'carousel-item-right');
					items[next].classList.remove('carousel-item-next', 'carousel-item-prev', 'carousel-item-left', 'carousel-item-right');
					items[next].classList.add('active');
					dots[cur].classList.remove('active');
					dots[next].classList.add('active');
					cur = next;
					transitioning = false;
				}

				items[cur].addEventListener('transitionend', onTransitionEnd);
				items[next].addEventListener('transitionend', onTransitionEnd);
			}

			function reset() {
				clearInterval(timer);
				if (!document.hidden) {
					timer = setInterval(function () { go(cur + 1); }, 5000);
				}
			}

			document.addEventListener('visibilitychange', function () {
				if (document.hidden) {
					clearInterval(timer);
				} else {
					reset();
				}
			});

			el.querySelector('.carousel-control-next').addEventListener('click', function (e) {
				e.preventDefault(); go(cur + 1); reset();
			});
			el.querySelector('.carousel-control-prev').addEventListener('click', function (e) {
				e.preventDefault(); go(cur - 1); reset();
			});
			dots.forEach(function (d, i) {
				d.addEventListener('click', function () { go(i); reset(); });
			});

			// Touch/swipe support: change slide by dragging with finger
			var touchStartX = 0;
			var touchEndX = 0;
			var minSwipeDistance = 50;

			el.addEventListener('touchstart', function (e) {
				touchStartX = e.changedTouches ? e.changedTouches[0].screenX : e.screenX;
			}, { passive: true });

			el.addEventListener('touchend', function (e) {
				touchEndX = e.changedTouches ? e.changedTouches[0].screenX : e.screenX;
				var diff = touchStartX - touchEndX;
				if (Math.abs(diff) >= minSwipeDistance) {
					if (diff > 0) {
						go(cur + 1);
					} else {
						go(cur - 1);
					}
					reset();
				}
			}, { passive: true });

			reset();
		});
	}());
	</script>
	<?php
}

function etheme_render_home_hero( $attributes ) {
	$data = etheme_hero_parse_attributes( $attributes );
	?>
	<section class="banner-con position-relative">
		<?php etheme_hero_side_images( $data['theme_uri'] ); ?>
		<div class="container position-relative">
			<?php etheme_hero_carousel( $data ); ?>
		</div>
		<?php etheme_hero_carousel_js(); ?>
	</section>
	<?php
}
