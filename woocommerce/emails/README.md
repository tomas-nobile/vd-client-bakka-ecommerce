# woocommerce/emails/ — excepción a no-wc-overrides

Esta carpeta es **la única excepción** a la regla `.claude/rules/no-wc-overrides.md`.

## Por qué

WooCommerce resuelve sus mails transaccionales mediante templates PHP (`templates/emails/*`). La única vía soportada para personalizar visualmente un mail transaccional (logo, tipografía, footer con datos del negocio) es overridear estos templates copiándolos a `woocommerce/emails/` en el tema. No hay filtros ni hooks equivalentes para reemplazar el layout completo de un mail.

Ver [spec 22](../../specs/22.transactional-emails.md) para alcance detallado.

## Qué está permitido acá

- `email-header.php`, `email-footer.php`, `email-styles.php`, `email-order-details.php` — partials compartidos por todos los mails.
- `customer-processing-order.php`, `customer-completed-order.php` — mails al cliente.
- `admin-new-order.php`, `admin-failed-order.php` — mails al admin.
- `plain/` — versiones texto plano de los 4 anteriores (requeridas para deliverability).

## Qué NO está permitido

- Cualquier otro template del plugin WooCommerce (shop, cart, checkout, single-product, etc.). La regla `.claude/rules/no-wc-overrides.md` sigue vigente fuera de esta carpeta.
- Overridear templates de mail que no estén en la lista del spec 22. Si se quiere customizar otro mail (ej. `customer-refunded-order`), actualizar el spec primero.
