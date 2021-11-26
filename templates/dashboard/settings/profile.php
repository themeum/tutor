<?php
/**
 * @package TutorLMS/Templates
 * @version 1.6.2
 */

$user = wp_get_current_user();

// Prepare profile pic
$profile_placeholder = tutor()->url . 'assets/images/profile-photo.png';
$profile_photo_src   = $profile_placeholder;
$profile_photo_id    = get_user_meta( $user->ID, '_tutor_profile_photo', true );
if ( $profile_photo_id ) {
	$url                                 = wp_get_attachment_image_url( $profile_photo_id, 'full' );
	! empty( $url ) ? $profile_photo_src = $url : 0;
}

// Prepare cover photo
$cover_placeholder = tutor()->url . 'assets/images/cover-photo.jpg';
$cover_photo_src   = $cover_placeholder;
$cover_photo_id    = get_user_meta( $user->ID, '_tutor_cover_photo', true );
if ( $cover_photo_id ) {
	$url                               = wp_get_attachment_image_url( $cover_photo_id, 'full' );
	! empty( $url ) ? $cover_photo_src = $url : 0;
}

// Prepare display name
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

if ( ! in_array( $user->display_name, $public_display ) ) { // Only add this if it isn't duplicated elsewhere
	$public_display = array( 'display_displayname' => $user->display_name ) + $public_display;
}

$public_display = array_map( 'trim', $public_display );
$public_display = array_unique( $public_display );

?>

