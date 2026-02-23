<?php
/**
 * Display Permission denied
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

tutor_utils()->tutor_custom_header();
?>
<div class="tutor-wrap tutor-wrap-parent tutor-page-permission-denied tutor-my-8 tutor-ml-auto tutor-mr-auto tutor-shadow-md" style="max-width: 600px;">
	<div class="tutor-card tutor-text-center tutor-px-10 tutor-py-14 tutor-m-5">
		<div class="tutor-svg-img">
			<img src="<?php echo esc_url( tutor()->url . 'assets/images/permission-denied.svg' ); ?>" alt="<?php esc_html_e( 'Permission Denied', 'tutor' ); ?>">    
		</div>

		<div class="tutor-font-medium"><?php echo isset( $message ) ? esc_html( $message ) : esc_html__( 'You don\'t have permission to access this page', 'tutor' ); ?></div>
		<div class="tutor-text-medium tutor-text-muted tutor-mt-5"><?php echo isset( $description ) ? esc_html( $description ) : esc_html__( 'Please make sure you are logged in to correct account if the content needs authorization.', 'tutor' ); ?></div>

		<?php
		if ( ! isset( $button ) ) {
			$button = array(
				'url'  => get_home_url(),
				'text' => 'Homepage',
			);
		}
		?>
		<a href="<?php echo esc_url( $button['url'] ); ?>" class="tutor-btn tutor-btn-primary tutor-mt-10">
			<?php echo esc_html( $button['text'] ); ?>
		</a>
	</div>
</div>

<?php
tutor_utils()->tutor_custom_footer();
