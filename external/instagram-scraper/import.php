<?php
/**
 * Instagram → WordPress Import Script
 *
 * Lee el JSON exportado por la extensión de Chrome y crea entradas en el CPT social_post.
 * Descarga imágenes y videos a la librería de medios de WordPress.
 * Usa el shortcode de Instagram como ID único para evitar duplicados.
 *
 * Uso:
 *   wp eval-file wp-content/themes/bakka/external/instagram-scraper/import.php -- /ruta/instagram-posts-2024-01-01.json
 *
 * Opciones:
 *   --dry-run   Simula la importación sin crear nada.
 *
 * Notas:
 *   - Las URLs de Instagram expiran en ~24 hs. Corré el script el mismo día del export.
 *   - Si una descarga falla, el post se crea igual sin esa imagen (se loguea el error).
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( "Corré este script con WP-CLI:\n  wp eval-file import.php -- posts.json\n" );
}

// ── Argumentos ───────────────────────────────────────────────────────────────

$json_path = $args[0] ?? '';
$dry_run   = in_array( '--dry-run', $args, true );

if ( ! $json_path || ! file_exists( $json_path ) ) {
	WP_CLI::error( "Archivo no encontrado: {$json_path}" );
	return;
}

if ( $dry_run ) {
	WP_CLI::log( '⚠  DRY RUN — no se creará nada.' );
}

// ── Cargar JSON ───────────────────────────────────────────────────────────────

$raw   = file_get_contents( $json_path );
$posts = json_decode( $raw, true );

if ( ! is_array( $posts ) || empty( $posts ) ) {
	WP_CLI::error( 'El archivo JSON está vacío o tiene formato incorrecto.' );
	return;
}

WP_CLI::log( sprintf( 'Total a procesar: %d publicaciones.', count( $posts ) ) );

// ── WordPress media helpers ───────────────────────────────────────────────────

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// ── Importación ───────────────────────────────────────────────────────────────

$created = 0;
$skipped = 0;
$errors  = 0;

foreach ( $posts as $i => $data ) {
	$shortcode = sanitize_text_field( $data['shortcode'] ?? '' );

	if ( ! $shortcode ) {
		WP_CLI::warning( "  [{$i}] Sin shortcode — saltado." );
		$errors++;
		continue;
	}

	// ── Deduplicación ────────────────────────────────────────────────────────

	$existing = get_posts( array(
		'post_type'   => 'social_post',
		'meta_key'    => '_instagram_shortcode',
		'meta_value'  => $shortcode,
		'post_status' => 'any',
		'numberposts' => 1,
		'fields'      => 'ids',
	) );

	if ( ! empty( $existing ) ) {
		WP_CLI::log( "  SKIP  {$shortcode}" );
		$skipped++;
		continue;
	}

	// ── Fecha ─────────────────────────────────────────────────────────────────

	$date_iso  = $data['date'] ?? '';
	$ts        = $date_iso ? strtotime( $date_iso ) : time();
	$date_ymd  = gmdate( 'Y-m-d', $ts );
	$post_date = gmdate( 'Y-m-d H:i:s', $ts );

	// ── Título (primeras palabras del caption, fallback al shortcode) ─────────

	$caption   = sanitize_textarea_field( $data['caption'] ?? '' );
	$post_title = $caption
		? wp_trim_words( $caption, 8, '' ) ?: $shortcode
		: $shortcode;

	if ( $dry_run ) {
		WP_CLI::log( sprintf( '  DRY   %s — "%s" (%s)', $shortcode, $post_title, $date_ymd ) );
		$created++;
		continue;
	}

	// ── Crear post ────────────────────────────────────────────────────────────

	$post_id = wp_insert_post( array(
		'post_title'  => $post_title,
		'post_status' => 'publish',
		'post_type'   => 'social_post',
		'post_date'   => $post_date,
	) );

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( "  ERROR {$shortcode}: " . $post_id->get_error_message() );
		$errors++;
		continue;
	}

	// Guardar shortcode antes de procesar media (si algo falla, no queda huérfano)
	update_post_meta( $post_id, '_instagram_shortcode', $shortcode );

	// ── Descargar media ───────────────────────────────────────────────────────

	$media_items    = $data['media'] ?? array();
	$attachment_ids = array();
	$video_urls     = array();
	$ordered_media  = array();
	$first_image_id = 0;

	foreach ( $media_items as $idx => $item ) {
		$type = $item['type'] ?? 'image';
		$url  = $item['url']  ?? '';

		if ( ! $url ) continue;

		if ( $type === 'image' ) {
			$att_id = ig_download_image( $url, $post_id, $shortcode, $idx );
			if ( $att_id ) {
				$attachment_ids[] = $att_id;
				$ordered_media[]  = array( 'type' => 'image', 'id' => $att_id );
				if ( ! $first_image_id ) {
					$first_image_id = $att_id;
				}
			}
		} elseif ( $type === 'video' ) {
			$att_id = ig_download_video( $url, $post_id, $shortcode, $idx );
			if ( $att_id ) {
				$att_url         = wp_get_attachment_url( $att_id );
				$video_urls[]    = $att_url;
				$ordered_media[] = array( 'type' => 'video', 'url' => $att_url );
			} else {
				// Fallback: guardar URL original si la descarga falló
				$video_urls[]    = $url;
				$ordered_media[] = array( 'type' => 'video', 'url' => $url );
			}
		}
	}

	// ── Imagen destacada ──────────────────────────────────────────────────────

	if ( $first_image_id ) {
		set_post_thumbnail( $post_id, $first_image_id );
	}

	// ── Meta fields ───────────────────────────────────────────────────────────

	update_post_meta( $post_id, 'social_post_description', $caption );
	update_post_meta( $post_id, 'social_post_network',     'instagram' );
	update_post_meta( $post_id, 'social_post_date',        $date_ymd );

	if ( ! empty( $ordered_media ) ) {
		update_post_meta( $post_id, 'social_post_media', wp_json_encode( $ordered_media ) );
	}
	if ( ! empty( $attachment_ids ) ) {
		update_post_meta( $post_id, 'social_post_images', wp_json_encode( $attachment_ids ) );
	}
	if ( ! empty( $video_urls ) ) {
		update_post_meta( $post_id, 'social_post_videos', wp_json_encode( $video_urls ) );
	}

	WP_CLI::log( sprintf(
		'  OK    %s — %d media — "%s"',
		$shortcode,
		count( $media_items ),
		wp_trim_words( $caption, 6, '...' ) ?: '(sin caption)'
	) );

	$created++;

	// Pausa entre posts para no saturar el CDN de Instagram
	usleep( 300000 ); // 300 ms
}

// ── Resumen ───────────────────────────────────────────────────────────────────

WP_CLI::success( sprintf(
	'%d creados — %d ya existían (saltados) — %d errores.',
	$created,
	$skipped,
	$errors
) );

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Descarga una imagen de Instagram y la agrega a la librería de medios.
 *
 * @return int Attachment ID, 0 en caso de error.
 */
