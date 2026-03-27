# Bakka — WordPress Block Theme

WordPress Full Site Editing (FSE) theme for WooCommerce with modular block architecture.

**Theme Identifiers:**

| Item | Value |
|------|-------|
| Theme slug | `bakka` |
| Block namespace | `etheme/*` |
| Text domain | `etheme` |

---

## Project Overview

Bakka is a custom WordPress block theme built for WooCommerce storefronts. It uses server-side rendering for SEO, component-based PHP architecture, and modular JavaScript for interactivity.

**Stack:**
- WordPress 6.0+ with Full Site Editing
- WooCommerce integration
- `@wordpress/scripts` for block compilation
- Tailwind CSS 4.x
- Component-based PHP rendering
- Modular JavaScript architecture

**Design Philosophy:**
- Server-side rendering first (SEO-optimized)
- Component reusability across pages
- Minimal JavaScript (progressive enhancement)
- Orchestrator pattern: one index block per template
- Shared UI components in `src/core/`

---

## AI Agent Instructions

**READ THIS FIRST** before modifying any files.

### Navigation Rules

1. **Architecture pattern:** Each template uses an orchestrator block (`src/{group}/index/`) that loads PHP components from `components/` and initializes JS from `scripts/`
2. **Shared components:** Reusable UI (cards, modals, sub-banners) lives in `src/core/components/` and `src/core/styles/`
3. **Specifications:** Feature specs are in `/specs/` — read relevant spec before implementing
4. **Build process:** DO NOT run `npm run build` during development — use `npm run start:watch` (runs separately)

### Safe to Modify

- `src/{group}/index/render.php` — orchestrator logic
- `src/{group}/components/*.php` — component markup
- `src/{group}/scripts/*.js` — component interactivity
- `src/{group}/styles/*.scss` — component styles
- `src/{group}/includes/*.php` — helpers and AJAX handlers
- `functions.php` — block registration and enqueues
- `theme.json` — global styles and settings

### Requires Review

- `templates/*.html` — FSE template structure
- `src/core/` — shared components used across multiple pages
- `package.json` — build scripts and dependencies
- `webpack.config.js` — build configuration
- `tailwind.config.js` — Tailwind configuration

### Do Not Modify

- `build/` — auto-generated compiled files
- `node_modules/` — dependencies
- `.git/` — version control

### Critical Constraints

1. **No build commands:** Never run `npm run build` or `npm run buildwp` during agent work unless explicitly requested
2. **CSS structure:** No `*.scss` files beside PHP components — use `src/{group}/styles/` partials imported via block's `style.scss`
3. **Frontend CSS loading:** Import `./style.scss` from block's `index.js` (not from `edit.js`) to ensure SSR blocks have styles
4. **PHP structure:** Orchestrator (`render.php`) loads components — components render markup — no HTML in orchestrator
5. **Security:** Always escape output (`esc_html`, `esc_url`, `esc_attr`) and sanitize input
6. **Specs first:** Read `/specs/` documentation before implementing features
7. **Logging:** Use error logging only — no general trace logging
8. **README updates:** Update this file when behavior or structure changes

### Where to Look

| Task | Files |
|------|-------|
| Register blocks | `functions.php` |
| Block configuration | `block.json` |
| Editor UI | `edit.js` |
| Frontend markup | `render.php`, `components/*.php` |
| Frontend JS | `view.js`, `scripts/*.js` |
| Shared utilities | `includes/*.php` |
| Shared components | `src/core/components/*.php` |
| Shared styles | `src/core/styles/*.scss` |
| Feature specs | `specs/*.md` |

### Common Pitfalls

1. **Missing frontend styles:** Ensure `import './style.scss';` is in block's `index.js` (not just `edit.js`)
2. **Duplicate components:** Check `src/core/components/` before creating new shared UI
3. **Build errors:** If styles missing, check `build/{group}/{block}/style-index.css` exists
4. **AJAX handlers:** Must be loaded in `functions.php` before components use them
5. **Bilingual content:** Some specs are in Spanish — match language when implementing

---

## Theme Architecture

### Orchestrator Pattern

Each FSE template uses one main block (orchestrator) that:
1. Loads PHP components from `components/`
2. Initializes JavaScript from `view.js`
3. Imports styles from `style.scss`

