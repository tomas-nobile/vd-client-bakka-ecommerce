---
alwaysApply: true
---

# Seguridad PHP — escape y nonces

## Salida

Toda salida PHP debe escaparse según el contexto:

| Contexto | Función |
|---|---|
| HTML | `esc_html()` |
| Atributos HTML | `esc_attr()` |
| URLs | `esc_url()` |
| JS inline | `esc_js()` |

## Entrada

Toda entrada debe sanitizarse antes de usarse:

- `sanitize_text_field()` — texto plano
- `sanitize_email()` — emails
- `wc_clean()` — datos de WooCommerce
- `absint()` — enteros positivos (IDs, cantidades)

## AJAX handlers

Todo handler AJAX con POST debe verificar nonce antes de procesar:

```php
check_ajax_referer( 'nombre-del-nonce', 'nonce' );
```

Sin esta verificación el handler no debe ejecutarse.
