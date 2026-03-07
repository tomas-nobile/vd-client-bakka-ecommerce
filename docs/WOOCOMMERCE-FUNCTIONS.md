# Funciones de WooCommerce - Referencia Completa

Este documento contiene una referencia completa de las funciones más importantes de WooCommerce organizadas por categorías.

---

## 📦 Productos

### Obtener Productos

#### `wc_get_product( $product_id )`
Obtiene un objeto de producto WooCommerce.

```php
$product = wc_get_product( 123 );
if ( $product ) {
    echo $product->get_name();
    echo $product->get_price();
}
```

**Parámetros:**
- `$product_id` (int): ID del producto

**Retorna:** `WC_Product` object o `false` si no existe

---

#### `wc_get_products( $args )`
Obtiene múltiples productos con filtros avanzados.

```php
$products = wc_get_products( array(
    'status'         => 'publish',
    'limit'          => 10,
    'category'       => array( 'clothing' ),
    'tag'            => array( 'sale' ),
    'stock_status'   => 'instock',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_key'       => '_featured',
    'meta_value'     => 'yes',
) );
```

**Parámetros comunes:**
- `status` (string|array): Estado del producto ('publish', 'draft', etc.)
- `limit` (int): Número de productos a retornar (-1 para todos)
- `offset` (int): Número de productos a saltar
- `orderby` (string): Campo para ordenar ('date', 'title', 'price', 'popularity', 'rating')
- `order` (string): 'ASC' o 'DESC'
- `category` (array): Slugs de categorías
- `tag` (array): Slugs de tags
- `stock_status` (string|array): 'instock', 'outofstock', 'onbackorder'
- `meta_key` (string): Clave meta para filtrar
- `meta_value` (string): Valor meta para filtrar
- `include` (array): IDs específicos a incluir
- `exclude` (array): IDs específicos a excluir
- `on_sale` (bool): Solo productos en oferta
- `featured` (bool): Solo productos destacados

**Retorna:** Array de objetos `WC_Product`

---

#### `wc_get_product_ids_on_sale()`
Obtiene IDs de productos que están en oferta.

```php
$sale_ids = wc_get_product_ids_on_sale();
```

**Retorna:** Array de IDs de productos

---

#### `wc_get_related_products( $product_id, $limit = 5 )`
Obtiene productos relacionados.

```php
$related = wc_get_related_products( 123, 4 );
```

**Parámetros:**
- `$product_id` (int): ID del producto
- `$limit` (int): Número de productos relacionados

**Retorna:** Array de IDs de productos

---

### Información de Productos

#### `wc_get_product_category_list( $product_id, $sep = ', ', $before = '', $after = '' )`
Obtiene la lista de categorías del producto como HTML.

```php
$categories = wc_get_product_category_list( 123, ', ', '<span>', '</span>' );
```

---

#### `wc_get_product_tag_list( $product_id, $sep = ', ', $before = '', $after = '' )`
Obtiene la lista de tags del producto como HTML.

```php
$tags = wc_get_product_tag_list( 123, ', ' );
```

---

#### `wc_get_product_terms( $product_id, $taxonomy, $args = array() )`
Obtiene términos de taxonomía del producto.

```php
$terms = wc_get_product_terms( 123, 'product_cat', array( 'fields' => 'names' ) );
```

**Parámetros:**
- `$product_id` (int): ID del producto
- `$taxonomy` (string): Nombre de la taxonomía
- `$args` (array): Argumentos para `get_the_terms()`

---

### Métodos del Objeto WC_Product

