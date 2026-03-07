# Test Theme - WordPress Block Theme for WooCommerce

A modern block theme for WordPress designed for WooCommerce, built with the Block Editor and Full Site Editing (FSE).

## 📋 Description

Test Theme is a custom block theme that provides reusable components and a modular structure for creating e-commerce sites with WooCommerce. The theme uses the WordPress Block API and is optimized for the modern block editor.

## ✨ Features

- **Full Site Editing (FSE)**: Compatible with WordPress's full site editing system
- **Template Blocks**: Each FSE template (HTML) has a corresponding block structure in `src/` with components and scripts
- **Custom Blocks**: Modular components organized by groups
- **Scripts**: Modular JavaScript architecture - component scripts in `scripts/` directories, imported and initialized by `view.js` entry point
- **Includes**: Place shared utilities in `includes/` before components
- **WooCommerce Ready**: Optimized for online stores
- **Modern Development**: Uses `@wordpress/scripts` for compilation and development
- **Modular Structure**: Blocks organized in logical groups (core, index)
- **Automation Scripts**: Tools to easily create new blocks
- **Product Search**: Search results template uses the archive index block
- **Navbar Search**: Uses FiboSearch shortcode when active, falls back to default search

## 🏗️ Project Structure

```
bakka/
├── build/              # Compiled files (auto-generated)
│   ├── 0_block/        # Base block
│   ├── core/           # Core blocks (navbar, footer, header)
│   ├── front-page/     # Front page (home) blocks
│   │   ├── index/      # Main home page block (orchestrator)
│   │   ├── header/     # Legacy header block
│   │   ├── components/ # PHP component functions
│   │   │   ├── hero.php
│   │   │   ├── popular-products.php
│   │   │   ├── categories.php
│   │   │   ├── reviews.php
│   │   │   ├── blog.php
│   │   │   └── newsletter.php
│   │   ├── scripts/    # JavaScript component scripts
│   │   │   ├── home-newsletter.newsletter.js
│   │   │   └── home-popular-products.product-tabs.js
│   │   └── includes/   # Helper functions
│   │       ├── front-page-index.helpers.php
│   │       ├── home-newsletter.ajax-handlers.php
│   │       └── home-reviews.cpt-review.php
│   ├── archive-product/ # Product archive blocks
│   ├── single-product/ # Single product blocks
│   ├── page-cart/      # Cart page blocks
│   └── page-checkout/  # Checkout page blocks
├── src/                # Source code
│   ├── 0_block/        # Base block (template for new blocks)
│   ├── core/           # Core blocks
│   ├── front-page/     # Front page blocks
│   ├── archive-product/ # Product archive
│   │   ├── index/      # Main archive block
│   │   ├── components/ # PHP component functions
│   │   │   ├── searchbar.php
│   │   │   ├── sorting.php
│   │   │   ├── filter-button.php
│   │   │   ├── filter-menu.php
│   │   │   ├── pagination.php
│   │   │   └── card.php
│   │   ├── scripts/    # JavaScript component scripts
│   │   │   ├── filter-menu.js
│   │   │   └── filter-button.js
│   │   └── includes/   # Helper functions
│   ├── single-product/ # Single product page
│   │   ├── index/      # Main single product block
│   │   ├── components/ # PHP component functions
│   │   │   ├── breadcrumb.php
│   │   │   ├── gallery.php
│   │   │   ├── product-info.php
│   │   │   ├── variations.php
│   │   │   ├── add-to-cart.php
│   │   │   ├── tabs.php
│   │   │   └── related-products.php
│   │   └── scripts/     # JavaScript component scripts
│   │       ├── gallery.js
│   │       └── tabs.js
│   ├── page-cart/       # Cart page
│   │   ├── index/       # Main cart page block
│   │   ├── components/  # PHP component functions
│   │   │   ├── header-cart.php
│   │   │   ├── product-cart.php
│   │   │   ├── postal-code-shipping.php
│   │   │   ├── coupon-form.php
│   │   │   ├── basket-totals.php
│   │   │   ├── checkout-actions.php
│   │   │   └── empty-cart.php
│   │   ├── scripts/     # JavaScript component scripts
│   │   │   ├── quantity.js
│   │   │   ├── remove-item.js
│   │   │   ├── shipping-calculator.js
│   │   │   ├── coupon.js
│   │   │   └── cart-ajax.js
│   │   └── includes/    # Helper functions and AJAX handlers
│   ├── page-checkout/   # Checkout page
│   │   ├── index/       # Main checkout page block
│   │   ├── components/  # PHP component functions
│   │   │   ├── checkout-header.php
│   │   │   ├── contact-information.php
│   │   │   ├── shipping-address.php
│   │   │   ├── shipping-options.php
│   │   │   ├── order-summary.php
│   │   │   ├── payment-options.php
│   │   │   ├── legal-terms.php
│   │   │   ├── place-order.php
│   │   │   └── return-to-cart.php
│   │   ├── scripts/     # JavaScript component scripts
│   │   │   ├── payment-tiles.js
│   │   │   └── shipping-options.js
│   │   └── includes/    # Helper functions
│   └── page-myaccount/  # My Account page (login, register, dashboard)
│       ├── index/       # Main my account block
│       ├── components/  # PHP component functions
│       │   ├── login-form.php
│       │   ├── register-form.php
│       │   └── account-dashboard.php
│       └── scripts/     # JavaScript component scripts
│           └── form-toggle.js
├── templates/          # FSE (Full Site Editing) templates
│   ├── index.html
│   ├── front-page.html
│   ├── archive-product.html
│   ├── single-product.html
│   ├── page-cart.html
│   ├── page-checkout.html
│   ├── page-my-account.html
│   └── product-search-results.html
├── scripts/            # Automation scripts
│   ├── setup.sh             # Initial setup script
│   ├── create-block.sh      # Create new blocks
│   ├── create-component.sh  # Create new PHP components
│   └── delete-block.sh      # Delete blocks
├── specs/              # Feature specifications and documentation
├── functions.php       # Theme functions and block registration
├── theme.json         # FSE theme configuration
├── style.css          # Main theme styles
└── package.json       # npm dependencies and scripts
```

