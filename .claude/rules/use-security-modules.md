---
alwaysApply: true
---

# Usar módulos de seguridad centralizados

Todos los formularios y requests fetch usan los módulos en `src/core/security/`. No escribir validaciones ad-hoc.

## Módulos disponibles

| Módulo | Uso |
|---|---|
| `sanitizers.js` | Limpiar texto, email, teléfono, cupones antes de validar |
| `validators.js` | Validar campos (required, email, teléfono, CP argentino) |
| `messages.js` | Catálogo de mensajes de error en español |
| `ui-feedback.js` | Mostrar errores inline, `aria-invalid`, estados de loading |
| `request-guard.js` | Fetch con timeout, AbortController y lock anti-doble-submit |

## Orden obligatorio

```
SANITIZE → VALIDATE → REQUEST GUARD → UI FEEDBACK
```

## Superficies que ya los usan (referencia)

- Formulario de contacto: `src/contact/scripts/`
- Cupón de descuento: `src/page-cart/scripts/`
- Checkout: `src/page-checkout/scripts/`
