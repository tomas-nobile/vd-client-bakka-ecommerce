/**
 * Tailwind v4: main sources live in `src/index.css` (`@source`).
 * This file is kept for tooling that still reads `content` (exclude `build/` to avoid stale paths).
 */
module.exports = {
	content: [
		'./src/**/*.{php,html,js,jsx}',
		'./templates/**/*.{html,php}',
		'./*.{php,html,js}',
		'!./node_modules/**',
		'!./build/**',
	],
};
