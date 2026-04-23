<?php
/**
 * Email Footer (override)
 *
 * @see specs/22.transactional-emails.md
 */

defined( 'ABSPATH' ) || exit;

$etheme_config    = function_exists( 'etheme_get_core_config' ) ? etheme_get_core_config() : array();
$etheme_site_name = function_exists( 'etheme_get_email_site_name' ) ? etheme_get_email_site_name() : get_bloginfo( 'name' );
$etheme_site_url  = isset( $etheme_config['site']['url'] ) && $etheme_config['site']['url'] ? $etheme_config['site']['url'] : home_url();
$etheme_address   = isset( $etheme_config['site']['address'] ) ? (string) $etheme_config['site']['address'] : '';
$etheme_cuit      = isset( $etheme_config['site']['cuit'] ) ? (string) $etheme_config['site']['cuit'] : '';
$etheme_phone     = isset( $etheme_config['contact']['phoneLabel'] ) ? (string) $etheme_config['contact']['phoneLabel'] : '';
$etheme_email     = isset( $etheme_config['contact']['email'] ) ? (string) $etheme_config['contact']['email'] : '';
$etheme_social    = isset( $etheme_config['social'] ) && is_array( $etheme_config['social'] ) ? $etheme_config['social'] : array();
?>
					</td>
				</tr>

				<!-- Footer -->
				<tr>
					<td style="background-color:#f7f7f7;padding:24px 32px;">

						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td style="padding-bottom:16px;">
									<p class="etheme-email-footer-text" style="margin:0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:13px;color:#666666;line-height:1.6;">
										<strong style="color:#333333;"><?php echo esc_html( $etheme_site_name ); ?></strong><br />
										<?php if ( $etheme_address ) : ?>
											<?php echo esc_html( $etheme_address ); ?><br />
										<?php endif; ?>
										<?php if ( $etheme_cuit ) : ?>
											CUIT: <?php echo esc_html( $etheme_cuit ); ?><br />
										<?php endif; ?>
										<?php if ( $etheme_phone ) : ?>
											Tel: <?php echo esc_html( $etheme_phone ); ?><br />
										<?php endif; ?>
										<?php if ( $etheme_email ) : ?>
											<a href="<?php echo esc_url( 'mailto:' . $etheme_email ); ?>" style="color:#fb704f;text-decoration:underline;"><?php echo esc_html( $etheme_email ); ?></a>
										<?php endif; ?>
									</p>
								</td>
							</tr>

							<?php if ( ! empty( $etheme_social ) ) : ?>
								<tr>
									<td style="padding-bottom:16px;">
										<p class="etheme-email-footer-text" style="margin:0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:13px;color:#666666;line-height:1.6;">
											<?php
											$etheme_social_links = array();
											foreach ( $etheme_social as $platform => $data ) {
												if ( empty( $data['url'] ) || '#' === $data['url'] ) {
													continue;
												}
												$label = ucfirst( (string) $platform );
												$etheme_social_links[] = '<a href="' . esc_url( $data['url'] ) . '" style="color:#fb704f;text-decoration:underline;">' . esc_html( $label ) . '</a>';
											}
											echo wp_kses_post( implode( ' &middot; ', $etheme_social_links ) );
											?>
										</p>
									</td>
								</tr>
							<?php endif; ?>

							<tr>
								<td>
									<p class="etheme-email-footer-text" style="margin:0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:12px;color:#999999;line-height:1.5;">
										<?php
										printf(
											/* translators: %s: site name */
											esc_html__( 'Recibiste este mail porque interactuaste con %s. No respondas a este mail — para consultas usá los canales de contacto.', 'etheme' ),
											esc_html( $etheme_site_name )
										);
										?>
									</p>
								</td>
							</tr>
						</table>

					</td>
				</tr>

			</table>

		</td>
	</tr>
</table>

</body>
</html>