## 🚀 Installation

### Prerequisites

- WordPress 6.0 or higher
- Node.js 16.x or higher
- npm or yarn
- WooCommerce (plugin)

### Installation Steps

1. **Clone or download the theme** to your WordPress themes directory:
   ```bash
   wp-content/themes/test_theme/
   ```

2. **Install dependencies**:
   ```bash
   npm install
   ```

3. **Build the blocks**:
   ```bash
   npm run build
   ```

4. **Activate the theme** from the WordPress admin panel:
   - Appearance → Themes → Activate "Test Theme"

## 🛠️ Development

### Available Scripts

- `npm run build`: Compiles all blocks for production
- `npm run start`: Starts development mode with hot-reload
- `npm run format`: Formats code according to WordPress standards
- `npm run lint:css`: Lints CSS styles
- `npm run lint:js`: Lints JavaScript code

### Creating a New Block

The theme includes an interactive script to create new blocks:

```bash
./scripts/create-block.sh
```

The script will guide you through:
1. Group selection (core, front-page, archive-product, or groups based on templates)
2. Block name (lowercase letters, numbers, and hyphens only)
3. Block title

The new block will be automatically created and registered in `functions.php`.

### Creating a New PHP Component

For dynamic content that doesn't need to be a full block (like product cards, filters, etc.), use PHP components:

```bash
./scripts/create-component.sh
```

The script will guide you through:
1. Page/Group selection (determines where the component will be created)
2. Component name (lowercase letters, numbers, and hyphens only)
3. Component description

Components are created in `src/{page}/index/components/` and can be used as PHP functions in your block's `render.php`.

### Block Structure

Each block contains:

- `block.json`: Block metadata and configuration
- `edit.js`: React component for the editor
- `render.php`: PHP template for the frontend (orchestrator — loads components, does not render HTML directly)
- `style.scss`: Frontend styles
- `editor.scss`: Editor styles
- `view.js`: Frontend JavaScript entry point — imports and initializes all component scripts on `DOMContentLoaded`
- `index.js`: Block entry point
- `scripts/<component>.js`: Individual component scripts, each exporting an initialization function (e.g. `initFilters()`)
- `components/<component>.php`: PHP functions for dynamic content rendering
- `includes/<helpers>.php`: Shared utilities and AJAX handlers

