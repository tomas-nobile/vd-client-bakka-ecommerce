/**
 * My Account Page Block
 *
 * Registers the my account page block for WordPress.
 */

import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';
import metadata from './block.json';

/**
 * Account icon for the block
 */
const accountIcon = (
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
		<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
		<circle cx="12" cy="7" r="4" />
	</svg>
);

/**
 * Register the block
 */
registerBlockType( metadata.name, {
	...metadata,
	icon: accountIcon,
	edit: Edit,
	save: () => null, // Server-side rendered
} );
