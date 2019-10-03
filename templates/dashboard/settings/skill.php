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
        ?>
        <ul>
            <li>
                <a href="<?php echo esc_url($settings_url);  ?>"> <?php _e('Profile', 'tutor'); ?></a>
            </li>
            <!--<li>
                <a href="<?php /*echo esc_url($education);  */?>"> <?php /*_e('Education'); */?></a>
            </li>
            <li class="active">
                <a href="<?php /*echo esc_url($skill);  */?>"> <?php /*_e('Expertise & Skill'); */?></a>
            </li>-->
            <?php if(current_user_can(tutor()->instructor_role)){ ?>
                <li>
                    <a href="<?php echo esc_url($withdraw);  ?>"> <?php _e('Withdraw', 'tutor'); ?></a>
                </li>
            <?php } ?>
        </ul>
    </div>

</div>