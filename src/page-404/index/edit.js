// page-404 — editor UI.
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import './editor.scss';

export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<div style={ { padding: '24px', textAlign: 'center', color: '#6a6a6a' } }>
				<strong style={ { display: 'block', marginBottom: '8px', fontSize: '18px' } }>
					{ __( 'Página 404 — Error', 'etheme' ) }
				</strong>
				<p style={ { margin: 0, fontSize: '13px' } }>
					{ __( 'Se renderiza server-side.', 'etheme' ) }
				</p>
			</div>
		</div>
	);
}
