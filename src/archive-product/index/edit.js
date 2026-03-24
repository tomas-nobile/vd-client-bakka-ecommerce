/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { PanelBody, RangeControl, SelectControl, ToggleControl, TextControl, Button } from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

const DEFAULT_FILTERS = [
	{ type: 'category' },
	{ type: 'color', taxonomy: 'pa_color', termOverrides: {} },
	{ type: 'attribute', taxonomy: 'pa_size', label: 'Size' },
	{ type: 'price', rangeColor: '#fb704f' },
];

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
		columns,
		perPage,
		defaultOrderBy,
		defaultOrder,
		showSorting,
		showSearch,
		filterParentCategories,
		useParentChildrenOnly,
		showParentCategoryBar,
		filters = DEFAULT_FILTERS,
	} = attributes;

	const paTaxonomies = useSelect((select) => {
		const taxonomies = select('core').getTaxonomies?.({ per_page: -1 }) || [];
		return taxonomies.filter((t) => t.slug && t.slug.startsWith('pa_'));
	}, []);

	const taxonomyOptions = [
		{ label: __('Select taxonomy', 'etheme'), value: '' },
		...paTaxonomies.map((t) => ({ label: t.name || t.slug, value: t.slug })),
	];

	const safeFilters = Array.isArray(filters) && filters.length ? filters : DEFAULT_FILTERS;

	const updateFilter = (index, next) => {
		const nextFilters = [...safeFilters];
		nextFilters[index] = next;
		setAttributes({ filters: nextFilters });
	};

	const removeFilter = (index) => {
		if (safeFilters[index].type === 'category') return;
		const nextFilters = safeFilters.filter((_, i) => i !== index);
		setAttributes({ filters: nextFilters });
	};

	const addFilter = (type) => {
		const newItem =
			type === 'color'
				? { type: 'color', taxonomy: 'pa_color', termOverrides: {} }
				: type === 'attribute'
					? { type: 'attribute', taxonomy: 'pa_size', label: __('Attribute', 'etheme') }
					: { type: 'price', rangeColor: '#fb704f' };
		const hasCategory = safeFilters.some((f) => f.type === 'category');
		const nextFilters = hasCategory ? [...safeFilters, newItem] : [{ type: 'category' }, newItem];
		setAttributes({ filters: nextFilters });
	};

	const updateColorTermOverride = (filterIndex, slugKey, field, value) => {
		const filter = safeFilters[filterIndex];
		if (filter.type !== 'color' || !filter.termOverrides) return;
		const nextOverrides = { ...filter.termOverrides };
		const current = nextOverrides[slugKey] || { hex: '#cccccc', name: '' };
		if (field === 'slug') {
			if (slugKey !== value && value) {
				delete nextOverrides[slugKey];
				nextOverrides[value] = current;
			}
			return updateFilter(filterIndex, { ...filter, termOverrides: nextOverrides });
		}
		nextOverrides[slugKey] = { ...current, [field]: value };
		updateFilter(filterIndex, { ...filter, termOverrides: nextOverrides });
	};

	const addColorOverride = (filterIndex) => {
		const filter = safeFilters[filterIndex];
		if (filter.type !== 'color') return;
		const termOverrides = { ...(filter.termOverrides || {}), new: { hex: '#cccccc', name: __('New', 'etheme') } };
		updateFilter(filterIndex, { ...filter, termOverrides });
	};

	const removeColorOverride = (filterIndex, slugKey) => {
		const filter = safeFilters[filterIndex];
		if (filter.type !== 'color' || !filter.termOverrides) return;
		const nextOverrides = { ...filter.termOverrides };
		delete nextOverrides[slugKey];
		updateFilter(filterIndex, { ...filter, termOverrides: nextOverrides });
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Grid Settings', 'etheme')}>
					<RangeControl
						label={__('Columns', 'etheme')}
						value={columns}
						onChange={(value) => setAttributes({ columns: value })}
						min={1}
						max={6}
					/>
					<SelectControl
						label={__('Products per page', 'etheme')}
						value={perPage}
						options={[
							{ label: '12', value: 12 },
							{ label: '24', value: 24 },
							{ label: '36', value: 36 },
							{ label: '48', value: 48 },
						]}
						onChange={(value) => setAttributes({ perPage: parseInt(value) })}
					/>
				</PanelBody>
				<PanelBody title={__('Default Sorting', 'etheme')}>
					<SelectControl
						label={__('Order by', 'etheme')}
						value={defaultOrderBy}
						options={[
							{ label: __('Date', 'etheme'), value: 'date' },
							{ label: __('Price', 'etheme'), value: 'price' },
							{ label: __('Popularity', 'etheme'), value: 'popularity' },
						]}
						onChange={(value) => setAttributes({ defaultOrderBy: value })}
					/>
					<SelectControl
						label={__('Order', 'etheme')}
						value={defaultOrder}
						options={[
							{ label: __('Ascending', 'etheme'), value: 'asc' },
							{ label: __('Descending', 'etheme'), value: 'desc' },
						]}
						onChange={(value) => setAttributes({ defaultOrder: value })}
					/>
				</PanelBody>
				<PanelBody title={__('Filters', 'etheme')} initialOpen={true}>
					{safeFilters.map((filter, index) => (
						<div key={index} style={{ marginBottom: 16, paddingBottom: 16, borderBottom: '1px solid #ddd' }}>
							{filter.type === 'category' && (
								<p style={{ margin: 0, color: '#666' }}>{__('Category (always shown)', 'etheme')}</p>
							)}
							{filter.type === 'color' && (
								<>
									<p style={{ margin: '0 0 8px 0', fontWeight: 600 }}>{__('Color filter', 'etheme')}</p>
									<SelectControl
										label={__('Taxonomy', 'etheme')}
										value={filter.taxonomy || 'pa_color'}
										options={taxonomyOptions}
										onChange={(value) => updateFilter(index, { ...filter, taxonomy: value || 'pa_color' })}
									/>
									<p style={{ margin: '12px 0 6px 0', fontSize: 12 }}>{__('Term overrides (slug, color, name for hover)', 'etheme')}</p>
									{filter.termOverrides &&
										Object.entries(filter.termOverrides).map(([slug, data]) => (
											<div key={slug} style={{ display: 'flex', gap: 8, alignItems: 'center', marginBottom: 8, flexWrap: 'wrap' }}>
												<TextControl
													value={slug}
													onChange={(value) => updateColorTermOverride(index, slug, 'slug', value)}
													placeholder={__('Slug', 'etheme')}
													style={{ width: 80 }}
												/>
												<input
													type="color"
													value={data.hex || '#cccccc'}
													onChange={(e) => updateColorTermOverride(index, slug, 'hex', e.target.value)}
													style={{ width: 32, height: 28, padding: 0, border: '1px solid #ccc' }}
												/>
												<TextControl
													value={data.name || ''}
													onChange={(value) => updateColorTermOverride(index, slug, 'name', value)}
													placeholder={__('Name (hover)', 'etheme')}
													style={{ flex: 1, minWidth: 80 }}
												/>
												<Button isDestructive isSmall onClick={() => removeColorOverride(index, slug)}>
													{__('Remove', 'etheme')}
												</Button>
											</div>
										))}
									<Button isSecondary isSmall onClick={() => addColorOverride(index)} style={{ marginTop: 4 }}>
										{__('Add override', 'etheme')}
									</Button>
									{index > 0 && (
										<Button isDestructive isSmall onClick={() => removeFilter(index)} style={{ marginLeft: 8 }}>
											{__('Remove filter', 'etheme')}
										</Button>
									)}
								</>
							)}
							{filter.type === 'attribute' && (
								<>
									<p style={{ margin: '0 0 8px 0', fontWeight: 600 }}>{__('Attribute filter', 'etheme')}</p>
									<SelectControl
										label={__('Taxonomy', 'etheme')}
										value={filter.taxonomy || 'pa_size'}
										options={taxonomyOptions}
										onChange={(value) => updateFilter(index, { ...filter, taxonomy: value || 'pa_size' })}
									/>
									<TextControl
										label={__('Label', 'etheme')}
										value={filter.label || ''}
										onChange={(value) => updateFilter(index, { ...filter, label: value })}
										placeholder={__('e.g. Size, Talla', 'etheme')}
									/>
									{index > 0 && (
										<Button isDestructive isSmall onClick={() => removeFilter(index)} style={{ marginTop: 8 }}>
											{__('Remove filter', 'etheme')}
										</Button>
									)}
								</>
							)}
							{filter.type === 'price' && (
								<>
									<p style={{ margin: '0 0 8px 0', fontWeight: 600 }}>{__('Price range', 'etheme')}</p>
									<div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
										<label style={{ fontSize: 12 }}>{__('Range color', 'etheme')}</label>
										<input
											type="color"
											value={filter.rangeColor || '#fb704f'}
											onChange={(e) => updateFilter(index, { ...filter, rangeColor: e.target.value })}
											style={{ width: 36, height: 28, padding: 0, border: '1px solid #ccc' }}
										/>
									</div>
									{index > 0 && (
										<Button isDestructive isSmall onClick={() => removeFilter(index)} style={{ marginTop: 8 }}>
											{__('Remove filter', 'etheme')}
										</Button>
									)}
								</>
							)}
						</div>
					))}
					<div style={{ marginTop: 8 }}>
						<SelectControl
							label={__('Add filter', 'etheme')}
							value=""
							options={[
								{ label: __('— Select type —', 'etheme'), value: '' },
								{ label: __('Color', 'etheme'), value: 'color' },
								{ label: __('Attribute', 'etheme'), value: 'attribute' },
								{ label: __('Price', 'etheme'), value: 'price' },
							]}
							onChange={(value) => value && addFilter(value)}
						/>
					</div>
				</PanelBody>
				<PanelBody title={__('Display Options', 'etheme')}>
					<ToggleControl
						label={__('Show sorting dropdown', 'etheme')}
						checked={showSorting}
						onChange={(value) => setAttributes({ showSorting: value })}
					/>
					<ToggleControl
						label={__('Show search box', 'etheme')}
						checked={showSearch}
						onChange={(value) => setAttributes({ showSearch: value })}
					/>
					<ToggleControl
						label={__('Filter parent categories', 'etheme')}
						checked={filterParentCategories}
						onChange={(value) => setAttributes({ filterParentCategories: value })}
					/>
					<ToggleControl
						label={__('Show only children of current parent', 'etheme')}
						checked={useParentChildrenOnly}
						onChange={(value) => setAttributes({ useParentChildrenOnly: value })}
					/>
					<ToggleControl
						label={__('Show parent category bar', 'etheme')}
						checked={showParentCategoryBar}
						onChange={(value) => setAttributes({ showParentCategoryBar: value })}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<div style={{ padding: '20px', background: '#f0f0f0', border: '2px dashed #ccc' }}>
					<h3>{ __( 'Product Archive Index', 'etheme' ) }</h3>
					<p>{ __( 'Columns:', 'etheme' ) } <strong>{columns}</strong></p>
					<p>{ __( 'Products per page:', 'etheme' ) } <strong>{perPage}</strong></p>
					<p>{ __( 'Default sort:', 'etheme' ) } <strong>{defaultOrderBy}-{defaultOrder}</strong></p>
					<p>{ __( 'Show sorting:', 'etheme' ) } <strong>{showSorting ? 'Yes' : 'No'}</strong></p>
					<p>{ __( 'Show search:', 'etheme' ) } <strong>{showSearch ? 'Yes' : 'No'}</strong></p>
					<p>{ __( 'Filter parent categories:', 'etheme' ) } <strong>{filterParentCategories ? 'Yes' : 'No'}</strong></p>
					<p>{ __( 'Only parent children:', 'etheme' ) } <strong>{useParentChildrenOnly ? 'Yes' : 'No'}</strong></p>
					<p>{ __( 'Show parent bar:', 'etheme' ) } <strong>{showParentCategoryBar ? 'Yes' : 'No'}</strong></p>
					<p style={{ marginTop: '10px', fontSize: '12px', color: '#666' }}>
						{ __( 'This block will display products on the frontend with filters, sorting, and pagination.', 'etheme' ) }
					</p>
				</div>
			</div>
		</>
	);
}
