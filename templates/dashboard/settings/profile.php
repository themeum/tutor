<?php
/**
 * Profile
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.6.2
 */

$user = wp_get_current_user();

// Prepare profile pic.
$profile_placeholder = apply_filters( 'tutor_login_default_avatar', tutor()->url . 'assets/images/profile-photo.png' );
$profile_photo_src   = $profile_placeholder;
$profile_photo_id    = get_user_meta( $user->ID, '_tutor_profile_photo', true );
if ( $profile_photo_id ) {
	$url                                 = wp_get_attachment_image_url( $profile_photo_id, 'full' );
	! empty( $url ) ? $profile_photo_src = $url : 0;
}

// Prepare cover photo.
$cover_placeholder = tutor()->url . 'assets/images/cover-photo.jpg';
$cover_photo_src   = $cover_placeholder;
$cover_photo_id    = get_user_meta( $user->ID, '_tutor_cover_photo', true );
if ( $cover_photo_id ) {
	$url                               = wp_get_attachment_image_url( $cover_photo_id, 'full' );
	! empty( $url ) ? $cover_photo_src = $url : 0;
}

// Prepare display name.
$public_display                     = array();
$public_display['display_nickname'] = $user->nickname;
$public_display['display_username'] = $user->user_login;

if ( ! empty( $user->first_name ) ) {
	$public_display['display_firstname'] = $user->first_name;
}

if ( ! empty( $user->last_name ) ) {
	$public_display['display_lastname'] = $user->last_name;
}

if ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {
	$public_display['display_firstlast'] = $user->first_name . ' ' . $user->last_name;
	$public_display['display_lastfirst'] = $user->last_name . ' ' . $user->first_name;
}

if ( ! in_array( $user->display_name, $public_display ) ) { // Only add this if it isn't duplicated elsewhere.
	$public_display = array( 'display_displayname' => $user->display_name ) + $public_display;
}

$public_display = array_map( 'trim', $public_display );
$public_display = array_unique( $public_display );
$max_filesize   = floatval( ini_get( 'upload_max_filesize' ) ) * ( 1024 * 1024 );
?>

