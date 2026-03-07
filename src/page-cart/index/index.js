/**
 * Cart Page Block
 *
 * Registers the cart page block for WordPress.
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import Edit from './edit';
import metadata from './block.json';

/**
 * Cart icon for the block
 */
const cartIcon = (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 24 24"
		width="24"
		height="24"
		fill="none"
		stroke="currentColor"
		strokeWidth="2"
		strokeLinecap="round"
		strokeLinejoin="round"
	>
		<circle cx="9" cy="21" r="1" />
		<circle cx="20" cy="21" r="1" />
		<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
	</svg>
);

/**
 * Register the block
 */
registerBlockType( metadata.name, {
	...metadata,
	icon: cartIcon,
	edit: Edit,
	save: () => null, // Server-side rendered
} );
