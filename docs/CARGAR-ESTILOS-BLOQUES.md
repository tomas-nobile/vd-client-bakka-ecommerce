# Carga de estilos en bloques y componentes

Guía para que los estilos de un bloque o componente se vean correctamente en el front al crear uno nuevo.

---

## 1. Patrón recomendado

- **Componente con SCSS propio:** Si el componente tiene diseño que no se hace solo con Tailwind, crea `src/[grupo]/components/[nombre].scss` con los estilos (anidados bajo la clase del bloque, p. ej. `.wp-block-etheme-nombre-del-bloque`).
- **El bloque importa ese SCSS:** En el bloque que usa el componente, `src/[grupo]/index/style.scss` debe importar el componente: `@import '../components/[nombre].scss';`.
- **Una sola salida:** Webpack compila el `style.scss` del bloque y genera `build/[grupo]/index/style-index.css`. Ese es el archivo “style” del bloque (`block.json` → `"style": "file:./style-index.css"`). No crees un entry ni un CSS aparte para el componente.

---

## 2. Problema: estilos que no cargan en el front

Cuando el bloque se usa en una **plantilla** (p. ej. `front-page.html`, `archive-product.html`) y no dentro del contenido de una entrada, WordPress **no siempre encola** el archivo “style” del bloque (`style-index.css`). El CSS existe en `build/`, pero no se incluye en la página y el diseño no se ve.

Además, con `npm run start:watch`:
- Tailwind escribe en `build/index.css`.
- Si metes los estilos del bloque en el entry principal (`src/index.js`), ese CSS puede quedar mezclado o sobrescrito por Tailwind. **No uses el bundle principal para estilos de bloques.**

---

## 3. Solución: encolar el style del bloque cuando haga falta

En `functions.php`, dentro de `wp_enqueue_scripts` (o la función donde encolas estilos), **encola explícitamente** el `style-index.css` del bloque en las páginas donde ese bloque se renderiza.

Ejemplo genérico:

```php
// Bloque usado solo en la portada
$block_style = get_template_directory() . '/build/[grupo]/index/style-index.css';
if ( is_front_page() && file_exists( $block_style ) ) {
    wp_enqueue_style(
        'etheme-[grupo]-index-style',
        get_theme_file_uri( '/build/[grupo]/index/style-index.css' ),
        array(),
        filemtime( $block_style )
    );
}
```

Ajusta la condición según dónde use el bloque (p. ej. `is_front_page()`, `is_shop()`, `is_product_category()`, o una plantilla concreta). Siempre comprueba `file_exists()` para no romper si el build no se ha ejecutado.

---

## 4. Qué no hacer

- **No** incluir el SCSS del bloque/componente en `src/index.js` para cargarlo en el front: el bundle principal compite con Tailwind y los estilos pueden no aplicarse.
- **No** crear un entry en webpack que solo importe SCSS para generar un CSS aparte: duplicas la fuente de estilos. El bloque ya genera `style-index.css`; úsalo y encólalo donde corresponda.
- **No** asumir que WordPress encolará el style del bloque solo por estar en `block.json`: en bloques usados en plantillas, encola tú el `style-index.css` en `functions.php`.

---

## 5. Checklist al crear un bloque o componente con estilos propios

1. ¿El bloque tiene entry en `webpack.config.js`? (p. ej. `'front-page/index/index': ...`). Sin entry no se genera `style-index.css`.
2. ¿El componente tiene su `.scss` y el bloque lo importa en su `style.scss`?
3. ¿El bloque se usa en una plantilla (HTML) y no solo en contenido de entradas? Si sí, añade en `functions.php` el `wp_enqueue_style` del `style-index.css` del bloque con la condición adecuada (p. ej. `is_front_page()`).
4. Usa una sola fuente de estilos: el `style-index.css` que ya produce el build del bloque.
