<?php
/**
 * Social Profile Template
 *
 * @package TutorLMS/Templates
 * @version v2.0.0
 */

$user = wp_get_current_user();
?>

<h3><?php _e( 'Settings', 'tutor' ); ?></h3>

<div class="tutor-dashboard-setting-social tutor-dashboard-content-inner">

	<div class="tutor-dashboard-inline-links">
		<?php
			tutor_load_template( 'dashboard.settings.nav-bar', array( 'active_setting_nav' => 'social-profile' ) );
		?>
		
		<h3><?php _e( 'Social Profile Link', 'tutor' ); ?></h3>
	</div>

	<form action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
		<input type="hidden" value="tutor_social_profile" name="tutor_action" />
		<?php

			do_action( 'tutor_profile_edit_before_social_media', $user );

			$tutor_user_social_icons = tutor_utils()->tutor_user_social_icons();
		foreach ( $tutor_user_social_icons as $key => $social_icon ) {
			?>
					<div class="tutor-bs-row tutor-bs-align-items-center tutor-mb-30 tutor-social-field">
						<div class="tutor-bs-col-12 tutor-bs-col-sm-4 tutor-bs-col-md-12 tutor-bs-col-lg-3">
							<i class="<?php echo esc_html( $social_icon['icon_classes'] ); ?>"></i>
						<?php echo esc_html( $social_icon['label'] ); ?>
						</div>
						<div class="tutor-bs-col-12 tutor-bs-col-sm-8 tutor-bs-col-md-12 tutor-bs-col-lg-6">
							<input class="tutor-form-control" type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_html( get_user_meta( $user->ID, $key, true ) ); ?>" placeholder="<?php echo esc_html( $social_icon['placeholder'] ); ?>">
						</div>
					</div>
		<?php
		}
		?>

		<div class="tutor-bs-row">
			<div class="tutor-bs-col-12">
				<button type="submit" class="tutor-btn">
					<?php _e( 'Update Profile', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</form>
</div>
