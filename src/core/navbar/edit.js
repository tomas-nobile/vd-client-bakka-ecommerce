import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, MediaUpload } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button, IconButton } from '@wordpress/components';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { logoUrl, menuItems, searchUrl, cartUrl, accountUrl } = attributes;

	const addMenuItem = () => {
		const newItems = [...menuItems, { label: 'New Item', url: '#' }];
		setAttributes({ menuItems: newItems });
	};

	const updateMenuItem = (index, field, value) => {
		const newItems = [...menuItems];
		newItems[index][field] = value;
		setAttributes({ menuItems: newItems });
	};

	const removeMenuItem = (index) => {
		const newItems = menuItems.filter((_, i) => i !== index);
		setAttributes({ menuItems: newItems });
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Logo Settings', 'navbar')}>
					<MediaUpload
						onSelect={(media) => setAttributes({ logoUrl: media.url })}
						allowedTypes={['image']}
						render={({ open }) => (
							<Button onClick={open} variant="secondary">
								{logoUrl ? __('Change Logo', 'navbar') : __('Select Logo', 'navbar')}
							</Button>
						)}
					/>
					{logoUrl && <img src={logoUrl} alt="Logo" style={{ marginTop: '10px', maxWidth: '100%' }} />}
				</PanelBody>

				<PanelBody title={__('Menu Items', 'navbar')}>
					{menuItems.map((item, index) => (
						<div key={index} style={{ marginBottom: '15px', padding: '10px', border: '1px solid #ddd' }}>
							<TextControl
								label={__('Label', 'navbar')}
								value={item.label}
								onChange={(value) => updateMenuItem(index, 'label', value)}
							/>
							<TextControl
								label={__('URL', 'navbar')}
								value={item.url}
								onChange={(value) => updateMenuItem(index, 'url', value)}
							/>
							<Button onClick={() => removeMenuItem(index)} variant="secondary" isDestructive>
								{__('Remove', 'navbar')}
							</Button>
						</div>
					))}
					<Button onClick={addMenuItem} variant="primary">
						{__('Add Menu Item', 'navbar')}
					</Button>
				</PanelBody>

				<PanelBody title={__('Icon Links', 'navbar')}>
					<TextControl
						label={__('Search URL', 'navbar')}
						value={searchUrl}
						onChange={(value) => setAttributes({ searchUrl: value })}
					/>
					<TextControl
						label={__('Cart URL', 'navbar')}
						value={cartUrl}
						onChange={(value) => setAttributes({ cartUrl: value })}
					/>
					<TextControl
						label={__('Account URL', 'navbar')}
						value={accountUrl}
						onChange={(value) => setAttributes({ accountUrl: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...useBlockProps()}>
				<div style={{ padding: '20px', background: '#000', color: '#fff' }}>
					<p>{__('Navigation Bar', 'navbar')}</p>
					<p style={{ fontSize: '12px', opacity: 0.7 }}>
						{__('Configure menu items and settings in the sidebar →', 'navbar')}
					</p>
					{menuItems.length > 0 && (
						<div style={{ marginTop: '10px' }}>
							{menuItems.map((item, index) => (
								<span key={index} style={{ marginRight: '15px' }}>
									{item.label}
								</span>
							))}
						</div>
					)}
				</div>
			</div>
		</>
	);
}
