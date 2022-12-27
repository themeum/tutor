<?php
/**
 * Uninstall view page
 *
 * @package Tutor\Views
 * @subpackage Tutor\Uninstall
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

?>

<div class="wrap tutor-uninstall-wrap">
	<h2><?php esc_html_e( 'Uninstall Tutor', 'tutor' ); ?></h2>
	<p class="desc"><?php esc_html_e( 'Just deactivate tutor plugin or completely uninstall and erase all of data saved before by tutor.', 'tutor' ); ?></p>

	<div class="tutor-uninstall-btn-group">
		<?php $plugin_file = tutor()->basename; ?>
		<a href="<?php echo esc_url( wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . urlencode( $plugin_file ) ) . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'deactivate-plugin_' . $plugin_file ); ?>" class="tutor-btn tutor-btn-outline-primary"><?php esc_html_e( 'Deactivate', 'tutor' ); ?></a>
		<a href="admin.php?action=uninstall_tutor_and_erase" class="tutor-btn tutor-btn-outline-primary"><?php esc_html_e( 'Completely Uninstall and erase all data', 'tutor' ); ?></a>
	</div>
</div>
