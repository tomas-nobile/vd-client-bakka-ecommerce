# Cómo crear productos en WooCommerce

## Dónde se crean

WordPress admin → **Productos → Añadir nuevo**

---

## Tipos de producto

Cuando creás un producto, lo primero que elegís es el tipo. Aparece como un desplegable arriba a la derecha del formulario.

---

### Producto simple

El caso más común. Un solo artículo con un precio, sin variantes.

**Cuándo usarlo:** silla de una sola medida, cuadro, espejo, cualquier cosa que no tenga opciones.

**Datos que pedirá:**
- Precio regular
- Precio de oferta (opcional, con fechas si querés)
- SKU — código interno para identificar el producto
- Stock — si querés manejar inventario
- Peso y dimensiones — para cálculo de envío

---

### Producto variable

Un producto que existe en distintas versiones (tallas, colores, materiales). Cada combinación puede tener su propio precio y stock.

**Cuándo usarlo:** silla que viene en blanco o negro, mesa con 3 medidas distintas, ropa.

**Cómo se arma:**
1. Elegís "Producto variable" en el desplegable
2. Vas a la pestaña **Atributos** y creás los atributos (ej: Color → blanco, negro)
3. Vas a la pestaña **Variaciones** y generás o creás las combinaciones
4. A cada variación le ponés su precio, stock y foto

---

### Atributos — guía completa

#### Atributo global vs. atributo por producto

**Atributo global** — se crea una sola vez y se reutiliza en todos los productos.

Cómo crearlo:
1. WordPress admin → **Productos → Atributos**
2. Nombre: `Color` (o `Talle`, `Material`, lo que necesites)
3. Clic en **Añadir atributo**
4. Aparece en la lista — clic en **Configurar términos**
5. Ahí agregás los valores: `Blanco`, `Negro`, `Natural`, etc.
6. Cada valor tiene su propio slug (ver más abajo)

Después, cuando editás un producto variable:
1. Pestaña **Atributos** → desplegable → elegís el atributo global que creaste
2. Seleccionás qué valores aplican a ese producto
3. Tildás **Usado para variaciones**

**Atributo por producto** — se crea directo en el producto, sin pasar por el menú de atributos. Solo sirve para ese producto.

Cuándo usarlo: un atributo muy específico que no vas a repetir en ningún otro producto.

---

#### ¿Los valores van en español o inglés?

En español. El tema reconoce los nombres de colores en español automáticamente y los convierte al color visual correspondiente (en los puntitos de la card y en la ficha de producto).

El slug se genera automáticamente en minúsculas sin acentos (`blanco`, `negro`, `roble-natural`). El slug se usa en la URL de filtros y en la base de datos — el cliente no lo ve.

---

#### ¿Tiene que ser variable para que aparezca el selector de color?

Sí. El selector (desplegable o círculo) solo aparece en productos **variables**, porque el cliente tiene que elegir una variación antes de comprar.

En un producto **simple** también podés agregar atributos, pero solo aparecen como datos informativos en la tabla de especificaciones — no como selector interactivo.

| Situación | Tipo de producto | Qué ve el cliente |
|---|---|---|
| La silla viene solo en blanco | Simple | "Color: Blanco" en la ficha técnica |
| La silla viene en blanco o negro, misma ficha | Variable | Selector para elegir color |
| Querés mostrar "Material: Roble" como dato | Simple, atributo visible | "Material: Roble" en la ficha técnica |

Para que un atributo aparezca en la tabla de especificaciones de un producto simple: pestaña Atributos → agregarlo → tildar **Visible en la página del producto** (sin tildar "Usado para variaciones").

---

#### ¿Se puede mostrar como selector de colores en vez de desplegable?

Por defecto WooCommerce muestra un `<select>` (desplegable de texto) para elegir variaciones. Para mostrar círculos de color o botones visuales necesitás un plugin de swatches.

Plugin gratuito recomendado: **Variation Swatches for WooCommerce** (by Emran Ahmed — disponible en el repositorio oficial de WordPress).

Con ese plugin instalado:
1. Al crear los términos del atributo Color, aparece un campo para elegir el tipo: **Color**, **Imagen** o **Texto**
2. Elegís Color y seleccionás el valor hexadecimal
3. En la página de producto se muestran círculos en lugar del desplegable

