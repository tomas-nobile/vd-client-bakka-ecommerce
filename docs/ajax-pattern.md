# Patrón AJAX en el tema Bakka

Guía completa para implementar un endpoint AJAX nuevo siguiendo el patrón del tema.

---

## Estructura de archivos

```
src/{grupo}/
├── includes/
│   └── ajax-handlers.php     ← handler PHP del endpoint
└── scripts/
    └── {componente}.js       ← llamada fetch desde el frontend
```

El handler se registra en `functions.php` con `require_once`.

---

## 1. PHP — Handler

**`src/{grupo}/includes/ajax-handlers.php`**

```php
<?php
// Registrar el action (usuarios logueados y no logueados)
add_action( 'wp_ajax_etheme_{accion}', 'etheme_{grupo}_{accion}_handler' );
add_action( 'wp_ajax_nopriv_etheme_{accion}', 'etheme_{grupo}_{accion}_handler' );

function etheme_{grupo}_{accion}_handler() {
    // 1. Verificar nonce
    check_ajax_referer( 'etheme-{grupo}-nonce', 'nonce' );

    // 2. Sanitizar input
    $dato = sanitize_text_field( wp_unslash( $_POST['dato'] ?? '' ) );

    // 3. Validar
    if ( empty( $dato ) ) {
        wp_send_json_error( array( 'message' => 'Dato requerido.' ), 400 );
    }

    // 4. Lógica de negocio
    $resultado = /* ... */;

    // 5. Responder
    wp_send_json_success( array( 'data' => $resultado ) );
}
```

**Registrar en `functions.php`:**
```php
require_once get_template_directory() . '/src/{grupo}/includes/ajax-handlers.php';
```

**Pasar nonce al frontend (en `render.php` del bloque):**
```php
<div
    id="etheme-{grupo}"
    data-nonce="<?php echo esc_attr( wp_create_nonce( 'etheme-{grupo}-nonce' ) ); ?>"
>
```

---

## 2. JavaScript — Llamada fetch

**`src/{grupo}/scripts/{componente}.js`**

```js
import { requestGuard } from '../../core/security/request-guard.js';
import { sanitizeText } from '../../core/security/sanitizers.js';
import { showFieldError, setLoading } from '../../core/security/ui-feedback.js';

export function initComponente() {
    const el = document.getElementById( 'etheme-{grupo}' );
    if ( ! el ) return;

    const nonce = el.dataset.nonce;

    el.querySelector( '[data-action="submit"]' )?.addEventListener( 'click', async () => {
        const dato = sanitizeText( el.querySelector( '[name="dato"]' ).value );

        const response = await requestGuard( {
            url: window.ethemeAjax?.url ?? '/wp-admin/admin-ajax.php',
            body: {
                action: 'etheme_{grupo}_{accion}',
                nonce,
                dato,
            },
        } );

        if ( response.success ) {
            // manejar respuesta exitosa
        } else {
            showFieldError( el.querySelector( '[name="dato"]' ), response.data?.message );
        }
    } );
}
```

---

## 3. Pasar la URL de admin-ajax al frontend

En `functions.php`, dentro del enqueue del bloque:

```php
wp_localize_script(
    'etheme-{grupo}-index-view-script',
    'ethemeAjax',
    array( 'url' => admin_url( 'admin-ajax.php' ) )
);
```

O via `wp_add_inline_script` si el handle ya está registrado por `block.json`.

---

## Respuestas estándar

| Caso | Función PHP | Shape JS |
|---|---|---|
| Éxito | `wp_send_json_success( $data )` | `{ success: true, data: ... }` |
| Error validación | `wp_send_json_error( $data, 400 )` | `{ success: false, data: ... }` |
| Error no autorizado | `wp_send_json_error( $data, 403 )` | `{ success: false, data: ... }` |

`wp_send_json_*` llama `die()` internamente — no escribir nada después.

---

## Ejemplos en el tema (referencia)

| Feature | Handler PHP | Script JS |
|---|---|---|
| Carrito (update qty, remove, coupon) | `src/page-cart/includes/ajax-handlers.php` | `src/page-cart/scripts/` |
| Newsletter | `src/front-page/includes/home-newsletter.ajax-handlers.php` | `src/front-page/scripts/` |
| Posteos (load more) | `src/page-posteos/includes/ajax-handlers.php` | `src/page-posteos/scripts/` |
