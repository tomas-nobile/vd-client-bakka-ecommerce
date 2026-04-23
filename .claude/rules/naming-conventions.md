---
alwaysApply: true
---

# Convenciones de nomenclatura

## Archivos

| Tipo | Formato | Ejemplo |
|---|---|---|
| Componente PHP | `{component-name}.php` | `filter-menu.php` |
| Script de componente | `{component-name}.js` | `filter-menu.js` |
| Script compartido (core) | `core.{name}.js` | `core.drawer.js` |
| Parcial SCSS | `{component-name}.scss` | `filter-menu.scss` |

## Funciones PHP

| Tipo | Formato | Ejemplo |
|---|---|---|
| Render de componente | `etheme_render_{component_name}()` | `etheme_render_product_card()` |
| Helper | `etheme_{group}_{helper_name}()` | `etheme_cart_get_totals()` |
| Enqueue | `etheme_enqueue_{context}_styles()` | `etheme_enqueue_front_page_styles()` |

## CSS

- Clases utilitarias: Tailwind (sin prefijo)
- Clases custom: `etheme-{nombre}` o prefijo del componente
- BEM para componentes complejos: `etheme-card__title`, `etheme-card--featured`

## JavaScript

- Variables globales: `etheme_{nombre}`
- Funciones de inicialización: `init{ComponentName}()`
