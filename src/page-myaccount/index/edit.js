/**
 * My Account Page Block - Editor Component
 *
 * Provides block controls and preview in the WordPress editor.
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import './editor.scss';

/**
 * My Account Edit Component
 *
 * @param {Object}   props               Block properties.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Function to update attributes.
 * @return {JSX.Element} Editor component.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { showRegister } = attributes;

	const blockProps = useBlockProps( {
		className: 'page-myaccount-block-editor',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Account Settings', 'etheme' ) }
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Show Registration Form', 'etheme' ) }
						help={ __(
							'Allow new customers to register on the My Account page',
							'etheme'
						) }
						checked={ showRegister }
						onChange={ ( value ) =>
							setAttributes( { showRegister: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="myaccount-preview">
					<div className="myaccount-preview-header">
						<h2>{ __( 'My Account', 'etheme' ) }</h2>
						<p className="myaccount-preview-subtitle">
							{ __( 'Login & Registration page preview', 'etheme' ) }
						</p>
					</div>

					<div className="myaccount-preview-content">
						<div className="myaccount-preview-forms">
							{ /* Login Preview */ }
							<div className="myaccount-preview-form">
								<h3>{ __( 'Sign In', 'etheme' ) }</h3>
								<div className="myaccount-preview-field"></div>
								<div className="myaccount-preview-field"></div>
								<div className="myaccount-preview-btn">
									{ __( 'Sign In', 'etheme' ) }
								</div>
							</div>

							{ showRegister && (
								<div className="myaccount-preview-form">
									<h3>{ __( 'Create Account', 'etheme' ) }</h3>
									<div className="myaccount-preview-field"></div>
									<div className="myaccount-preview-btn myaccount-preview-btn--outline">
										{ __( 'Create Account', 'etheme' ) }
									</div>
								</div>
							) }
						</div>
					</div>
				</div>
			</div>
		</>
	);
}
