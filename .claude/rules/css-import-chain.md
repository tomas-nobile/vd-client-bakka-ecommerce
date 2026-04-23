---
alwaysApply: true
---

# Cadena de importación CSS

Los bloques son server-side rendered (SSR). Para que los estilos carguen en el frontend, la cadena de importación debe ser exacta.

## Cadena obligatoria

```
index.js  →  import './style.scss'  →  @import './styles/componente.scss'
```

## Reglas

1. `import './style.scss'` va en `index.js`, **no** en `edit.js`
2. Los SCSS parciales viven en `src/{grupo}/styles/` — no sueltos al lado de los `.php`
3. `block.json` debe declarar `"style": "file:./style-index.css"`
4. Para bloques usados en templates (no en post content), enqueue manual en `functions.php`

## Checklist al crear estilos nuevos

- [ ] Parcial creado en `src/{grupo}/styles/{componente}.scss`
- [ ] Importado en `src/{grupo}/index/style.scss`
- [ ] `import './style.scss'` presente en `index.js`
- [ ] `build/{grupo}/{bloque}/style-index.css` existe tras compilar

## Diagnóstico: estilos no cargan en el frontend

**Causa más común:** se editó `style.scss` pero no se verificó que `index.js` tenga el import.  
Sin ese import, webpack no compila el SCSS y el CSS nunca existe en `build/`.

Checklist de diagnóstico en orden:

1. ¿`index.js` tiene `import './style.scss'`? → si no, agregarlo.
2. ¿`block.json` tiene `"style": "file:./style-index.css"`? → si no, agregarlo.
3. ¿El bloque se usa en un template (no en post content)? → enqueue manual en `functions.php` siguiendo el patrón de `etheme_enqueue_front_page_styles` o `etheme_enqueue_wc_page_template_block_styles`.
4. ¿El archivo `build/{grupo}/{bloque}/style-index.css` existe y tiene contenido? → si no, el watch no está corriendo o hay un error de compilación.
