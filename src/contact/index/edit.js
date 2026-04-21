// contact-index — editor UI.
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const {
		infoEyebrow,
		infoTitle,
		locationTitle,
		locationText,
		locationUrl,
		phoneTitle,
		phoneLabel,
		whatsappUrl,
		emailTitle,
		email,
		formEyebrow,
		formTitle,
		formButtonText,
		formEndpoint,
	} = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Contacto — encabezado', 'etheme' ) } initialOpen>
					<TextControl
						label={ __( 'Eyebrow (h6)', 'etheme' ) }
						value={ infoEyebrow }
						onChange={ ( v ) => setAttributes( { infoEyebrow: v } ) }
					/>
					<TextControl
						label={ __( 'Título (h2)', 'etheme' ) }
						value={ infoTitle }
						onChange={ ( v ) => setAttributes( { infoTitle: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Ubicación', 'etheme' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Título de la tarjeta', 'etheme' ) }
						value={ locationTitle }
						onChange={ ( v ) => setAttributes( { locationTitle: v } ) }
					/>
					<TextareaControl
						label={ __( 'Texto (ej. ciudad)', 'etheme' ) }
						value={ locationText }
						onChange={ ( v ) => setAttributes( { locationText: v } ) }
					/>
					<TextControl
						label={ __( 'URL (Maps u otra)', 'etheme' ) }
						value={ locationUrl }
						onChange={ ( v ) => setAttributes( { locationUrl: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Teléfono', 'etheme' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Título de la tarjeta', 'etheme' ) }
						value={ phoneTitle }
						onChange={ ( v ) => setAttributes( { phoneTitle: v } ) }
					/>
					<TextControl
						label={ __( 'Texto visible del teléfono', 'etheme' ) }
						value={ phoneLabel }
						onChange={ ( v ) => setAttributes( { phoneLabel: v } ) }
					/>
					<TextareaControl
						label={ __( 'Enlace opcional (ej. wa.me para WhatsApp)', 'etheme' ) }
						value={ whatsappUrl }
						onChange={ ( v ) => setAttributes( { whatsappUrl: v } ) }
						rows={ 3 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Email', 'etheme' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Título de la tarjeta', 'etheme' ) }
						value={ emailTitle }
						onChange={ ( v ) => setAttributes( { emailTitle: v } ) }
					/>
					<TextControl
						label={ __( 'Dirección de email', 'etheme' ) }
						value={ email }
						onChange={ ( v ) => setAttributes( { email: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Formulario', 'etheme' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Eyebrow (h6)', 'etheme' ) }
						value={ formEyebrow }
						onChange={ ( v ) => setAttributes( { formEyebrow: v } ) }
					/>
					<TextControl
						label={ __( 'Título (h2)', 'etheme' ) }
						value={ formTitle }
						onChange={ ( v ) => setAttributes( { formTitle: v } ) }
					/>
					<TextControl
						label={ __( 'Texto del botón', 'etheme' ) }
						value={ formButtonText }
						onChange={ ( v ) => setAttributes( { formButtonText: v } ) }
					/>
					<TextareaControl
						label={ __( 'Endpoint del formulario (POST JSON)', 'etheme' ) }
						value={ formEndpoint }
						onChange={ ( v ) => setAttributes( { formEndpoint: v } ) }
						rows={ 3 }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...useBlockProps() }>
				<div style={ { padding: '24px', textAlign: 'center', color: '#6a6a6a' } }>
					<strong style={ { display: 'block', marginBottom: '8px', fontSize: '18px' } }>
						{ __( 'Página de Contacto', 'etheme' ) }
					</strong>
					<p style={ { margin: 0, fontSize: '13px' } }>
						{ __( 'Secciones: información de contacto y formulario. Editá los datos en el panel lateral.', 'etheme' ) }
					</p>
				</div>
			</div>
		</>
	);
}
