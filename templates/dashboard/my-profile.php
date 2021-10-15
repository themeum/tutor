<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */


$uid = get_current_user_id();
$user = get_userdata( $uid );

$profile_settings_link = tutor_utils()->get_tutor_dashboard_page_permalink('settings');
$rdate = date( "D d M Y, h:i:s a", strtotime( $user->user_registered ) );
$fname = $user->first_name;
$lname = $user->last_name;
$uname = $user->user_login;
$email = $user->user_email;
$phone = get_user_meta($uid,'phone_number',true);
$bio = nl2br(strip_tags(get_user_meta($uid,'_tutor_profile_bio',true)));

$profile_data = array(
    array(__('Registration Date', 'tutor'), esc_html($rdate)),
    array(__('First Name', 'tutor'), ($fname ? $fname : esc_html('________'))),
    array(__('Last Name', 'tutor'), ($lname ? $lname : __('________'))),
    array(__('Username', 'tutor'), $uname),
    array(__('Email', 'tutor'), $email),
    array(__('Phone Number', 'tutor'), ($phone ? $phone : "________")),
    array(__('Bio', 'tutor'), $bio ? $bio : '________')
)
?>

<h3><?php _e('My Profile', 'tutor'); ?></h3>
<div class="tutor-dashboard-content-inner tutor-dashboard-profile-data">
    <?php 
        foreach($profile_data as $data) {
            ?>
            <div class="tutor-row">
                <div class="tutor-col-12 tutor-col-sm-5 tutor-col-md-4">
                    <span><?php echo $data[0]; ?></span>
                </div>
                <div class="tutor-col-12 tutor-col-sm-7 tutor-col-md-8">
                    <p><?php echo $data[1]; ?></p>
                </div>
            </div>
            <?php
        }
    ?>
</div>