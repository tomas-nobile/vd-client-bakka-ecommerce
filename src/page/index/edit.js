/**
 * Editor placeholder for etheme/page-index.
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

import './editor.scss';

export default function Edit() {
	const blockProps = useBlockProps( {
		className: 'page-account-block-editor',
	} );

	return (
		<div { ...blockProps }>
			<div className="page-account-block-editor__inner">
				<p className="page-account-block-editor__title">
					{ __( 'Mi cuenta (WooCommerce)', 'etheme' ) }
				</p>
				<p className="page-account-block-editor__hint">
					{ __(
						'Login, registro, recuperación de contraseña y panel de cliente. Renderizado en el servidor con el estilo Contrive.',
						'etheme'
					) }
				</p>
			</div>
		</div>
	);
}
