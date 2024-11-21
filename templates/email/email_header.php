<?php
/**
 * E-mail template header
 *
 * @since 2.0.0
 *
 * @package TutorPro
 * @subpackage Templates\Email
 * @author Themeum <support@themeum.com>
 */

$tutor_logo    = tutor()->url . 'assets/images/tutor-logo.png';
$logo          = apply_filters( 'tutor_email_logo_src', $tutor_logo );
$logo_height   = get_tutor_option( 'email_logo_height', 30 );
$logo_alt_text = get_tutor_option( 'email_logo_alt_text', 'logo' );
?>
<div class="tutor-email-header">
	<table>
		<tr>
			<td>
				<a class="tutor-email-logo" target="_blank" href="{site_url}">
					<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $logo_alt_text ); ?>" height="<?php echo esc_attr( $logo_height ); ?>" data-source="email-title-logo" />
				</a>
			</td>
		</tr>
	</table>
</div>
