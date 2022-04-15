<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */


$uid  = get_current_user_id();
$user = get_userdata( $uid );

$profile_settings_link = tutor_utils()->get_tutor_dashboard_page_permalink( 'settings' );

$rdate = tutor_utils()->convert_date_into_wp_timezone( date( 'D d M Y, h:i:s a', strtotime( $user->user_registered ) ) );
$fname = $user->first_name;
$lname = $user->last_name;
$uname = $user->user_login;
$email = $user->user_email;
$phone = get_user_meta( $uid, 'phone_number', true );
$job   = nl2br( strip_tags( get_user_meta( $uid, '_tutor_profile_job_title', true ) ) );
$bio   = nl2br( strip_tags( get_user_meta( $uid, '_tutor_profile_bio', true ) ) );

$profile_data = array(
	array( __( 'Registration Date', 'tutor' ), ( $rdate ? date_i18n( 'D d M Y, h:i:s a', $rdate ) : esc_html( '-' ) ) ),
	array( __( 'First Name', 'tutor' ), ( $fname ? $fname : esc_html( '-' ) ) ),
	array( __( 'Last Name', 'tutor' ), ( $lname ? $lname : __( '-' ) ) ),
	array( __( 'Username', 'tutor' ), $uname ),
	array( __( 'Email', 'tutor' ), $email ),
	array( __( 'Phone Number', 'tutor' ), ( $phone ? $phone : '-' ) ),
	array( __( 'Skill/Occupation', 'tutor' ), ( $job ? $job : '-' ) ),
	array( __( 'Biography', 'tutor' ), $bio ? $bio : '-' ),
);
?>

<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24"><?php _e( 'My Profile', 'tutor' ); ?></div>
<div class="tutor-dashboard-content-inner tutor-dashboard-profile-data">
	<?php foreach ( $profile_data as $key => $data ) : ?>
		<div class="tutor-row tutor-mb-24">
			<div class="tutor-col-12 tutor-col-sm-5 tutor-col-lg-3">
				<span class="tutor-fs-6 tutor-color-secondary"><?php echo $data[0]; ?></span>
			</div>
			<div class="tutor-col-12 tutor-col-sm-7 tutor-col-lg-9">
				<?php echo $data[0] == 'Biography' ? '<span class="tutor-fs-6 tutor-color-secondary">' . $data[1] . '</span>' : '<span class="tutor-fs-6 tutor-fw-medium tutor-color-black">' . $data[1] . '</span>'; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
