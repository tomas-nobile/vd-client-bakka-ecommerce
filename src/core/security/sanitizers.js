/**
 * Client-side string sanitization utilities.
 *
 * SANITIZE → VALIDATE → ESCAPE
 * This module covers the SANITIZE step: normalize user input before validation.
 */

const CONTROL_CHARS = /[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g;
const MULTI_SPACE = /\s{2,}/g;

/**
 * @param {string} value
 * @returns {string}
 */
export function trimValue( value ) {
	return typeof value === 'string' ? value.trim() : '';
}

/**
 * Collapse multiple whitespace characters into a single space and trim.
 *
 * @param {string} value
 * @returns {string}
 */
export function normalizeWhitespace( value ) {
	return trimValue( value ).replace( MULTI_SPACE, ' ' );
}

/**
 * Strip C0 control characters (except \t, \n, \r) that have no business in form input.
 *
 * @param {string} value
 * @returns {string}
 */
export function stripControlChars( value ) {
	return typeof value === 'string' ? value.replace( CONTROL_CHARS, '' ) : '';
}

/**
 * Full sanitize pipeline for a generic text field.
 *
 * @param {string} value
 * @returns {string}
 */
export function sanitizeText( value ) {
	return normalizeWhitespace( stripControlChars( value ) );
}

/**
 * Sanitize an email: trim, lowercase, strip control chars.
 *
 * @param {string} value
 * @returns {string}
 */
export function sanitizeEmail( value ) {
	return stripControlChars( trimValue( value ) ).toLowerCase();
}

/**
 * Sanitize a phone input: keep only digits, plus, hyphens, spaces, parens.
 *
 * @param {string} value
 * @returns {string}
 */
export function sanitizePhone( value ) {
	return trimValue( value ).replace( /[^\d+\-() ]/g, '' );
}

/**
 * Sanitize to digits only.
 *
 * @param {string} value
 * @returns {string}
 */
export function sanitizeDigits( value ) {
	return trimValue( value ).replace( /\D/g, '' );
}

/**
 * Sanitize a coupon code: trim, uppercase, strip anything outside [A-Z0-9_-].
 *
 * @param {string} value
 * @returns {string}
 */
export function sanitizeCoupon( value ) {
	return trimValue( value )
		.toUpperCase()
		.replace( /[^A-Z0-9_\-]/g, '' );
}