<div class="tutor-dashboard-setting-profile tutor-dashboard-content-inner">

	<?php do_action( 'tutor_profile_edit_form_before' ); ?>

	<div id="tutor_profile_cover_photo_editor">

		<input id="tutor_photo_dialogue_box" type="file" accept=".png,.jpg,.jpeg"/>
		<input type="hidden" class="upload_max_filesize" value="<?php echo esc_attr( $max_filesize ); ?>">
		<div id="tutor_cover_area" data-fallback="<?php echo esc_attr( $cover_placeholder ); ?>" style="background-image:url(<?php echo esc_url( $cover_photo_src ); ?>)">
			<span class="tutor_cover_deleter">
				<span class="dashboard-profile-delete tutor-icon-trash-can-bold"></span>
			</span>
			<div class="tutor_overlay">
				<button class="tutor_cover_uploader tutor-btn tutor-btn-primary">
					<i class="tutor-icon-camera tutor-mr-12" area-hidden="true"></i>
					<span><?php echo $profile_photo_id ? esc_html__( 'Update Cover Photo', 'tutor' ) : esc_html__( 'Upload Cover Photo', 'tutor' ); ?></span>
				</button>
			</div>
		</div>
		<div id="tutor_photo_meta_area">
			<img src="<?php echo esc_url( tutor()->url . '/assets/images/' ); ?>info-icon.svg" />
			<span><?php esc_html_e( 'Profile Photo Size', 'tutor' ); ?>: <span><?php esc_html_e( '200x200', 'tutor' ); ?></span> <?php esc_html_e( 'pixels', 'tutor' ); ?></span>
			<span>&nbsp;&nbsp;&nbsp;&nbsp;<?php esc_html_e( 'Cover Photo Size', 'tutor' ); ?>: <span><?php esc_html_e( '700x430', 'tutor' ); ?></span> <?php esc_html_e( 'pixels', 'tutor' ); ?> </span>
			<span class="loader-area"><?php esc_html_e( 'Saving...', 'tutor' ); ?></span>
		</div>
		<div id="tutor_profile_area" data-fallback="<?php echo esc_attr( $profile_placeholder ); ?>" style="background-image:url(<?php echo esc_url( $profile_photo_src ); ?>)">
			<div class="tutor_overlay">
				<i class="tutor-icon-camera"></i>
			</div>
		</div>
		<div id="tutor_pp_option">
			<div class="up-arrow">
				<i></i>
			</div>

			<span class="tutor_pp_uploader profile-uploader">
				<i class="profile-upload-icon tutor-icon-image-landscape tutor-mr-4"></i> <?php esc_html_e( 'Upload Photo', 'tutor' ); ?>
			</span>
			<span class="tutor_pp_deleter profile-uploader">
				<i class="profile-upload-icon tutor-icon-trash-can-bold tutor-mr-4"></i> <?php esc_html_e( 'Delete', 'tutor' ); ?>
			</span>

			<div></div>
		</div>
	</div>

	<form action="" method="post" enctype="multipart/form-data">
		<?php
		$errors = apply_filters( 'tutor_profile_edit_validation_errors', array() );
		if ( is_array( $errors ) && count( $errors ) ) {
			echo '<div class="tutor-alert-warning tutor-mb-12"><ul class="tutor-required-fields">';
			foreach ( $errors as $error_key => $error_value ) {
				echo '<li>' . esc_html( $error_value ) . '</li>';
			}
			echo '</ul></div>';
		}
		?>

		<?php do_action( 'tutor_profile_edit_input_before' ); ?>

		<div class="tutor-row">
			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6 tutor-mb-32">
				<label class="tutor-form-label tutor-color-secondary">
					<?php esc_html_e( 'First Name', 'tutor' ); ?>
				</label>
				<input class="tutor-form-control" type="text" name="first_name" value="<?php echo esc_attr( $user->first_name ); ?>" placeholder="<?php esc_attr_e( 'First Name', 'tutor' ); ?>">
			</div>

			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6 tutor-mb-32">
				<label class="tutor-form-label tutor-color-secondary">
					<?php esc_html_e( 'Last Name', 'tutor' ); ?>
				</label>
				<input class="tutor-form-control" type="text" name="last_name" value="<?php echo esc_attr( $user->last_name ); ?>" placeholder="<?php esc_attr_e( 'Last Name', 'tutor' ); ?>">
			</div>
		</div>

		<div class="tutor-row">
			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6 tutor-mb-32">
				<label class="tutor-form-label tutor-color-secondary">
					<?php esc_html_e( 'User Name', 'tutor' ); ?>
				</label>
				<input class="tutor-form-control" type="text" disabled="disabled" value="<?php echo esc_attr( $user->user_login ); ?>">
			</div>

			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6 tutor-mb-32">
				<label class="tutor-form-label tutor-color-secondary">
					<?php esc_html_e( 'Phone Number', 'tutor' ); ?>
				</label>
				<input class="tutor-form-control" type="tel" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="phone_number" value="<?php echo esc_html( filter_var( get_user_meta( $user->ID, 'phone_number', true ), FILTER_SANITIZE_NUMBER_INT ) ); ?>" placeholder="<?php esc_attr_e( 'Phone Number', 'tutor' ); ?>">
			</div>
		</div>

		<div class="tutor-row">
			<div class="tutor-col-12 tutor-mb-32">
				<label class="tutor-form-label tutor-color-secondary">
					<?php esc_html_e( 'Skill/Occupation', 'tutor' ); ?>
				</label>
				<input class="tutor-form-control" type="text" name="tutor_profile_job_title" value="<?php echo esc_attr( get_user_meta( $user->ID, '_tutor_profile_job_title', true ) ); ?>" placeholder="<?php esc_attr_e( 'UX Designer', 'tutor' ); ?>">
			</div>
		</div>

		<div class="tutor-row">
			<div class="tutor-col-12 tutor-mb-32">
				<label class="tutor-form-label tutor-color-secondary">
					<?php esc_html_e( 'Bio', 'tutor' ); ?>
				</label>
				<textarea class="tutor-form-control" name="tutor_profile_bio"><?php echo esc_html( strip_tags( get_user_meta( $user->ID, '_tutor_profile_bio', true ) ) ); ?></textarea>
			</div>
		</div>

		<div class="tutor-row">
			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6 tutor-mb-32">
				<label class="tutor-form-label tutor-color-secondary">
					<?php esc_html_e( 'Display name publicly as', 'tutor' ); ?>

				</label>
				<select class="tutor-form-select" name="display_name">
					<?php
					foreach ( $public_display as $id => $item ) {
						?>
								<option <?php selected( $user->display_name, $item ); ?>><?php echo esc_html( $item ); ?></option>
							<?php
					}
					?>
				</select>
				<div class="tutor-fs-7 tutor-color-secondary tutor-mt-12">
					<?php esc_html_e( 'The display name is shown in all public fields, such as the author name, instructor name, student name, and name that will be printed on the certificate.', 'tutor' ); ?>
				</div>
			</div>
		</div>
		<?php do_action( 'tutor_profile_edit_input_after', $user ); ?>

		<div class="tutor-row">
			<div class="tutor-col-12">
				<button type="submit" class="tutor-btn tutor-btn-primary tutor-profile-settings-save">
					<?php esc_html_e( 'Update Profile', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</form>

	<?php do_action( 'tutor_profile_edit_form_after' ); ?>
</div>
<style>
	.tutor-form-control.invalid{border-color: red;}
</style>
