# EMAILS — setup, testing y mantenimiento

Implementación según [spec 22](../specs/22.transactional-emails.md).

## Resumen

El tema personaliza 4 mails de WooCommerce (2 al cliente + 2 al admin) y agrega 1 mail propio (notificación del formulario de contacto).

| # | Mail | Destinatario | Trigger |
|---|------|--------------|---------|
| C1 | Pago recibido / pedido en preparación | cliente | pedido pasa a `processing` |
| C2 | Pedido entregado | cliente | pedido pasa a `completed` |
| A1 | Nuevo pedido | admin | pedido pasa a `processing` |
| A2 | Pedido fallido | admin | pedido pasa a `failed` |
| A3 | Mensaje de formulario de contacto | admin | envío del form `#etheme-contact-form` |

Los mails WC que no están overrideados (`on-hold`, `refunded`, `customer-note`, `reset-password`, `new-account`, `admin-cancelled`) siguen funcionando — heredan el header/footer/estilos custom automáticamente porque overrideamos `email-header.php`, `email-footer.php` y `email-styles.php`.

---

## Archivos principales

```
woocommerce/emails/
├── README.md                         ← excepción documentada a no-wc-overrides
├── email-header.php                  ← logo + barra coral
├── email-footer.php                  ← datos del negocio + redes + legal
├── email-styles.php                  ← estilos para clientes que leen <style>
├── email-order-details.php           ← tabla de ítems + totales
├── customer-processing-order.php     ← C1
├── customer-completed-order.php      ← C2
├── admin-new-order.php               ← A1
├── admin-failed-order.php            ← A2
└── plain/
    ├── customer-processing-order.php
    ├── customer-completed-order.php
    ├── admin-new-order.php
    └── admin-failed-order.php

src/core/emails/
├── helpers.php                       ← etheme_get_email_* + etheme_send_contact_notification()
├── contact-message-email.php         ← A3 HTML
├── contact-message-email-plain.php   ← A3 plain
└── includes/
    ├── class-etheme-contact-message-email.php  ← WC_Email class
    └── email-hooks.php               ← filtros from/subject/heading + registro de la clase
```

Config: [src/core/config/config.json](../src/core/config/config.json) expone `site.*`, `email.*` y `shipping.preparationTime`.

---

## Cómo testear cada mail

### Desde el admin WC

**WooCommerce → Estado → Herramientas → "Enviar email de prueba"** permite mandar cualquier mail con una orden real:

1. Elegir el tipo (`customer_processing_order`, `new_order`, etc.).
2. Elegir una orden existente.
3. Destinatario — el tuyo para testear.
4. Enviar.

Para el mail A3 (contacto), la forma más rápida es enviar el formulario de contacto desde el sitio; si está pointed al endpoint de AWS, habrá que dispararlo manual desde PHP:

```php
etheme_send_contact_notification( array(
    'name'    => 'Juan Prueba',
    'email'   => 'juan@example.com',
    'phone'   => '+54 9 11 1234 5678',
    'message' => "Hola, quería consultar por...",
) );
```

### Flujo end-to-end en prueba

1. Crear un pedido desde el frontend con gateway "Pago contra entrega" o "Transferencia" → llega **A1** al admin.
2. Cambiar manualmente el estado a **Procesando** → llega **C1** al cliente y (según gateway) **A1** al admin.
3. Cambiar a **Completado** → llega **C2** al cliente.
4. Crear otro pedido, simular falla del gateway → llega **A2**.
5. Enviar el formulario de contacto → llega **A3** al admin con Reply-To = email del cliente.

---

## Configuración SMTP (obligatorio antes de producción)

`wp_mail()` por PHP nativo tiene deliverability malísima en hosting compartido AR. Setup obligatorio:

### Opción recomendada: FluentSMTP + Brevo (ambos gratis)