function ig_download_image( string $url, int $post_id, string $shortcode, int $idx ): int {
	$tmp = download_url( $url );

	if ( is_wp_error( $tmp ) ) {
		WP_CLI::warning( "    No se pudo descargar imagen [{$idx}]: " . $tmp->get_error_message() );
		return 0;
	}

	$filename = "ig-{$shortcode}-{$idx}.jpg";
	$file     = array( 'name' => $filename, 'tmp_name' => $tmp );
	$att_id   = media_handle_sideload( $file, $post_id );

	if ( file_exists( $tmp ) ) {
		@unlink( $tmp );
	}

	if ( is_wp_error( $att_id ) ) {
		WP_CLI::warning( "    No se pudo adjuntar imagen [{$idx}]: " . $att_id->get_error_message() );
		return 0;
	}

	return $att_id;
}

/**
 * Descarga un video de Instagram y lo agrega a la librería de medios.
 *
 * @return int Attachment ID, 0 en caso de error.
 */
function ig_download_video( string $url, int $post_id, string $shortcode, int $idx ): int {
	$tmp = download_url( $url );

	if ( is_wp_error( $tmp ) ) {
		WP_CLI::warning( "    No se pudo descargar video [{$idx}]: " . $tmp->get_error_message() );
		return 0;
	}

	$filename = "ig-{$shortcode}-{$idx}.mp4";
	$file     = array( 'name' => $filename, 'tmp_name' => $tmp );
	$att_id   = media_handle_sideload( $file, $post_id );

	if ( file_exists( $tmp ) ) {
		@unlink( $tmp );
	}

	if ( is_wp_error( $att_id ) ) {
		WP_CLI::warning( "    No se pudo adjuntar video [{$idx}]: " . $att_id->get_error_message() );
		return 0;
	}

	return $att_id;
}
