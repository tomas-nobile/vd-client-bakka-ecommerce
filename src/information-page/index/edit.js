// information-page — editor UI.
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import './editor.scss';

const PAGE_OPTIONS = [
	{ label: __( 'Política de Privacidad', 'etheme' ),        value: 'privacy' },
	{ label: __( 'Términos y Condiciones', 'etheme' ),         value: 'terms' },
	{ label: __( 'Condiciones de Compra', 'etheme' ),          value: 'commerceConditions' },
];

export default function Edit( { attributes, setAttributes } ) {
	const { pageKey } = attributes;

	const selected = PAGE_OPTIONS.find( ( o ) => o.value === pageKey );
	const label    = selected ? selected.label : pageKey;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Página de información', 'etheme' ) } initialOpen>
					<SelectControl
						label={ __( 'Contenido a mostrar', 'etheme' ) }
						value={ pageKey }
						options={ PAGE_OPTIONS }
						onChange={ ( v ) => setAttributes( { pageKey: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...useBlockProps() }>
				<div style={ { padding: '24px', textAlign: 'center', color: '#6a6a6a' } }>
					<strong style={ { display: 'block', marginBottom: '8px', fontSize: '18px' } }>
						{ __( 'Página de Información', 'etheme' ) }
					</strong>
					<p style={ { margin: 0, fontSize: '13px' } }>
						{ label }
					</p>
				</div>
			</div>
		</>
	);
}