**JS naming convention:** Use the component name for component-specific scripts (e.g. `filter-menu.js` for `filter-menu.php`). For shared/core functionality use `core.{name}.js` (e.g. `core.utils.js`).

## 📦 Included Blocks

### Core Blocks

- **core-navbar**: Main navigation bar
- **core-footer**: Site footer
- **core-header**: Site header

### Page Blocks

- **front-page-index**: Home page orchestrator block (Hero, Products, Categories, Reviews, Blog, Newsletter)
- **front-page-header**: Front page header (legacy, replaced by front-page-index)

### WooCommerce Blocks

#### Product Archive Index (`etheme/archive-product-index`)

A powerful orchestrator block for product archives with advanced filtering, sorting, and pagination capabilities.

**Architecture:**
The Product Archive Index uses a **component-based architecture** with PHP functions for dynamic content rendering and modular JavaScript for interactivity:

**Components** (in `src/archive-product/components/`):
- **searchbar.php**: Search box for keyword filtering
- **filter-button.php**: Toggle button for filters
- **filter-menu.php**: Categories, price range, and sale filters
- **sorting.php**: Sort dropdown (price, date, popularity)
- **pagination.php**: Page navigation controls
- **card.php**: Individual product display

**JavaScript Scripts** (in `src/archive-product/scripts/`):
- **filter-menu.js**: Handles filter form submission (removes empty fields)
- **filter-button.js**: Handles filter menu toggle button functionality

**Why Components Instead of Blocks?**
WordPress blocks are designed for user insertion in the editor, not for programmatic rendering of dynamic content. Components are PHP functions that can be called with parameters, making them perfect for:
- Dynamic product lists (generated from database queries)
- Reusable UI elements that need different data
- Content that changes based on filters/search/pagination

**Features:**
- Server-side rendering for optimal SEO and performance
- Component-based architecture for maximum flexibility
- Responsive grid layout (1-6 columns configurable)
- Advanced filtering: categories, price range, on sale
- Category filter shows only child categories of the active parent and hides when none are available
- Sorting by price, date, and popularity
- Search functionality
- Search header uses the search term when present
- Category filter can be scoped to categories found in search results
- Pagination with URL parameter preservation
- TailwindCSS styling with smooth hover effects
- Cache-friendly (no JavaScript required for core functionality)

**Block Attributes:**
- `columns` (1-6): Number of columns on large screens
- `perPage` (12, 24, 36, 48): Products displayed per page
- `defaultOrderBy` (date, price, popularity): Default sorting field
- `defaultOrder` (asc, desc): Default sorting direction
- `showSorting` (boolean): Toggle sorting selector visibility
- `showSearch` (boolean): Toggle search box visibility

**URL Parameters:**
- `?orderby=price-asc`: Sort by price ascending
- `?orderby=price-desc`: Sort by price descending
- `?orderby=popularity-desc`: Sort by most popular
- `?orderby=date-desc`: Sort by newest
- `?s=search+term`: Search products
- `?filter_categories[]=1&filter_categories[]=2`: Filter by categories
- `?min_price=10&max_price=100`: Filter by price range
- `?on_sale=1`: Show only products on sale
- `?paged=2`: Navigate to page 2

**Usage:**
1. Add the "Product Archive Index" block to any page or template
2. Configure settings in the block inspector panel (right sidebar)
3. The block will automatically render all child blocks (filters, sorting, pagination)
4. Users can filter, search, sort, and navigate through products
5. All state is maintained in the URL for bookmarking and sharing

#### Product Card (`etheme/archive-product-card`)

A reusable product card component used by the Product Archive Index block.

**Features:**
- Square aspect ratio product images
- Sale badge for discounted products
- Formatted WooCommerce pricing
- Add to cart button integration
- Hover effects and smooth transitions
- Dark mode support

**Note:** This block is automatically used by the Product Archive Index block but can also be used independently in the Loop block or custom templates.

#### Single Product Index (`etheme/single-product-index`)

A comprehensive orchestrator block for single product pages with a Zara/Nike-inspired layout, gallery, product information, add to cart, tabs, and related products.

**Architecture:**
The Single Product Index uses a **component-based architecture** with PHP functions for dynamic content rendering and modular JavaScript for interactivity:

