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
					title={ __( 'Configuración del carrito', 'etheme' ) }
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Mostrar calculadora de envío', 'etheme' ) }
						help={ __(
							'Mostrar calculadora de envío por código postal',
							'etheme'
						) }
						checked={ showShippingCalculator }
						onChange={ ( value ) =>
							setAttributes( { showShippingCalculator: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Mostrar formulario de cupón', 'etheme' ) }
						help={ __(
							'Mostrar formulario para ingresar código de cupón',
							'etheme'
						) }
						checked={ showCouponForm }
						onChange={ ( value ) =>
							setAttributes( { showCouponForm: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Mostrar "Seguir comprando"', 'etheme' ) }
						help={ __(
							'Mostrar enlace para seguir comprando',
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
						<h2>{ __( 'Carrito de compras', 'etheme' ) }</h2>
						<p className="cart-preview-subtitle">
							{ __( 'Vista previa del carrito', 'etheme' ) }
						</p>
					</div>

					<div className="cart-preview-content">
						{ /* Product Item Preview */ }
						<div className="cart-preview-item">
							<div className="cart-preview-item-image"></div>
							<div className="cart-preview-item-details">
								<div className="cart-preview-item-name">
									{ __( 'Nombre del producto', 'etheme' ) }
								</div>
								<div className="cart-preview-item-meta">
									{ __( 'Color: Negro', 'etheme' ) }
								</div>
								<div className="cart-preview-item-price">
									$99.00
								</div>
							</div>
							<div className="cart-preview-item-qty">
								<span>Cant.: 1</span>
							</div>
						</div>

						{ /* Another Product Item Preview */ }
						<div className="cart-preview-item">
							<div className="cart-preview-item-image"></div>
							<div className="cart-preview-item-details">
								<div className="cart-preview-item-name">
									{ __( 'Otro producto', 'etheme' ) }
								</div>
								<div className="cart-preview-item-meta">
									{ __( 'Talle: Grande', 'etheme' ) }
								</div>
								<div className="cart-preview-item-price">
									$49.00
								</div>
							</div>
							<div className="cart-preview-item-qty">
								<span>Cant.: 2</span>
							</div>
						</div>
					</div>

					<div className="cart-preview-footer">
						{ showShippingCalculator && (
							<div className="cart-preview-section">
								<span className="cart-preview-section-title">
									{ __( 'Calculadora de envío', 'etheme' ) }
								</span>
								<span className="cart-preview-badge">
									{ __( 'Activado', 'etheme' ) }
								</span>
							</div>
						) }
						{ showCouponForm && (
							<div className="cart-preview-section">
								<span className="cart-preview-section-title">
									{ __( 'Formulario de cupón', 'etheme' ) }
								</span>
								<span className="cart-preview-badge">
									{ __( 'Activado', 'etheme' ) }
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
							{ __( 'Finalizar compra', 'etheme' ) }
						</div>
						{ showContinueShopping && (
							<div className="cart-preview-continue">
								{ __( 'Seguir comprando →', 'etheme' ) }
							</div>
						) }
					</div>
				</div>
			</div>
		</>
	);
}