1. Instalar el plugin **FluentSMTP** (gratis, sin límites).
2. Crear cuenta en **[Brevo](https://www.brevo.com/)** — 300 mails/día gratis.
3. Verificar el dominio del remitente (`bakka.deco` o el que corresponda) agregando los registros **SPF** y **DKIM** en el DNS. Brevo los lista en el onboarding.
4. En FluentSMTP:
   - Agregar conexión → driver **Brevo**.
   - API key de la cuenta de Brevo.
   - Set as default.
5. **Enviar email de prueba** desde la misma UI. Si llega, estamos.

### Alternativa: Resend

3.000 mails/mes gratis, 100/día. Deliverability ligeramente mejor que Brevo. FluentSMTP también lo soporta. Mismo setup (verificar dominio SPF+DKIM, API key).

### Valores críticos

Estos vienen de `src/core/config/config.json` → `email.*`:

| Campo | Uso |
|-------|-----|
| `fromName` | `From:` de todos los mails |
| `fromAddress` | `From:` de todos los mails — **debe coincidir con el dominio verificado en Brevo/Resend** |
| `replyTo` | Reply-To default (salvo A3, que usa el mail del sender) |
| `adminRecipient` | Dónde llegan A1, A2, A3 |

**El setup del SMTP es responsabilidad de ops/cliente, no del código del tema.** El tema solo expone los filtros `woocommerce_email_from_name` / `from_address` que leen de `config.json`.

---

## Personalización visual

### Tokens de color

Definidos en los templates (inline). Si se cambian, revisar en orden:

1. `woocommerce/emails/email-header.php` — barra coral `#fb704f`
2. `woocommerce/emails/email-footer.php` — fondo gris `#f7f7f7`
3. `woocommerce/emails/email-styles.php` — fallback en `<style>`
4. Templates individuales — CTA coral, alerta A2

### Logo PNG (prerequisito)

**SVG no renderiza en Gmail, Outlook ni Apple Mail** — aproximadamente 80% de clientes no lo muestran.

Exportar el logo a PNG:
- Archivo: `assets/images/logo-email.png`
- Tamaño: ~96px de alto, ancho proporcional (~160–200px)
- Fondo transparente
- Peso: <50 KB

El helper `etheme_get_email_logo_url()` usa ese PNG si existe. Si falta, cae al SVG con un TODO comentado. **Mientras falte el PNG, el logo se va a ver mal en ~80% de los inboxes.**

### Tipografía

Stack: `Jost, 'Helvetica Neue', Helvetica, Arial, sans-serif`. Jost no carga en mail (la mayoría strippea `@font-face`), cae a Arial. Esto está bien y es intencional.

---

## Seguridad

- Todo valor dinámico en templates está escapado con `esc_html` / `esc_attr` / `esc_url` / `wp_kses_post`.
- A3 usa `sanitize_email` + `is_email` antes de poner el mail del cliente en Reply-To.
- El handler AJAX de contacto ya tiene verificación de nonce (spec 16) — la parte de mail la toma por `etheme_send_contact_notification()`.
- `error_log` solo se escribe si el envío falla, no hay logs de tracing.

---

## Troubleshooting

**Los mails no llegan:**
- ¿Está activo FluentSMTP con un driver configurado? Test de envío desde su UI.
- ¿Está verificado el dominio `fromAddress` en Brevo/Resend con SPF+DKIM?
- Revisar logs de FluentSMTP (pestaña "Email Logs") para ver si se envió y si hubo bounce.

**Los mails llegan a spam:**
- Confirmar SPF+DKIM. Agregar DMARC si no está.
- El `fromAddress` debe ser del mismo dominio verificado, no un gmail/yahoo.

**El logo no se ve:**
- Verificar que `assets/images/logo-email.png` existe. SVG no renderiza en Gmail/Outlook.

**Los mails de A3 no llegan:**
- Confirmar que `Etheme_Contact_Message_Email` está registrado: WooCommerce → Ajustes → Emails → buscar "Mensaje de formulario de contacto" en la lista.
- Confirmar que el handler del form de contacto está llamando `etheme_send_contact_notification()`. Si el form envía a un endpoint externo (AWS), el WC_Email del tema no se dispara — hay que wirearlo.