**Example structure:**
```
src/archive-product/
├── index/                    # Orchestrator block
│   ├── block.json           # Block configuration
│   ├── render.php           # Loads components
│   ├── view.js              # Initializes scripts
│   ├── edit.js              # Editor UI
│   ├── index.js             # Entry (imports style.scss)
│   └── style.scss           # Imports partials
├── components/              # PHP markup functions
│   ├── filter-menu.php
│   ├── grid.php
│   └── card.php
├── scripts/                 # JS component modules
│   ├── filter-menu.js
│   └── filter-button.js
├── styles/                  # SCSS partials
│   └── filters.scss
└── includes/                # Helpers and AJAX
    └── helpers.php
```

### Component Architecture

**PHP Components:**
- Functions that render markup
- Accept parameters for flexibility
- Located in `components/*.php`
- Called from orchestrator's `render.php`

**JavaScript Components:**
- Export initialization functions
- Located in `scripts/*.js`
- Imported and called from `view.js`
- Run on `DOMContentLoaded`

**Shared Components:**
- Reusable across pages
- Located in `src/core/components/`
- Examples: product cards, sub-banners, modals

### CSS Architecture

**Structure:**
- Block styles: `src/{group}/index/style.scss`
- Component partials: `src/{group}/styles/*.scss`
- Shared partials: `src/core/styles/*.scss`
- Import partials via `@import` in block's `style.scss`

**Loading:**
- Frontend: `import './style.scss';` in block's `index.js`
- Editor: `import './editor.scss';` in block's `edit.js`
- Compiled to: `build/{group}/{block}/style-index.css`

**Rules:**
- No `*.scss` files beside PHP components
- One partial can cover multiple components
- Import shared styles from `src/core/styles/`

---

## Folder Structure

```
bakka/
├── build/                   # Compiled assets (auto-generated)
├── src/                     # Source code
│   ├── 0_block/            # Block template
│   ├── core/               # Shared components
│   │   ├── components/     # Reusable PHP (navbar, footer, cards)
│   │   └── styles/         # Shared SCSS
│   ├── front-page/         # Home page
│   │   └── index/          # Orchestrator
│   ├── archive-product/    # Product archive
│   │   ├── index/          # Orchestrator
│   │   ├── components/     # Filter menu, grid, cards
│   │   ├── scripts/        # Filter JS, drawer JS
│   │   ├── styles/         # Filter styles
│   │   └── includes/       # Helpers
│   ├── single-product/     # Product detail page
│   │   ├── index/          # Orchestrator
│   │   ├── components/     # Gallery, info, tabs
│   │   └── scripts/        # Gallery JS, tabs JS
│   ├── page-cart/          # Shopping cart
│   │   ├── index/          # Orchestrator
│   │   ├── components/     # Cart items, totals, shipping
│   │   ├── scripts/        # Quantity, remove, AJAX
│   │   └── includes/       # AJAX handlers
│   ├── page-checkout/      # Checkout
│   │   ├── index/          # Orchestrator
│   │   ├── components/     # Address, payment, summary
│   │   ├── scripts/        # Payment tiles, shipping
│   │   └── includes/       # Helpers
│   ├── page/               # Login/registro/panel «Mi cuenta» (WooCommerce)
│   │   ├── index/          # Orchestrator
│   │   ├── components/     # Login, register, dashboard
│   │   └── scripts/        # Form toggle
│   └── page-posteos/       # Social posts page
│       ├── index/          # Orchestrator
│       ├── scripts/        # Modal, load-more
│       └── includes/       # AJAX handlers
├── templates/              # FSE templates
│   ├── front-page.html
│   ├── archive-product.html
│   ├── single-product.html
│   ├── page-cart.html
│   ├── page-checkout.html
│   └── page.html            # páginas genéricas; WooCommerce «Mi cuenta» suele usar esta plantilla
├── parts/                  # Template parts
│   ├── header.html
│   ├── footer.html
│   └── mini-cart.html
├── includes/               # Theme utilities
│   ├── classes/
│   └── patterns/
├── scripts/                # Automation scripts
│   ├── create-block.sh
│   ├── create-component.sh
│   └── delete-block.sh
├── specs/                  # Feature specifications
├── assets/                 # Static assets
├── functions.php           # Block registration
├── theme.json             # FSE configuration
├── style.css              # Theme metadata
├── package.json           # Dependencies
├── webpack.config.js      # Build config
└── tailwind.config.js     # Tailwind config
```

---

## Installation & Setup

### Prerequisites

- WordPress 6.0+
- Node.js 16.x+
- npm or yarn
- WooCommerce plugin

### Steps

1. Clone theme to `wp-content/themes/bakka/`
2. Install dependencies:
   ```bash
   npm install
   ```
3. Start development watch:
   ```bash
   npm run start:watch
   ```
4. Activate theme in WordPress Admin
5. Activate WooCommerce plugin

---

## Development Workflow

### Available Scripts