```php
$product = wc_get_product( 123 );

// Información básica
$product->get_id();
$product->get_name();
$product->get_slug();
$product->get_description();
$product->get_short_description();
$product->get_sku();
$product->get_price();
$product->get_regular_price();
$product->get_sale_price();
$product->get_price_html();

// Stock
$product->get_stock_status(); // 'instock', 'outofstock', 'onbackorder'
$product->get_stock_quantity();
$product->is_in_stock();
$product->is_on_backorder();

// Tipos y variaciones
$product->get_type(); // 'simple', 'variable', 'grouped', 'external'
$product->is_type( 'variable' );
$product->is_virtual();
$product->is_downloadable();
$product->is_sold_individually();

// Imágenes
$product->get_image_id();
$product->get_image( $size = 'woocommerce_thumbnail' );
$product->get_gallery_image_ids();

// Categorías y tags
$product->get_category_ids();
$product->get_tag_ids();
$product->get_categories();
$product->get_tags();

// Atributos
$product->get_attributes();
$product->get_variation_attributes();
$product->get_available_variations();

// Ofertas y destacados
$product->is_on_sale();
$product->is_featured();

// Fechas
$product->get_date_created();
$product->get_date_modified();
$product->get_date_on_sale_from();
$product->get_date_on_sale_to();

// Ratings
$product->get_average_rating();
$product->get_rating_count();
$product->get_review_count();

// URLs
$product->get_permalink();
$product->add_to_cart_url();
$product->add_to_cart_text();
```

---

## 🛒 Carrito

### Funciones del Carrito

#### `WC()->cart`
Acceso al objeto del carrito.

```php
$cart = WC()->cart;

// Contenido del carrito
$cart->get_cart(); // Array de items
$cart->get_cart_contents_count(); // Número de items
$cart->get_cart_contents_total(); // Total sin impuestos
$cart->get_cart_total(); // Total con formato
$cart->get_cart_subtotal(); // Subtotal
$cart->get_cart_subtotal_ex_tax(); // Subtotal sin impuestos
$cart->is_empty(); // Verifica si está vacío

// Agregar/Remover items
$cart->add_to_cart( $product_id, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array() );
$cart->remove_cart_item( $cart_item_key );
$cart->empty_cart();

// Cupones
$cart->get_applied_coupons();
$cart->apply_coupon( $coupon_code );
$cart->remove_coupon( $coupon_code );

// Totales
$cart->calculate_totals();
$cart->get_total();
$cart->get_total_ex_tax();
$cart->get_cart_contents_total();
$cart->get_subtotal();
$cart->get_subtotal_ex_tax();
$cart->get_discount_total();
$cart->get_discount_tax();
$cart->get_fee_total();
$cart->get_fee_tax();
$cart->get_shipping_total();
$cart->get_shipping_tax();
$cart->get_total_tax();
```

---

#### `wc_get_cart_url()`
Obtiene la URL de la página del carrito.

```php
$cart_url = wc_get_cart_url();
```

---

#### `wc_get_cart_contents_count()`
Obtiene el número de items en el carrito.

```php
$count = wc_get_cart_contents_count();
```

---

#### `wc_cart_totals_subtotal_html()`
Muestra el subtotal del carrito formateado.

```php
echo wc_cart_totals_subtotal_html();
```

---

## 👤 Clientes y Usuarios

### Crear Cliente / Usuario

#### `wc_create_new_customer( $email, $username = '', $password = '' )`
Función helper de WooCommerce para crear un nuevo usuario con rol `customer`.

```php
$user_id = wc_create_new_customer(
    'cliente@example.com', // $email
    'cliente123',          // $username (opcional, puede ir vacío)
    'PasswordSegura123!'   // $password (opcional, Woo genera una si va vacío)
);

if ( is_wp_error( $user_id ) ) {
    // Hubo un error al crear la cuenta
    error_log( $user_id->get_error_message() );
} else {
    // Cuenta creada correctamente
}
```

**Parámetros:**
- `$email` (string): Email del nuevo cliente (obligatorio)
- `$username` (string): Nombre de usuario (si se deja vacío, WooCommerce puede generar uno)
- `$password` (string): Contraseña (si se deja vacío, WooCommerce genera una y la envía por email según ajustes)

