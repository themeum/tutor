<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */


$uid  = get_current_user_id();
$user = get_userdata( $uid );

$profile_settings_link = tutor_utils()->get_tutor_dashboard_page_permalink( 'settings' );

$rdate                 = date_i18n( 'D d M Y, h:i:s a', strtotime( $user->user_registered ) );
$fname                 = $user->first_name;
$lname                 = $user->last_name;
$uname                 = $user->user_login;
$email                 = $user->user_email;
$phone                 = get_user_meta( $uid, 'phone_number', true );
$job                   = nl2br( strip_tags( get_user_meta( $uid, '_tutor_profile_job_title', true ) ) );
$bio                   = nl2br( strip_tags( get_user_meta( $uid, '_tutor_profile_bio', true ) ) );

$profile_data = array(
	array( __( 'Registration Date', 'tutor' ), ( $rdate ? $rdate : esc_html( '-' ) ) ),
	array( __( 'First Name', 'tutor' ), ( $fname ? $fname : esc_html( '-' ) ) ),
	array( __( 'Last Name', 'tutor' ), ( $lname ? $lname : __( '-' ) ) ),
	array( __( 'Username', 'tutor' ), $uname ),
	array( __( 'Email', 'tutor' ), $email ),
	array( __( 'Phone Number', 'tutor' ), ( $phone ? $phone : '-' ) ),
	array( __( 'Skill/Occupation', 'tutor' ), ( $job ? $job : '-' ) ),
	array( __( 'Biography', 'tutor' ), $bio ? $bio : '-' )
)
?>

<div class="tutor-text-medium-h5 tutor-color-text-primary tutor-mb-25 tutor-capitalize-text"><?php _e( 'My Profile', 'tutor' ); ?></div>
<div class="tutor-dashboard-content-inner tutor-dashboard-profile-data">
	<?php

	foreach ( $profile_data as $key => $data ) {
		$first_name_class = ($data[0] == 'First Name' || $data[0] == 'Last Name') ? 'tutor-capitalize-text' : '';
		?>
			<div class="tutor-bs-row">
				<div class="tutor-bs-col-12 tutor-bs-col-sm-5 tutor-bs-col-lg-3">
					<span class="tutor-text-regular-body tutor-color-text-subsued"><?php echo $data[0]; ?></span>
				</div>
				<div class="tutor-bs-col-12 tutor-bs-col-sm-7 tutor-bs-col-lg-9">
					<?php echo $data[0] == 'Biography' ? '<span class="tutor-text-regular-body tutor-color-text-subsued">'.$data[1].'</span>' : '<span class="tutor-text-medium-body tutor-color-text-primary ' . $first_name_class . ' ">' . $data[1] . '</span>'; ?>
				</div>
			</div>
		<?php
	}
	?>
</div>
