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

        <div class="tutor-bs-row">
            <div class="tutor-bs-col-12 tutor-bs-col-sm-8 tutor-bs-col-md-12 tutor-bs-col-lg-7 tutor-mb-30">
                <label> <?php _e('Current Password', 'tutor'); ?> </label>
                <input class="tutor-form-control" type="password" name="previous_password">
            </div>
        </div>
        
        <div class="tutor-bs-row">
            <div class="tutor-bs-col-12 tutor-bs-col-sm-8 tutor-bs-col-md-12 tutor-bs-col-lg-7 tutor-mb-30">
                <div class="tutor-password-strength-checker">
                    <div class="tutor-password-field">
                        <label class="field-label tutor-form-label" for="new-password-1">
                            <?php _e('New Password', 'tutor'); ?>
                        </label>
                        <div class="field-group">
                            <input
                                class="password-checker tutor-form-control"
                                id="new-password-1"
                                type="password"
                                name="new_password"
                                placeholder="Type Password"
                            />
                            <span class="show-hide-btn"></span>
                        </div>
                    </div>
                    <div class="tutor-passowrd-strength-hint">
                        <div class="indicator">
                            <span class="weak"></span>
                            <span class="medium"></span>
                            <span class="strong"></span>
                        </div>
                        <div class="text text-regular-caption color-text-hints"></div>
                    </div>
                </div>
            </div>
        </div>


        <div class="tutor-bs-row">
            <div class="tutor-bs-col-12 tutor-bs-col-sm-8 tutor-bs-col-md-12 tutor-bs-col-lg-7 tutor-mb-30">
                <div class="tutor-password-field tutor-settings-pass-field">
                    <label class="tutor-form-label" for="password-field-icon-1">
                        <?php _e('Re-type New Password', 'tutor'); ?>
                    </label>
                    <div class="field-group tutor-input-group">
                        <input
                        class="tutor-form-control"
                        id="cpassword-field-icon-1"
                        type="password"
                        placeholder="Type Password"
                        name="confirm_new_password"
                        />
                        <span class="validation-icon valid-btn ttr-mark-filled" style="display:none"></span>
                    </div>
                </div>
            </div>
        </div>
        
        <?php do_action('tutor_reset_password_input_after') ?>

        <div class="tutor-bs-row">
            <div class="tutor-bs-col-12">
                <button type="submit" class="tutor-btn">
                    <?php _e('Reset Password', 'tutor'); ?>
                </button>
            </div>
        </div>
    </form>
</div>
