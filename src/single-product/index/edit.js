/**
 * Single Product Block - Editor Component
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl } from '@wordpress/components';

/**
 * Editor styles
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const {
		showThumbnails,
		showSku,
		showCategories,
		showTags,
		showRelatedProducts,
		relatedProductsCount,
	} = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Gallery Settings', 'etheme')}>
					<ToggleControl
						label={__('Show thumbnails', 'etheme')}
						help={__('Display thumbnail navigation below main image', 'etheme')}
						checked={showThumbnails}
						onChange={(value) => setAttributes({ showThumbnails: value })}
					/>
				</PanelBody>
				<PanelBody title={__('Product Info Settings', 'etheme')}>
					<ToggleControl
						label={__('Show SKU', 'etheme')}
						checked={showSku}
						onChange={(value) => setAttributes({ showSku: value })}
					/>
					<ToggleControl
						label={__('Show categories', 'etheme')}
						checked={showCategories}
						onChange={(value) => setAttributes({ showCategories: value })}
					/>
					<ToggleControl
						label={__('Show tags', 'etheme')}
						checked={showTags}
						onChange={(value) => setAttributes({ showTags: value })}
					/>
				</PanelBody>
				<PanelBody title={__('Related Products', 'etheme')}>
					<ToggleControl
						label={__('Show related products', 'etheme')}
						checked={showRelatedProducts}
						onChange={(value) => setAttributes({ showRelatedProducts: value })}
					/>
					{showRelatedProducts && (
						<RangeControl
							label={__('Number of related products', 'etheme')}
							value={relatedProductsCount}
							onChange={(value) => setAttributes({ relatedProductsCount: value })}
							min={2}
							max={6}
						/>
					)}
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<div style={{ padding: '20px', background: '#f0f0f0', border: '2px dashed #ccc' }}>
					<h3>{__('Single Product', 'etheme')}</h3>
					<div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginTop: '15px' }}>
						<div style={{ background: '#e0e0e0', padding: '15px', borderRadius: '4px' }}>
							<strong>{__('Gallery', 'etheme')}</strong>
							<p style={{ fontSize: '12px', margin: '5px 0 0' }}>
								{__('Thumbnails:', 'etheme')} {showThumbnails ? __('Yes', 'etheme') : __('No', 'etheme')}
							</p>
						</div>
						<div style={{ background: '#e0e0e0', padding: '15px', borderRadius: '4px' }}>
							<strong>{__('Product Info', 'etheme')}</strong>
							<p style={{ fontSize: '12px', margin: '5px 0 0' }}>
								{__('SKU:', 'etheme')} {showSku ? __('Yes', 'etheme') : __('No', 'etheme')}<br />
								{__('Categories:', 'etheme')} {showCategories ? __('Yes', 'etheme') : __('No', 'etheme')}<br />
								{__('Tags:', 'etheme')} {showTags ? __('Yes', 'etheme') : __('No', 'etheme')}
							</p>
						</div>
					</div>
					<div style={{ marginTop: '15px', background: '#e0e0e0', padding: '15px', borderRadius: '4px' }}>
						<strong>{__('Related Products', 'etheme')}</strong>
						<p style={{ fontSize: '12px', margin: '5px 0 0' }}>
							{showRelatedProducts
								? __('Showing', 'etheme') + ' ' + relatedProductsCount + ' ' + __('products', 'etheme')
								: __('Hidden', 'etheme')}
						</p>
					</div>
					<p style={{ marginTop: '15px', fontSize: '12px', color: '#666' }}>
						{__('This block displays the complete single product page on the frontend.', 'etheme')}
					</p>
				</div>
			</div>
		</>
	);
}
