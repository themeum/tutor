<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */


$uid  = get_current_user_id();
$user = get_userdata( $uid );

$profile_settings_link = tutor_utils()->get_tutor_dashboard_page_permalink( 'settings' );
$rdate                 = date( 'D d M Y, h:i:s a', strtotime( $user->user_registered ) );
$fname                 = $user->first_name;
$lname                 = $user->last_name;
$uname                 = $user->user_login;
$email                 = $user->user_email;
$phone                 = get_user_meta( $uid, 'phone_number', true );
$bio                   = nl2br( strip_tags( get_user_meta( $uid, '_tutor_profile_bio', true ) ) );
?>

<h3><?php _e( 'My Profile', 'tutor' ); ?></h3>
<div class="tutor-dashboard-content-inner">
	<div class="tutor-dashboard-profile">
		<div class="tutor-dashboard-profile-item">
			<div class="heading">
				<span><?php _e( 'Registration Date', 'tutor' ); ?></span>
			</div>
			<div class="content">
				<p><?php echo esc_html( $rdate ); ?>&nbsp;</p>
			</div>
		</div>
		<div class="tutor-dashboard-profile-item">
			<div class="heading">
				<span><?php _e( 'First Name', 'tutor' ); ?></span>
			</div>
			<div class="content">
				<p><?php echo $fname ? _esc_html( $fname ) : esc_html( '________' ); ?>&nbsp;</p>
			</div>
		</div>
		<div class="tutor-dashboard-profile-item">
			<div class="heading">
				<span><?php _e( 'Last Name', 'tutor' ); ?></span>
			</div>
			<div class="content">
				<p><?php echo $lname ? _esc_html( $lname ) : __( '________' ); ?>&nbsp;</p>
			</div>
		</div>
		<div class="tutor-dashboard-profile-item">
			<div class="heading">
				<span><?php _e( 'Username', 'tutor' ); ?></span>
			</div>
			<div class="content">
				<p><?php echo _esc_html( $uname ); ?>&nbsp;</p>
			</div>
		</div>
		<div class="tutor-dashboard-profile-item">
			<div class="heading">
				<span><?php _e( 'Email', 'tutor' ); ?></span>
			</div>
			<div class="content">
				<p><?php echo esc_attr( $email ); ?>&nbsp;</p>
			</div>
		</div>
		<div class="tutor-dashboard-profile-item">
			<div class="heading">
				<span><?php _e( 'Phone Number', 'tutor' ); ?></span>
			</div>
			<div class="content">
				<p><?php echo $phone ? esc_attr( $phone ) : '________'; ?>&nbsp;</p>
			</div>
		</div>

		<div class="tutor-dashboard-profile-item">
			<div class="heading">
				<span><?php _e( 'Bio', 'tutor' ); ?></span>
			</div>
			<div class="content">
				<p><?php echo $bio ? _esc_html( $bio ) : '________'; ?>&nbsp;</p>
			</div>
		</div>


	</div>

</div>

