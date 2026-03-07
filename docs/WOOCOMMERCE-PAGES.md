# Páginas de WooCommerce - Guía Completa

Este documento explica para qué sirve cada página/template en WooCommerce y cuándo se utiliza cada uno.

---

## 🛍️ Páginas Principales de WooCommerce

### `archive-product.html`
**Propósito:** Muestra la página principal de la tienda (shop) con todos los productos.

**Cuándo se usa:**
- Cuando el usuario visita la URL de la tienda (configurada en WooCommerce → Ajustes → Productos)
- Muestra una lista/grid de todos los productos disponibles
- Incluye opciones de filtrado, ordenamiento y paginación

**Contenido típico:**
- Título de la tienda
- Contador de resultados
- Opciones de ordenamiento
- Grid/lista de productos
- Paginación
- Filtros de productos (categorías, precios, atributos)

**URL ejemplo:** `/shop/` o la URL configurada en WooCommerce

---

### `single-product.html`
**Propósito:** Muestra la página de un producto individual con todos sus detalles.

**Cuándo se usa:**
- Cuando el usuario hace clic en un producto específico
- Muestra información detallada de un solo producto

**Contenido típico:**
- Galería de imágenes del producto
- Nombre y descripción del producto
- Precio y SKU
- Variaciones (tallas, colores, etc.)
- Botón "Agregar al carrito"
- Tabs con información adicional (descripción, características, reseñas)
- Productos relacionados
- Productos recientemente vistos

**URL ejemplo:** `/product/nombre-del-producto/`

---

### `page-cart.html`
**Propósito:** Muestra el carrito de compras del usuario con todos los productos agregados.

**Cuándo se usa:**
- Cuando el usuario hace clic en el icono del carrito
- Muestra todos los productos que el usuario ha agregado pero aún no ha comprado

**Contenido típico:**
- Lista de productos en el carrito
- Cantidad de cada producto (con opción de modificar)
- Precio unitario y total por producto
- Botón para eliminar productos
- Subtotal
- Formulario para aplicar cupones de descuento
- Cálculo de envío (si aplica)
- Impuestos
- Total del carrito
- Botón "Proceder al checkout"
- Productos relacionados (cross-sells)
- Si el carrito está vacío: mensaje y botón para seguir comprando

**URL ejemplo:** `/cart/` o la URL configurada en WooCommerce

**Estados:**
- **Carrito con productos:** Muestra todos los items y opciones de checkout
- **Carrito vacío:** Muestra mensaje y sugerencias de productos

---

### `page-checkout.html`
**Propósito:** Proceso de pago donde el usuario completa su información y realiza la compra.

**Cuándo se usa:**
- Cuando el usuario hace clic en "Proceder al checkout" desde el carrito
- Último paso antes de completar la compra

**Contenido típico:**
- Formulario de información de facturación
- Formulario de información de envío (si aplica)
- Resumen del pedido
- Métodos de envío disponibles
- Métodos de pago disponibles
- Términos y condiciones (checkbox)
- Botón "Realizar pedido"
- Información de seguridad y métodos de pago aceptados

**URL ejemplo:** `/checkout/` o la URL configurada en WooCommerce

**Nota:** Esta página es crítica para la conversión. Debe ser clara, segura y fácil de completar.

---

### `order-confirmation.html`
**Propósito:** Muestra la confirmación de la orden después de que el usuario completa el pago.

**Cuándo se usa:**
- Inmediatamente después de que el usuario completa el checkout
- También cuando el usuario visita una orden específica desde "Mi Cuenta"

**Contenido típico:**
- Número de orden
- Estado de la orden (procesando, completada, etc.)
- Resumen de productos comprados
- Detalles de la orden (subtotal, envío, impuestos, total)
- Dirección de facturación
- Dirección de envío
- Método de pago utilizado
- Método de envío seleccionado
- Enlaces para descargar productos digitales (si aplica)
- Botón para ver la orden en "Mi Cuenta"
- Botón para continuar comprando

**URL ejemplo:** `/checkout/order-received/123/?key=wc_order_xxx`

