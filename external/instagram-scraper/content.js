// Corre en el mundo aislado (ISOLATED) — recibe posts de injected.js y maneja el auto-scroll.

(function () {
	const TAG = '[IG-Scraper]';

	// ── Recibir posts de injected.js ─────────────────────────────────────────

	window.addEventListener('message', (event) => {
		if (event.source !== window || event.data?.type !== 'IG_POSTS_FOUND') return;

		const incoming = event.data.posts;
		if (!Array.isArray(incoming) || incoming.length === 0) return;

		chrome.storage.local.get(['ig_posts'], ({ ig_posts: existing = [] }) => {
			const seen  = new Set(existing.map((p) => p.shortcode));
			const toAdd = incoming.filter((p) => p.shortcode && !seen.has(p.shortcode));
			if (toAdd.length === 0) {
				console.log(TAG, `${incoming.length} posts recibidos, todos ya estaban guardados`);
				return;
			}
			console.log(TAG, `agregando ${toAdd.length} posts nuevos (total: ${existing.length + toAdd.length})`);
			chrome.storage.local.set({ ig_posts: [...existing, ...toAdd] });
		});
	});

	// ── Auto-scroll ──────────────────────────────────────────────────────────

	let scrollTimer    = null;
	let lastCount      = 0;
	let staleChecks    = 0;
	const STALE_LIMIT  = 8;    // detiene si no llegan posts nuevos en N ciclos
	const SCROLL_STEP  = 1000; // px por ciclo
	const SCROLL_DELAY = 1800; // ms entre pasos

	// Encuentra el contenedor que realmente scrollea en esta página.
	// Instagram a veces scrollea window, a veces un wrapper interno.
	function findScrollTarget() {
		const docHeight = Math.max(
			document.body.scrollHeight,
			document.documentElement.scrollHeight
		);
		if (docHeight > window.innerHeight + 100) {
			return { type: 'window', el: null };
		}

		const all = document.querySelectorAll('main, [role="main"], div');
		for (const el of all) {
			if (el.scrollHeight > el.clientHeight + 100) {
				const style = getComputedStyle(el);
				if (style.overflowY === 'auto' || style.overflowY === 'scroll') {
					return { type: 'element', el };
				}
			}
		}

		return { type: 'window', el: null };
	}

	function doScroll(target) {
		if (target.type === 'window') {
			window.scrollBy({ top: SCROLL_STEP, behavior: 'smooth' });
		} else if (target.el) {
			target.el.scrollBy({ top: SCROLL_STEP, behavior: 'smooth' });
		}
	}

	function isAtBottom(target) {
		if (target.type === 'window') {
			return window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 300;
		}
		if (target.el) {
			return target.el.scrollTop + target.el.clientHeight >= target.el.scrollHeight - 300;
		}
		return false;
	}

	function startScroll() {
		if (scrollTimer) return;
		staleChecks = 0;

		const target = findScrollTarget();
		console.log(TAG, 'scroll target:', target.type, target.el?.tagName ?? '');

		chrome.storage.local.get(['ig_posts'], ({ ig_posts = [] }) => {
			lastCount = ig_posts.length;
		});

		scrollTimer = setInterval(() => {
			doScroll(target);

			chrome.storage.local.get(['ig_posts'], ({ ig_posts = [] }) => {
				const current = ig_posts.length;
				if (current > lastCount) {
					lastCount   = current;
					staleChecks = 0;
				} else {
					staleChecks++;
					console.log(TAG, `sin posts nuevos (${staleChecks}/${STALE_LIMIT})`);
				}

				if (isAtBottom(target) || staleChecks >= STALE_LIMIT) {
					console.log(TAG, 'fin del scroll. atBottom:', isAtBottom(target), 'stale:', staleChecks);
					stopScroll();
					chrome.runtime.sendMessage({ type: 'IG_SCROLL_DONE' });
				}
			});
		}, SCROLL_DELAY);
	}

	function stopScroll() {
		if (!scrollTimer) return;
		clearInterval(scrollTimer);
		scrollTimer = null;
	}

	// ── Mensajes desde el popup ──────────────────────────────────────────────

	chrome.runtime.onMessage.addListener((message) => {
		if (message.type === 'IG_START_SCROLL') {
			console.log(TAG, 'IG_START_SCROLL recibido');
			startScroll();
		}
		if (message.type === 'IG_STOP_SCROLL') {
			console.log(TAG, 'IG_STOP_SCROLL recibido');
			stopScroll();
		}
	});

	console.log(TAG, 'content.js cargado en ISOLATED world');
})();
