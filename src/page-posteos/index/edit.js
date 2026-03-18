// page-posteos-index/edit.js
/**
 * Editor component for the /posteos block.
 * Displays an inspector panel and a static placeholder in the canvas.
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { postsPerPage, bannerTitle, bannerSubtitle } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Configuración del banner', 'etheme' ) }>
					<TextControl
						label={ __( 'Título del banner', 'etheme' ) }
						value={ bannerTitle }
						onChange={ ( v ) => setAttributes( { bannerTitle: v } ) }
					/>
					<TextControl
						label={ __( 'Subtítulo del banner', 'etheme' ) }
						value={ bannerSubtitle }
						onChange={ ( v ) => setAttributes( { bannerSubtitle: v } ) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Listado de posteos', 'etheme' ) }>
					<RangeControl
						label={ __( 'Posteos por página', 'etheme' ) }
						value={ postsPerPage }
						onChange={ ( v ) => setAttributes( { postsPerPage: v } ) }
						min={ 3 }
						max={ 30 }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...useBlockProps( { className: 'wp-block-etheme-page-posteos-index' } ) }>
				<div style={ { padding: '2rem', background: '#fff3f0', borderRadius: '8px', textAlign: 'center' } }>
					<p style={ { fontWeight: 'bold', fontSize: '1.2rem' } }>
						{ __( 'Página de Posteos', 'etheme' ) }
					</p>
					<p style={ { color: '#6a6a6a', marginTop: '0.5rem' } }>
						{ __( 'Banner + listado de posteos sociales + botón "Mostrar más".', 'etheme' ) }
						{ ' ' }{ __( 'Renderizado en el servidor.', 'etheme' ) }
					</p>
					<p style={ { color: '#6a6a6a', marginTop: '0.25rem' } }>
						{ __( 'Primeros', 'etheme' ) } { postsPerPage } { __( 'posteos por página.', 'etheme' ) }
					</p>
				</div>
			</div>
		</>
	);
}
