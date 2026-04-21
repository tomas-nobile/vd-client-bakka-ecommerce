const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
	...defaultConfig,
	entry: {
		// Main theme entry point
		index: path.resolve(process.cwd(), 'src', 'index.js'),
		// Block entries
		'0_block/index': path.resolve(process.cwd(), 'src/0_block', 'index.js'),
		'0_block/view': path.resolve(process.cwd(), 'src/0_block', 'view.js'),
		'core/header/index': path.resolve(process.cwd(), 'src/core/header', 'index.js'),
		'core/header/view': path.resolve(process.cwd(), 'src/core/header', 'view.js'),
		'core/footer/index': path.resolve(process.cwd(), 'src/core/footer', 'index.js'),
		'core/footer/view': path.resolve(process.cwd(), 'src/core/footer', 'view.js'),
		'core/navbar/index': path.resolve(process.cwd(), 'src/core/navbar', 'index.js'),
		'core/navbar/view': path.resolve(process.cwd(), 'src/core/navbar', 'view.js'),
		'front-page/index/index': path.resolve(process.cwd(), 'src/front-page/index', 'index.js'),
		'front-page/index/view': path.resolve(process.cwd(), 'src/front-page/index', 'view.js'),
		'archive-product/index/index': path.resolve(process.cwd(), 'src/archive-product/index', 'index.js'),
		'archive-product/index/view': path.resolve(process.cwd(), 'src/archive-product/index', 'view.js'),
		'single-product/index/index': path.resolve(process.cwd(), 'src/single-product/index', 'index.js'),
		'single-product/index/view': path.resolve(process.cwd(), 'src/single-product/index', 'view.js'),
		'page-cart/index/index': path.resolve(process.cwd(), 'src/page-cart/index', 'index.js'),
		'page-cart/index/view': path.resolve(process.cwd(), 'src/page-cart/index', 'view.js'),
		'page-checkout/index/index': path.resolve(process.cwd(), 'src/page-checkout/index', 'index.js'),
		'page-checkout/index/view': path.resolve(process.cwd(), 'src/page-checkout/index', 'view.js'),
		'page/index/index': path.resolve(process.cwd(), 'src/page/index', 'index.js'),
		'page/index/view': path.resolve(process.cwd(), 'src/page/index', 'view.js'),
		'page-posteos/index/index': path.resolve(process.cwd(), 'src/page-posteos/index', 'index.js'),
		'page-posteos/index/view': path.resolve(process.cwd(), 'src/page-posteos/index', 'view.js'),
		'contact/index/index': path.resolve(process.cwd(), 'src/contact/index', 'index.js'),
		'contact/index/view': path.resolve(process.cwd(), 'src/contact/index', 'view.js'),
		'information-page/index/index': path.resolve(process.cwd(), 'src/information-page/index', 'index.js'),
	},
	output: {
		...defaultConfig.output,
		clean: {
			keep: (asset) => asset.includes('index.css'),
		}
	},
};
