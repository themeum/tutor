<?php
/**
 * @package TutorLMS/Templates
 * @version 2.0
 */

$user = wp_get_current_user();
?>

<h3><?php _e('Settings', 'tutor') ?></h3>

<div class="tutor-dashboard-setting-social tutor-dashboard-content-inner">

    <div class="tutor-dashboard-inline-links">
        <?php
            tutor_load_template('dashboard.settings.nav-bar', ['active_setting_nav'=>'social-profile']);
        ?>
        
        <h3><?php _e('Social Profile Link', 'tutor'); ?></h3>
    </div>

    <form action="" method="post" enctype="multipart/form-data">
        <?php
        
            do_action('tutor_profile_edit_before_social_media', $user);

            $tutor_user_social_icons = tutor_utils()->tutor_user_social_icons();
            foreach ($tutor_user_social_icons as $key => $social_icon){
                ?>
                    <div class="tutor-row tutor-align-items-center tutor-mb-30 tutor-social-field">
                        <div class="tutor-col-12 tutor-col-sm-4 tutor-col-md-12 tutor-col-lg-3">
                            <i class="<?php echo $social_icon['icon_classes']; ?>"></i>
                            <?php echo esc_html($social_icon['label']); ?>
                        </div>
                        <div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-6">
                            <input class="tutor-form-control" type="text" name="<?php echo esc_attr($key); ?>" value="<?php echo get_user_meta($user->ID,$key,true); ?>" placeholder="<?php echo esc_html($social_icon['placeholder']); ?>">
                        </div>
                    </div>
                <?php
            }
        ?>

        <div class="tutor-row">
            <div class="tutor-col-12 tutor-col-sm-4 tutor-col-md-12 tutor-col-lg-3">
            </div>
            <div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-6">
                <button type="submit" class="tutor-btn">
                    <?php _e('Update Profile', 'tutor'); ?>
                </button>
            </div>
        </div>
    </form>
</div>