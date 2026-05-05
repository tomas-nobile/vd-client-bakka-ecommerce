const DEFAULT_TARGET = 5;

const countEl   = document.getElementById('count');
const barEl     = document.getElementById('progress-bar');
const targetEl  = document.getElementById('target');
const statusEl  = document.getElementById('status');
const btnStart  = document.getElementById('btn-start');
const btnExport = document.getElementById('btn-export');
const btnClear  = document.getElementById('btn-clear');
const warningEl = document.getElementById('warning');

let isRunning = false;

// ── Render ────────────────────────────────────────────────────────────────────

function getTarget() {
	return Math.max(1, parseInt(targetEl.value, 10) || DEFAULT_TARGET);
}

function render(posts) {
	const n      = posts.length;
	const target = getTarget();
	countEl.textContent = n;
	barEl.style.width   = Math.min(100, (n / target) * 100) + '%';
	barEl.style.background = n >= target ? '#22c55e' : '#e1306c';
	btnExport.disabled  = n === 0;
	btnClear.disabled   = n === 0;
}

function setStatus(text, type = '') {
	statusEl.textContent  = text;
	statusEl.className    = 'status ' + type;
}

function setRunning(running) {
	isRunning            = running;
	btnStart.textContent = running ? 'Detener' : 'Iniciar scraping';
	btnStart.classList.toggle('running', running);
	if (running) setStatus('Scrapeando...', '');
}

// ── Init ──────────────────────────────────────────────────────────────────────

chrome.storage.local.get(['ig_posts', 'ig_target', 'ig_running'], ({ ig_posts = [], ig_target, ig_running }) => {
	if (ig_target) targetEl.value = ig_target;
	render(ig_posts);
	if (ig_running) setRunning(true);
});

// ── Live updates ──────────────────────────────────────────────────────────────

chrome.storage.onChanged.addListener((changes) => {
	if (changes.ig_posts) render(changes.ig_posts.newValue ?? []);
});

chrome.runtime.onMessage.addListener((message) => {
	if (message.type === 'IG_SCROLL_DONE') {
		setRunning(false);
		chrome.storage.local.set({ ig_running: false });
		chrome.storage.local.get(['ig_posts'], ({ ig_posts = [] }) => {
			const done = ig_posts.length >= getTarget();
			setStatus(
				done ? '¡Meta alcanzada! Exportá el JSON.' : `Fin del perfil. ${ig_posts.length} capturadas.`,
				done ? 'done' : 'idle'
			);
		});
	}
});

// ── Target input ──────────────────────────────────────────────────────────────

targetEl.addEventListener('input', () => {
	chrome.storage.local.set({ ig_target: getTarget() });
	chrome.storage.local.get(['ig_posts'], ({ ig_posts = [] }) => render(ig_posts));
});

// ── Botón Iniciar / Detener ───────────────────────────────────────────────────

btnStart.addEventListener('click', () => {
	chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
		const tab = tabs[0];
		const onInstagram = tab?.url?.includes('instagram.com');

		if (!onInstagram) {
			warningEl.classList.remove('hidden');
			return;
		}
		warningEl.classList.add('hidden');

		if (isRunning) {
			chrome.tabs.sendMessage(tab.id, { type: 'IG_STOP_SCROLL' });
			chrome.storage.local.set({ ig_running: false });
			setRunning(false);
			setStatus('Detenido.', 'idle');
		} else {
			chrome.tabs.sendMessage(tab.id, { type: 'IG_START_SCROLL' });
			chrome.storage.local.set({ ig_running: true });
			setRunning(true);
		}
	});
});

// ── Exportar ──────────────────────────────────────────────────────────────────

btnExport.addEventListener('click', () => {
	chrome.storage.local.get(['ig_posts'], ({ ig_posts = [] }) => {
		const json = JSON.stringify(ig_posts, null, 2);
		const blob = new Blob([json], { type: 'application/json' });
		const url  = URL.createObjectURL(blob);
		const date = new Date().toISOString().slice(0, 10);
		chrome.downloads.download({ url, filename: `instagram-posts-${date}.json`, saveAs: true });
	});
});

// ── Limpiar ───────────────────────────────────────────────────────────────────

btnClear.addEventListener('click', () => {
	chrome.storage.local.set({ ig_posts: [], ig_running: false });
	setRunning(false);
	setStatus('Listo para iniciar', 'idle');
});