| Command | Purpose |
|---------|---------|
| `npm run start` | Development entry point |
| `npm run start:watch` | Webpack + Tailwind watch (use for development) |
| `npm run build` | Production build (use for deployment only) |
| `npm run buildwp` | Webpack build only |
| `npm run wpstart` | Webpack watch only |
| `npm run tailwindbuild` | Tailwind build only |
| `npm run tailwindwatch` | Tailwind watch only |
| `npm run format` | Format code |
| `npm run lint:css` | Lint styles |
| `npm run lint:js` | Lint JavaScript |
| `npm run cb` | Create block (interactive) |
| `npm run cc` | Create component (interactive) |
| `npm run db` | Delete block (interactive) |

### Development Process

1. Run `npm run start:watch` in terminal (leave running)
2. Edit files in `src/`
3. Changes auto-compile to `build/`
4. Refresh browser to see changes

### Creating Blocks

```bash
./scripts/create-block.sh
```

Follow prompts to:
1. Select group (core, front-page, archive-product, etc.)
2. Enter block name (lowercase, hyphens only)
3. Enter block title

Block is created and registered automatically.

### Creating Components

```bash
./scripts/create-component.sh
```

Follow prompts to:
1. Select page/group
2. Enter component name (lowercase, hyphens only)
3. Enter description

Component created in `src/{group}/components/`.

### Block Structure

Each block contains:

- `block.json` — metadata, attributes, scripts, styles
- `render.php` — server-side rendering (orchestrator)
- `edit.js` — editor UI (React component)
- `index.js` — entry point (imports `style.scss`)
- `style.scss` — frontend styles (imports partials)
- `editor.scss` — editor-only styles
- `view.js` — frontend JS entry (imports and initializes scripts)

### Frontend CSS Loading Checklist

For server-side rendered blocks, ensure styles load correctly:

1. `block.json` has `"style": "file:./style-index.css"`
2. Block's `index.js` imports `./style.scss`
3. Block's `style.scss` imports partials from `styles/` or `src/core/styles/`
4. Compiled `build/{group}/{block}/style-index.css` contains all styles
5. If block used in templates (not post content), enqueue manually in `functions.php`

**Common issue:** Styles in `edit.js` only load in editor — frontend needs `index.js` import.

---

## Block Theme Concepts

### Full Site Editing (FSE)

- Templates in `templates/` (HTML files)
- Template parts in `parts/` (header, footer)
- Global styles in `theme.json`
- Block-based editing for entire site

### Template Hierarchy

- `front-page.html` — home page
- `archive-product.html` — product category/archive
- `single-product.html` — product detail
- `page-cart.html` — cart page
- `page-checkout.html` — checkout page (2-step flow + sub-banner header, sin breadcrumbs)
- `page.html` — páginas estándar (incluye la página «Mi cuenta» con el bloque orquestador en el contenido)
- `index.html` — fallback template

### Theme.json

Defines:
- Color palette
- Typography scale
- Spacing scale
- Layout settings
- Template parts

---

## Feature Organization

Detailed feature specifications are in `/specs/` directory:

| Spec | Feature |
|------|---------|
| `1.home.md` | Home page architecture |
| `2.home-header.md` | Hero section |
| `3.home-popularproducts.md` | Product tabs |
| `4.home-categories.md` | Category grid |
| `5.home-blog.md` | Blog section |
| `6.home-newsletter.md` | Newsletter signup |
| `7.home-why.md` | Why choose us |
| `8.blog.md` | Blog archive |
| `9.archive-product-header.md` | Archive header |
| `10.archive-product-filters.md` | Product filters |
| `11.archive-product-grid.md` | Product grid |
| `12.single-product.md` | Product detail |
| `13.page-cart.md` | Shopping cart |
| `14.page-checkout.md` | Checkout flow |
| `15.page.md` | Mi cuenta: login + área logueada (`page.html`, WooCommerce) |

**Do not duplicate spec content in this README.** Reference specs for implementation details.

---

## Coding Conventions

### PHP

- Escape all output: `esc_html()`, `esc_url()`, `esc_attr()`
- Sanitize input: `sanitize_text_field()`, `sanitize_email()`
- Use nonces for forms: `wp_nonce_field()`, `wp_verify_nonce()`
- Prefix functions: `etheme_*`
- Component functions: `etheme_render_{component_name}()`
- Helper functions: `etheme_{group}_{helper_name}()`

### JavaScript

- Export initialization functions from scripts
- Import and call from `view.js`
- Run on `DOMContentLoaded`
- Use event delegation for dynamic content
- Prefix global variables: `etheme_*`