**Components** (in `src/single-product/components/`):
- **breadcrumb.php**: Breadcrumb navigation (Home > Category > Product)
- **gallery.php**: Vertical thumbnail rail + main product image
- **product-info.php**: Product information (title, price, rating, stock status, short description)
- **variations.php**: Variation selectors for variable products
- **add-to-cart.php**: Add to cart form with quantity selector
- **tabs.php**: Product detail accordions (Description, Reviews, Shipping & Returns)
- **related-products.php**: Related products grid

**JavaScript Scripts** (in `src/single-product/scripts/`):
- **gallery.js**: Handles thumbnail clicks to change main product image
- **tabs.js**: Handles tab switching functionality

**Features:**
- Zara/Nike-style layout with sticky info column on desktop
- Vertical thumbnail rail with large hero image
- Minimal typography and full-width CTA
- Product details as accordion sections
- Full WooCommerce integration and related products display
- SEO-friendly breadcrumbs
- Server-side rendering for optimal performance
- Modular JavaScript architecture for maintainability

**Usage:**
1. The block is automatically used in the `single-product.html` template
2. All components are auto-loaded and rendered
3. JavaScript scripts handle interactive features (gallery, tabs)

#### Cart Page Index (`etheme/page-cart-index`)

A modern shopping cart page block with product list, shipping calculator, coupons, and totals.

**Architecture:**
The Cart Page Index uses a **component-based architecture** with PHP functions for dynamic content rendering and modular JavaScript for AJAX cart operations:

**Components** (in `src/page-cart/components/`):
- **header-cart.php**: Cart page header with title and item count
- **product-cart.php**: Individual cart item with image, title, price, attributes, quantity controls, and remove button
- **postal-code-shipping.php**: Shipping calculator with postal code input, easily adaptable for different countries/carriers
- **coupon-form.php**: Coupon code input with applied coupons display
- **basket-totals.php**: Cart totals (subtotal, discount, shipping, tax, total)
- **checkout-actions.php**: Proceed to checkout button and continue shopping link
- **empty-cart.php**: Empty cart state with return to shop button

**JavaScript Scripts** (in `src/page-cart/scripts/`):
- **quantity.js**: Handles quantity increase/decrease with debounced AJAX updates
- **remove-item.js**: Handles cart item removal with animations
- **shipping-calculator.js**: Handles postal code shipping calculation
- **coupon.js**: Handles coupon application and removal
- **cart-ajax.js**: Core AJAX functions for cart operations

**Helper Functions** (in `src/page-cart/includes/`):
- **helpers.php**: Utility functions for cart data (attributes, stock status, price info)
- **ajax-handlers.php**: AJAX endpoints for cart operations

**Features:**
- Modern light theme design inspired by premium e-commerce sites
- Real-time quantity updates without page reload
- Animated item removal
- Postal code shipping calculator (configured for Argentina, easily adaptable)
- Coupon code application and removal
- Dynamic totals calculation
- Stock status indicators (in stock, out of stock, backorder)
- Responsive design (2 columns desktop, 1 column mobile)
- Full WooCommerce integration
- Accessible with ARIA labels and keyboard navigation

**Block Attributes:**
- `showShippingCalculator` (boolean, default: true): Show/hide shipping calculator
- `showCouponForm` (boolean, default: true): Show/hide coupon form
- `showContinueShopping` (boolean, default: true): Show/hide continue shopping link

**Usage:**
1. The block is automatically used in the `page-cart.html` template
2. All components are auto-loaded and rendered
3. JavaScript handles interactive features via AJAX
4. Shipping calculator can be customized for different countries by modifying the country selector

#### Checkout Page Index (`etheme/page-checkout-index`)

A modern checkout page block with contact details, shipping address, shipping method selection, dynamic payment gateways, legal acceptance, and order summary.

**Architecture:**
The Checkout Page Index uses a **component-based architecture** with server-side rendering and progressive JavaScript enhancement:

**Components** (in `src/page-checkout/components/`):
- **checkout-header.php**: Checkout title and item count
- **contact-information.php**: Email field and contact context
- **shipping-address.php**: Shipping address fields with country/state integration
- **shipping-options.php**: Available shipping methods for current cart packages
- **order-summary.php**: Product lines and totals summary
- **payment-options.php**: Dynamic gateway list with visual variants
- **legal-terms.php**: Terms and privacy acknowledgement
- **place-order.php**: Secure place-order button and checkout nonce
- **return-to-cart.php**: Link back to cart page

