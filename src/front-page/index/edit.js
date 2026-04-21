// front-page-index.
import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	SelectControl,
	TextControl,
	TextareaControl,
	Button,
} from '@wordpress/components';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const {
		heroTitle,
		heroTitle2,
		heroTitle3,
		heroSubtitle,
		heroDescription,
		heroCtaText,
		heroCtaUrl,
		heroImageId,
		heroImageId2,
		heroImageId3,
		heroDiscountNumber,
		heroDiscountLabel,
		heroDiscountSublabel,
		productsOrderBy,
		productsPerCategory,
		categoriesMode,
		reviewsCount,
		reviewsOrderBy,
		blogCount,
		blogPostType,
		faqsEyebrow,
		faqsTitle,
		faqsImageId,
	} = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Hero', 'etheme' ) }>
					<TextControl
						label={ __( 'Título principal', 'etheme' ) }
						value={ heroTitle }
						onChange={ ( v ) => setAttributes( { heroTitle: v } ) }
					/>
					<TextControl
						label={ __( 'Título slide 2', 'etheme' ) }
						help={ __(
							'Si se deja vacío, usa el título principal.',
							'etheme'
						) }
						value={ heroTitle2 }
						onChange={ ( v ) => setAttributes( { heroTitle2: v } ) }
					/>
					<TextControl
						label={ __( 'Título slide 3', 'etheme' ) }
						help={ __(
							'Si se deja vacío, usa el título principal.',
							'etheme'
						) }
						value={ heroTitle3 }
						onChange={ ( v ) => setAttributes( { heroTitle3: v } ) }
					/>
					<TextControl
						label={ __( 'Subtítulo', 'etheme' ) }
						value={ heroSubtitle }
						onChange={ ( v ) => setAttributes( { heroSubtitle: v } ) }
					/>
					<TextareaControl
						label={ __( 'Descripción', 'etheme' ) }
						value={ heroDescription }
						onChange={ ( v ) =>
							setAttributes( { heroDescription: v } )
						}
					/>
					<TextControl
						label={ __( 'Texto del botón CTA', 'etheme' ) }
						value={ heroCtaText }
						onChange={ ( v ) => setAttributes( { heroCtaText: v } ) }
					/>
					<TextControl
						label={ __( 'URL del botón CTA', 'etheme' ) }
						value={ heroCtaUrl }
						onChange={ ( v ) => setAttributes( { heroCtaUrl: v } ) }
					/>
					<p style={ { marginTop: '1em', fontWeight: 600 } }>
						{ __( 'Imágenes del slider', 'etheme' ) }
					</p>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( { heroImageId: media.id } )
							}
							allowedTypes={ [ 'image' ] }
							value={ heroImageId }
							render={ ( { open } ) => (
								<Button
									variant="secondary"
									onClick={ open }
									style={ { marginBottom: '0.5em' } }
								>
									{ heroImageId
										? __(
												'Cambiar imagen slide 1',
												'etheme'
										  )
										: __(
												'Elegir imagen slide 1',
												'etheme'
										  ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( { heroImageId2: media.id } )
							}
							allowedTypes={ [ 'image' ] }
							value={ heroImageId2 }
							render={ ( { open } ) => (
								<Button
									variant="secondary"
									onClick={ open }
									style={ { marginBottom: '0.5em' } }
								>
									{ heroImageId2
										? __(
												'Cambiar imagen slide 2',
												'etheme'
										  )
										: __(
												'Elegir imagen slide 2',
												'etheme'
										  ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( { heroImageId3: media.id } )
							}
							allowedTypes={ [ 'image' ] }
							value={ heroImageId3 }
							render={ ( { open } ) => (
								<Button
									variant="secondary"
									onClick={ open }
								>
									{ heroImageId3
										? __(
												'Cambiar imagen slide 3',
												'etheme'
										  )
										: __(
												'Elegir imagen slide 3',
												'etheme'
										  ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					<TextControl
						label={ __( 'Oferta: número', 'etheme' ) }
						help={ __( 'Ej: 50 para "50 % OFF"', 'etheme' ) }
						value={ heroDiscountNumber ?? '50' }
						onChange={ ( v ) =>
							setAttributes( { heroDiscountNumber: v } )
						}
					/>
					<TextControl
						label={ __( 'Oferta: etiqueta', 'etheme' ) }
						help={ __( 'Ej: OFF', 'etheme' ) }
						value={ heroDiscountLabel ?? 'OFF' }
						onChange={ ( v ) =>
							setAttributes( { heroDiscountLabel: v } )
						}
					/>
					<TextControl
						label={ __( 'Oferta: subetiqueta', 'etheme' ) }
						help={ __( 'Ej: En todos los productos', 'etheme' ) }
						value={ heroDiscountSublabel ?? __( 'En todos los productos', 'etheme' ) }
						onChange={ ( v ) =>
							setAttributes( { heroDiscountSublabel: v } )
						}
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Productos Populares', 'etheme' ) }
					initialOpen={ false }
				>
					<SelectControl
						label={ __( 'Ordenar por', 'etheme' ) }
						help={ __(
							'Criterio de popularidad. Actualmente solo ventas (total_sales). Extensible a destacados, recientes, valoración.',
							'etheme'
						) }
						value={ productsOrderBy }
						options={ [
							{
								label: __( 'Más vendidos', 'etheme' ),
								value: 'total_sales',
							},
							{
								label: __(
									'Destacados (futuro)',
									'etheme'
								),
								value: 'featured',
							},
							{
								label: __( 'Más recientes (futuro)', 'etheme' ),
								value: 'date',
							},
							{
								label: __(
									'Mejor valorados (futuro)',
									'etheme'
								),
								value: 'rating',
							},
						] }
						onChange={ ( v ) =>
							setAttributes( { productsOrderBy: v } )
						}
					/>
					<RangeControl
						label={ __( 'Productos por categoría', 'etheme' ) }
						value={ productsPerCategory }
						onChange={ ( v ) =>
							setAttributes( { productsPerCategory: v } )
						}
						min={ 2 }
						max={ 12 }
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Categorías', 'etheme' ) }
					initialOpen={ false }
				>
					<SelectControl
						label={ __( 'Modo de categorías', 'etheme' ) }
						help={ __(
							'Mostrar todas, incluir específicas o excluir algunas.',
							'etheme'
						) }
						value={ categoriesMode }
						options={ [
							{
								label: __( 'Todas', 'etheme' ),
								value: 'all',
							},
							{
								label: __(
									'Solo las seleccionadas',
									'etheme'
								),
								value: 'include',
							},
							{
								label: __( 'Excluir algunas', 'etheme' ),
								value: 'exclude',
							},
						] }
						onChange={ ( v ) =>
							setAttributes( { categoriesMode: v } )
						}
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Reviews / Testimonios', 'etheme' ) }
					initialOpen={ false }
				>
					<RangeControl
						label={ __( 'Cantidad de reviews', 'etheme' ) }
						value={ reviewsCount }
						onChange={ ( v ) =>
							setAttributes( { reviewsCount: v } )
						}
						min={ 1 }
						max={ 12 }
					/>
					<SelectControl
						label={ __( 'Orden', 'etheme' ) }
						value={ reviewsOrderBy }
						options={ [
							{
								label: __( 'Más recientes', 'etheme' ),
								value: 'date',
							},
							{
								label: __( 'Aleatorio', 'etheme' ),
								value: 'rand',
							},
						] }
						onChange={ ( v ) =>
							setAttributes( { reviewsOrderBy: v } )
						}
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Blog', 'etheme' ) }
					initialOpen={ false }
				>
					<SelectControl
						label={ __( 'Tipo de contenido', 'etheme' ) }
						value={ blogPostType || 'social_post' }
						options={ [
							{ value: 'social_post', label: __( 'Posteos sociales', 'etheme' ) },
							{ value: 'post', label: __( 'Entradas del blog', 'etheme' ) },
						] }
						onChange={ ( v ) =>
							setAttributes( { blogPostType: v } )
						}
					/>
					<RangeControl
						label={ __( 'Cantidad de posts', 'etheme' ) }
						value={ blogCount }
						onChange={ ( v ) =>
							setAttributes( { blogCount: v } )
						}
						min={ 1 }
						max={ 9 }
					/>
				</PanelBody>

			<PanelBody
				title={ __( 'FAQs (sección home)', 'etheme' ) }
				initialOpen={ false }
			>
				<p style={ { fontSize: '12px', color: '#757575', margin: '0 0 12px' } }>
					{ __( 'El contenido proviene de config.json → homeFaqs. Eyebrow y título sobreescriben los valores del config si se rellenan.', 'etheme' ) }
				</p>
				<TextControl
					label={ __( 'Eyebrow (h6)', 'etheme' ) }
					value={ faqsEyebrow }
					onChange={ ( v ) => setAttributes( { faqsEyebrow: v } ) }
				/>
				<TextControl
					label={ __( 'Título (h2)', 'etheme' ) }
					value={ faqsTitle }
					onChange={ ( v ) => setAttributes( { faqsTitle: v } ) }
				/>
				<MediaUploadCheck>
					<MediaUpload
						onSelect={ ( media ) => setAttributes( { faqsImageId: media.id } ) }
						allowedTypes={ [ 'image' ] }
						value={ faqsImageId }
						render={ ( { open } ) => (
							<div>
								<p style={ { fontSize: '12px', fontWeight: 600, margin: '12px 0 4px' } }>
									{ __( 'Imagen lateral', 'etheme' ) }
								</p>
								{ faqsImageId > 0 && (
									<Button
										isDestructive
										variant="link"
										style={ { marginBottom: '6px', fontSize: '12px' } }
										onClick={ () => setAttributes( { faqsImageId: 0 } ) }
									>
										{ __( 'Quitar imagen', 'etheme' ) }
									</Button>
								) }
								<Button variant="secondary" onClick={ open } style={ { width: '100%' } }>
									{ faqsImageId > 0
										? __( 'Cambiar imagen', 'etheme' )
										: __( 'Seleccionar imagen', 'etheme' ) }
								</Button>
							</div>
						) }
					/>
				</MediaUploadCheck>
			</PanelBody>
			</InspectorControls>

			<div { ...useBlockProps() }>
				<div
					style={ {
						padding: '20px',
						background: '#f0f0f0',
						border: '2px dashed #ccc',
					} }
				>
					<h3>{ __( 'Home Page', 'etheme' ) }</h3>
					<p>
						<strong>Hero:</strong> { heroTitle }
					</p>
					<p>
						<strong>
							{ __( 'Productos por categoría:', 'etheme' ) }
						</strong>{ ' ' }
						{ productsPerCategory } — { productsOrderBy }
					</p>
					<p>
						<strong>{ __( 'Reviews:', 'etheme' ) }</strong>{ ' ' }
						{ reviewsCount } ({ reviewsOrderBy })
					</p>
					<p>
						<strong>{ __( 'Blog:', 'etheme' ) }</strong>{ ' ' }
						{ blogCount }{ ' ' }
						{ blogPostType === 'social_post'
							? __( '(posteos sociales)', 'etheme' )
							: __( '(entradas)', 'etheme' ) }
					</p>
				<p>
					<strong>{ __( 'FAQs:', 'etheme' ) }</strong>{ ' ' }
					{ faqsTitle || __( '(desde config.json)', 'etheme' ) }
				</p>
				<p
					style={ {
						marginTop: '10px',
						fontSize: '12px',
						color: '#666',
					} }
				>
					{ __(
						'Este bloque renderiza la home page completa en el frontend con Hero, Productos, Categorías, Reviews, Blog y FAQs.',
						'etheme'
					) }
				</p>
				</div>
			</div>
		</>
	);
}
