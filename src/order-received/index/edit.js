/**
 * Order Received block editor preview.
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

import './editor.scss';

export default function Edit() {
	const blockProps = useBlockProps( {
		className: 'order-received-block-editor',
	} );

	return (
		<div { ...blockProps }>
			<div className="order-received-preview">
				<p className="order-received-preview-title">
					{ __( 'Thank You / Order Received', 'etheme' ) }
				</p>
				<p className="order-received-preview-row">
					{ __( 'Hero (icon + heading + thank-you copy)', 'etheme' ) }
				</p>
				<p className="order-received-preview-row">
					{ __( 'Order summary (number, date, email, total, payment method)', 'etheme' ) }
				</p>
				<p className="order-received-preview-row">
					{ __( 'Order items (thumbnail + name + qty + subtotal)', 'etheme' ) }
				</p>
				<p className="order-received-preview-row">
					{ __( 'Shipping / billing address (if applicable)', 'etheme' ) }
				</p>
				<p className="order-received-preview-row">
					{ __( 'Exit CTAs (home / my orders / shop)', 'etheme' ) }
				</p>
			</div>
		</div>
	);
}
