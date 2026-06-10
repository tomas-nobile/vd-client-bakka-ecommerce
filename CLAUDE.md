# CLAUDE.md — Bakka Theme

## Rules

Las siguientes reglas aplican siempre. Leerlas antes de cualquier tarea:

- [block-theme-orchestrator](.claude/rules/block-theme-orchestrator.md) — patrón orquestador, nunca HTML en render.php
- [no-build-commands](.claude/rules/no-build-commands.md) — no correr npm run build
- [no-build-edits](.claude/rules/no-build-edits.md) — nunca editar build/
- [css-import-chain](.claude/rules/css-import-chain.md) — cadena index.js → style.scss → parciales
- [use-security-modules](.claude/rules/use-security-modules.md) — sanitize → validate → request-guard
- [php-escape-and-nonce](.claude/rules/php-escape-and-nonce.md) — esc_html, nonces en AJAX
- [shared-components-first](.claude/rules/shared-components-first.md) — revisar src/core/components/ primero
- [specs-before-code](.claude/rules/specs-before-code.md) — leer specs/ antes de implementar
- [no-wc-overrides](.claude/rules/no-wc-overrides.md) — WooCommerce via bloques y hooks, no template overrides
- [errors-only-logging](.claude/rules/errors-only-logging.md) — solo console.error / error_log para errores
- [naming-conventions](.claude/rules/naming-conventions.md) — kebab-case archivos, prefijo etheme_ funciones

---

## Docs

Documentación de referencia disponible en `docs/`:

| Doc | Contenido |
|---|---|
| [ajax-pattern.md](docs/ajax-pattern.md) | Patrón completo para implementar un endpoint AJAX (PHP handler + JS fetch + nonce) |
| [config-json.md](docs/config-json.md) | Qué hay en `src/core/config/config.json` y cómo leerlo desde PHP y JS |
| [CARGAR-ESTILOS-BLOQUES.md](docs/CARGAR-ESTILOS-BLOQUES.md) | Cómo asegurar que los estilos de un bloque carguen en el frontend |
| [WOOCOMMERCE-FUNCTIONS.md](docs/WOOCOMMERCE-FUNCTIONS.md) | Referencia de funciones WooCommerce: productos, carrito, órdenes, clientes |
| [WOOCOMMERCE-PAGES.md](docs/WOOCOMMERCE-PAGES.md) | Para qué sirve cada template y cuándo se usa |
| [SECURITY_TESTING.md](docs/SECURITY_TESTING.md) | Guía de pruebas de seguridad para cart, checkout y contacto |

---

## Project Overview

**Bakka** is a custom WordPress Full Site Editing (FSE) block theme for a WooCommerce storefront (Argentine furniture e-commerce). It uses server-side rendered blocks, Tailwind CSS, and a modular orchestrator architecture.

- Theme slug: `bakka` | Namespace: `etheme` | Text domain: `etheme`
- Stack: WordPress FSE + WooCommerce + Tailwind v4 + custom Webpack build

---

## Core Architecture

### Orchestrator Pattern

Every page section is a **custom block** in `src/{group}/index/`. Each orchestrator block:

1. Registers via `functions.php` → `register_block_type('build/{group}/index')`
2. Renders server-side in `render.php` (loads PHP components)
3. Initializes frontend behavior via `view.js`
4. Styles via `style.scss` (imports SCSS partials from `styles/`)

```
src/{group}/
├── index/           ← orchestrator block
│   ├── block.json   ← metadata, attributes, scripts, styles
│   ├── render.php   ← server-side HTML output
│   ├── edit.js      ← block editor UI (React)
│   ├── index.js     ← editor entry (imports editor.scss)
│   ├── view.js      ← frontend JS initialization
│   └── style.scss   ← frontend styles entry
├── components/      ← reusable PHP partials (render.php loads these)
├── scripts/         ← JS modules (imported by view.js)
├── styles/          ← SCSS partials (imported by style.scss)
└── includes/        ← PHP helpers, AJAX handlers, CPTs
```

### Registered Blocks (13 total)

