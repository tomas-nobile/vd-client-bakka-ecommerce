---
alwaysApply: true
---

# Solo logs de errores

## JavaScript

Solo usar `console.error()` para errores reales.
Prohibido: `console.log()`, `console.warn()`, `console.info()`, `console.debug()`.

## PHP

Solo usar `error_log()` para errores o excepciones.
Prohibido: `error_log()` para tracing de flujo, variables de debug, o mensajes informativos.

## Regla

Si agregaste logs para debuggear durante el desarrollo, borrarlos antes de terminar la tarea.
