---
alwaysApply: true
---

# Componentes compartidos primero

Antes de crear un componente nuevo, revisar si ya existe en `src/core/components/`.

## Componentes disponibles

| Componente | Archivo | Usado en |
|---|---|---|
| Tarjeta de producto | `product-card.php` | archive, home, relacionados |
| Tarjeta de blog | `blog-card.php` | home, posteos |
| Tarjeta blog con modal | `blog-card-modal.php` | posteos |
| Sub-banner de sección | `sub-banner.php` | múltiples páginas |

## Regla

Si el componente que necesitás existe: usalo, no lo dupliques.
Si necesitás una variación: agregá un parámetro al componente existente.
Si es realmente nuevo y va a usarse en más de una página: crealo en `src/core/components/`.
Si es específico de una sola página: crealo en `src/{grupo}/components/`.
