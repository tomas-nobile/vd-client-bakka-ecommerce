# config.json — Configuración del sitio

Archivo: `src/core/config/config.json`

Centraliza todos los valores de configuración del sitio que cambian por cliente: datos de contacto, redes sociales, FAQs, y contenido de las páginas legales.

**Regla:** Nunca hardcodear estos valores en PHP o JS. Siempre leer de este archivo.

---

## Estructura

```
config.json
├── contact          ← datos de contacto y endpoint del formulario
├── social           ← redes sociales con URLs e iconos
├── homeFaqs         ← preguntas frecuentes del home
├── privacy          ← contenido de Política de Privacidad
├── terms            ← contenido de Términos y Condiciones
└── commerceConditions ← contenido de Condiciones de Compra
```

---

## Claves principales

### `contact`
```json
{
  "locationText": "Buenos Aires, Argentina",
  "locationUrl": "https://maps.google.com/...",
  "phoneLabel": "+54 9 11 1234-5678",
  "whatsappUrl": "https://wa.me/...",
  "email": "hola@bakkamuebles.com.ar",
  "formEndpoint": "https://api.example.com/contact"
}
```

### `social`
Objeto con claves `whatsapp`, `instagram`, `facebook`, `tiktok`, `pinterest`.
Cada uno tiene `url` y opcionalmente `handle` e `icon` (ruta al SVG en `assets/icons/`).

### `homeFaqs`
```json
{
  "title": "Preguntas frecuentes",
  "items": [{ "question": "...", "answer": "..." }]
}
```

### `privacy` / `terms` / `commerceConditions`
```json
{
  "title": "...",
  "breadcrumbLabel": "...",
  "subtitle": "...",
  "intro": "...",
  "sections": [{ "heading": "1. ...", "body": "..." }]
}
```

---

## Cómo leerlo desde PHP

```php
function etheme_get_config() {
    static $config = null;
    if ( $config === null ) {
        $path = get_template_directory() . '/src/core/config/config.json';
        $config = json_decode( file_get_contents( $path ), true );
    }
    return $config;
}

// Uso
$config  = etheme_get_config();
$email   = esc_html( $config['contact']['email'] );
$wa_url  = esc_url( $config['contact']['whatsappUrl'] );
$faqs    = $config['homeFaqs']['items'];
```

> Usar `static` para no leer el archivo más de una vez por request.

---

## Cómo pasarlo a JavaScript

En `render.php`, pasando solo los datos necesarios via `data-*`:

```php
$config = etheme_get_config();
?>
<div
    id="etheme-contact"
    data-endpoint="<?php echo esc_attr( $config['contact']['formEndpoint'] ); ?>"
    data-whatsapp="<?php echo esc_attr( $config['social']['whatsapp']['url'] ); ?>"
>
```

O via `wp_localize_script` para objetos más grandes:
```php
wp_localize_script( 'etheme-front-page-index-view-script', 'ethemeConfig', array(
    'faqs' => $config['homeFaqs']['items'],
) );
```

---

## Cómo adaptar para un cliente nuevo

1. Abrir `src/core/config/config.json`
2. Actualizar `contact` con el email, teléfono y endpoint reales del cliente
3. Actualizar `social` con las URLs de redes del cliente
4. Actualizar `homeFaqs.items` con las preguntas del cliente
5. Actualizar `privacy`, `terms` y `commerceConditions` con el contenido legal del cliente
