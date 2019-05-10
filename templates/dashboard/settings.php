
<h3><?php _e('Settings', 'tutor') ?></h3>

<div class="tutor-dashboard-content-inner">

    <div class="tutor-dashboard-inline-links">
        <?php
            $settings_url = tutor_utils()->get_tutor_dashboard_page_permalink('settings');
            $education = tutor_utils()->get_tutor_dashboard_page_permalink('settings/education');
            $skill = tutor_utils()->get_tutor_dashboard_page_permalink('settings/skill');
            $withdraw = tutor_utils()->get_tutor_dashboard_page_permalink('settings/withdraw-settings');
            $reset_password = tutor_utils()->get_tutor_dashboard_page_permalink('settings/reset-password');
        ?>
        <ul>
            <li class="active">
                <a href="<?php echo esc_url($settings_url);  ?>"> <?php _e('Profile', 'tutor'); ?></a>
            </li>
            <li>
                <a href="<?php echo esc_url($reset_password);  ?>"> <?php _e('Reset Password', 'tutor'); ?></a>
            </li>
            <li>
                <a href="<?php echo esc_url($withdraw);  ?>"> <?php _e('Withdraw', 'tutor'); ?></a>
            </li>

        </ul>
    </div>

</div>

<?php
    tutor_load_template('dashboard.settings.profile');
?>