**JavaScript Scripts** (in `src/page-checkout/scripts/`):
- **payment-tiles.js**: Keeps selected payment tile state in sync
- **shipping-options.js**: Keeps selected shipping option state in sync

**Helper Functions** (in `src/page-checkout/includes/`):
- **helpers.php**: Checkout field rendering helpers, shipping and payment data mapping

**Features:**
- Server-side first checkout flow (works without JavaScript)
- Dynamic country and state fields using WooCommerce country APIs
- Dynamic shipping methods by cart package and session selection
- Dynamic payment gateways using WooCommerce available gateways API
- Terms and privacy consent with WooCommerce-compatible checkout submit flow
- Responsive 2-column desktop and single-column mobile layout
- Nike-inspired minimal layout with strong typography and whitespace

**Block Attributes:**
- `showOrderNotes` (boolean, default: true): Show/hide order notes section
- `showReturnToCart` (boolean, default: true): Show/hide return to cart link
- `stickySummaryDesktop` (boolean, default: true): Sticky summary column on desktop

**Usage:**
1. The block is automatically used in the `page-checkout.html` template
2. All components are auto-loaded and rendered
3. JavaScript enhances payment/shipping tile visual states

#### My Account Page Index (`etheme/page-myaccount-index`)

Login, email-only registration, and account dashboard for the My Account page.

**Architecture:**
The My Account Page Index uses a **component-based architecture** with server-side rendering. Forms use WooCommerce-native nonces and field names so `WC_Form_Handler` processes login and registration automatically — no custom form processing needed.

**Components** (in `src/page-myaccount/components/`):
- **login-form.php**: Login form (username/email, password, remember me, lost password link)
- **register-form.php**: Registration form (email only — WooCommerce sends password setup link)
- **account-dashboard.php**: Logged-in dashboard with links to WooCommerce endpoints

**JavaScript Scripts** (in `src/page-myaccount/scripts/`):
- **form-toggle.js**: Toggle between login/register panels, password visibility toggle

**Features:**
- Email-only registration (compatible with WooCommerce "Send password setup link" option)
- WooCommerce-native form processing (nonces, field names, error handling)
- Toggle between login and register views
- Password visibility toggle
- Account dashboard with links to orders, addresses, account details, downloads
- Logout link
- Responsive design with Tailwind CSS
- Accessible with ARIA labels, keyboard navigation, focus management

**Block Attributes:**
- `showRegister` (boolean, default: true): Show/hide registration form

**WooCommerce Integration:**
- Login: uses `woocommerce-login-nonce`, fields `username`, `password`, `rememberme`, `login`
- Register: uses `woocommerce-register-nonce`, fields `email`, `register`
- Both processed by `WC_Form_Handler` on WordPress `init` hook
- Errors displayed via `wc_print_notices()`
- Registration respects `woocommerce_enable_myaccount_registration` option

**Usage:**
1. The block is automatically used in the `page-my-account.html` template
2. Ensure WooCommerce My Account page slug matches `my-account` (or rename template)
3. In WooCommerce settings, enable "Allow customers to create an account on My Account page"
4. Enable "Send password setup link" for email-only registration

#### Front Page Index (`etheme/front-page-index`)

A main orchestrator block for the home page with six internal sections: Hero, Popular Products, Categories, Reviews, Blog, and Newsletter.

**Architecture:**
The Front Page Index uses a **component-based architecture** with one PHP function per section, following the same orchestrator pattern as archive-product and single-product blocks:

**Components** (in `src/front-page/components/`):
- **hero.php**: Hero/banner section (design from `banner-con`): title, subtitle, description, CTA, feature image, and discount circle badge (number, label, sublabel). Markup uses BEM classes (e.g. `hero-banner`, `hero-banner__content`, `hero-banner__circle-text`).
- **popular-products.php**: WooCommerce products grouped by category with tab navigation, ordered by popularity (total_sales)
- **categories.php**: Visual product category cards linking to archive pages
- **reviews.php**: Testimonials from CPT `etheme_review` with ACF fields (not WooCommerce reviews)
- **blog.php**: Recent WordPress posts (standard `post` type)
- **newsletter.php**: Email subscription form stored in a custom DB table

