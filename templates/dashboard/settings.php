<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>
<h3><?php _e('Settings', 'tutor') ?></h3>

<div class="tutor-dashboard-content-inner">

    <div class="tutor-dashboard-inline-links">
        <?php
            $settings_url = tutor_utils()->get_tutor_dashboard_page_permalink('settings');
            $education = tutor_utils()->get_tutor_dashboard_page_permalink('settings/education');
            $skill = tutor_utils()->get_tutor_dashboard_page_permalink('settings/skill');
            $withdraw = tutor_utils()->get_tutor_dashboard_page_permalink('settings/withdraw-settings');
            $reset_password = tutor_utils()->get_tutor_dashboard_page_permalink('settings/reset-password');

            $setting_menus=array(
                'reset_password' => array(
                    'url' => esc_url($reset_password),
                    'title' => __('Reset Password', 'tutor'),
                    'role' => false
                ),
                'withdrawal' => array(
                    'url' => esc_url($withdraw),
                    'title' => __('Withdraw', 'tutor'),
                    'role' => 'instructor'
                )
            );

            $setting_menus = apply_filters('tutor_dashboard/nav_items/settings/nav_items', $setting_menus);
        ?>
        <ul>
            <li class="active">
                <a href="<?php echo esc_url($settings_url);  ?>"> <?php _e('Profile', 'tutor'); ?></a>
            </li>

            <?php
                foreach($setting_menus as $menu){
                    $valid = !$menu['role'] || ($menu['role']=='instructor' && current_user_can(tutor()->instructor_role));

                    if($valid){
                        ?>
                        <li>
                            <a href="<?php echo $menu['url'];  ?>"> <?php echo $menu['title']; ?></a>
                        </li>
                        <?php
                    }
                }
            ?>
        </ul>
    </div>

</div>

<?php
    tutor_load_template('dashboard.settings.profile');
?>