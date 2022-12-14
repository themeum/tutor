<?php
/**
 * Send reset password template
 *
 * @package Tutor\Templates
 * @subpackage Email
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

defined( 'ABSPATH' ) || exit;
?>


<p>
	<?php printf( esc_html__( 'Hi %s,', 'tutor' ), esc_html( $user_login ) ); ?>
</p>

<p>
	<?php printf( esc_html__( 'Someone has requested a new password for the following account on %s:', 'tutor' ), esc_html( wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) ) ); ?>
</p>

<p><?php printf( esc_html__( 'Username: %s', 'tutor' ), esc_html( $user_login ) ); ?></p>
<p>
	<?php esc_html_e( 'If you didn\'t make this request, just ignore this email. If you\'d like to proceed:', 'tutor' ); ?>
</p>
<p>
	<a class="link" href="<?php echo add_query_arg( array( 'reset_key' => $reset_key, 'user_id' => $user_id ), tutor_utils()->tutor_dashboard_url('retrieve-password') ); ?>"><?php // phpcs:ignore ?>
		<?php esc_html_e( 'Click here to reset your password', 'tutor' ); ?>
	</a>
</p>
<p><?php esc_html_e( 'Thanks for reading.', 'tutor' ); ?></p>