**Estados de orden:**
- `pending` - Pendiente de pago
- `processing` - Procesando
- `on-hold` - En espera
- `completed` - Completada
- `cancelled` - Cancelada
- `refunded` - Reembolsada
- `failed` - Fallida

---

## 🏷️ Páginas de Taxonomías (Categorías y Tags)

### `taxonomy-product_cat.html`
**Propósito:** Muestra productos filtrados por una categoría específica.

**Cuándo se usa:**
- Cuando el usuario hace clic en una categoría de producto
- Muestra solo los productos que pertenecen a esa categoría

**Contenido típico:**
- Nombre de la categoría
- Descripción de la categoría (si existe)
- Contador de productos en la categoría
- Opciones de ordenamiento
- Grid/lista de productos de esa categoría
- Paginación
- Subcategorías (si existen)

**URL ejemplo:** `/product-category/electronica/` o `/product-category/ropa/camisetas/`

**Jerarquía:** Puede mostrar categorías padre e hijas

---

### `taxonomy-product_tag.html`
**Propósito:** Muestra productos filtrados por un tag específico.

**Cuándo se usa:**
- Cuando el usuario hace clic en un tag de producto
- Muestra productos que comparten ese tag

**Contenido típico:**
- Nombre del tag
- Contador de productos con ese tag
- Opciones de ordenamiento
- Grid/lista de productos con ese tag
- Paginación

**URL ejemplo:** `/product-tag/oferta/` o `/product-tag/verano/`

**Diferencia con categorías:** Los tags no tienen jerarquía, son más flexibles para agrupar productos por características.

---

### `taxonomy-product_attribute.html`
**Propósito:** Muestra productos filtrados por un atributo específico (como color, talla, material, etc.).

**Cuándo se usa:**
- Cuando el usuario filtra productos por atributos
- Útil para tiendas con productos con variaciones (ropa, zapatos, etc.)

**Contenido típico:**
- Nombre del atributo y valor (ej: "Color: Rojo")
- Contador de productos
- Grid/lista de productos con ese atributo
- Opciones de ordenamiento

**URL ejemplo:** `/attribute/color/rojo/` o `/attribute/talla/l/`

**Ejemplos de atributos comunes:**
- Color
- Talla
- Material
- Marca
- Estilo

---

## 🔍 Páginas de Búsqueda

### `product-search-results.html`
**Propósito:** Muestra los resultados de búsqueda cuando el usuario busca productos.

**Cuándo se usa:**
- Cuando el usuario utiliza la barra de búsqueda para buscar productos
- Muestra productos que coinciden con el término de búsqueda

**Contenido típico:**
- Término de búsqueda utilizado
- Contador de resultados encontrados
- Opciones de ordenamiento
- Grid/lista de productos que coinciden
- Paginación
- Sugerencias si no hay resultados

**URL ejemplo:** `/shop/?s=camiseta` o `/search/?q=producto`

**Funcionalidad:**
- Busca en nombres, descripciones, SKU, tags y categorías
- Puede incluir filtros adicionales

---

### `search.html`
**Propósito:** Página de búsqueda general de WordPress (puede incluir productos y otros contenidos).

**Cuándo se usa:**
- Búsqueda general del sitio (no solo productos)
- Puede incluir productos, páginas, posts, etc.

**Contenido típico:**
- Término de búsqueda
- Resultados de diferentes tipos de contenido
- Filtros por tipo de contenido

**URL ejemplo:** `/search/?s=termino`

---

## 📄 Páginas Generales de WordPress

### `page.html`
**Propósito:** Template genérico para páginas estándar de WordPress.

**Cuándo se usa:**
- Para cualquier página estática creada en WordPress
- Puede usarse para páginas como "Sobre nosotros", "Contacto", "Política de privacidad", etc.
- También puede usarse para la página de "Mi Cuenta" si no hay template específico

**Contenido típico:**
- Título de la página
- Contenido de la página (editor de bloques)
- Sidebar (opcional)
- Footer

**URL ejemplo:** `/sobre-nosotros/`, `/contacto/`, `/mi-cuenta/`