**JavaScript Scripts** (in `src/front-page/scripts/`):
- **home-newsletter.newsletter.js**: AJAX form submission for newsletter subscriptions
- **home-popular-products.product-tabs.js**: Category tab switching for popular products section

**Helper Functions** (in `src/front-page/includes/`):
- **front-page-index.helpers.php**: Data-fetching functions (popular products, categories, reviews, blog posts, star rating renderer)
- **home-newsletter.ajax-handlers.php**: Newsletter AJAX endpoint and custom table creation
- **home-reviews.cpt-review.php**: Registers `etheme_review` CPT with ACF field fallback

**Product Popularity System:**
Products are ordered using WooCommerce's `total_sales` meta key (tracks number of completed sales per product). The ordering criterion is configurable in the block inspector via `productsOrderBy` attribute. Currently implemented: `total_sales` (most sold). Prepared for future criteria: `featured`, `date` (newest), `rating` (best rated) — add new cases to `etheme_get_popular_products()` in `helpers.php` and corresponding options in `edit.js`.

**Reviews (CPT + ACF):**
Testimonials use a custom post type `etheme_review` with ACF fields:
- `review_client_name` (text): Client name
- `review_client_role` (text): Client role/title
- `review_rating` (number, 1-5): Star rating
- `review_avatar` (image, returns ID): Client photo

When ACF is not active, fields are registered as standard post meta via `register_post_meta()`.

**Newsletter Storage:**
Subscriber emails are stored in a custom database table `{prefix}etheme_newsletter` with columns: `id`, `email` (unique), `status`, `created_at`. The table is created automatically on theme activation or first load.

Extension point for external providers: `do_action('etheme_newsletter_after_subscribe', $email)` fires after successful subscription. Hook into it to integrate Mailchimp, SendGrid, etc. See `src/front-page/includes/home-newsletter.ajax-handlers.php`.

**Hero styles (CSS loading):**  
Hero layout and visuals live in `src/front-page/styles/hero.scss` (component styles live in the `styles/` folder, not inside `components/`). That file is imported by the block’s `src/front-page/index/style.scss`. The build outputs `build/front-page/index/style-index.css`. To ensure it loads when the block is used in the front-page template, `functions.php` enqueues that file when `is_front_page()` is true. One build artifact, no extra webpack entry, no duplicate hero CSS.

**Block Attributes:**
- `heroTitle`, `heroSubtitle`, `heroDescription`, `heroCtaText`, `heroCtaUrl`, `heroImageId`: Hero section content
- `heroDiscountNumber`, `heroDiscountLabel`, `heroDiscountSublabel`: Discount badge in the hero (e.g. "50 % OFF / En todos los productos")
- `productsOrderBy` (total_sales): Popularity criterion for products
- `productsPerCategory` (2-12, default: 6): Products shown per category tab
- `categoriesMode` (all/include/exclude): Category display mode
- `categoriesInclude`, `categoriesExclude`: Category ID arrays for filtering
- `reviewsCount` (1-12, default: 6): Number of testimonials
- `reviewsOrderBy` (date/rand): Review ordering
- `blogCount` (1-9, default: 3): Number of blog posts
- `blogCategories`: Blog category ID filter
- `newsletterTitle`, `newsletterSubtitle`, `newsletterButtonText`: Newsletter section text

**SEO & Accessibility:**
- Semantic HTML: `<header>`, `<section>`, `<article>`, `<h1>`-`<h3>`, `<time>`
- Single `<h1>` in hero, proper heading hierarchy throughout
- ARIA roles on product tabs (`role="tablist"`, `role="tab"`, `role="tabpanel"`)
- Newsletter form with `<label>`, `aria-describedby`, `aria-live="polite"` for messages
- `alt` attributes on all images
- Review cards readable by screen readers
- All text wrapped in `__()` / `esc_html_e()` for i18n (text domain: `etheme`)

