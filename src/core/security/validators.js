/**
 * Pure validation functions.
 *
 * Each validator receives an already-sanitized value and returns
 * `true` (valid) or `false` (invalid). They carry no side effects.
 */

/**
 * @param {string} value
 * @returns {boolean}
 */
export function required( value ) {
	return typeof value === 'string' && value.trim().length > 0;
}

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

/**
 * @param {string} value
 * @returns {boolean}
 */
export function email( value ) {
	return EMAIL_RE.test( value );
}

/**
 * Digits-only phone part (area code or number).
 *
 * @param {string} value
 * @returns {boolean}
 */
export function phoneDigits( value ) {
	return /^\d+$/.test( value ) && value.length >= 2;
}

/**
 * @param {string} value
 * @returns {boolean}
 */
export function numeric( value ) {
	return /^\d+$/.test( value );
}

/**
 * @param {string} value
 * @param {number} min
 * @param {number} max
 * @returns {boolean}
 */
export function length( value, min, max ) {
	const len = typeof value === 'string' ? value.length : 0;
	return len >= min && len <= max;
}

/**
 * Argentine postcode: exactly 4 digits.
 *
 * @param {string} value
 * @returns {boolean}
 */
export function postcodeAR( value ) {
	return /^\d{4}$/.test( value );
}

/**
 * Human name validation (letters, spaces, apostrophes, hyphens, dots).
 * Rejects digits and symbols commonly used in payloads.
 *
 * @param {string} value
 * @returns {boolean}
 */
export function personName( value ) {
	return /^(?=.*[A-Za-zÀ-ÖØ-öø-ÿ])[A-Za-zÀ-ÖØ-öø-ÿ'’.\-\s]+$/.test(
		value
	);
}

/**
 * Match against a custom regex pattern.
 *
 * @param {string} value
 * @param {RegExp} pattern
 * @returns {boolean}
 */
export function pattern( value, pattern ) {
	return pattern.test( value );
}

/**
 * Coupon code: 2-30 chars, alphanumeric + underscores + hyphens.
 *
 * @param {string} value
 * @returns {boolean}
 */
export function couponCode( value ) {
	return /^[A-Za-z0-9_\-]{2,30}$/.test( value );
}