Sin el plugin, el desplegable funciona igual — solo es menos visual.

---

#### Checklist para armar un producto variable con colores

- [ ] Crear atributo global "Color" en Productos → Atributos
- [ ] Agregar los valores (Blanco, Negro, etc.) con sus slugs
- [ ] En el producto: pestaña Atributos → elegir "Color" → seleccionar valores → tildar "Usado para variaciones"
- [ ] Pestaña Variaciones → "Generar variaciones" → asignar precio y stock a cada una
- [ ] (Opcional) Subir una foto distinta para cada variación

---

### Producto agrupado

Un grupo de productos simples que se muestran juntos en una misma página, pero se compran por separado.

**Cuándo usarlo:** set de mesas — mesa chica, mesa mediana, mesa grande — donde el cliente elige cuántas unidades de cada una quiere.

**Cómo se arma:**
1. Primero creás cada producto simple por separado
2. Creás un producto agrupado y en el campo **Productos agrupados** buscás y agregás los simples

El producto agrupado no tiene precio propio — el precio lo pone cada simple.

---

### Producto externo / afiliado

Producto que se muestra en tu tienda pero se compra en otro sitio. El botón "Añadir al carrito" redirige a una URL externa.

**Cuándo usarlo:** si vendés en Mercado Libre y querés listar el producto en tu sitio también, o si sos afiliado de otra marca.

**Datos extra:**
- URL del producto externo
- Texto del botón (por defecto "Comprar")

---

## Campos comunes a todos los tipos

### Nombre del producto
El título visible en la tienda. Sé descriptivo pero corto.

### Slug
La parte de la URL que identifica al producto.

- Si el producto se llama "Mesa ratona roble", el slug por defecto será `mesa-ratona-roble`
- La URL del producto quedará: `tusitio.com/producto/mesa-ratona-roble`

**Por qué importa:**
- Los buscadores (Google) indexan la URL — un slug claro ayuda al posicionamiento
- Una vez que el producto tiene visitas o links, cambiar el slug rompe esas URLs

**Buenas prácticas:**
- Minúsculas, sin acentos, palabras separadas por guiones
- Sin palabras vacías: no "la-mesa-de-roble", sí "mesa-roble"
- No lo toques después de publicar salvo que sea realmente necesario

### Descripción corta

Aparece debajo del precio, antes del botón "Agregar al carrito". Máximo 2–3 líneas con lo más importante del producto.

**Cómo cargarla:**
El editor visual puede no funcionar. Ir directamente a la pestaña **Código** y escribir HTML simple:

```html
<p>Silla de madera maciza con tapizado en lino natural. Disponible en blanco y negro.</p>
```

Para negrita o itálica dentro del texto:
```html
<p>Mesa de <strong>roble macizo</strong>. Medidas: 120 × 60 cm.</p>
```

---

### Descripción larga

Aparece en la sección **"DESCRIPCIÓN"** — un acordeón que está más abajo en la página de producto, debajo de la galería y el precio. El acordeón arranca cerrado; el cliente tiene que hacer clic para abrirlo.

**Cómo cargarla:**
El editor de bloques (Gutenberg) puede no responder en el campo de descripción. En ese caso hacer clic en los **tres puntos** arriba a la derecha del bloque de contenido → **Editar como HTML**, y escribir directo:

```html
<p>Descripción completa del producto. Podés escribir varios párrafos.</p>

<p>Segundo párrafo con más detalle: materiales, medidas, instrucciones de cuidado.</p>
```

Para listas:
```html
<ul>
  <li>Material: madera maciza de roble</li>
  <li>Dimensiones: 120 × 60 × 75 cm</li>
  <li>Peso: 18 kg</li>
  <li>Color disponible: natural, oscuro</li>
</ul>
```

### Categorías y etiquetas
Las categorías organizan el catálogo (Sillas, Mesas, Iluminación). Las etiquetas son opcionales y más libres.
Asignale **siempre al menos una categoría** — el filtro del listado de productos depende de esto.

### Imagen del producto

La foto principal del producto. Aparece en la grilla del listado, en la ficha de detalle y en el carrito.