### CSS/SCSS

- Use Tailwind utilities first
- Custom styles in SCSS partials
- BEM naming for custom components
- Prefix custom classes: `etheme-*` or component-specific
- Mobile-first responsive design

### File Naming

- Lowercase with hyphens: `filter-menu.php`
- Component scripts: `{component-name}.js`
- Shared scripts: `core.{name}.js`
- SCSS partials: `_{partial-name}.scss`

---

## WooCommerce Integration

### Supported Features

- Product archives with filtering
- Product detail pages
- Shopping cart
- Checkout flow
- My Account (login/register/dashboard)
- Product search
- Related products
- Product variations
- Shipping calculator
- Coupon codes

### WooCommerce Hooks

Theme uses standard WooCommerce hooks and filters. Custom implementations in:
- `src/archive-product/` — archive customization
- `src/single-product/` — product page customization
- `src/page-cart/` — cart customization
- `src/page-checkout/` — checkout customization
- `src/page/` — página «Mi cuenta» (login, registro, panel de cliente)

### Product Data

Access via WooCommerce functions:
- `wc_get_product()` — get product object
- `wc_get_products()` — query products
- `WC()->cart` — cart object
- `WC()->session` — session data

---

## Common Tasks

### Add New Block

1. Run `./scripts/create-block.sh`
2. Select group and enter name
3. Edit `src/{group}/{block}/render.php` for markup
4. Edit `src/{group}/{block}/edit.js` for editor UI
5. Add attributes to `block.json` if needed
6. Block auto-registered in `functions.php`

### Add Component to Existing Block

1. Run `./scripts/create-component.sh`
2. Select group and enter name
3. Edit `src/{group}/components/{component}.php`
4. Call from orchestrator's `render.php`
5. Add styles to `src/{group}/styles/{component}.scss`
6. Import partial in block's `style.scss`

### Add JavaScript Interactivity

1. Create `src/{group}/scripts/{component}.js`
2. Export initialization function
3. Import in block's `view.js`
4. Call on `DOMContentLoaded`

### Add AJAX Handler

1. Create handler in `src/{group}/includes/ajax-handlers.php`
2. Register with `wp_ajax_*` and `wp_ajax_nopriv_*`
3. Load file in `functions.php`
4. Call from JavaScript with `wp.ajax` or `fetch()`

### Update Shared Component

1. Edit `src/core/components/{component}.php`
2. Update styles in `src/core/styles/{component}.scss`
3. Test all pages using component
4. Update documentation if API changes

---

## Troubleshooting

### Styles Not Loading

1. Check `import './style.scss';` in block's `index.js`
2. Verify `build/{group}/{block}/style-index.css` exists
3. Check `block.json` has `"style": "file:./style-index.css"`
4. For template blocks, check manual enqueue in `functions.php`
5. Clear browser cache and WordPress cache

### JavaScript Not Running

1. Check `view.js` imports script
2. Verify initialization function called on `DOMContentLoaded`
3. Check browser console for errors
4. Verify `build/{group}/{block}/view.js` exists
5. Check `block.json` has `"viewScript": "file:./view.js"`

### Build Errors

1. Stop `npm run start:watch`
2. Delete `build/` directory
3. Run `npm install`
4. Run `npm run start:watch` again
5. Check console for specific errors

### AJAX Not Working

1. Verify handler registered in `functions.php`
2. Check nonce verification in handler
3. Verify action name matches in JS and PHP
4. Check browser network tab for request/response
5. Check WordPress debug log for PHP errors

### Component Not Rendering

1. Verify component file loaded in `functions.php` (if in `includes/`)
2. Check function called from `render.php`
3. Verify function name matches
4. Check for PHP errors in debug log
5. Verify component returns/echoes output

---

## Contributing

### Before Making Changes

1. Read this README completely
2. Read relevant spec in `/specs/`
3. Check existing components in `src/core/`
4. Review similar implementations
5. Test in development environment

### Code Quality

- Follow coding conventions above
- Escape all output
- Sanitize all input
- Use nonces for forms
- Write semantic HTML
- Mobile-first responsive
- Test accessibility
- Check browser console for errors

### Documentation

- Update README when structure changes
- Comment complex logic
- Document component parameters
- Update specs if behavior changes

---

## License

GPL-2.0-or-later

## Author

The WordPress Contributors

---

## Resources

- [WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [WooCommerce Developer Documentation](https://woocommerce.com/documentation/plugins/woocommerce/)
- [Full Site Editing](https://developer.wordpress.org/block-editor/getting-started/full-site-editing/)
- [Theme.json Reference](https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-json/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
