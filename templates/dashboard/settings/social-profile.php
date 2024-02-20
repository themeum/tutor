<?php
/**
 * Social Profile Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$user = wp_get_current_user();
?>

<div class="tutor-fs-5 tutor-fw-medium tutor-mb-24"><?php esc_html_e( 'Settings', 'tutor' ); ?></div>

<div class="tutor-dashboard-setting-social tutor-dashboard-content-inner">

	<div class="tutor-mb-32">
		<?php tutor_load_template( 'dashboard.settings.nav-bar', array( 'active_setting_nav' => 'social-profile' ) ); ?>
		<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mt-32"><?php esc_html_e( 'Social Profile Link', 'tutor' ); ?></div>
	</div>

	<form id="user_social_form" action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
		<input type="hidden" value="tutor_social_profile" name="tutor_action" />
		<?php
			do_action( 'tutor_profile_edit_before_social_media', $user );
			$tutor_user_social_icons = tutor_utils()->tutor_user_social_icons();
		foreach ( $tutor_user_social_icons as $key => $social_icon ) :
			?>
			<div class="tutor-row tutor-align-center tutor-mb-32 tutor-social-field">
				<div class="tutor-col-12 tutor-col-sm-4 tutor-col-md-12 tutor-col-lg-3">
					<i class="<?php echo esc_html( $social_icon['icon_classes'] ); ?>"></i>
				<?php echo esc_html( $social_icon['label'] ); ?>
				</div>
				<div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-6">
					<input class="tutor-form-control" type="url" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_url( get_user_meta( $user->ID, $key, true ) ); ?>" placeholder="<?php echo esc_html( $social_icon['placeholder'] ); ?>">
				</div>
			</div>
		<?php endforeach; ?>

		<div class="tutor-row">
			<div class="tutor-col-12">
				<button type="submit" class="tutor-btn tutor-btn-primary">
					<?php esc_html_e( 'Update Profile', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</form>
</div>
