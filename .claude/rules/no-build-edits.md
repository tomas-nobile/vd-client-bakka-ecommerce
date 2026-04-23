---
alwaysApply: true
---

# No editar build/

`build/` es output compilado automáticamente por webpack y Tailwind. Cualquier cambio manual se pisará en el próximo ciclo de compilación.

## Regla

Nunca editar archivos en `build/`. Todo cambio va en `src/`.

| Querés modificar | Editá en |
|---|---|
| Estilos de un bloque | `src/{grupo}/styles/*.scss` |
| JS del frontend | `src/{grupo}/scripts/*.js` |
| Markup del bloque | `src/{grupo}/components/*.php` |
| Estilos globales | `src/index.css` |
