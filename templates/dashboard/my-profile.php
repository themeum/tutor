
<?php

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
?>

<h3>My Profile</h3>
<div class="tutor-dashboard-content-inner">
    <div class="tutor-dashboard-profile">
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span><?php esc_html_e('Registration Date', 'tutor'); ?></span>
            </div>
            <div class="content">
                <p><?php echo esc_html($rdate) ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span><?php esc_html_e('First Name', 'tutor'); ?></span>
            </div>
            <div class="content">
                <p><?php echo $fname ? esc_html($fname) : esc_html('________'); ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span><?php esc_html_e('Last Name', 'tutor'); ?></span>
            </div>
            <div class="content">
                <p><?php echo $lname ? esc_html_e($lname) : esc_html('________'); ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span><?php esc_html_e('Username', 'tutor'); ?></span>
            </div>
            <div class="content">
                <p><?php echo esc_html($uname); ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span><?php esc_html_e('Email', 'tutor'); ?></span>
            </div>
            <div class="content">
                <p><?php echo esc_html($email); ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>Phone Number</span>
            </div>
            <div class="content">
                <p><?php echo $phone ? esc_html($phone) : esc_html('________'); ?>&nbsp;</p>
            </div>
        </div>

        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span><?php esc_html_e('Bio', 'tutor'); ?></span>
            </div>
            <div class="content">
                <p><?php echo $bio ? $bio : '________'; ?>&nbsp;</p>
            </div>
        </div>



    </div>

</div>