**Nota:** En WooCommerce, la página "Mi Cuenta" puede usar este template o tener su propio template personalizado.

---

### `page-wide.html`
**Propósito:** Similar a `page.html` pero con ancho completo (sin restricciones de ancho).

**Cuándo se usa:**
- Para páginas que necesitan usar todo el ancho de la pantalla
- Útil para landing pages, páginas con contenido visual amplio

**Diferencia con `page.html`:**
- `page.html`: Ancho contenido (típicamente 1200px o similar)
- `page-wide.html`: Ancho completo de la pantalla

---

### `page-cart.html` y `page-checkout.html`
**Nota:** Ya explicados arriba en la sección de páginas principales de WooCommerce.

---

## 📚 Páginas de Archivos

### `archive.html`
**Propósito:** Template genérico para archivos de WordPress (posts, categorías de blog, etc.).

**Cuándo se usa:**
- Para archivos de blog (si el sitio tiene blog además de tienda)
- Archivos de categorías de blog
- Archivos de fechas
- Archivos de autores

**Contenido típico:**
- Título del archivo
- Lista de posts
- Paginación

**URL ejemplo:** `/category/noticias/`, `/author/admin/`, `/2024/01/`

---

### `archive-product.html`
**Nota:** Ya explicado arriba como página principal de la tienda.

---

### `archive-course.html` y `archive-events.html`
**Propósito:** Templates específicos para plugins de terceros (Sensei LMS para cursos, The Events Calendar para eventos).

**Cuándo se usa:**
- Si el sitio usa plugins como Sensei LMS o The Events Calendar
- Muestra listados de cursos o eventos respectivamente

**Contenido típico:**
- Lista de cursos/eventos
- Filtros y ordenamiento
- Información específica del plugin

---

## 👤 Páginas de Autor

### `author.html`
**Propósito:** Muestra el perfil y contenido de un autor específico.

**Cuándo se usa:**
- Cuando el usuario visita la página de un autor
- Útil si el sitio tiene múltiples autores de blog o productos

**Contenido típico:**
- Información del autor (nombre, biografía, avatar)
- Lista de posts/productos del autor
- Estadísticas del autor

**URL ejemplo:** `/author/nombre-autor/`

---

## 📝 Páginas de Contenido Individual

### `single.html`
**Propósito:** Template genérico para posts individuales de blog.

**Cuándo se usa:**
- Para posts de blog individuales
- No se usa para productos (esos usan `single-product.html`)

**Contenido típico:**
- Título del post
- Autor y fecha
- Contenido del post
- Comentarios
- Navegación entre posts

**URL ejemplo:** `/2024/01/nombre-del-post/`

---

### `single-product.html`
**Nota:** Ya explicado arriba en la sección de páginas principales.

---

### `single-course.html` y `single-event.html`
**Propósito:** Templates para contenido individual de plugins de terceros.

**Cuándo se usa:**
- Para páginas individuales de cursos (Sensei LMS)
- Para páginas individuales de eventos (The Events Calendar)

---

### `single-sensei_email.html`
**Propósito:** Template específico para emails de Sensei LMS.

**Cuándo se usa:**
- Para emails enviados por el sistema de cursos
- Template especializado para contenido de email

---

## 🚫 Páginas de Error

### `404.html`
**Propósito:** Muestra la página de error 404 cuando no se encuentra contenido.

**Cuándo se usa:**
- Cuando el usuario visita una URL que no existe
- Cuando se busca contenido que fue eliminado o movido

**Contenido típico:**
- Mensaje de error amigable
- Búsqueda
- Enlaces a páginas importantes (inicio, tienda)
- Productos destacados o populares

**URL ejemplo:** Cualquier URL que no existe

---

## 🎨 Páginas Especiales

### `blank.html`
**Propósito:** Template en blanco sin header ni footer.

**Cuándo se usa:**
- Para páginas que necesitan diseño completamente personalizado
- Para landing pages con diseño único
- Para páginas de mantenimiento

**Características:**
- Sin header
- Sin footer
- Solo el contenido de la página
- Control total sobre el diseño

