<?php
// social-post.metabox.
/**
 * Native metabox for Social Post CPT: description, date, social network.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Meta keys for social_post */
define( 'ETHEME_SOCIAL_POST_META_DESCRIPTION', 'social_post_description' );
define( 'ETHEME_SOCIAL_POST_META_DATE', 'social_post_date' );
define( 'ETHEME_SOCIAL_POST_META_NETWORK', 'social_post_network' );
define( 'ETHEME_SOCIAL_POST_META_LINK', 'social_post_link' );

/** Allowed social network values (default: instagram) */
function etheme_get_social_post_network_options() {
	return array(
		'instagram' => __( 'Instagram', 'etheme' ),
		'facebook'  => __( 'Facebook', 'etheme' ),
		'tiktok'    => __( 'TikTok', 'etheme' ),
		'pinterest' => __( 'Pinterest', 'etheme' ),
	);
}

function etheme_add_social_post_metabox() {
	add_meta_box(
		'social_post_fields',
		__( 'Datos del posteo social', 'etheme' ),
		'etheme_render_social_post_metabox',
		'social_post',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'etheme_add_social_post_metabox' );

/**
 * Render metabox HTML.
 *
 * @param WP_Post $post Current post.
 */
function etheme_render_social_post_metabox( $post ) {
	wp_nonce_field( 'etheme_social_post_metabox', 'etheme_social_post_metabox_nonce' );

	$description = get_post_meta( $post->ID, ETHEME_SOCIAL_POST_META_DESCRIPTION, true );
	$date        = get_post_meta( $post->ID, ETHEME_SOCIAL_POST_META_DATE, true );
	$network     = get_post_meta( $post->ID, ETHEME_SOCIAL_POST_META_NETWORK, true );
	$link        = get_post_meta( $post->ID, ETHEME_SOCIAL_POST_META_LINK, true );

	// Si no hay fecha guardada, usar la fecha de hoy como valor por defecto.
	if ( '' === $date ) {
		$date = current_time( 'Y-m-d' );
	}

	if ( ! $network ) {
		$network = 'instagram';
	}

	$network_options   = etheme_get_social_post_network_options();
	$category_terms    = get_terms( array( 'taxonomy' => 'posteo_category', 'hide_empty' => false ) );
	$assigned_term_ids = wp_get_object_terms( $post->ID, 'posteo_category', array( 'fields' => 'ids' ) );
	?>
	<table class="form-table" role="presentation">
		<?php if ( ! is_wp_error( $category_terms ) && ! empty( $category_terms ) ) : ?>
		<tr>
			<th scope="row"><label for="posteo_category_term"><?php esc_html_e( 'Categoría', 'etheme' ); ?></label></th>
			<td>
				<select name="posteo_category_term" id="posteo_category_term">
					<option value=""><?php esc_html_e( '— Ninguna —', 'etheme' ); ?></option>
					<?php
					$selected_id = ! empty( $assigned_term_ids ) ? (int) $assigned_term_ids[0] : 0;
					foreach ( $category_terms as $term ) :
					?>
						<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( $selected_id, $term->term_id ); ?>>
							<?php echo esc_html( $term->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<th scope="row"><label for="social_post_description"><?php esc_html_e( 'Descripción', 'etheme' ); ?></label></th>
			<td>
				<textarea name="social_post_description" id="social_post_description" class="large-text" rows="3"><?php echo esc_textarea( $description ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Texto que aparece después de @handle (ej. bakka.deco). Si está vacío se usa el extracto o el contenido del post.', 'etheme' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="social_post_date"><?php esc_html_e( 'Fecha', 'etheme' ); ?></label></th>
			<td>
				<input type="date" name="social_post_date" id="social_post_date" value="<?php echo esc_attr( $date ); ?>" class="regular-text" />
				<p class="description"><?php esc_html_e( 'Obligatoria. Si se deja vacío al guardar, se rellenará automáticamente con la fecha de hoy.', 'etheme' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="social_post_network"><?php esc_html_e( 'Red social', 'etheme' ); ?></label></th>
			<td>
				<select name="social_post_network" id="social_post_network">
					<?php foreach ( $network_options as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $network, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="social_post_link"><?php esc_html_e( 'Link del post (opcional)', 'etheme' ); ?></label></th>
			<td>
				<input
					type="url"
					name="social_post_link"
					id="social_post_link"
					value="<?php echo esc_attr( $link ); ?>"
					class="regular-text"
					placeholder="https://www.instagram.com/p/..."
				/>
				<p class="description">
					<?php esc_html_e( 'URL directa a la publicación en la red social (ej. post de Instagram). Si está vacío se usará el perfil genérico.', 'etheme' ); ?>
				</p>
			</td>
		</tr>
	</table>
	<?php
}

function etheme_save_social_post_metabox( $post_id ) {
	if ( ! isset( $_POST['etheme_social_post_metabox_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['etheme_social_post_metabox_nonce'] ) ), 'etheme_social_post_metabox' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'social_post' ) {
		return;
	}

	$description = isset( $_POST['social_post_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['social_post_description'] ) ) : '';
	$date        = isset( $_POST['social_post_date'] ) ? sanitize_text_field( wp_unslash( $_POST['social_post_date'] ) ) : '';
	$network     = isset( $_POST['social_post_network'] ) ? sanitize_text_field( wp_unslash( $_POST['social_post_network'] ) ) : 'instagram';
	$link        = isset( $_POST['social_post_link'] ) ? esc_url_raw( wp_unslash( $_POST['social_post_link'] ) ) : '';
	$allowed     = array_keys( etheme_get_social_post_network_options() );
	if ( ! in_array( $network, $allowed, true ) ) {
		$network = 'instagram';
	}

	// Si no se envía fecha o se deja en blanco, usar fecha de hoy como default obligatorio.
	if ( '' === $date ) {
		$date = current_time( 'Y-m-d' );
	}

	update_post_meta( $post_id, ETHEME_SOCIAL_POST_META_DESCRIPTION, $description );
	update_post_meta( $post_id, ETHEME_SOCIAL_POST_META_DATE, $date );
	update_post_meta( $post_id, ETHEME_SOCIAL_POST_META_NETWORK, $network );
	update_post_meta( $post_id, ETHEME_SOCIAL_POST_META_LINK, $link );

	$category_id = isset( $_POST['posteo_category_term'] ) ? absint( $_POST['posteo_category_term'] ) : 0;
	wp_set_object_terms( $post_id, $category_id > 0 ? array( $category_id ) : array(), 'posteo_category' );
}
add_action( 'save_post_social_post', 'etheme_save_social_post_metabox' );
