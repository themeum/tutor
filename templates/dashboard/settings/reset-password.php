<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<div class="tutor-dashboard-content-inner">
    <div class="tutor-dashboard-inline-links">
        <?php
            tutor_load_template('dashboard.settings.nav-bar', ['active_setting_nav'=>'reset_password']);
        ?>
    </div>

    <?php
    $success_msg = tutor_utils()->get_flash_msg('success');
    if ($success_msg){
        ?>
        <div class="tutor-success-msg">
            <?php echo $success_msg; ?>
        </div>
        <?php
    }
    ?>

    <form action="" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
        <input type="hidden" value="tutor_reset_password" name="tutor_action" />

        <?php
        $errors = apply_filters('tutor_reset_password_validation_errors', array());
        if (is_array($errors) && count($errors)){
            echo '<div class="tutor-alert-warning tutor-mb-10"><ul class="tutor-required-fields">';
            foreach ($errors as $error_key => $error_value){
                echo "<li>{$error_value}</li>";
            }
            echo '</ul></div>';
        }
        ?>

        <?php do_action('tutor_reset_password_input_before') ?>

        <div class="tutor-row">
            <div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-7 tutor-mb-30">
                <label> <?php _e('Current Password', 'tutor'); ?> </label>
                <input class="tutor-form-control" type="password" name="previous_password">
            </div>
        </div>
        
        <div class="tutor-row">
            <div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-7 tutor-mb-30">
                <label><?php _e('New Password', 'tutor'); ?></label>
                <input class="tutor-form-control" type="password" name="new_password">
            </div>
        </div>
        
        <div class="tutor-row">
            <div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-7 tutor-mb-30">
                <label><?php _e('Re-type New Password', 'tutor'); ?></label>
                <input class="tutor-form-control" type="password" name="confirm_new_password">
            </div>
        </div>
        
        <?php do_action('tutor_reset_password_input_after') ?>

        <div class="tutor-row">
            <div class="tutor-col-12">
                <button type="submit" class="tutor-btn">
                    <?php _e('Reset Password', 'tutor'); ?>
                </button>
            </div>
        </div>
    </form>
</div>
