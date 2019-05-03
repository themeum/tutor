
<?php

    $uid = get_current_user_id();
    $user = get_userdata( $uid );

    // @TODO: translate static text & check empty value

?>

<h3>My Profile</h3>
<div class="tutor-dashboard-content-inner">
    <div class="tutor-dashboard-profile">
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>Registration Date</span>
            </div>
            <div class="content">
                <p><?php echo esc_html(date( "D d M Y, h:i:s a", strtotime( $user->user_registered ) )) ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>First Name</span>
            </div>
            <div class="content">
                <p><?php echo esc_html($user->first_name); ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>Last Name</span>
            </div>
            <div class="content">
                <p><?php echo esc_html($user->last_name); ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>Username</span>
            </div>
            <div class="content">
                <p><?php echo esc_html($user->user_login); ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>Email</span>
            </div>
            <div class="content">
                <p><?php echo esc_html($user->user_email); ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>Phone Number</span>
            </div>
            <div class="content">
                <p><?php echo esc_html(get_user_meta($uid,'phone_number',true)); ?>&nbsp;</p>
            </div>
        </div>

        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>Bio</span>
            </div>
            <div class="content">
                <p><?php echo esc_html(strip_tags(get_user_meta($uid,'_tutor_profile_bio',true))); ?>&nbsp;</p>
            </div>
        </div>
    </div>

</div>

