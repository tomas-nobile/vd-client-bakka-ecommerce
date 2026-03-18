// page-posteos-index/index.js
/**
 * Block registration for the Gutenberg editor.
 */

import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit.js';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null, // Server-side rendered via render.php.
} );
