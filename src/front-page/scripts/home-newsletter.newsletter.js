// home-newsletter.
/**
 * Newsletter form AJAX submission handler.
 */
export function initNewsletter() {
	const form = document.getElementById( 'etheme-newsletter-form' );
	if ( ! form ) {
		return;
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		handleSubmit( form );
	} );
}

async function handleSubmit( form ) {
	const msgEl = document.getElementById( 'etheme-newsletter-message' );
	const emailInput = form.querySelector( 'input[name="email"]' );
	const submitBtn = form.querySelector( 'button[type="submit"]' );
	const email = emailInput.value.trim();

	if ( ! email || ! isValidEmail( email ) ) {
		showMessage( msgEl, 'Por favor, ingresá un email válido.', true );
		return;
	}

	submitBtn.disabled = true;
	submitBtn.textContent = 'Enviando…';
	showMessage( msgEl, '', false );

	try {
		const body = new FormData();
		body.append( 'action', 'etheme_newsletter_subscribe' );
		body.append( 'nonce', form.dataset.nonce );
		body.append( 'email', email );

		const res = await fetch( form.dataset.ajaxUrl, {
			method: 'POST',
			body,
		} );
		const json = await res.json();

		if ( json.success ) {
			showMessage( msgEl, json.data.message, false );
			emailInput.value = '';
		} else {
			showMessage( msgEl, json.data.message, true );
		}
	} catch {
		showMessage( msgEl, 'Error de conexión. Intentá de nuevo.', true );
	} finally {
		submitBtn.disabled = false;
		submitBtn.textContent = submitBtn.dataset.buttonText || 'Suscribirse';
	}
}

function isValidEmail( email ) {
	return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email );
}

function showMessage( el, text, isError ) {
	if ( ! el ) {
		return;
	}
	el.textContent = text;
	el.className = 'newsletter-message ' + ( isError ? 'is-error' : 'is-success' );
}