**Retorna:** `int` ID del nuevo usuario o `WP_Error` si falla  

**Notas:**
- Respeta los ajustes de WooCommerce para creación de cuentas (emails, roles, etc.).
- Es la opción recomendada cuando creas clientes desde lógica relacionada con WooCommerce.

---

#### `wp_create_user( $username, $password, $email = '' )`
Función nativa de WordPress para crear un usuario simple.

```php
$user_id = wp_create_user(
    'cliente123',          // $username
    'PasswordSegura123!',  // $password
    'cliente@example.com'  // $email
);

if ( is_wp_error( $user_id ) ) {
    // Hubo un error
    error_log( $user_id->get_error_message() );
} else {
    // Asignar rol de cliente si es necesario
    $user = new WP_User( $user_id );
    $user->set_role( 'customer' );
}
```

**Parámetros:**
- `$username` (string): Nombre de usuario
- `$password` (string): Contraseña
- `$email` (string): Email del usuario (opcional pero recomendable)

**Retorna:** `int` ID del nuevo usuario o `WP_Error` si falla  

**Cuándo usar cuál:**
- Usa **`wc_create_new_customer()`** cuando estás trabajando en flujos de WooCommerce (checkout, formularios de cliente, etc.).
- Usa **`wp_create_user()`** para lógica más genérica de WordPress donde luego tú controlas el rol y comportamiento.

---

### Obtener Cliente

#### `new WC_Customer( $user_id )`
Crea un objeto de cliente WooCommerce.

```php
$customer = new WC_Customer( get_current_user_id() );
```

---

#### `WC()->customer`
Acceso al objeto del cliente actual.

```php
$customer = WC()->customer;
```

---

### Métodos del Objeto WC_Customer

```php
$customer = new WC_Customer( $user_id );

// Información básica
$customer->get_id();
$customer->get_email();
$customer->get_username();
$customer->get_first_name();
$customer->get_last_name();
$customer->get_display_name();
$customer->get_role();
$customer->get_date_created();
$customer->get_date_modified();

// Dirección de facturación
$customer->get_billing_first_name();
$customer->get_billing_last_name();
$customer->get_billing_company();
$customer->get_billing_address_1();
$customer->get_billing_address_2();
$customer->get_billing_city();
$customer->get_billing_state();
$customer->get_billing_postcode();
$customer->get_billing_country();
$customer->get_billing_email();
$customer->get_billing_phone();
$customer->get_billing_address();

// Dirección de envío
$customer->get_shipping_first_name();
$customer->get_shipping_last_name();
$customer->get_shipping_company();
$customer->get_shipping_address_1();
$customer->get_shipping_address_2();
$customer->get_shipping_city();
$customer->get_shipping_state();
$customer->get_shipping_postcode();
$customer->get_shipping_country();
$customer->get_shipping_address();

// Métodos
$customer->get_is_paying_customer();
$customer->get_orders_count();
$customer->get_total_spent();
$customer->get_avatar_url();
```

---

### Funciones de Usuario

#### `is_user_logged_in()`
Verifica si hay un usuario logueado.

```php
if ( is_user_logged_in() ) {
    // Usuario logueado
}
```

---

#### `get_current_user_id()`
Obtiene el ID del usuario actual.

```php
$user_id = get_current_user_id();
```

---

#### `wp_get_current_user()`
Obtiene el objeto del usuario actual.

```php
$current_user = wp_get_current_user();
echo $current_user->user_email;
echo $current_user->display_name;
```

---

## 📋 Órdenes

### Obtener Órdenes

#### `wc_get_order( $order_id )`
Obtiene un objeto de orden WooCommerce.

```php
$order = wc_get_order( 456 );
if ( $order ) {
    echo $order->get_total();
    echo $order->get_status();
}
```

**Parámetros:**
- `$order_id` (int): ID de la orden

**Retorna:** `WC_Order` object o `false` si no existe

