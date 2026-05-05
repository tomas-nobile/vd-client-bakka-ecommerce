// Corre en el mundo MAIN (contexto de la página) — declarado en manifest.json.
// No requiere inyección de <script>; Chrome lo carga directamente en el contexto de la página.

(function () {
	const TAG = '[IG-Scraper]';

	// ── fetch interceptor ────────────────────────────────────────────────────

	const _fetch = window.fetch;
	window.fetch = async function (...args) {
		const response = await _fetch.apply(this, args);
		const url = typeof args[0] === 'string' ? args[0] : (args[0]?.url ?? '');
		if (isIgDataUrl(url)) {
			console.log(TAG, 'fetch capturado:', url);
			response.clone().json().then(handleData).catch((err) => {
				console.warn(TAG, 'no es JSON:', url, err?.message);
			});
		}
		return response;
	};

	// ── XHR interceptor ──────────────────────────────────────────────────────

	const _open = XMLHttpRequest.prototype.open;
	const _send = XMLHttpRequest.prototype.send;

	XMLHttpRequest.prototype.open = function (method, url) {
		this._igUrl = url;
		return _open.apply(this, arguments);
	};

	XMLHttpRequest.prototype.send = function () {
		if (isIgDataUrl(this._igUrl ?? '')) {
			console.log(TAG, 'XHR capturado:', this._igUrl);
			this.addEventListener('load', () => {
				try { handleData(JSON.parse(this.responseText)); } catch (_) {}
			});
		}
		return _send.apply(this, arguments);
	};

	// ── Helpers ───────────────────────────────────────────────────────────────

	function isIgDataUrl(url) {
		if (!url || typeof url !== 'string') return false;
		// Cubre: /graphql/query, instagram.com/graphql, /api/v1/feed/user/, etc.
		return (
			url.includes('/graphql') ||
			url.includes('/api/v1/feed/') ||
			url.includes('/api/v1/clips/') ||
			url.includes('/api/v1/users/web_profile_info')
		);
	}

	function handleData(data) {
		const posts = extractPosts(data);
		if (posts.length > 0) {
			console.log(TAG, `parseados ${posts.length} posts del response`);
			window.postMessage({ type: 'IG_POSTS_FOUND', posts }, '*');
		} else {
			// Útil para descubrir nuevas formas del response
			console.log(TAG, 'response sin posts reconocibles. Keys:', Object.keys(data?.data ?? data ?? {}));
		}
	}

	function extractPosts(data) {
		const candidates = [
			data?.data?.user?.edge_owner_to_timeline_media?.edges,
			data?.data?.xdt_api__v1__feed__user_timeline_graphql_connection?.edges,
			data?.data?.edge_owner_to_timeline_media?.edges,
			data?.items?.map((item) => ({ node: item })),
		];

		for (const edges of candidates) {
			if (!Array.isArray(edges) || edges.length === 0) continue;
			const parsed = edges
				.map((edge) => { try { return parsePost(edge.node ?? edge); } catch (err) { console.warn(TAG, 'parsePost falló:', err?.message); return null; } })
				.filter((p) => p && p.shortcode && p.media.length > 0);
			if (parsed.length > 0) return parsed;
		}
		return [];
	}

	function parsePost(node) {
		const typename  = node.__typename ?? '';
		const mediaType = node.media_type;
		const media     = [];

		// Carousel: GraphSidecar (graphql viejo) o media_type 8 (api/v1)
		if (typename === 'GraphSidecar' || mediaType === 8) {
			const edges =
				node.edge_sidecar_to_children?.edges ??
				(node.carousel_media?.map((m) => ({ node: m })) ?? []);
			for (const edge of edges) {
				const item    = edge.node ?? edge;
				const isVideo = item.__typename === 'GraphVideo' || item.media_type === 2;
				media.push(isVideo
					? { type: 'video', url: bestVideoUrl(item), thumbnail: bestImageUrl(item) }
					: { type: 'image', url: bestImageUrl(item) });
			}
		} else if (typename === 'GraphVideo' || mediaType === 2) {
			media.push({ type: 'video', url: bestVideoUrl(node), thumbnail: bestImageUrl(node) });
		} else {
			media.push({ type: 'image', url: bestImageUrl(node) });
		}

		const caption = node.edge_media_to_caption?.edges?.[0]?.node?.text ?? node.caption?.text ?? '';
		const ts      = node.taken_at_timestamp ?? node.taken_at;

		return {
			shortcode:    node.shortcode ?? node.code ?? '',
			instagram_id: String(node.id ?? node.pk ?? ''),
			date:         ts ? new Date(ts * 1000).toISOString() : new Date().toISOString(),
			caption,
			network: 'instagram',
			media:   media.filter((m) => m.url),
		};
	}

	function bestImageUrl(node) {
		const c = node.image_versions2?.candidates;
		if (Array.isArray(c) && c.length > 0) return c[0].url ?? '';
		return node.display_url ?? node.thumbnail_src ?? '';
	}

	function bestVideoUrl(node) {
		const v = node.video_versions;
		if (Array.isArray(v) && v.length > 0) return v[0].url ?? '';
		return node.video_url ?? '';
	}

	console.log(TAG, 'injected.js cargado en MAIN world');
})();
