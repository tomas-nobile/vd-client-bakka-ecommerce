---
alwaysApply: true
---

# No correr comandos de build

`npm run start:watch` ya está corriendo en segundo plano. Webpack y Tailwind observan cambios automáticamente.

## Prohibido

- `npm run build`
- `npm run buildwp`
- `npm run tailwindbuild`
- `npm run start:watch`
- `npm run wpstart`

## Permitido solo si el usuario lo pide explícitamente

- `npm run build` para generar assets de producción antes de un deploy

Editar archivos en `src/` es suficiente — los cambios se compilan solos.