---

### `no-title.html`
**Propósito:** Similar a `page.html` pero sin mostrar el título de la página.

**Cuándo se usa:**
- Cuando quieres ocultar el título de la página
- Útil para páginas donde el título está incluido en el contenido

**Diferencia con `page.html`:**
- `page.html`: Muestra el título automáticamente
- `no-title.html`: No muestra el título

---

### `front-page.html`
**Propósito:** Template para la página de inicio del sitio.

**Cuándo se usa:**
- Cuando se configura una página estática como página de inicio
- Primera impresión del sitio para los visitantes

**Contenido típico:**
- Hero section
- Productos destacados
- Categorías principales
- Testimonios
- Call-to-actions
- Información de la empresa

**URL ejemplo:** `/` (página principal)

---

## 🔄 Jerarquía de Templates en WordPress/WooCommerce

WordPress y WooCommerce siguen una jerarquía de templates. Si un template más específico no existe, se usa uno más genérico:

### Para Productos:
1. `single-product-{slug}.html` (producto específico)
2. `single-product-{id}.html` (ID específico)
3. `single-product.html` ✅ (este es el que tienes)
4. `single.html`
5. `singular.html`
6. `index.html`

### Para Archivos de Productos:
1. `taxonomy-{taxonomy}-{term}.html` (término específico)
2. `taxonomy-{taxonomy}.html` ✅ (ej: `taxonomy-product_cat.html`)
3. `archive-product.html` ✅
4. `archive.html`
5. `index.html`

### Para Páginas:
1. `page-{slug}.html` (página específica)
2. `page-{id}.html` (ID específico)
3. `page-{template}.html` (template específico)
4. `page.html` ✅
5. `singular.html`
6. `index.html`

---

## 📋 Resumen Rápido

| Template | Propósito | URL Típica |
|----------|-----------|------------|
| `archive-product.html` | Tienda principal | `/shop/` |
| `single-product.html` | Producto individual | `/product/nombre/` |
| `page-cart.html` | Carrito de compras | `/cart/` |
| `page-checkout.html` | Proceso de pago | `/checkout/` |
| `order-confirmation.html` | Confirmación de orden | `/checkout/order-received/` |
| `taxonomy-product_cat.html` | Categoría de productos | `/product-category/nombre/` |
| `taxonomy-product_tag.html` | Tag de productos | `/product-tag/nombre/` |
| `taxonomy-product_attribute.html` | Atributo de producto | `/attribute/nombre/valor/` |
| `product-search-results.html` | Búsqueda de productos | `/shop/?s=termino` |
| `page.html` | Página genérica | `/cualquier-pagina/` |
| `front-page.html` | Página de inicio | `/` |
| `404.html` | Error 404 | Cualquier URL no válida |

---

## 🎯 Mejores Prácticas

1. **Personaliza templates según necesidades:**
   - Usa templates específicos para mejorar la experiencia del usuario
   - Mantén consistencia visual entre templates relacionados

2. **Optimiza para conversión:**
   - `page-checkout.html` debe ser simple y claro
   - `single-product.html` debe destacar el botón "Agregar al carrito"
   - `page-cart.html` debe facilitar el proceso hacia el checkout

3. **Mantén la jerarquía:**
   - No dupliques código innecesariamente
   - Usa template parts para elementos comunes (header, footer)

4. **Prueba en diferentes dispositivos:**
   - Asegúrate de que todos los templates sean responsive
   - Especialmente importante para `page-cart.html` y `page-checkout.html`

5. **Considera el rendimiento:**
   - `archive-product.html` puede tener muchos productos, optimiza la carga
   - Usa lazy loading para imágenes en listados

---

## 🔗 Recursos Adicionales

- [Documentación de Templates de WooCommerce](https://woocommerce.com/document/template-structure/)
- [Jerarquía de Templates de WordPress](https://developer.wordpress.org/themes/basics/template-hierarchy/)
- [Full Site Editing en WordPress](https://wordpress.org/documentation/article/full-site-editing/)

---

**Última actualización:** Enero 2025