---

#### `wc_get_orders( $args )`
Obtiene múltiples órdenes con filtros.

```php
$orders = wc_get_orders( array(
    'customer_id' => get_current_user_id(),
    'status'      => array( 'wc-completed', 'wc-processing' ),
    'limit'       => 10,
    'orderby'     => 'date',
    'order'       => 'DESC',
    'date_created' => '2024-01-01...2024-12-31',
) );
```

**Parámetros comunes:**
- `customer_id` (int): ID del cliente
- `status` (string|array): Estado de la orden ('wc-completed', 'wc-processing', etc.)
- `limit` (int): Número de órdenes (-1 para todas)
- `offset` (int): Número de órdenes a saltar
- `orderby` (string): Campo para ordenar ('date', 'id', 'total', etc.)
- `order` (string): 'ASC' o 'DESC'
- `date_created` (string): Rango de fechas (formato: 'YYYY-MM-DD...YYYY-MM-DD')
- `include` (array): IDs específicos a incluir
- `exclude` (array): IDs específicos a excluir
- `meta_key` (string): Clave meta para filtrar
- `meta_value` (string): Valor meta para filtrar
- `customer` (string|array): Email o IDs de clientes
- `product` (int): ID de producto (órdenes que contienen este producto)
- `parent` (int): ID de orden padre (para sub-órdenes)

**Retorna:** Array de objetos `WC_Order`

---

### Métodos del Objeto WC_Order

```php
$order = wc_get_order( 456 );

// Información básica
$order->get_id();
$order->get_order_number();
$order->get_status(); // 'completed', 'processing', 'pending', etc.
$order->get_date_created();
$order->get_date_modified();
$order->get_date_paid();
$order->get_date_completed();

// Cliente
$order->get_customer_id();
$order->get_user_id();
$order->get_billing_email();
$order->get_billing_phone();
$order->get_billing_first_name();
$order->get_billing_last_name();
$order->get_billing_company();
$order->get_billing_address_1();
$order->get_billing_address_2();
$order->get_billing_city();
$order->get_billing_state();
$order->get_billing_postcode();
$order->get_billing_country();
$order->get_billing_address();

// Envío
$order->get_shipping_first_name();
$order->get_shipping_last_name();
$order->get_shipping_company();
$order->get_shipping_address_1();
$order->get_shipping_address_2();
$order->get_shipping_city();
$order->get_shipping_state();
$order->get_shipping_postcode();
$order->get_shipping_country();
$order->get_shipping_address();

// Items
$order->get_items(); // Todos los items
$order->get_items( 'line_item' ); // Solo productos
$order->get_items( 'shipping' ); // Solo envíos
$order->get_items( 'fee' ); // Solo fees
$order->get_items( 'tax' ); // Solo impuestos
$order->get_item_count();
$order->get_item_count_refunded();

// Totales
$order->get_subtotal();
$order->get_subtotal_to_display();
$order->get_total();
$order->get_total_tax();
$order->get_total_discount();
$order->get_total_shipping();
$order->get_total_refunded();
$order->get_total_tax_refunded();
$order->get_total_shipping_refunded();
$order->get_discount_total();
$order->get_discount_tax();
$order->get_shipping_total();
$order->get_shipping_tax();
$order->get_cart_tax();
$order->get_total_fees();

// Métodos de pago
$order->get_payment_method();
$order->get_payment_method_title();
$order->get_transaction_id();
$order->get_currency();
$order->get_currency_symbol();

// Métodos de envío
$order->get_shipping_method();
$order->get_shipping_methods();

// Cupones
$order->get_coupon_codes();
$order->get_used_coupons();

// Notas
$order->get_customer_order_notes();
$order->get_customer_note();

// URLs
$order->get_view_order_url();
$order->get_checkout_payment_url();
$order->get_checkout_order_received_url();

// Estados
$order->has_status( 'completed' );
$order->is_paid();
$order->needs_payment();
$order->needs_shipping_address();
```

