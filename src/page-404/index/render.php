<?php
// page-404.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/src/page-404/components/error-content.php';
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'page-404-block' ) ); ?>>
	<?php etheme_render_404_content(); ?>
</div>
