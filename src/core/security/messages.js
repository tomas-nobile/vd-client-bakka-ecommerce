/**
 * Centralized message catalog (Spanish).
 *
 * Consumed by coupon, checkout, and contact flows for consistency.
 */

export const FIELD = {
	required: 'Este campo es obligatorio.',
	nameInvalid: 'Ingresá solo letras en este campo.',
	emailInvalid: 'Ingresá un email válido.',
	phoneInvalid: 'Ingresá solo números.',
	phoneMin: 'El teléfono es demasiado corto.',
	postcodeInvalid: 'El código postal debe tener 4 dígitos.',
	lengthRange: ( min, max ) => `Debe tener entre ${ min } y ${ max } caracteres.`,
	couponFormat: 'El código de cupón contiene caracteres no válidos.',
	messageRequired: 'Por favor escribí tu mensaje.',
	nameRequired: 'Por favor ingresá tu nombre.',
	phoneRequired: 'Por favor ingresá tu teléfono.',
	emailRequired: 'Por favor ingresá tu email.',
};

export const FORM = {
	loading: 'Enviando...',
	success: 'Gracias por tu mensaje. Te responderemos a la brevedad.',
	errorNetwork: 'Error de conexión. Revisá tu internet e intentá de nuevo.',
	errorServer: 'Ocurrió un error. Por favor intentá más tarde.',
	errorGeneric: 'No pudimos procesar tu solicitud. Intentá nuevamente.',
	cooldown: 'Esperá unos segundos antes de volver a enviar.',
};

export const COUPON = {
	empty: 'Ingresá un código de cupón.',
	format: 'El cupón solo puede contener letras, números, guiones y guiones bajos.',
	applied: 'Cupón aplicado correctamente.',
	removed: 'Cupón eliminado.',
	invalid: 'Código de cupón inválido.',
	errorApply: 'No se pudo aplicar el cupón. Intentá de nuevo.',
	errorRemove: 'No se pudo eliminar el cupón.',
};

export const CHECKOUT = {
	step1Incomplete: 'Corregí los siguientes errores para continuar:',
	regionBlocked: 'Solo realizamos envíos a Capital Federal y Gran Buenos Aires.',
};

export const FIELD_LABELS = {
	billing_email: 'Email',
	billing_first_name: 'Nombre',
	billing_last_name: 'Apellido',
	billing_address_1: 'Dirección',
	billing_city: 'Ciudad',
	billing_postcode: 'Código postal',
	shipping_first_name: 'Nombre',
	shipping_last_name: 'Apellido',
	shipping_address_1: 'Dirección',
	shipping_city: 'Ciudad',
	shipping_address_2: 'Apartamento',
	shipping_postcode: 'Código postal',
	checkout_phone_area: 'Cód. de área',
	checkout_phone_number: 'Número de teléfono',
	'checkout-province-display': 'Provincia',
};