| Block | Location | Purpose |
|---|---|---|
| `core/navbar` | `src/core/navbar/` | Main navigation |
| `core/header` | `src/core/header/` | Page header |
| `core/footer` | `src/core/footer/` | Footer |
| `front-page/index` | `src/front-page/index/` | Home page |
| `archive-product/index` | `src/archive-product/index/` | Product listing + filters |
| `single-product/index` | `src/single-product/index/` | Product detail |
| `page-cart/index` | `src/page-cart/index/` | Shopping cart |
| `page-checkout/index` | `src/page-checkout/index/` | 2-step checkout |
| `page/index` | `src/page/index/` | Generic pages + My Account |
| `page-trabajos-realizados/index` | `src/page-trabajos-realizados/index/` | Blog |
| `contact/index` | `src/contact/index/` | Contact page |
| `information-page/index` | `src/information-page/index/` | Legal/info pages |
| `taxonomy-product_cat/index` | `src/archive-product/index/` | Category archive |

---

## Build System

### Commands

```bash
npm run start:watch   # Development: webpack + Tailwind in parallel (use this daily)
npm run build         # Production: webpack then Tailwind minified (before deploy)
npm run cb            # Interactive: create new block (runs scripts/create-block.sh)
npm run cc            # Interactive: create new component (runs scripts/create-component.sh)
npm run db            # Interactive: delete block (runs scripts/delete-block.sh)
```

### Webpack

- Config: `webpack.config.js` — **24 entry points**, one `index.js` + `view.js` per block
- Output: `build/{group}/{block}/` — PHP, CSS, JS all compiled here
- Extends `@wordpress/scripts` defaults

### Tailwind CSS v4

- Entry: `src/index.css` (uses `@source` directive)
- Output: `build/index.css` (minified)
- Config: `tailwind.config.js` — scans `src/**/*.{php,html,js,jsx}` and `templates/`
- Enqueued globally via `test_theme_load_assets()` in `functions.php`

### When you add a new block

1. Run `npm run cb` to scaffold it
2. Add entry points to `webpack.config.js`
3. Register it in `functions.php` with `register_block_type()`
4. Add the block to the relevant template in `templates/`

---

## CSS Architecture

Styles are loaded in layers — **do not mix them up**:

| Layer | File | Scope |
|---|---|---|
| Global utilities | `build/index.css` (Tailwind) | All pages |
| WooCommerce base | `assets/css/woocommerce.css` | All pages |
| Block styles | `build/{group}/{block}/style-index.css` | Per-block, lazy |
| Editor styles | `build/{group}/{block}/index.css` | WP editor only |
| Navbar | `build/core/navbar/style-index.css` | All pages (priority 15) |
| Footer | `build/core/footer/style-index.css` | All pages (priority 15) |

Block styles are enqueued by `etheme_enqueue_block_style_index()` in `functions.php`. If adding styles to a block, always import the partial in `style.scss` — never enqueue manually unless necessary.

---

## PHP Conventions

- **Text domain**: always `etheme`
- **Function prefix**: `etheme_` for all custom functions
- **Nonces**: required on all AJAX POST handlers (`check_ajax_referer()`)
- **Escaping**: always escape output (`esc_html()`, `esc_url()`, `esc_attr()`)
- Components are plain PHP functions in `components/*.php`, required inside `render.php`
- AJAX handlers live in `includes/ajax-handlers.php` — registered in `functions.php`

---

## JavaScript Conventions

- **No jQuery** — vanilla JS only
- **Modules**: ES modules (`import`/`export`), compiled by webpack
- **Initialization**: `view.js` runs `DOMContentLoaded` → calls init functions from `scripts/`
- **Security utilities** (always import from `src/core/security/`):
  - `sanitizers.js` — text/email/phone/coupon sanitization
  - `validators.js` — validation functions
  - `messages.js` — Spanish error messages
  - `ui-feedback.js` — inline field errors, `aria-invalid`
  - `request-guard.js` — fetch with timeout + AbortController + double-submit lock
- **AJAX**: use `request-guard.js` wrapper, always pass nonce in request body

---

## WooCommerce Integration

No `woocommerce/` template overrides — integration is via custom blocks:

- Product data accessed with WooCommerce functions inside `render.php`
- Cart/checkout AJAX handlers in `src/page-cart/includes/` and `src/page-checkout/includes/`
- Theme supports declared in `functions.php`: `woocommerce`, `wc-product-gallery-zoom/lightbox/slider`
- Registration: `woocommerce_registration_auth_new_customer` filter disables auto-login
- Search: `posts_search` filter expands product search to include variations

---

## Template System

### FSE Templates (`templates/` — 30 files)

Each template contains one or two block tags referencing a registered block, e.g.:

