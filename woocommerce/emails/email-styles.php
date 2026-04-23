<?php
/**
 * Email Styles (override)
 *
 * Inline styles live in each template — this file targets clients that DO read
 * <style> blocks (Apple Mail, Gmail web con limitaciones). Mantener minimalista.
 */

defined( 'ABSPATH' ) || exit;
?>
body {
	margin: 0 !important;
	padding: 0 !important;
	background-color: #f7f7f7;
	-webkit-font-smoothing: antialiased;
}

table {
	border-collapse: collapse;
	mso-table-lspace: 0pt;
	mso-table-rspace: 0pt;
}

img {
	border: 0;
	display: block;
	outline: none;
	text-decoration: none;
	-ms-interpolation-mode: bicubic;
}

a {
	color: #fb704f;
	text-decoration: underline;
}

.etheme-email-card {
	background-color: #ffffff;
}

.etheme-email-heading {
	font-family: Jost, 'Helvetica Neue', Helvetica, Arial, sans-serif;
	font-size: 24px;
	font-weight: 600;
	color: #333333;
	margin: 0 0 16px 0;
	line-height: 1.3;
}

.etheme-email-body {
	font-family: Jost, 'Helvetica Neue', Helvetica, Arial, sans-serif;
	font-size: 16px;
	line-height: 1.6;
	color: #333333;
	margin: 0 0 16px 0;
}

.etheme-email-order-table th,
.etheme-email-order-table td {
	font-family: Jost, 'Helvetica Neue', Helvetica, Arial, sans-serif;
	font-size: 14px;
	color: #333333;
	border-bottom: 1px solid #e5e5e5;
	padding: 12px 8px;
	text-align: left;
	vertical-align: top;
}

.etheme-email-order-table th {
	font-weight: 600;
	color: #666666;
	text-transform: uppercase;
	font-size: 12px;
	letter-spacing: 0.5px;
}

.etheme-email-footer-text {
	font-family: Jost, 'Helvetica Neue', Helvetica, Arial, sans-serif;
	font-size: 13px;
	color: #666666;
	line-height: 1.6;
	margin: 0;
}
