/**
 * "Lanzar campaña" admin screen — live counter, coupon warnings, test send,
 * and batched campaign launch with progress.
 *
 * Plain vanilla JS, enqueued directly (admin-only, outside the webpack build).
 */
( function () {
	'use strict';

	var BATCH_SIZE = 25;

	var root = null;
	var running = false;

	function $( id ) {
		return document.getElementById( id );
	}

	function postForm( fields ) {
		var formData = new FormData();
		formData.append( 'nonce', root.dataset.nonce );
		Object.keys( fields ).forEach( function ( key ) {
			var value = fields[ key ];
			if ( Array.isArray( value ) ) {
				value.forEach( function ( v ) {
					formData.append( key + '[]', v );
				} );
			} else {
				formData.append( key, value );
			}
		} );
		return fetch( root.dataset.ajaxUrl, { method: 'POST', body: formData } ).then( function ( res ) {
			return res.json();
		} );
	}

	function getFilters() {
		var minTotal = $( 'etheme-campaign-min-total' ).value;
		return {
			days: $( 'etheme-campaign-days' ).value,
			min_total: minTotal === '' ? '0' : minTotal,
		};
	}

	function getEmailOptions() {
		return {
			subject: $( 'etheme-campaign-subject' ).value,
			intro: $( 'etheme-campaign-intro' ).value,
			coupon: $( 'etheme-campaign-coupon' ).value,
		};
	}

	function refreshCount() {
		var countEl = $( 'etheme-campaign-count' );
		countEl.textContent = '…';
		var fields = getFilters();
		fields.action = 'etheme_lead_campaign_count';
		postForm( fields )
			.then( function ( res ) {
				if ( res && res.success ) {
					countEl.textContent = res.data.count + ' leads';
				} else {
					countEl.textContent = '—';
				}
			} )
			.catch( function ( err ) {
				countEl.textContent = '—';
				console.error( 'etheme campaign count failed:', err );
			} );
	}

	function checkCouponWarning() {
		var select = $( 'etheme-campaign-coupon' );
		var warning = $( 'etheme-campaign-coupon-warning' );
		var option = select.options[ select.selectedIndex ];
		var text = '';

		if ( option && option.dataset.expired === '1' ) {
			text = 'Este cupón está vencido — elegí otro o extendé su vencimiento en Marketing → Cupones.';
		} else if ( option && option.dataset.exhausted === '1' ) {
			text = 'Este cupón agotó sus usos disponibles.';
		}

		warning.textContent = text;
		warning.hidden = text === '';
	}

	function showResult( message, isError ) {
		var result = $( 'etheme-campaign-result' );
		result.innerHTML = '';
		var p = document.createElement( 'div' );
		p.className = 'notice ' + ( isError ? 'notice-error' : 'notice-success' );
		var inner = document.createElement( 'p' );
		inner.textContent = message;
		p.appendChild( inner );
		result.appendChild( p );
	}

	function setButtonsDisabled( disabled ) {
		$( 'etheme-campaign-test' ).disabled = disabled;
		$( 'etheme-campaign-launch' ).disabled = disabled;
	}

	function requireCoupon() {
		var coupon = $( 'etheme-campaign-coupon' ).value;
		if ( ! coupon ) {
			showResult( 'Elegí el cupón de la campaña antes de enviar.', true );
			return false;
		}
		return true;
	}

	function sendTest() {
		if ( running || ! requireCoupon() ) {
			return;
		}
		setButtonsDisabled( true );
		var fields = getEmailOptions();
		fields.action = 'etheme_lead_campaign_test';
		postForm( fields )
			.then( function ( res ) {
				if ( res && res.success ) {
					showResult( res.data.message, false );
				} else {
					showResult( ( res && res.data && res.data.message ) || 'No se pudo enviar la prueba.', true );
				}
			} )
			.catch( function ( err ) {
				showResult( 'Error de red al enviar la prueba.', true );
				console.error( 'etheme campaign test failed:', err );
			} )
			.then( function () {
				setButtonsDisabled( false );
			} );
	}

	function updateProgress( done, total, failed ) {
		var box = $( 'etheme-campaign-progress' );
		var bar = $( 'etheme-campaign-progress-bar' );
		var text = $( 'etheme-campaign-progress-text' );
		box.hidden = false;
		bar.style.width = total > 0 ? Math.round( ( done / total ) * 100 ) + '%' : '0%';
		text.textContent = 'Enviados ' + done + ' / ' + total + ( failed > 0 ? ' (fallidos: ' + failed + ')' : '' );
	}

	function sendBatches( ids, options, filters ) {
		var totalSent = 0;
		var totalFailed = 0;
		var index = 0;

		function next() {
			if ( index >= ids.length ) {
				return finish();
			}
			var batch = ids.slice( index, index + BATCH_SIZE );
			index += BATCH_SIZE;

			var fields = {
				action: 'etheme_lead_campaign_send_batch',
				lead_ids: batch,
				subject: options.subject,
				intro: options.intro,
				coupon: options.coupon,
			};

			return postForm( fields )
				.then( function ( res ) {
					if ( res && res.success ) {
						totalSent += res.data.sent;
						totalFailed += res.data.failed;
					} else {
						totalFailed += batch.length;
						console.error( 'etheme campaign batch failed:', res && res.data );
					}
					updateProgress( Math.min( index, ids.length ), ids.length, totalFailed );
					return next();
				} )
				.catch( function ( err ) {
					totalFailed += batch.length;
					console.error( 'etheme campaign batch failed:', err );
					updateProgress( Math.min( index, ids.length ), ids.length, totalFailed );
					return next();
				} );
		}

		function finish() {
			var fields = {
				action: 'etheme_lead_campaign_finish',
				sent: String( totalSent ),
				failed: String( totalFailed ),
				coupon: options.coupon,
				days: filters.days,
				min_total: filters.min_total,
			};
			return postForm( fields )
				.catch( function ( err ) {
					console.error( 'etheme campaign log failed:', err );
				} )
				.then( function () {
					running = false;
					setButtonsDisabled( false );
					showResult(
						'Campaña enviada: ' + totalSent + ' ok' + ( totalFailed > 0 ? ', ' + totalFailed + ' fallidos (quedan como "Interesado" para reintentar).' : '.' ),
						totalFailed > 0
					);
					refreshCount();
				} );
		}

		updateProgress( 0, ids.length, 0 );
		return next();
	}

	function launch() {
		if ( running || ! requireCoupon() ) {
			return;
		}

		var filters = getFilters();
		var options = getEmailOptions();
		var fields = getFilters();
		fields.action = 'etheme_lead_campaign_ids';

		setButtonsDisabled( true );
		postForm( fields )
			.then( function ( res ) {
				if ( ! res || ! res.success ) {
					setButtonsDisabled( false );
					showResult( ( res && res.data && res.data.message ) || 'No se pudo obtener la lista de destinatarios.', true );
					return;
				}
				var ids = res.data.ids;
				if ( ! ids.length ) {
					setButtonsDisabled( false );
					showResult( 'No hay leads que coincidan con los filtros.', true );
					return;
				}
				if ( ! window.confirm( 'Vas a enviar el email a ' + ids.length + ' personas. ¿Confirmás?' ) ) {
					setButtonsDisabled( false );
					return;
				}
				running = true;
				sendBatches( ids, options, filters );
			} )
			.catch( function ( err ) {
				setButtonsDisabled( false );
				showResult( 'Error de red al preparar la campaña.', true );
				console.error( 'etheme campaign launch failed:', err );
			} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		root = $( 'etheme-lead-campaign' );
		if ( ! root ) {
			return;
		}

		document.querySelectorAll( '[data-campaign-filter]' ).forEach( function ( el ) {
			el.addEventListener( 'change', refreshCount );
		} );
		$( 'etheme-campaign-coupon' ).addEventListener( 'change', checkCouponWarning );
		$( 'etheme-campaign-test' ).addEventListener( 'click', sendTest );
		$( 'etheme-campaign-launch' ).addEventListener( 'click', launch );

		refreshCount();
	} );
} )();