```html
<!-- wp:template-part {"slug":"header"} /-->
<!-- wp:etheme/front-page-index /-->
<!-- wp:template-part {"slug":"footer"} /-->
```

### Template Parts (`parts/` — 24 files)

Header variants: `header.html`, `header-dark.html`, `header-transparent.html`, `header-cta.html`
Footer variants: `footer.html`, `footer-dark.html`
WooCommerce: `checkout-header.html`, `mini-cart.html`
Products: `product-collection-all-archives.html`, `related-products-minimal.html`
Posts: `posts-2-column.html`, `posts-4-column.html`, `posts-stack.html`

### Custom Templates (registered in `theme.json`)

- `page-politica-de-privacidad` — Privacy Policy
- `page-terminos-y-condiciones` — Terms & Conditions
- `page-condiciones-de-compra` — Purchase Conditions
- `page-contacto` — Contact
- `page-trabajos-realizados` — Blog

---

## Custom Post Types

Registered in `src/front-page/includes/`:

- `review` — customer reviews (CPT, shown on home page)
- `social-post` — social media posts (CPT with metabox for image/link/platform)

---

## Core Configuration

`src/core/config/config.json` — site-wide settings:
- Contact info, social links, FAQs
- Legal page URLs (privacy, terms, conditions)
- WooCommerce configuration values

---

## Shared Components

`src/core/components/` — PHP partials included by multiple blocks:

- `product-card.php` — product display card (used in archive, home, related)
- `blog-card.php` + `blog-card-modal.php` — post cards
- `sub-banner.php` — reusable section banner

Always prefer these shared components over duplicating HTML in block `render.php` files.

---

## Block Patterns

60+ PHP patterns in `includes/patterns/`. Categories:
- Cards, Featured/Hero, Footer, Header, Pages, Post Content, Team, Testimonials

These are the block inserter patterns visible in the WordPress editor.

---

## Feature Specifications

Detailed specs in `specs/` (20 `.md` files). Always read the relevant spec before implementing a feature:

| # | File | Feature |
|---|---|---|
| 1 | `1.home.md` | Home page |
| 9 | `9-11.*` | Archive product (header, filters, grid) |
| 12 | `12.single-product.md` | Product detail — related products unification |
| 12a | `12a.single-product-dual-color.md` | Product detail — dual-color variant selector + gallery behavior |
| 13 | `13.page-cart.md` | Shopping cart |
| 14 | `14.page-checkout.md` | Checkout |
| 15 | `15.page.md` | My Account |
| 16 | `16.contact.md` | Contact |
| 17 | `17.navbar.md` | Navbar |
| 18 | `18.security.md` | Security layer |
| 20 | `20.footer.md` | Footer |

---

## Key Files Reference

| File | Purpose |
|---|---|
| `functions.php` | Block registration, enqueues, AJAX, CPTs, WC hooks |
| `theme.json` | FSE custom template declarations |
| `style.css` | Theme metadata header only |
| `webpack.config.js` | 24 entry points for all blocks |
| `tailwind.config.js` | Tailwind v4 content paths |
| `src/core/config/config.json` | Site-wide configuration values |
| `src/index.css` | Tailwind entry (global utilities) |
| `src/index.js` | Theme JS entry |
| `build/` | Compiled output — never edit directly |
| `scripts/` | Bash automation (create-block, create-component, delete-block) |

---

## What NOT to Do

- **Never edit `build/`** — it is auto-generated by webpack/Tailwind
- **Never use jQuery** — vanilla JS only
- **Never hardcode site config** — use `src/core/config/config.json`
- **Never skip nonce verification** on AJAX handlers
- **Never output unescaped HTML** in PHP templates
- **Never enqueue a new stylesheet manually** without checking if the block's `style.scss` + `block.json` handles it
- **Never bypass the orchestrator pattern** — do not add display logic directly to templates

---

## Reusable Ecommerce Checklist

When starting a new WooCommerce block theme from this codebase:

1. Update `style.css` theme metadata (name, description, author, version)
2. Update `src/core/config/config.json` with client's contact info and social links
3. Update text domain from `etheme` to the new theme slug in all PHP files
4. Update block namespace from `etheme` to the new slug in `block.json` files
5. Update `theme.json` custom templates to match the client's page structure
6. Update Tailwind color tokens to match the client's brand palette
7. Review and adapt `specs/` files for the client's specific requirements
8. Run `npm run build` to verify the full build succeeds before development
