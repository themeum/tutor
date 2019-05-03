
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
    $bio = strip_tags(get_user_meta($uid,'_tutor_profile_bio',true));


/*
    if(empty($fname)){
        $fname = sprintf(__('%s First name is empty, %s click here to set %s %s', 'tutor'), "<small>", "<a href='$profile_settings_link'>", "</a>", "</small>");
    }

    if(empty($lname)){
        $lname = sprintf(__('%s Last name is empty, %s click here to set %s %s', 'tutor'), "<small>", "<a href='$profile_settings_link'>", "</a>", "</small>");
    }

    if(empty($phone)){
        $phone = sprintf(__('%s Phone number is empty, %s click here to set %s %s', 'tutor'), "<small>", "<a href='$profile_settings_link'>", "</a>", "</small>");
    }

    if(empty($bio)){
        $bio = sprintf(__('%s Bio data is empty, %s click here to set %s %s', 'tutor'), "<small>", "<a href='$profile_settings_link'>", "</a>", "</small>");
    }*/




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
                <p><?php echo esc_html($rdate) ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>First Name</span>
            </div>
            <div class="content">
                <p><?php echo $fname ? $fname : '________'; ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>Last Name</span>
            </div>
            <div class="content">
                <p><?php echo $lname ? $lname : '________'; ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>Username</span>
            </div>
            <div class="content">
                <p><?php echo $uname; ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>Email</span>
            </div>
            <div class="content">
                <p><?php echo $email; ?>&nbsp;</p>
            </div>
        </div>
        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>Phone Number</span>
            </div>
            <div class="content">
                <p><?php echo $phone ? $phone : '________'; ?>&nbsp;</p>
            </div>
        </div>

        <div class="tutor-dashboard-profile-item">
            <div class="heading">
                <span>Bio</span>
            </div>
            <div class="content">
                <p><?php echo $bio ? $bio : '________'; ?>&nbsp;</p>
            </div>
        </div>
    </div>

</div>

