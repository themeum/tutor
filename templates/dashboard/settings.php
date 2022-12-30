<?php
/**
 * Frontend Settings Page
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.4.3
 */

?>
<div class="tutor-fs-5 tutor-fw-medium tutor-mb-24"><?php esc_html_e( 'Settings', 'tutor' ); ?></div>

<div class="tutor-dashboard-content-inner">
	<div class="tutor-mb-32">
		<?php
			tutor_load_template( 'dashboard.settings.nav-bar', array( 'active_setting_nav' => 'profile' ) );
		?>
	</div>
</div>

<?php
if ( isset( $GLOBALS['tutor_setting_nav']['profile'] ) ) {
	tutor_load_template( 'dashboard.settings.profile' );
} else {
	foreach ( $GLOBALS['tutor_setting_nav'] as $page ) {
		echo '<script>window.location.replace("', esc_url( $page['url'] ), '");</script>';
		break;
	}
}
?>
