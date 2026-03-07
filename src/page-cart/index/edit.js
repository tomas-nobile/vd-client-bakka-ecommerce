/**
 * Cart Page Block - Editor Component
 *
 * Provides block controls and preview in the WordPress editor.
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import './editor.scss';

/**
 * Cart Page Edit Component
 *
 * @param {Object} props               Block properties.
 * @param {Object} props.attributes    Block attributes.
 * @param {Function} props.setAttributes Function to update attributes.
 * @return {JSX.Element} Editor component.
 */
export default function Edit( { attributes, setAttributes } ) {
	const {
		showShippingCalculator,
		showCouponForm,
		showContinueShopping,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'page-cart-block-editor',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Cart Settings', 'etheme' ) }
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Show Shipping Calculator', 'etheme' ) }
						help={ __(
							'Display postal code shipping calculator',
							'etheme'
						) }
						checked={ showShippingCalculator }
						onChange={ ( value ) =>
							setAttributes( { showShippingCalculator: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show Coupon Form', 'etheme' ) }
						help={ __(
							'Display coupon code input form',
							'etheme'
						) }
						checked={ showCouponForm }
						onChange={ ( value ) =>
							setAttributes( { showCouponForm: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show Continue Shopping', 'etheme' ) }
						help={ __(
							'Display continue shopping link',
							'etheme'
						) }
						checked={ showContinueShopping }
						onChange={ ( value ) =>
							setAttributes( { showContinueShopping: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="cart-preview">
					<div className="cart-preview-header">
						<h2>{ __( 'Shopping Cart', 'etheme' ) }</h2>
						<p className="cart-preview-subtitle">
							{ __( 'Cart page preview', 'etheme' ) }
						</p>
					</div>

					<div className="cart-preview-content">
						{ /* Product Item Preview */ }
						<div className="cart-preview-item">
							<div className="cart-preview-item-image"></div>
							<div className="cart-preview-item-details">
								<div className="cart-preview-item-name">
									{ __( 'Product Name', 'etheme' ) }
								</div>
								<div className="cart-preview-item-meta">
									{ __( 'Color: Black', 'etheme' ) }
								</div>
								<div className="cart-preview-item-price">
									$99.00
								</div>
							</div>
							<div className="cart-preview-item-qty">
								<span>Qty: 1</span>
							</div>
						</div>

						{ /* Another Product Item Preview */ }
						<div className="cart-preview-item">
							<div className="cart-preview-item-image"></div>
							<div className="cart-preview-item-details">
								<div className="cart-preview-item-name">
									{ __( 'Another Product', 'etheme' ) }
								</div>
								<div className="cart-preview-item-meta">
									{ __( 'Size: Large', 'etheme' ) }
								</div>
								<div className="cart-preview-item-price">
									$49.00
								</div>
							</div>
							<div className="cart-preview-item-qty">
								<span>Qty: 2</span>
							</div>
						</div>
					</div>

					<div className="cart-preview-footer">
						{ showShippingCalculator && (
							<div className="cart-preview-section">
								<span className="cart-preview-section-title">
									{ __( 'Shipping Calculator', 'etheme' ) }
								</span>
								<span className="cart-preview-badge">
									{ __( 'Enabled', 'etheme' ) }
								</span>
							</div>
						) }
						{ showCouponForm && (
							<div className="cart-preview-section">
								<span className="cart-preview-section-title">
									{ __( 'Coupon Form', 'etheme' ) }
								</span>
								<span className="cart-preview-badge">
									{ __( 'Enabled', 'etheme' ) }
								</span>
							</div>
						) }
						<div className="cart-preview-totals">
							<div className="cart-preview-total-row">
								<span>{ __( 'Subtotal', 'etheme' ) }</span>
								<span>$197.00</span>
							</div>
							<div className="cart-preview-total-row cart-preview-total-final">
								<span>{ __( 'Total', 'etheme' ) }</span>
								<span>$197.00</span>
							</div>
						</div>
						<div className="cart-preview-checkout-btn">
							{ __( 'Proceed to Checkout', 'etheme' ) }
						</div>
						{ showContinueShopping && (
							<div className="cart-preview-continue">
								{ __( 'Continue Shopping →', 'etheme' ) }
							</div>
						) }
					</div>
				</div>
			</div>
		</>
	);
}