**Responsive Design:**
- Mobile-first with Tailwind breakpoints (sm, md, lg)
- Hero: stacked on mobile, side-by-side on desktop
- Product grid: 1 col mobile, 2 col tablet, 3 col desktop
- Categories: 1 col mobile, 2 col tablet, 4 col desktop
- Blog: 1 col mobile, 2 col tablet, 3 col desktop
- Newsletter: stacked form on mobile, inline on tablet+

**Empty States:**
- No products/categories → section is hidden (no PHP errors)
- No reviews → section is hidden
- No blog posts → section is hidden
- Duplicate newsletter email → user-friendly error message

**Usage:**
1. The block is used in the `front-page.html` template
2. All sections are configurable via the block inspector (sidebar)
3. Newsletter uses AJAX for seamless submission
4. Product tabs switch without page reload

### Base Block

- **0_block**: Base block used as a template for creating new blocks

## 🎨 Customization

### Theme Configuration

The `theme.json` file allows you to configure:
- Color palette
- Typography
- Spacing
- And other FSE theme settings

### Styles
Prefer using Tailwind CSS for styling. For complex designs that cannot be achieved with Tailwind CSS, use `style.scss` and grouped `styles/*.scss` files.
Styles are organized in:
- `style.css`: Global theme styles
- `src/[group]/[block]/style.scss`: Block-specific styles (imports the group/component styles it needs)
- `src/[group]/[block]/editor.scss`: Editor styles for each block
- `src/[group]/styles/*.scss`: Component/section styles for that group. **All component styles MUST live in this folder (not inside `components/`) and MUST be imported from the block’s `style.scss` entry.** When you create a new component that needs custom CSS, add a new `.scss` file here and import it from the corresponding block.

**Block styles on the front (Front Page Index):**  
The front-page block uses a **group styles SCSS** (`src/front-page/styles/hero.scss`) for the hero banner design. The block’s `style.scss` imports it (`@import '../styles/hero.scss'`). Webpack builds this into `build/front-page/index/style-index.css`. Because the block is used in the **template** (`front-page.html`) and not in post content, WordPress does not always enqueue this style automatically. The theme therefore **enqueues it in `functions.php` when `is_front_page()`**: it loads `build/front-page/index/style-index.css` so the hero (two columns, circle badge, CTA, etc.) displays correctly. No separate build entry or duplicate CSS file is used — a single source (the block’s style) is built once and enqueued only on the front page.

### Inline CSS and JavaScript fallback

When behaviour or layout breaks with separate files (scripts in `scripts/` or styles in `style.scss`), the cause is usually **load order**: block view scripts and styles are enqueued by WordPress and can run or apply in an order where the block’s DOM is not yet ready, or where another stylesheet overrides. (Build is often automatic via `npm run start:watch`, so the issue is not missing build.) Other possible causes are **stacking context** (parents with `transform`, `opacity`, `filter`, or `overflow` affecting layering) or CSS specificity. In those cases **prefer inline CSS and JavaScript** in the block’s `render.php` so the code runs or applies right after the block markup and works reliably. Keep inline code **as modular and readable as possible** (clear names, small blocks, comments). If you later move it to separate files after fixing load order or stacking context, keep the same structure.

## 🔧 WooCommerce Configuration

This theme is designed to work with WooCommerce. To optimize the experience:

1. **Install WooCommerce**: Make sure the plugin is active
2. **Configure templates**: Customize templates in `templates/` according to your needs
3. **Add products**: Create WooCommerce products to populate the archive and home page sections
4. **Use archive block**: The `etheme/archive-product-index` block powers product listing pages

### Product Archive Setup