---

### Crear y Actualizar Órdenes

```php
// Crear nueva orden
$order = wc_create_order();

// Agregar productos
$order->add_product( wc_get_product( 123 ), 2 ); // Producto ID 123, cantidad 2

// Agregar shipping
$order->set_shipping_total( 10.00 );
$order->set_shipping_method( 'flat_rate' );

// Agregar fee
$order->add_fee( new WC_Order_Item_Fee() );

// Establecer direcciones
$order->set_billing_first_name( 'John' );
$order->set_billing_last_name( 'Doe' );
$order->set_billing_email( 'john@example.com' );
// ... más campos de billing y shipping

// Establecer método de pago
$order->set_payment_method( 'bacs' );

// Calcular totales
$order->calculate_totals();

// Guardar
$order->save();

// Actualizar estado
$order->update_status( 'completed', 'Orden completada manualmente' );
```

---

## 🏪 Páginas de WooCommerce

### URLs de Páginas

#### `wc_get_page_permalink( $page )`
Obtiene la URL de una página de WooCommerce.

```php
$shop_url = wc_get_page_permalink( 'shop' );
$cart_url = wc_get_page_permalink( 'cart' );
$checkout_url = wc_get_page_permalink( 'checkout' );
$myaccount_url = wc_get_page_permalink( 'myaccount' );
```

**Parámetros:**
- `$page` (string): 'shop', 'cart', 'checkout', 'myaccount', 'terms'

**Retorna:** URL de la página o `false` si no existe

---

#### `wc_get_page_id( $page )`
Obtiene el ID de una página de WooCommerce.

```php
$shop_id = wc_get_page_id( 'shop' );
```

---

### Funciones de URLs Específicas

```php
wc_get_cart_url(); // URL del carrito
wc_get_checkout_url(); // URL del checkout
wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ); // URL de órdenes en My Account
```

---

## 💰 Precios y Formato

### Formatear Precios

#### `wc_price( $price, $args = array() )`
Formatea un precio según la configuración de WooCommerce.

```php
echo wc_price( 29.99 );
// Output: $29.99 (o formato según configuración)

echo wc_price( 29.99, array( 'currency' => 'EUR' ) );
```

**Parámetros:**
- `$price` (float|string): Precio a formatear
- `$args` (array): Argumentos adicionales
  - `currency` (string): Código de moneda
  - `decimal_separator` (string): Separador decimal
  - `thousand_separator` (string): Separador de miles
  - `decimals` (int): Número de decimales

---

#### `wc_format_decimal( $number, $dp = false, $trim_zeros = false )`
Formatea un número decimal.

```php
$formatted = wc_format_decimal( 29.999, 2 );
```

---

#### `wc_trim_zeros( $price )`
Elimina ceros innecesarios de un precio.

```php
$price = wc_trim_zeros( '29.00' ); // Retorna '29'
```

---

## 🏷️ Categorías y Taxonomías

### Obtener Categorías

#### `wc_get_product_categories( $args = array() )`
Obtiene categorías de productos.

```php
$categories = wc_get_product_categories( array(
    'orderby' => 'name',
    'order'   => 'ASC',
    'hide_empty' => false,
) );
```

---

#### `get_terms( 'product_cat', $args )`
Obtiene términos de la taxonomía de categorías.

```php
$categories = get_terms( array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
    'parent'     => 0, // Solo categorías padre
) );
```

---

#### `wc_get_product_cat_slugs()`
Obtiene los slugs de todas las categorías.

```php
$slugs = wc_get_product_cat_slugs();
```

---

### Obtener Tags

#### `get_terms( 'product_tag', $args )`
Obtiene tags de productos.

```php
$tags = get_terms( array(
    'taxonomy'   => 'product_tag',
    'hide_empty' => false,
) );
```

---

