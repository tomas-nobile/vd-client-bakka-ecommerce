<?php
/**
 * Email Header (override)
 *
 * @param string $email_heading Heading del mail.
 * @see specs/22.transactional-emails.md
 */

defined( 'ABSPATH' ) || exit;

$etheme_logo_url = function_exists( 'etheme_get_email_logo_url' ) ? etheme_get_email_logo_url() : '';
$etheme_site_name = function_exists( 'etheme_get_email_site_name' ) ? etheme_get_email_site_name() : get_bloginfo( 'name' );
?><!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="color-scheme" content="light only" />
	<meta name="supported-color-schemes" content="light only" />
	<title><?php echo esc_html( $email_heading ); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#f7f7f7;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;color:#333333;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f7f7;">
	<tr>
		<td align="center" style="padding:32px 16px;">

			<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

				<!-- Logo + barra coral -->
				<tr>
					<td align="center" style="background-color:#ffffff;padding:24px 24px 20px 24px;">
						<?php if ( $etheme_logo_url ) : ?>
							<img src="<?php echo esc_url( $etheme_logo_url ); ?>" alt="<?php echo esc_attr( $etheme_site_name ); ?>" width="160" height="48" style="display:block;height:48px;width:auto;max-width:200px;" />
						<?php else : ?>
							<div style="font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:22px;font-weight:600;color:#333333;"><?php echo esc_html( $etheme_site_name ); ?></div>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td style="background-color:#fb704f;height:4px;line-height:4px;font-size:4px;">&nbsp;</td>
				</tr>

				<!-- Card -->
				<tr>
					<td class="etheme-email-card" style="background-color:#ffffff;padding:32px;">

						<h1 class="etheme-email-heading" style="margin:0 0 16px 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:24px;font-weight:600;color:#333333;line-height:1.3;"><?php echo esc_html( $email_heading ); ?></h1>
