---
alwaysApply: true
---

# No crear template overrides de WooCommerce

Este tema resuelve toda la presentación de WooCommerce a través de **bloques custom y hooks PHP**, no copiando templates del plugin.

## Cómo funciona WooCommerce en este tema

Cada página de WooCommerce tiene su propio bloque orquestador:

| Página | Bloque |
|---|---|
| Listado de productos | `src/archive-product/index/` |
| Detalle de producto | `src/single-product/index/` |
| Carrito | `src/page-cart/index/` |
| Checkout | `src/page-checkout/index/` |
| Mi cuenta | `src/page/index/` |

## Regla

No crear la carpeta `woocommerce/` en el tema.
Si hay que modificar comportamiento de WooCommerce, usar `add_filter` / `add_action` en `functions.php`.