## 🎟️ Cupones

### Obtener Cupones

#### `new WC_Coupon( $code )`
Crea un objeto de cupón.

```php
$coupon = new WC_Coupon( 'SUMMER2024' );
```

---

#### `wc_get_coupon_id_by_code( $code )`
Obtiene el ID de un cupón por su código.

```php
$coupon_id = wc_get_coupon_id_by_code( 'SUMMER2024' );
```

---

### Métodos del Objeto WC_Coupon

```php
$coupon = new WC_Coupon( 'SUMMER2024' );

$coupon->get_id();
$coupon->get_code();
$coupon->get_amount(); // Descuento
$coupon->get_discount_type(); // 'fixed_cart', 'percent', 'fixed_product', 'percent_product'
$coupon->get_date_expires();
$coupon->get_date_created();
$coupon->get_usage_count();
$coupon->get_usage_limit();
$coupon->get_usage_limit_per_user();
$coupon->get_minimum_amount();
$coupon->get_maximum_amount();
$coupon->get_email_restrictions();
$coupon->is_valid();
$coupon->get_free_shipping();
```

---

## 🚚 Envío

### Zonas de Envío

#### `WC_Shipping_Zones::get_zones()`
Obtiene todas las zonas de envío.

```php
$zones = WC_Shipping_Zones::get_zones();
```

---

#### `WC_Shipping_Zones::get_zone_by( $zone_id )`
Obtiene una zona específica.

```php
$zone = WC_Shipping_Zones::get_zone_by( 1 );
```

---

### Métodos de Envío

```php
$shipping_methods = WC()->shipping()->get_shipping_methods();
```

---

## 💳 Pagos

### Métodos de Pago

#### `WC()->payment_gateways->get_available_payment_gateways()`
Obtiene métodos de pago disponibles.

```php
$gateways = WC()->payment_gateways->get_available_payment_gateways();
foreach ( $gateways as $gateway ) {
    echo $gateway->get_title();
    echo $gateway->get_description();
}
```

---

#### `WC()->payment_gateways->payment_gateways()`
Obtiene todos los métodos de pago (incluyendo deshabilitados).

```php
$all_gateways = WC()->payment_gateways->payment_gateways();
```

---

## 🔍 Búsqueda y Filtros

### Funciones de Búsqueda

#### `wc_get_products( $args )`
Búsqueda avanzada de productos (ya documentada arriba).

---

#### `wc_product_search( $term, $include_variations = false )`
Búsqueda simple de productos.

```php
$products = wc_product_search( 'camiseta' );
```

---

### Filtros y Hooks

WooCommerce usa muchos hooks (filtros y acciones) para extender funcionalidad:

```php
// Filtros comunes
apply_filters( 'woocommerce_product_get_price', $price, $product );
apply_filters( 'woocommerce_cart_item_price', $price, $cart_item, $cart_item_key );
apply_filters( 'woocommerce_get_price_html', $price_html, $product );

// Acciones comunes
do_action( 'woocommerce_add_to_cart', $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data );
do_action( 'woocommerce_checkout_order_processed', $order_id, $posted_data, $order );
do_action( 'woocommerce_payment_complete', $order_id );
```

---

## 🛠️ Utilidades

### Verificar WooCommerce

#### `class_exists( 'WooCommerce' )`
Verifica si WooCommerce está activo.

```php
if ( class_exists( 'WooCommerce' ) ) {
    // WooCommerce está activo
}
```

---

#### `function_exists( 'WC' )`
Verifica si la función WC() está disponible.

```php
if ( function_exists( 'WC' ) ) {
    $woocommerce = WC();
}
```

---

#### `WC()`
Obtiene la instancia principal de WooCommerce.

```php
$woocommerce = WC();
```

---

### Funciones de Configuración

#### `get_option( 'woocommerce_myaccount_page_id' )`
Obtiene el ID de la página de Mi Cuenta.

