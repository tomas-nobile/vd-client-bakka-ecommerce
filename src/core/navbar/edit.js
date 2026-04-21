// navbar — editor UI.
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { menuLocation, showSearch, showCart } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Menú', 'etheme' ) } initialOpen>
					<TextControl
						label={ __( 'Theme location (slug)', 'etheme' ) }
						value={ menuLocation }
						onChange={ ( v ) => setAttributes( { menuLocation: v } ) }
						help={ __( 'Slug de la ubicación registrada. Por defecto: etheme-primary', 'etheme' ) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Acciones', 'etheme' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Mostrar buscador', 'etheme' ) }
						checked={ showSearch }
						onChange={ ( v ) => setAttributes( { showSearch: v } ) }
					/>
					<ToggleControl
						label={ __( 'Mostrar carrito', 'etheme' ) }
						checked={ showCart }
						onChange={ ( v ) => setAttributes( { showCart: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...useBlockProps() }>
				<div className="etheme-navbar-editor-preview">
					<strong>{ __( 'Navbar', 'etheme' ) }</strong>
					<span className="etheme-navbar-editor-preview__meta">
						{ __( 'Menú:', 'etheme' ) }{ ' ' }
						<code>{ menuLocation }</code>
						{ showSearch && ' · búsqueda' }
						{ showCart && ' · carrito' }
					</span>
				</div>
			</div>
		</>
	);
}
