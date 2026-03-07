/**
 * Checkout block editor preview.
 */

import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { showOrderNotes, showReturnToCart, stickySummaryDesktop } = attributes;

	const blockProps = useBlockProps( {
		className: 'page-checkout-block-editor',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Checkout Settings', 'etheme' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show order notes', 'etheme' ) }
						checked={ showOrderNotes }
						onChange={ ( value ) => setAttributes( { showOrderNotes: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show return to cart', 'etheme' ) }
						checked={ showReturnToCart }
						onChange={ ( value ) => setAttributes( { showReturnToCart: value } ) }
					/>
					<ToggleControl
						label={ __( 'Sticky summary on desktop', 'etheme' ) }
						checked={ stickySummaryDesktop }
						onChange={ ( value ) =>
							setAttributes( { stickySummaryDesktop: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="checkout-preview">
					<div className="checkout-preview-main">
						<p className="checkout-preview-title">
							{ __( 'Checkout form (left column)', 'etheme' ) }
						</p>
						<p className="checkout-preview-row">
							{ __( 'Contact Information', 'etheme' ) }
						</p>
						<p className="checkout-preview-row">
							{ __( 'Shipping address', 'etheme' ) }
						</p>
						<p className="checkout-preview-row">
							{ __( 'Shipping options', 'etheme' ) }
						</p>
						{ showOrderNotes && (
							<p className="checkout-preview-row">
								{ __( 'Order notes', 'etheme' ) }
							</p>
						) }
					</div>
					<div className="checkout-preview-aside">
						<p className="checkout-preview-title">
							{ __( 'Summary + Payment (right column)', 'etheme' ) }
						</p>
						<p className="checkout-preview-row">
							{ __( 'Order summary', 'etheme' ) }
						</p>
						<p className="checkout-preview-row">
							{ __( 'Payment methods', 'etheme' ) }
						</p>
						<p className="checkout-preview-row">
							{ __( 'Terms and place order', 'etheme' ) }
						</p>
						{ showReturnToCart && (
							<p className="checkout-preview-row">
								{ __( 'Return to cart link', 'etheme' ) }
							</p>
						) }
						<p className="checkout-preview-meta">
							{ stickySummaryDesktop
								? __( 'Sticky summary enabled', 'etheme' )
								: __( 'Sticky summary disabled', 'etheme' ) }
						</p>
					</div>
				</div>
			</div>
		</>
	);
}