<div class="tutor-dashboard-setting-profile tutor-dashboard-content-inner">

	<?php do_action( 'tutor_profile_edit_form_before' ); ?>

	<div id="tutor_profile_cover_photo_editor">
		<input id="tutor_photo_dialogue_box" type="file" accept=".png,.jpg,.jpeg"/>
		<div id="tutor_cover_area" data-fallback="<?php esc_attr_e( $cover_placeholder ); ?>" style="background-image:url(<?php echo esc_url( $cover_photo_src ); ?>)">
			<span class="tutor_cover_deleter">
				<span class="dashboard-profile-delete ttr-delete-fill-filled"></span>
			</span>
			<div class="tutor_overlay">
				<button class="tutor_cover_uploader">
					<i class="tutor-icon-image-ans"></i>
					<span>
					   <?php
							echo $profile_photo_id ? esc_html__( 'Update Cover Photo', 'tutor' ) : esc_html__( 'Upload Cover Photo', 'tutor' );
						?>
						 
					</span>
				</button>
			</div>
		</div>
		<div id="tutor_photo_meta_area">
			<img src="<?php echo esc_url( tutor()->url . '/assets/images/' ); ?>info-icon.svg" />
			<span><?php esc_html_e( 'Profile Photo Size', 'tutor' ); ?>: <span><?php esc_html_e( '200x200', 'tutor' ); ?></span> <?php esc_html_e( 'pixels', 'tutor' ); ?>,</span>
			<span>&nbsp;&nbsp;&nbsp;&nbsp;<?php esc_html_e( 'Cover Photo Size', 'tutor' ); ?>: <span><?php esc_html_e( '700x430', 'tutor' ); ?></span> <?php esc_html_e( 'pixels', 'tutor' ); ?> </span>
			<span class="loader-area"><?php esc_html_e( 'Saving...', 'tutor' ); ?></span>
		</div>
		<div id="tutor_profile_area" data-fallback="<?php esc_attr_e( $profile_placeholder ); ?>" style="background-image:url(<?php echo esc_url( $profile_photo_src ); ?>)">
			<div class="tutor_overlay">
				<i class="tutor-icon-image-ans"></i>
			</div>
		</div>
		<div id="tutor_pp_option">
			<div class="up-arrow">
				<i></i>
			</div>
			
			<span class="tutor_pp_uploader profile-uploader">
				<i class="profile-upload-icon ttr-image-filled"></i> <?php esc_html_e( 'Upload Photo', 'tutor' ); ?>
			</span>
			<span class="tutor_pp_deleter profile-uploader">
				<i class="profile-upload-icon ttr-delete-fill-filled"></i> <?php esc_html_e( 'Delete', 'tutor' ); ?>
			</span>

			<div></div>
		</div>
	</div>

	<form action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
		<input type="hidden" value="tutor_profile_edit" name="tutor_action" />

		<?php
		$errors = apply_filters( 'tutor_profile_edit_validation_errors', array() );
		if ( is_array( $errors ) && count( $errors ) ) {
			echo wp_kses_post('<div class="tutor-alert-warning tutor-mb-10"><ul class="tutor-required-fields">');
			foreach ( $errors as $error_key => $error_value ) {
				echo wp_kses_post("<li>{$error_value}</li>");
			}
			echo wp_kses_post('</ul></div>');
		}
		?>

		<?php do_action( 'tutor_profile_edit_input_before' ); ?>

		<div class="tutor-bs-row">
			<div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-12 tutor-bs-col-lg-6 tutor-mb-30">
				<label>
					<?php esc_html_e( 'First Name', 'tutor' ); ?>
					<input class="tutor-form-control" type="text" name="first_name" value="<?php esc_attr_e( $user->first_name ); ?>" placeholder="<?php esc_attr_e( 'First Name', 'tutor' ); ?>">
				</label>
			</div>

			<div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-12 tutor-bs-col-lg-6 tutor-mb-30">
				<label>
					<?php esc_html_e( 'Last Name', 'tutor' ); ?>
					<input class="tutor-form-control" type="text" name="last_name" value="<?php esc_attr_e( $user->last_name ); ?>" placeholder="<?php esc_attr_e( 'Last Name', 'tutor' ); ?>">
				</label>
			</div>
		</div>
		
		<div class="tutor-bs-row">
			<div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-12 tutor-bs-col-lg-6 tutor-mb-30">
				<label>
					<?php esc_html_e( 'User Name', 'tutor' ); ?>
					<input class="tutor-form-control" type="text" disabled="disabled" value="<?php esc_attr_e( $user->user_login ); ?>">
				</label>
			</div>

			<div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-12 tutor-bs-col-lg-6 tutor-mb-30">
				<label>
					<?php esc_html_e( 'Phone Number', 'tutor' ); ?>
					<input class="tutor-form-control" type="tel" name="phone_number" value="<?php esc_attr_e( get_user_meta( $user->ID, 'phone_number', true ) ); ?>" placeholder="<?php esc_attr_e( 'Phone Number', 'tutor' ); ?>">
				</label>
			</div>
		</div>

		<div class="tutor-bs-row">
			<div class="tutor-bs-col-12 tutor-mb-30">
				<label>
					<?php esc_html_e( 'Skill/Occupation', 'tutor' ); ?>
					<input class="tutor-form-control" type="text" name="tutor_profile_job_title" value="<?php esc_attr_e( get_user_meta( $user->ID, '_tutor_profile_job_title', true ) ); ?>" placeholder="<?php esc_attr_e( 'UX Designer', 'tutor' ); ?>">
				</label>
			</div>
		</div>

		<div class="tutor-bs-row">
			<div class="tutor-bs-col-12 tutor-mb-30">
				<label>
					<?php _e( 'Bio', 'tutor' ); ?>
					<textarea class="tutor-form-control" name="tutor_profile_bio"><?php echo strip_tags( get_user_meta( $user->ID, '_tutor_profile_bio', true ) ); ?></textarea>
				</label>
			</div>
		</div>

		<div class="tutor-bs-row">
			<div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-12 tutor-bs-col-lg-6 tutor-mb-30">
				<label>
					<?php _e( 'Display name publicly as', 'tutor' ); ?>

					<select class="tutor-form-select" name="display_name">
						<?php
						foreach ( $public_display as $id => $item ) {
							?>
									<option <?php selected( $user->display_name, $item ); ?>><?php esc_html_e($item); ?></option>
								<?php
						}
						?>
					</select>
				</label>
				<p>
					<small>
						<?php esc_html_e( 'The display name is shown in all public fields, such as the author name, instructor name, student name, and name that will be printed on the certificate.', 'tutor' ); ?>
					</small> 
				</p>
			</div>
		</div>

		<?php do_action( 'tutor_profile_edit_input_after', $user ); ?>

		<div class="tutor-bs-row">
			<div class="tutor-bs-col-12">
				<button type="submit" class="tutor-btn">
					<?php esc_html_e( 'Update Profile', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</form>

	<?php do_action( 'tutor_profile_edit_form_after' ); ?>
</div>
