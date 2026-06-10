// announcement-bar — editor UI.
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import './editor.scss';

export default function Edit( { attributes } ) {
	const { message, bgColor } = attributes;

	return (
		<div { ...useBlockProps() }>
			<div
				className="etheme-announcement-bar-editor-preview"
				style={ { backgroundColor: bgColor } }
			>
				<span className="etheme-announcement-bar-editor-preview__msg">
					{ message || __( 'Mensaje de la barra de anuncios', 'etheme' ) }
				</span>
				<span className="etheme-announcement-bar-editor-preview__close" aria-hidden="true">
					&times;
				</span>
			</div>
		</div>
	);
}
