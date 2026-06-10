// announcement-bar — dismiss with persistence.
// The bar renders visible server-side (so it shows even if JS fails); this
// module removes it if the user previously closed it and wires the close button.

const STORAGE_KEY = 'etheme_announcement_closed';

export function initAnnouncementBar() {
	const bar = document.querySelector( '.etheme-announcement-bar' );

	if ( ! bar ) {
		return;
	}

	let dismissed = false;
	try {
		dismissed = window.localStorage.getItem( STORAGE_KEY ) === '1';
	} catch ( error ) {
		// localStorage may be unavailable (private mode) — show the bar.
	}

	if ( dismissed ) {
		bar.remove();
		return;
	}

	const closeBtn = bar.querySelector( '.etheme-announcement-bar__close' );
	if ( ! closeBtn ) {
		return;
	}

	closeBtn.addEventListener( 'click', () => {
		bar.remove();
		try {
			window.localStorage.setItem( STORAGE_KEY, '1' );
		} catch ( error ) {
			// Non-fatal: dismissal simply won't persist.
		}
	} );
}
