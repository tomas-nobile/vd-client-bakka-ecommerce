<?php
/**
 * Page My Account Index - Main orchestrator for login, register, and account dashboard.
 *
 * Uses WooCommerce-compatible nonces and field names so WC_Form_Handler
 * processes login and registration natively (password setup link, redirects, etc.).
 *
 * Logged-in routing:
 * - Base My Account page → custom dashboard (account-dashboard.php)
 * - WooCommerce endpoint (orders, addresses, etc.) → WooCommerce shortcode handles it
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	?>
	<div <?php echo get_block_wrapper_attributes(); ?>>
		<p class="text-center text-gray-500 py-8"><?php esc_html_e( 'WooCommerce is required for this page.', 'etheme' ); ?></p>
	</div>
	<?php
	return;
}

$components_dir = get_template_directory() . '/src/page-myaccount/components/';
$components     = array( 'login-form', 'register-form', 'account-dashboard' );

foreach ( $components as $component ) {
	$file = $components_dir . $component . '.php';
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

$defaults   = array(
	'showRegister' => true,
);
$attributes = wp_parse_args( $attributes, $defaults );

$is_logged_in  = is_user_logged_in();
$show_register = $attributes['showRegister'];
$myaccount_url = wc_get_page_permalink( 'myaccount' );
?>

<div <?php echo get_block_wrapper_attributes( array( 'class' => 'page-myaccount-block bg-white py-8 md:py-16 lg:py-24' ) ); ?>>
	<div class="mx-auto max-w-7xl px-4 md:px-6 lg:px-8">

		<?php wc_print_notices(); ?>

		<?php if ( $is_logged_in ) : ?>

			<?php if ( is_wc_endpoint_url() ) : ?>
				<?php echo do_shortcode( '[woocommerce_my_account]' ); ?>
			<?php else : ?>
				<?php
				if ( function_exists( 'etheme_render_account_dashboard' ) ) {
					etheme_render_account_dashboard();
				}
				?>
			<?php endif; ?>

		<?php else : ?>

			<div class="myaccount-auth mx-auto max-w-md">

				<!-- Login Form -->
				<?php
				if ( function_exists( 'etheme_render_login_form' ) ) {
					etheme_render_login_form( $myaccount_url, $show_register );
				}
				?>

				<?php if ( $show_register ) : ?>
				<!-- Register CTA -->
				<p class="mt-6 text-center text-sm text-gray-500">
					<?php esc_html_e( "Don't have an account?", 'etheme' ); ?>
					<button
						type="button"
						id="myaccount-open-register"
						class="font-semibold text-gray-900 underline decoration-gray-300 underline-offset-4 hover:decoration-gray-900 transition-colors"
					>
						<?php esc_html_e( 'Create one', 'etheme' ); ?>
					</button>
				</p>

				<!-- Register Modal -->
				<?php
				if ( function_exists( 'etheme_render_register_form' ) ) {
					etheme_render_register_form( $myaccount_url );
				}
				?>
				<?php endif; ?>

			</div>

		<?php endif; ?>

	</div>

	<?php if ( ! $is_logged_in ) : ?>
	<!-- Inline styles — works before build -->
	<style>
	/* Modal overlay */
	.myaccount-modal-overlay{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;opacity:0;visibility:hidden;transition:opacity .25s ease,visibility .25s ease}
	.myaccount-modal-overlay.is-open{opacity:1;visibility:visible}
	.myaccount-modal-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px)}
	/* Modal panel */
	.myaccount-modal-panel{position:relative;width:100%;max-width:420px;max-height:calc(100vh - 2rem);overflow-y:auto;background:#fff;border-radius:1rem;box-shadow:0 25px 50px -12px rgba(0,0,0,.25);transform:translateY(16px) scale(.97);transition:transform .25s ease;padding:1.75rem}
	.myaccount-modal-overlay.is-open .myaccount-modal-panel{transform:translateY(0) scale(1)}
	@media(min-width:768px){.myaccount-modal-panel{padding:2rem}}
	/* Close button */
	.myaccount-modal-close{position:absolute;top:1rem;right:1rem;width:2rem;height:2rem;display:flex;align-items:center;justify-content:center;border-radius:.5rem;border:none;background:transparent;cursor:pointer;color:#9ca3af;transition:color .15s,background .15s}
	.myaccount-modal-close:hover{color:#111;background:#f3f4f6}
	/* Focus ring for inputs inside modal (Tailwind handles login form) */
	.myaccount-modal-panel .myaccount-input:focus{border-color:#111;outline:none;box-shadow:0 0 0 1px #111}
	/* Body scroll lock */
	body.myaccount-modal-open{overflow:hidden}
	</style>

	<!-- Inline script — works before build -->
	<script>
	(function(){
		var overlay=document.getElementById('myaccount-register-modal');
		var openBtn=document.getElementById('myaccount-open-register');
		var closeBtn=overlay?overlay.querySelector('.myaccount-modal-close'):null;
		var backdrop=overlay?overlay.querySelector('.myaccount-modal-backdrop'):null;
		var panel=overlay?overlay.querySelector('.myaccount-modal-panel'):null;
		var emailInput=overlay?overlay.querySelector('#reg_email'):null;
		var lastFocused=null;

		if(!overlay||!openBtn)return;

		function openModal(){
			lastFocused=document.activeElement;
			overlay.classList.add('is-open');
			document.body.classList.add('myaccount-modal-open');
			if(emailInput)setTimeout(function(){emailInput.focus()},100);
		}
		function closeModal(){
			overlay.classList.remove('is-open');
			document.body.classList.remove('myaccount-modal-open');
			if(lastFocused)lastFocused.focus();
		}

		openBtn.addEventListener('click',openModal);
		if(closeBtn)closeBtn.addEventListener('click',closeModal);
		if(backdrop)backdrop.addEventListener('click',closeModal);

		document.addEventListener('keydown',function(e){
			if(e.key==='Escape'&&overlay.classList.contains('is-open'))closeModal();
		});

		/* Focus trap */
		if(panel){
			panel.addEventListener('keydown',function(e){
				if(e.key!=='Tab')return;
				var focusable=panel.querySelectorAll('input,button,a,[tabindex]:not([tabindex="-1"])');
				if(!focusable.length)return;
				var first=focusable[0],last=focusable[focusable.length-1];
				if(e.shiftKey){if(document.activeElement===first){e.preventDefault();last.focus()}}
				else{if(document.activeElement===last){e.preventDefault();first.focus()}}
			});
		}

		/* Password visibility toggle */
		document.addEventListener('click',function(e){
			var btn=e.target.closest('[data-toggle-password]');
			if(!btn)return;
			var id=btn.getAttribute('data-toggle-password');
			var inp=document.getElementById(id);
			if(!inp)return;
			var isPw=inp.type==='password';
			inp.type=isPw?'text':'password';
			btn.innerHTML=isPw
				?'<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
				:'<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
			btn.setAttribute('aria-label',isPw?'Hide password':'Show password');
		});
	})();
	</script>
	<?php endif; ?>
</div>
