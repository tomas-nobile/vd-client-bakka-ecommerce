---
alwaysApply: true
---

# Block Theme — Orchestrator Pattern

Este es un **WordPress FSE block theme**. Cada página del sitio está construida con un único bloque PHP orquestador.

## Estructura obligatoria

- Cada template `.html` en `templates/` solo referencia un bloque: `<!-- wp:etheme/nombre /-->`
- El orquestador (`src/{grupo}/index/render.php`) solo llama funciones de componentes — nunca tiene HTML propio
- Los componentes viven en `src/{grupo}/components/*.php` y son funciones PHP reutilizables
- El JS del frontend se inicializa desde `view.js` → importa módulos de `scripts/`

## Regla

Nunca agregar markup HTML directamente en `render.php` ni en los templates `.html`.
Si necesitás mostrar algo nuevo, creá un componente en `components/` y llamalo desde `render.php`.
