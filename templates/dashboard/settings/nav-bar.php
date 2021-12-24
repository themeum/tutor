<?php
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
<ul>
	<?php
	foreach ( $setting_menus as $menu_key => $menu ) {
		$valid = $menu_key == 'profile' || ! $menu['role'] || ( $menu['role'] == 'instructor' && current_user_can( tutor()->instructor_role ) );

		if ( $valid ) {
			?>
				<li class="<?php echo $active_setting_nav == $menu_key ? 'active' : ''; ?>">
					<a href="<?php echo $menu['url']; ?>"> <?php echo $menu['title']; ?></a>
				</li>
				<?php
		}
	}
	?>
</ul>
