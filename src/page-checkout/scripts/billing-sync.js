/**
 * Billing address synchronization script.
 * Synchronizes shipping fields with hidden billing fields.
 */

(function() {
	'use strict';

	// Flag to prevent infinite recursion
	let isSyncing = false;

	// Mapping of shipping fields to billing fields
	const FIELD_MAPPING = {
		'shipping_first_name': 'billing_first_name',
		'shipping_last_name': 'billing_last_name', 
		'shipping_company': 'billing_company',
		'shipping_country': 'billing_country',
		'shipping_address_1': 'billing_address_1',
		'shipping_address_2': 'billing_address_2',
		'shipping_city': 'billing_city',
		'shipping_state': 'billing_state',
		'shipping_postcode': 'billing_postcode'
	};

	// Email field is separate (from contact information)
	const EMAIL_FIELD = 'billing_email';

	/**
	 * Get form field value
	 * @param {string} fieldName 
	 * @returns {string}
	 */
	function getFieldValue(fieldName) {
		const field = document.querySelector(`[name="${fieldName}"]`);
		if (!field) return '';
		
		if (field.type === 'select-one') {
			return field.value || '';
		}
		
		return field.value || '';
	}

	/**
	 * Set form field value
	 * @param {string} fieldName 
	 * @param {string} value 
	 */
	function setFieldValue(fieldName, value) {
		const field = document.querySelector(`[name="${fieldName}"]`);
		if (!field) return;

		// Don't update if value is the same to avoid unnecessary events
		if (field.value === value) return;

		if (field.type === 'select-one') {
			// For select fields, try to set the value
			field.value = value;
			
			// If it's a Select2 field, trigger change only when not syncing
			if (!isSyncing && window.jQuery && window.jQuery(field).hasClass('select2-hidden-accessible')) {
				window.jQuery(field).trigger('change');
			}
		} else {
			field.value = value;
		}

		// Trigger change event for WooCommerce validation only when not syncing
		if (!isSyncing) {
			field.dispatchEvent(new Event('change', { bubbles: true }));
		}
	}

	/**
	 * Sync single field from shipping to billing
	 * @param {string} shippingField 
	 * @param {string} billingField 
	 */
	function syncField(shippingField, billingField) {
		const value = getFieldValue(shippingField);
		setFieldValue(billingField, value);
	}

	/**
	 * Sync all fields
	 */
	function syncAllFields() {
		// Prevent infinite recursion
		if (isSyncing) {
			return;
		}

		isSyncing = true;

		try {
			// Sync shipping -> billing fields
			Object.entries(FIELD_MAPPING).forEach(([shippingField, billingField]) => {
				syncField(shippingField, billingField);
			});

			// Special case: billing_phone is rendered in shipping section, ensure it syncs to hidden billing_phone too
			const phoneValue = getFieldValue('billing_phone');
			if (phoneValue) {
				const hiddenPhoneField = document.querySelector('#billing-address-sync [name="billing_phone"]');
				if (hiddenPhoneField && hiddenPhoneField.value !== phoneValue) {
					hiddenPhoneField.value = phoneValue;
				}
			}

			// Email stays in billing_email (no sync needed as it's already billing)
		} finally {
			isSyncing = false;
		}
	}

	/**
	 * Initialize field synchronization
	 */
	function initSync() {
		// Initial sync
		setTimeout(syncAllFields, 500);

		// Add event listeners to shipping fields only
		Object.keys(FIELD_MAPPING).forEach(shippingField => {
			const field = document.querySelector(`[name="${shippingField}"]`);
			if (!field) return;

			// Debounced sync function to prevent excessive calls
			let timeoutId;
			const debouncedSync = function() {
				clearTimeout(timeoutId);
				timeoutId = setTimeout(syncAllFields, 50);
			};

			// Regular change event
			field.addEventListener('change', debouncedSync);
			field.addEventListener('blur', debouncedSync);
			field.addEventListener('input', debouncedSync);

			// For Select2 fields
			if (window.jQuery && window.jQuery(field).hasClass('select2-hidden-accessible')) {
				window.jQuery(field).on('select2:select', debouncedSync);
				window.jQuery(field).on('select2:unselect', debouncedSync);
			}
		});

		// Also sync email field changes
		const emailField = document.querySelector(`[name="${EMAIL_FIELD}"]`);
		if (emailField) {
			let emailTimeoutId;
			const debouncedEmailSync = function() {
				clearTimeout(emailTimeoutId);
				emailTimeoutId = setTimeout(syncAllFields, 50);
			};
			
			emailField.addEventListener('change', debouncedEmailSync);
			emailField.addEventListener('blur', debouncedEmailSync);
		}

		// Also sync phone field changes (billing_phone is in shipping section)
		const phoneField = document.querySelector(`[name="billing_phone"]`);
		if (phoneField) {
			let phoneTimeoutId;
			const debouncedPhoneSync = function() {
				clearTimeout(phoneTimeoutId);
				phoneTimeoutId = setTimeout(syncAllFields, 50);
			};
			
			phoneField.addEventListener('change', debouncedPhoneSync);
			phoneField.addEventListener('blur', debouncedPhoneSync);
			phoneField.addEventListener('input', debouncedPhoneSync);
		}

		// Sync before form submission
		const checkoutForm = document.querySelector('form.checkout');
		if (checkoutForm) {
			checkoutForm.addEventListener('submit', function() {
				syncAllFields();
			});
		}

		// Also sync on WooCommerce checkout update
		if (window.jQuery) {
			window.jQuery(document.body).on('updated_checkout', function() {
				// Small delay to ensure fields are ready
				setTimeout(syncAllFields, 200);
			});
		}
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initSync);
	} else {
		initSync();
	}

	// Also initialize on load as fallback
	window.addEventListener('load', initSync);

	// Re-initialize after a delay (for dynamic content)
	setTimeout(initSync, 1000);
})();