**Cómo cargarla:**
1. En el panel lateral derecho buscá el bloque **Imagen del producto**
2. Hacé clic en "Establecer imagen del producto"
3. En la biblioteca de medios subí el archivo o elegí uno ya cargado
4. Antes de confirmar, completá el campo **Texto alternativo** (ver más abajo)
5. Clic en "Establecer imagen del producto"

**Requisitos técnicos:**
- Formato: JPG o WebP (JPG para fotos, WebP si querés mejor compresión)
- Proporción: cuadrada (1:1) — 800×800px mínimo, 1200×1200px ideal
- Fondo blanco o neutro para que se vea bien en la grilla
- Peso máximo recomendado: 200KB — imágenes pesadas hacen lenta la tienda

---

### Galería del producto

Fotos adicionales que el cliente puede ver en la ficha de detalle: ángulos, detalles, ambientación.

**Cómo cargarla:**
1. Debajo de la imagen principal encontrás **Galería del producto**
2. Clic en "Añadir a la galería del producto"
3. Seleccioná varias imágenes a la vez si querés
4. Completá el texto alternativo de cada una antes de confirmar
5. Podés reordenarlas arrastrando

**Recomendaciones:**
- Mínimo 3 fotos: frente, ángulo y detalle o ambientación
- Misma proporción que la imagen principal para que el carrusel no salte
- Si el producto tiene variaciones (color, material), agregá fotos de cada variante

---

### Texto alternativo (alt text)

> El texto alternativo es la descripción escrita de una imagen. Es uno de los factores de posicionamiento más ignorados y más fáciles de mejorar.

**Para qué sirve — las tres razones que importan:**

1. **SEO** — Google no puede "ver" imágenes. Lee el alt text para entender qué muestra la foto e indexarla en Google Imágenes. Una imagen sin alt text es invisible para el buscador.

2. **Accesibilidad** — los lectores de pantalla que usan personas con discapacidad visual leen el alt text en voz alta. Sin él, el usuario escucha "imagen" y nada más.

3. **Fallback** — si la imagen no carga (mala conexión, error de servidor), el navegador muestra el alt text en su lugar.

**Cómo escribirlo bien:**

| Malo | Bueno |
|---|---|
| `imagen` | `Mesa ratona de madera maciza roble natural` |
| `IMG_4823.jpg` | `Silla de comedor tapizada en tela gris antracita` |
| `producto1` | `Lámpara de pie con base de mármol blanco y pantalla lino` |
| `foto` | `Detalle del tejido de la butaca modelo Oslo` |

**Reglas:**
- Describí lo que se ve en esa imagen específica, no el producto en general
- Incluí el material y el color si son relevantes
- Sin "foto de", "imagen de" — Google lo sabe, es redundante
- Máximo 125 caracteres
- Si es una foto decorativa sin información útil, dejalo vacío (no pongas texto inventado)

---

## Precio de oferta

Podés poner un precio de oferta y activarlo solo en un rango de fechas. El precio normal se tacha y aparece el de oferta. Cuando pasan las fechas, vuelve al precio regular solo.

---

## Stock

Si activás la gestión de stock, WooCommerce descuenta unidades al confirmar cada pedido.

- **Cantidad en stock** — unidades disponibles
- **Umbral de stock bajo** — te avisa por email cuando llega a ese número
- **¿Permitir pedidos con stock agotado?** — si lo dejás en "No", el botón de compra se desactiva solo

Si no querés manejar stock, dejá "Gestionar stock" sin marcar y el producto siempre figurará disponible.

---

## SKU

Código único que vos le asignás al producto. Puede ser el código del proveedor, tu propio sistema, lo que uses internamente.

No es obligatorio, pero si manejás muchos productos es muy útil para filtrar pedidos y exportar datos.

---

## Estado del producto

- **Publicado** — visible en la tienda
- **Borrador** — guardado pero no visible
- **Privado** — visible solo para admins

Usá Borrador mientras armás un producto y Publicado cuando esté listo.

---

## Orden de carga de una ficha de producto

1. El cliente entra a la URL del producto
2. WooCommerce busca el post con ese slug
3. El bloque `single-product/index` renderiza la ficha
4. La galería, el precio y las variaciones se cargan desde la base de datos en el momento

No hace falta hacer nada especial en el tema — cualquier producto que publiques aparece automáticamente con el diseño del tema.
