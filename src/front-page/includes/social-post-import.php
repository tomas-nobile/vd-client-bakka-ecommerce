<?php
/**
 * Admin: Importador de publicaciones de Instagram
 *
 * Agrega un submenú bajo "Posteos Sociales" con un drag-and-drop para
 * cargar el JSON exportado por la extensión de Chrome.
 * Procesa un post por request AJAX para evitar timeouts.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Registro de menú ──────────────────────────────────────────────────────────

add_action( 'admin_menu', 'etheme_register_instagram_import_page' );

function etheme_register_instagram_import_page() {
	add_submenu_page(
		'edit.php?post_type=social_post',
		__( 'Importar desde Instagram', 'etheme' ),
		__( 'Importar', 'etheme' ),
		'manage_options',
		'etheme-import-instagram',
		'etheme_render_instagram_import_page'
	);
}

// ── Render de la página ───────────────────────────────────────────────────────

function etheme_render_instagram_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$nonce   = wp_create_nonce( 'etheme_import_instagram' );
	$ajaxurl = esc_url( admin_url( 'admin-ajax.php' ) );
	?>
	<div class="wrap" id="etheme-import-wrap">
		<h1><?php esc_html_e( 'Importar publicaciones de Instagram', 'etheme' ); ?></h1>
		<p class="description" style="margin-bottom:24px">
			<?php esc_html_e( 'Arrastrá el JSON exportado por la extensión de Chrome o hacé clic para seleccionarlo.', 'etheme' ); ?>
		</p>

		<!-- Drop zone -->
		<div id="ig-drop-zone">
			<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/><polyline points="16 8 12 4 8 8"/><line x1="12" y1="4" x2="12" y2="16"/></svg>
			<p id="ig-drop-label"><?php esc_html_e( 'Soltá el archivo JSON acá', 'etheme' ); ?></p>
			<p style="color:#aaa;font-size:12px"><?php esc_html_e( 'o', 'etheme' ); ?></p>
			<label class="button" for="ig-file-input"><?php esc_html_e( 'Seleccionar archivo', 'etheme' ); ?></label>
			<input type="file" id="ig-file-input" accept=".json" style="display:none">
		</div>

		<!-- Preview -->
		<div id="ig-preview" style="display:none">
			<div id="ig-preview-info"></div>
			<div style="margin-top:12px;display:flex;gap:10px">
				<button class="button button-primary" id="ig-btn-import"><?php esc_html_e( 'Importar', 'etheme' ); ?></button>
				<button class="button" id="ig-btn-cancel-preview"><?php esc_html_e( 'Cancelar', 'etheme' ); ?></button>
			</div>
		</div>

		<!-- Progreso -->
		<div id="ig-progress-wrap" style="display:none">
			<div style="display:flex;justify-content:space-between;margin-bottom:6px">
				<span id="ig-progress-label" style="font-size:13px;color:#555"><?php esc_html_e( 'Importando...', 'etheme' ); ?></span>
				<span id="ig-progress-count" style="font-size:13px;font-weight:600;color:#2271b1">0 / 0</span>
			</div>
			<div style="background:#f0f0f1;border-radius:4px;height:8px;overflow:hidden">
				<div id="ig-progress-bar" style="height:100%;width:0%;background:#2271b1;transition:width .3s;border-radius:4px"></div>
			</div>
			<button class="button" id="ig-btn-stop" style="margin-top:12px"><?php esc_html_e( 'Detener', 'etheme' ); ?></button>
		</div>

		<!-- Log -->
		<div id="ig-log" style="display:none">
			<h3 style="margin:20px 0 8px;font-size:13px"><?php esc_html_e( 'Detalle', 'etheme' ); ?></h3>
			<div id="ig-log-inner"></div>
		</div>
	</div>

	<style>
	#ig-drop-zone {
		border: 2px dashed #c3c4c7;
		border-radius: 8px;
		padding: 40px 20px;
		text-align: center;
		cursor: pointer;
		transition: border-color .2s, background .2s;
		max-width: 520px;
		color: #555;
	}
	#ig-drop-zone.over { border-color: #2271b1; background: #f0f6ff; }
	#ig-drop-zone svg  { margin-bottom: 10px; color: #2271b1; }
	#ig-drop-zone p    { margin: 4px 0; }
	#ig-preview {
		max-width: 520px;
		background: #f6f7f7;
		border: 1px solid #c3c4c7;
		border-radius: 8px;
		padding: 16px 20px;
		margin-top: 16px;
	}
	#ig-preview-info strong { color: #2271b1; font-size: 22px; }
	#ig-progress-wrap { max-width: 520px; margin-top: 20px; }
	#ig-log-inner {
		max-width: 520px;
		max-height: 300px;
		overflow-y: auto;
		font-size: 12px;
		font-family: monospace;
		background: #1e1e1e;
		color: #d4d4d4;
		border-radius: 6px;
		padding: 12px 16px;
		line-height: 1.7;
	}
	.ig-log-ok     { color: #4ec9b0; }
	.ig-log-skip   { color: #9cdcfe; }
	.ig-log-error  { color: #f48771; }
	.ig-log-done   { color: #dcdcaa; font-weight: bold; }
	</style>

	<script>
	(function () {
		const ajaxurl = <?php echo wp_json_encode( $ajaxurl ); ?>;
		const nonce   = <?php echo wp_json_encode( $nonce ); ?>;

		const dropZone     = document.getElementById('ig-drop-zone');
		const fileInput    = document.getElementById('ig-file-input');
		const preview      = document.getElementById('ig-preview');
		const previewInfo  = document.getElementById('ig-preview-info');
		const progressWrap = document.getElementById('ig-progress-wrap');
		const progressBar  = document.getElementById('ig-progress-bar');
		const progressLbl  = document.getElementById('ig-progress-label');
		const progressCnt  = document.getElementById('ig-progress-count');
		const logWrap      = document.getElementById('ig-log');
		const logInner     = document.getElementById('ig-log-inner');

		let posts     = [];
		let cancelled = false;

		// ── Drag & drop ───────────────────────────────────────────────────────

		dropZone.addEventListener('dragover',  (e) => { e.preventDefault(); dropZone.classList.add('over'); });
		dropZone.addEventListener('dragleave', ()  => dropZone.classList.remove('over'));
		dropZone.addEventListener('drop',      (e) => { e.preventDefault(); dropZone.classList.remove('over'); readFile(e.dataTransfer.files[0]); });
		dropZone.addEventListener('click',     ()  => fileInput.click());
		fileInput.addEventListener('change',   (e) => readFile(e.target.files[0]));

		// ── Leer JSON ─────────────────────────────────────────────────────────

		function readFile(file) {
			if (!file || !file.name.endsWith('.json')) {
				alert('Seleccioná un archivo .json válido.');
				return;
			}
			const reader = new FileReader();
			reader.onload = (e) => {
				try {
					posts = JSON.parse(e.target.result);
					if (!Array.isArray(posts) || posts.length === 0) throw new Error();
					showPreview();
				} catch (_) {
					alert('El archivo no tiene el formato correcto.');
				}
			};
			reader.readAsText(file);
		}

		function showPreview() {
			const videos = posts.reduce((acc, p) => acc + (p.media?.filter(m => m.type === 'video').length || 0), 0);
			const images = posts.reduce((acc, p) => acc + (p.media?.filter(m => m.type === 'image').length || 0), 0);
			previewInfo.innerHTML =
				`<strong>${posts.length}</strong> publicaciones encontradas<br>` +
				`<span style="font-size:12px;color:#777">${images} imágenes · ${videos} videos · se descargarán a la librería de medios</span>`;
			dropZone.style.display = 'none';
			preview.style.display  = 'block';
		}

		// ── Cancelar preview ──────────────────────────────────────────────────

		document.getElementById('ig-btn-cancel-preview').addEventListener('click', () => {
			posts = [];
			preview.style.display  = 'none';
			dropZone.style.display = 'block';
			fileInput.value        = '';
		});

		// ── Importar ──────────────────────────────────────────────────────────

		document.getElementById('ig-btn-import').addEventListener('click', () => {
			if (!posts.length) return;
			cancelled = false;
			preview.style.display      = 'none';
			progressWrap.style.display = 'block';
			logWrap.style.display      = 'block';
			logInner.innerHTML         = '';
			importBatch(posts, 0, 0, 0);
		});

		document.getElementById('ig-btn-stop').addEventListener('click', () => {
			cancelled = true;
		});

		async function importBatch(allPosts, index, created, skipped) {
			if (cancelled || index >= allPosts.length) {
				const errors = index - created - skipped;
				setProgress(index, allPosts.length);
				progressLbl.textContent = cancelled ? 'Importación detenida.' : '¡Importación completa!';
				progressBar.style.background = cancelled ? '#dba617' : '#00a32a';
				document.getElementById('ig-btn-stop').style.display = 'none';
				logLine(
					`── Fin: ${created} creados · ${skipped} ya existían · ${errors} errores ──`,
					'done'
				);
				return;
			}

			const post = allPosts[index];
			setProgress(index + 1, allPosts.length);
			progressLbl.textContent = `Importando ${index + 1} de ${allPosts.length}…`;

			try {
				const body = new FormData();
				body.append('action',    'etheme_import_instagram_post');
				body.append('nonce',     nonce);
				body.append('post_data', JSON.stringify(post));

				const res  = await fetch(ajaxurl, { method: 'POST', body });
				const data = await res.json();

				if (data.success) {
					const type = data.data.status;
					logLine(`${type === 'created' ? '✓' : '–'} ${post.shortcode} — ${data.data.message}`, type === 'created' ? 'ok' : 'skip');
					importBatch(allPosts, index + 1, created + (type === 'created' ? 1 : 0), skipped + (type === 'skipped' ? 1 : 0));
				} else {
					logLine(`✗ ${post.shortcode} — ${data.data || 'error desconocido'}`, 'error');
					importBatch(allPosts, index + 1, created, skipped);
				}
			} catch (err) {
				logLine(`✗ ${post.shortcode} — error de red`, 'error');
				importBatch(allPosts, index + 1, created, skipped);
			}
		}

		function setProgress(current, total) {
			const pct = total > 0 ? (current / total) * 100 : 0;
			progressBar.style.width    = pct + '%';
			progressCnt.textContent    = `${current} / ${total}`;
		}

		function logLine(text, type = '') {
			const line = document.createElement('div');
			if (type) line.className = `ig-log-${type}`;
			line.textContent = text;
			logInner.appendChild(line);
			logInner.scrollTop = logInner.scrollHeight;
		}
	})();
	</script>
	<?php
}

// ── AJAX handler ──────────────────────────────────────────────────────────────

add_action( 'wp_ajax_etheme_import_instagram_post', 'etheme_ajax_import_instagram_post' );

function etheme_ajax_import_instagram_post() {
	check_ajax_referer( 'etheme_import_instagram', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Sin permisos.' );
	}

	$raw = isset( $_POST['post_data'] ) ? wp_unslash( $_POST['post_data'] ) : '';
	$data = json_decode( $raw, true );

	if ( ! is_array( $data ) ) {
		wp_send_json_error( 'Datos inválidos.' );
	}

	$shortcode = sanitize_text_field( $data['shortcode'] ?? '' );

	if ( ! $shortcode ) {
		wp_send_json_error( 'Sin shortcode.' );
	}

	// ── Deduplicación ─────────────────────────────────────────────────────────

	$existing = get_posts( array(
		'post_type'   => 'social_post',
		'meta_key'    => '_instagram_shortcode',
		'meta_value'  => $shortcode,
		'post_status' => 'any',
		'numberposts' => 1,
		'fields'      => 'ids',
	) );

	if ( ! empty( $existing ) ) {
		wp_send_json_success( array( 'status' => 'skipped', 'message' => 'ya existe' ) );
	}

	// ── Fecha y título ────────────────────────────────────────────────────────

	$date_iso  = $data['date'] ?? '';
	$ts        = $date_iso ? strtotime( $date_iso ) : time();
	$date_ymd  = gmdate( 'Y-m-d', $ts );
	$post_date = gmdate( 'Y-m-d H:i:s', $ts );

	$caption    = sanitize_textarea_field( $data['caption'] ?? '' );
	$post_title = $caption ? wp_trim_words( $caption, 8, '' ) ?: $shortcode : $shortcode;

	// ── Crear post ────────────────────────────────────────────────────────────

	$post_id = wp_insert_post( array(
		'post_title'  => $post_title,
		'post_status' => 'publish',
		'post_type'   => 'social_post',
		'post_date'   => $post_date,
	) );

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( $post_id->get_error_message() );
	}

	update_post_meta( $post_id, '_instagram_shortcode', $shortcode );

	// ── Descargar media ───────────────────────────────────────────────────────

	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

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
			$att_id = etheme_import_sideload( $url, $post_id, "ig-{$shortcode}-{$idx}.jpg" );
			if ( $att_id ) {
				$attachment_ids[] = $att_id;
				$ordered_media[]  = array( 'type' => 'image', 'id' => $att_id );
				if ( ! $first_image_id ) $first_image_id = $att_id;
			}
		} elseif ( $type === 'video' ) {
			$att_id       = etheme_import_sideload( $url, $post_id, "ig-{$shortcode}-{$idx}.mp4" );
			$thumb_url    = $item['thumbnail'] ?? '';
			$thumb_att_id = 0;
			if ( $thumb_url ) {
				$thumb_att_id = etheme_import_sideload( $thumb_url, $post_id, "ig-{$shortcode}-{$idx}-thumb.jpg" );
			}
			if ( $att_id ) {
				$att_url      = wp_get_attachment_url( $att_id );
				$video_urls[] = $att_url;
				$media_entry  = array( 'type' => 'video', 'url' => $att_url );
				if ( $thumb_att_id ) $media_entry['thumbnail_id'] = $thumb_att_id;
				$ordered_media[] = $media_entry;
				if ( ! $first_image_id && $thumb_att_id ) $first_image_id = $thumb_att_id;
			} else {
				$video_urls[]    = $url;
				$media_entry     = array( 'type' => 'video', 'url' => $url );
				if ( $thumb_att_id ) $media_entry['thumbnail_id'] = $thumb_att_id;
				$ordered_media[] = $media_entry;
			}
		}
	}

	// ── Meta fields ───────────────────────────────────────────────────────────

	if ( $first_image_id ) set_post_thumbnail( $post_id, $first_image_id );

	update_post_meta( $post_id, 'social_post_description', $caption );
	update_post_meta( $post_id, 'social_post_network',     'instagram' );
	update_post_meta( $post_id, 'social_post_date',        $date_ymd );

	if ( ! empty( $ordered_media ) ) {
		update_post_meta( $post_id, 'social_post_media',  wp_json_encode( $ordered_media ) );
	}
	if ( ! empty( $attachment_ids ) ) {
		update_post_meta( $post_id, 'social_post_images', wp_json_encode( $attachment_ids ) );
	}
	if ( ! empty( $video_urls ) ) {
		update_post_meta( $post_id, 'social_post_videos', wp_json_encode( $video_urls ) );
	}

	$media_count = count( $media_items );
	wp_send_json_success( array(
		'status'  => 'created',
		'post_id' => $post_id,
		'message' => sprintf( '%d media descargados', $media_count ),
	) );
}

/**
 * Descarga un archivo externo y lo agrega a la librería de medios.
 *
 * @return int Attachment ID, 0 en caso de error.
 */
function etheme_import_sideload( string $url, int $post_id, string $filename ): int {
	$tmp = download_url( $url );
	if ( is_wp_error( $tmp ) ) return 0;

	$file   = array( 'name' => $filename, 'tmp_name' => $tmp );
	$att_id = media_handle_sideload( $file, $post_id );

	if ( file_exists( $tmp ) ) @unlink( $tmp );

	return is_wp_error( $att_id ) ? 0 : $att_id;
}