```php
$myaccount_id = get_option( 'woocommerce_myaccount_page_id' );
```

---

#### `wc_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' )`
Carga un template de WooCommerce.

```php
wc_get_template( 'single-product/add-to-cart/simple.php', array( 'product' => $product ) );
```

---

#### `wc_get_template_part( $slug, $name = '' )`
Carga una parte de template.

```php
wc_get_template_part( 'content', 'product' );
```

---

### Formatear Datos

#### `wc_clean( $var )`
Limpia una variable.

```php
$clean = wc_clean( $_POST['data'] );
```

---

#### `wc_sanitize_tooltip( $var )`
Sanitiza texto para tooltips.

```php
$tooltip = wc_sanitize_tooltip( $text );
```

---

#### `wc_format_datetime( $date, $format = '' )`
Formatea una fecha/hora.

```php
$formatted = wc_format_datetime( $order->get_date_created() );
```

---

## 📊 Estadísticas y Reportes

### Funciones de Estadísticas

```php
// Obtener ventas totales
$total_sales = wc_get_product( 123 )->get_total_sales();

// Obtener número de clientes
$customer_count = count_users();

// Obtener ingresos totales
$revenue = wc_get_orders( array(
    'status' => 'wc-completed',
    'limit' => -1,
    'return' => 'ids',
) );
$total_revenue = 0;
foreach ( $revenue as $order_id ) {
    $order = wc_get_order( $order_id );
    $total_revenue += $order->get_total();
}
```

---

## 🔐 Autenticación y Login

### Funciones de Login

#### `wp_login_url( $redirect = '' )`
Obtiene la URL de login de WordPress.

```php
$login_url = wp_login_url( wc_get_page_permalink( 'myaccount' ) );
```

---

#### `wp_signon( $credentials, $secure_cookie = '' )`
Inicia sesión programáticamente.

```php
$user = wp_signon( array(
    'user_login'    => 'username',
    'user_password' => 'password',
    'remember'      => true,
) );

if ( ! is_wp_error( $user ) ) {
    wp_set_current_user( $user->ID );
    wp_set_auth_cookie( $user->ID );
}
```

---

#### `wp_authenticate( $username, $password )`
Autentica un usuario.

```php
$user = wp_authenticate( 'username', 'password' );
if ( ! is_wp_error( $user ) ) {
    // Usuario válido
}
```

---

#### `wp_logout_url( $redirect = '' )`
Obtiene la URL de logout.

```php
$logout_url = wp_logout_url( home_url() );
```

---

## 📝 Notas Adicionales

### Mejores Prácticas

1. **Siempre verifica si WooCommerce está activo:**
   ```php
   if ( ! class_exists( 'WooCommerce' ) ) {
       return;
   }
   ```

2. **Usa `wc_get_products()` en lugar de `WP_Query`** para consultas de productos (mejor integración con WooCommerce).

3. **Verifica si el objeto existe antes de usar métodos:**
   ```php
   $product = wc_get_product( $id );
   if ( $product ) {
       echo $product->get_name();
   }
   ```

4. **Usa los métodos del objeto en lugar de funciones globales cuando sea posible:**
   ```php
   // Mejor
   $product->get_price();
   
   // En lugar de funciones globales cuando sea posible
   ```

5. **Cachea consultas costosas:**
   ```php
   $products = wp_cache_get( 'featured_products' );
   if ( false === $products ) {
       $products = wc_get_products( array( 'featured' => true ) );
       wp_cache_set( 'featured_products', $products, '', 3600 );
   }
   ```

---

## 🔗 Recursos Adicionales

- [Documentación Oficial de WooCommerce](https://woocommerce.com/documentation/)
- [WooCommerce Code Reference](https://woocommerce.github.io/code-reference/)
- [WooCommerce GitHub](https://github.com/woocommerce/woocommerce)

---

**Última actualización:** Enero 2025