The `archive-product.html` template automatically uses the `etheme/archive-product-index` block. Configuration is done via the block inspector panel in the editor. See the [Product Archive Index](#product-archive-index-ethemearchive-product-index) section for all available attributes and URL parameters.

## 📝 Specifications

Feature specifications and acceptance criteria are located in `specs/features/`. Home page section specs (e.g. Popular Products, Categories) are in `specs/` (e.g. `3.home-popularproducts.md`, `4.home-categories.md`). Consult these files before implementing new features.

### Login & Register (My Account)

**Status:** Implemented. See `specs/features/6.login-register.md` for full acceptance criteria.

**Key implementation decisions:**

1. **WooCommerce-native form processing:** Login and register forms use WC-compatible nonces and field names (`woocommerce-login-nonce`, `woocommerce-register-nonce`) so `WC_Form_Handler` processes them automatically. No custom PHP form handling.
2. **Email-only registration:** WooCommerce "Send password setup link" option is enabled. The register form has only an email field. WooCommerce generates username/password and sends the setup email.
3. **Template `page-my-account.html`:** Follows same pattern as `page-cart.html` and `page-checkout.html` — navbar, main group, block, footer.
4. **Toggle UX:** Login and register are two panels toggled via JS (`[data-toggle-panel]`). Default view is login.

## 🤝 Contributing

When developing new features:

1. Consult specifications in `specs/features/`
2. Follow SOLID principles and keep functions concise (max 20-30 lines)
3. Prioritize modularity and reusability
4. Update README when necessary
5. Use logs only for errors

## ⚡ Code Optimization Guidelines

### Performance Rules

1. **Cache static data**: Use `static` variables for data that doesn't change during a request (categories, config arrays, parsed params)
2. **Batch database queries**: Validate multiple items in one query instead of loops (use `get_terms()` with `include` for category validation)
3. **Apply defaults early**: Always use `wp_parse_args()` before passing attributes to query builders or other functions
4. **Optimize loops**: Extract product data before loops when possible (permalink, title, thumbnail_id)

### Code Cleanliness Rules

1. **Centralize duplicated logic**: Create helper functions for repeated code patterns (URL building, parameter preservation)
2. **Separate concerns**: Move JavaScript to `.js` files, avoid inline scripts and `onclick` attributes
3. **Use semantic helpers**: Prefer descriptive function names over generic ones (`etheme_build_url_query_args` vs `build_args`)
4. **Static arrays in functions**: Declare constant arrays as `static` to avoid recreation on each call

### Security Rules

1. **Escape output**: Always use `esc_attr()`, `esc_html()`, `esc_url()` for user data
2. **Sanitize HTML**: Use `wp_kses_post()` for WooCommerce-generated HTML (prices, descriptions)
3. **Validate input**: Check and sanitize all `$_GET`, `$_POST` parameters before use
4. **Use nonces**: Add nonce verification for form submissions when needed

### Architecture Rules

1. **Orchestrator pattern**: Blocks act as orchestrators in `render.php` that load and coordinate components, not render HTML directly
2. **Component-based architecture**: Use PHP components in `components/` for dynamic content rendering, not full blocks
3. **Block vs Component distinction**: Blocks are for editor insertion, components are for programmatic rendering with parameters
4. **Helper functions first**: Place shared utilities in `includes/` before components
5. **Component parameters**: Pass only required data to components, avoid global state
6. **Function size limits**: Keep functions focused and concise (max 20-30 lines), break down complex logic into smaller helpers
7. **Single responsibility**: Each function should have one clear purpose
8. **Modular JavaScript**: Organize scripts in `scripts/` directories, import and initialize from `view.js` entry point. When functionality fails with separate files — typically due to **asset load order** (script runs before block DOM or styles apply too late), or stacking context — use inline CSS/JS in `render.php` and keep that code modular and readable.
9. **JavaScript naming convention**: If a script belongs to a specific component, use the component name (e.g., `filter-menu.js` for `filter-menu.php`). For shared/core functionality, use `core.{descriptive-name}.js` (e.g., `core.utils.js`)
10. **Server-side rendering first**: Prioritize server-side rendering for SEO and performance, JavaScript enhances functionality
11. **URL state management**: Keep filter state in URL parameters for bookmarking and SEO
12. **Progressive enhancement**: Ensure core functionality works without JavaScript
13. **Composition over inheritance**: Prefer composing components and functions over inheritance patterns
14. **Loose coupling**: Design modules to be independent with minimal dependencies
15. **Use WooCommerce APIs**: Prefer `wc_get_products()` over `WP_Query` for product queries - better integration, cleaner code, and WooCommerce hooks support

## 📄 License

GPL-2.0-or-later

## 👤 Author

Developed as a test theme for WordPress and WooCommerce.

## 🔗 Resources

- [WordPress Block Editor Documentation](https://developer.wordpress.org/block-editor/)
- [Full Site Editing](https://developer.wordpress.org/block-editor/how-to-guides/full-site-editing/)
- [WooCommerce Developer Docs](https://woocommerce.com/documentation/plugins/woocommerce/)
