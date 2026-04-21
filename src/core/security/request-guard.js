/**
 * Request hardening helpers.
 *
 * Provides timeout, abort, double-submit lock, payload size guard,
 * retry for network errors, and normalized error handling.
 */

const DEFAULT_TIMEOUT_MS = 15000;
const MAX_PAYLOAD_BYTES = 50000;
const MAX_RETRIES = 1;

/**
 * Create an AbortController that auto-aborts after `ms`.
 *
 * @param {number} ms Timeout in milliseconds.
 * @returns {{ controller: AbortController, clear: () => void }}
 */
function timeoutController( ms ) {
	const controller = new AbortController();
	const id = setTimeout( () => controller.abort(), ms );
	return { controller, clear: () => clearTimeout( id ) };
}

/**
 * Guard: check that a JSON payload doesn't exceed the size cap.
 *
 * @param {*} payload
 * @returns {boolean}
 */
function isPayloadSafe( payload ) {
	try {
		return JSON.stringify( payload ).length <= MAX_PAYLOAD_BYTES;
	} catch {
		return false;
	}
}

/**
 * Normalize any fetch error into a simple { ok, status, message } shape.
 *
 * @param {Error} err
 * @returns {{ ok: false, status: number, message: string }}
 */
function normalizeError( err ) {
	if ( err.name === 'AbortError' ) {
		return { ok: false, status: 0, message: 'timeout' };
	}
	return { ok: false, status: 0, message: 'network' };
}

/**
 * Hardened JSON POST fetch with timeout, abort, and retry.
 *
 * @param {string}  url
 * @param {Object}  payload
 * @param {Object}  [options]
 * @param {number}  [options.timeout]
 * @param {Record<string,string>} [options.headers]
 * @returns {Promise<{ ok: boolean, status: number, data?: any, message?: string }>}
 */
export async function guardedJsonPost( url, payload, options = {} ) {
	if ( ! isPayloadSafe( payload ) ) {
		return { ok: false, status: 0, message: 'payload_too_large' };
	}

	const timeout = options.timeout || DEFAULT_TIMEOUT_MS;
	let lastError;

	for ( let attempt = 0; attempt <= MAX_RETRIES; attempt++ ) {
		const { controller, clear } = timeoutController( timeout );
		try {
			const res = await fetch( url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', ...( options.headers || {} ) },
				body: JSON.stringify( payload ),
				signal: controller.signal,
			} );
			clear();

			if ( ! res.ok ) {
				return { ok: false, status: res.status, message: 'server' };
			}

			const data = await res.json();
			return { ok: true, status: res.status, data };
		} catch ( err ) {
			clear();
			lastError = err;
			if ( err.name !== 'AbortError' && attempt < MAX_RETRIES ) {
				continue;
			}
			return normalizeError( err );
		}
	}

	return normalizeError( lastError );
}

/**
 * Hardened FormData POST (for WP AJAX endpoints) with timeout.
 *
 * @param {string}   url
 * @param {FormData} formData
 * @param {Object}   [options]
 * @param {number}   [options.timeout]
 * @returns {Promise<{ ok: boolean, status: number, data?: any, message?: string }>}
 */
export async function guardedFormPost( url, formData, options = {} ) {
	const timeout = options.timeout || DEFAULT_TIMEOUT_MS;
	const { controller, clear } = timeoutController( timeout );

	try {
		const res = await fetch( url, {
			method: 'POST',
			body: formData,
			signal: controller.signal,
		} );
		clear();

		const data = await res.json();
		return { ok: data.success !== false, status: res.status, data: data.data || data };
	} catch ( err ) {
		clear();
		return normalizeError( err );
	}
}

const _locks = new Set();

/**
 * Double-submit lock. Returns `true` if the lock was acquired.
 * Call `releaseLock( key )` when the operation finishes.
 *
 * @param {string} key Unique operation identifier.
 * @returns {boolean}
 */
export function acquireLock( key ) {
	if ( _locks.has( key ) ) return false;
	_locks.add( key );
	return true;
}

/**
 * Release a previously acquired lock.
 *
 * @param {string} key
 */
export function releaseLock( key ) {
	_locks.delete( key );
}
