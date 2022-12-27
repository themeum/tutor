<?php
/**
 * Template for displaying Assignments
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.6.2
 */

$settings_url   = tutor_utils()->get_tutor_dashboard_page_permalink( 'settings' );
$withdraw       = tutor_utils()->get_tutor_dashboard_page_permalink( 'settings/withdraw-settings' );
$reset_password = tutor_utils()->get_tutor_dashboard_page_permalink( 'settings/reset-password' );
$social         = tutor_utils()->get_tutor_dashboard_page_permalink( 'settings/social-profile' );

$setting_menus = array(
	'profile'        => array(
		'url'   => esc_url( $settings_url ),
		'title' => __( 'Profile', 'tutor' ),
		'role'  => false,
	),
	'reset_password' => array(
		'url'   => esc_url( $reset_password ),
		'title' => __( 'Password', 'tutor' ),
		'role'  => false,
	),
	'withdrawal'     => array(
		'url'   => esc_url( $withdraw ),
		'title' => __( 'Withdraw', 'tutor' ),
		'role'  => 'instructor',
	),
	'social-profile' => array(
		'url'   => esc_url( $social ),
		'title' => __( 'Social Profile', 'tutor' ),
		'role'  => false,
	),
);

$setting_menus                = apply_filters( 'tutor_dashboard/nav_items/settings/nav_items', $setting_menus );
$GLOBALS['tutor_setting_nav'] = $setting_menus;
?>

<ul class="tutor-nav" tutor-priority-nav>
	<?php foreach ( $setting_menus as $menu_key => $menu ) : ?>
		<?php $valid = 'profile' == $menu_key || ! $menu['role'] || ( 'instructor' == $menu['role'] && current_user_can( tutor()->instructor_role ) ); ?>
		<?php if ( $valid ) : ?>
			<li class="tutor-nav-item">
				<a class="tutor-nav-link<?php echo $active_setting_nav == $menu_key ? ' is-active' : ''; ?>" href="<?php echo esc_url( $menu['url'] ); ?>"><?php echo esc_html( $menu['title'] ); ?></a>
			</li>
		<?php endif; ?>
	<?php endforeach; ?>
	<li class="tutor-nav-item tutor-nav-more tutor-d-none">
		<a class="tutor-nav-link tutor-nav-more-item" href="#"><span class="tutor-mr-4"><?php esc_html_e( 'More', 'tutor' ); ?></span> <span class="tutor-nav-more-icon tutor-icon-times"></span></a>
		<ul class="tutor-nav-more-list tutor-dropdown"></ul>
	</li>
</ul>
