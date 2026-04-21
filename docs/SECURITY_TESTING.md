# Security Testing Guide (Bakka)

Este documento te sirve para validar que el hardening implementado en `cart`, `checkout` y `contact` realmente bloquea los ataques/riesgos enumerados en `specs/18.security.md`.

## Alcance y objetivo

- Validar controles de frontend: sanitización, validación, anti-abuso y UX segura.
- Validar contrato backend: nonce, validación server-side y no-cache en páginas sensibles.
- Confirmar que el flujo funcional de WooCommerce sigue funcionando.

## Pre-requisitos

- Tener entorno local/staging con WordPress + WooCommerce activos.
- Estar logueado y no logueado para probar ambos escenarios.
- Abrir DevTools (Network + Console).
- Opcional: usar Burp/OWASP ZAP para manipulación de requests.

## Matriz rápida de ataques y resultado esperado

| Riesgo | Dónde probar | Resultado esperado |
|---|---|---|
| XSS / HTML injection | Contact, Cupón, mensajes de error | Nunca se ejecuta HTML/JS del usuario; solo texto visible |
| CSRF | AJAX cart/coupon | Request sin nonce o nonce inválido falla |
| AJAX abuse / flood | Contact y cupón | Doble submit bloqueado; timing/cooldown activo |
| Parameter pollution | Checkout/cart endpoints | Parámetros extra/duplicados no rompen ni bypass-ean validaciones |
| SQL Injection (contract) | Handlers PHP tocados | No hay SQL concatenado con input; todo saneado/preparado |
| Business logic bypass | Checkout stepper | No avanza a paso 2 si step 1 inválido |
| Cache poisoning / data leak | Cart/Checkout/My Account | Respuestas no cacheables, sin datos de sesión cruzados |

## Casos de prueba por riesgo

### 1) XSS y HTML Injection

#### Contact form
1. En `Nombre` y `Mensaje`, enviar payloads:
   - `<script>alert(1)</script>`
   - `<img src=x onerror=alert(1)>`
   - `"><svg/onload=alert(1)>`
2. Enviar formulario.

Esperado:
- No se ejecuta ningún script.
- Los mensajes UI se renderizan como texto plano.
- No aparece `innerHTML` no confiable en esos flujos.

#### Cupón
1. Probar cupón con caracteres peligrosos: `<script>`, comillas, tags.
2. Intentar aplicar.

Esperado:
- Se bloquea por formato inválido o se sanea.
- No aparece render inseguro en tags/mensajes.

### 2) CSRF (nonce)

1. Capturar request AJAX de cupón o update cart.
2. Repetir request borrando `nonce`.
3. Repetir request con `nonce` falso.

Esperado:
- Backend responde error de seguridad (no aplica acción).
- Carrito/cupón no cambia.

### 3) Anti-abuso (flood / doble submit)

#### Contacto
1. Abrir página y enviar antes de 2.5s.
2. Completar honeypot manualmente (`website_url`) desde DevTools y enviar.
3. Enviar dos veces rápido.
4. Enviar, y reintentar antes de 10s.

Esperado:
- Timing gate bloquea envío demasiado rápido.
- Honeypot bloquea silenciosamente.
- Doble submit no dispara requests simultáneos.
- Cooldown bloquea reenvío inmediato.

#### Cupón
1. Hacer doble click rápido en aplicar cupón.
2. Repetir removiendo cupón en clicks múltiples.

Esperado:
- Solo un request activo por operación.
- UI refleja loading y evita race conditions.

### 4) Parameter pollution

1. Interceptar request y duplicar parámetros:
   - `coupon_code=VALID&coupon_code=<script>`
   - campos duplicados en checkout.
2. Alterar tipos (ej: `quantity[]=1`, `shipping_postcode=abcd`).

Esperado:
- Backend/JS procesa valores saneados y válidos.
- No hay bypass ni errores fatales.

### 5) SQL Injection (revisión técnica)

Checklist de code review en archivos tocados:
- Verificar que no haya queries SQL manuales concatenando input.
- Si hay SQL custom, confirmar uso de `$wpdb->prepare()`.
- Confirmar uso de `wc_clean`, `sanitize_text_field`, `wp_unslash`.

Esperado:
- Sin puntos de inyección SQL en cambios de esta tarea.

### 6) Business Logic Attacks (WooCommerce)

1. Intentar avanzar a paso 2 con step 1 incompleto.
2. Deshabilitar botón en DOM y forzar click.
3. Forzar submit directo del checkout con campos inválidos.
4. Elegir provincia no permitida y continuar.

Esperado:
- `checkout-stepper` revalida precondiciones antes de avanzar.
- `checkout-validation` marca errores inline y foco al primer error.
- Server-side mantiene bloqueo de región no permitida.

### 7) Cache Poisoning / fuga de datos por caché

1. En `cart`, `checkout`, `my-account`, inspeccionar headers de respuesta.
2. Confirmar presencia de no-cache (por `nocache_headers()`).
3. Probar en dos sesiones/navegadores distintos:
   - Agregar productos/cupones en sesión A.
   - Verificar sesión B no recibe estado de A.

Esperado:
- Páginas sensibles no cacheables.
- Sin contaminación de contenido entre usuarios/sesiones.

## Pruebas de regresión funcional mínimas

- Cart:
  - actualizar cantidad
  - remover item
  - aplicar/remover cupón válido
- Checkout:
  - completar paso 1 válido
  - avanzar a paso 2
  - crear orden con método de pago real/sandbox
- Contacto:
  - envío válido exitoso al endpoint AWS
  - errores de red muestran mensaje amigable

## Criterio de salida (go/no-go)

Puedes considerar el hardening aceptable para release cuando:

- 100% de tests críticos de seguridad pasan (XSS, CSRF, abuse, business logic, cache).
- No hay bypass reproducible de checkout step 1.
- Nonce inválido siempre bloquea acciones AJAX de carrito/cupón.
- No hay ejecución de payload HTML/JS en UI.
- Flujos de compra/contacto siguen funcionando end-to-end.

## Riesgos residuales (importante)

Aunque todo lo anterior pase, aún necesitas defensas server-side adicionales:

- Rate limit en backend (WordPress/AWS/WAF).
- Reglas WAF administradas (Cloudflare/AWS WAF).
- CORS estricto en endpoint de contacto.
- Monitoreo y alertas (picos de error, picos de requests, honeypot hits).
