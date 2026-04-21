// footer — editor UI.
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import './editor.scss';

export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<div className="etheme-footer-editor-preview">
				<strong>{ __( 'Footer', 'etheme' ) }</strong>
				<span className="etheme-footer-editor-preview__meta">
					{ __( 'Logo · Redes · Navegación · Legal · Copyright', 'etheme' ) }
				</span>
			</div>
		</div>
	);
}
