<form method="post" enctype="multipart/form-data">
	<?php wp_nonce_field( dozent()->nonce_action, dozent()->nonce ); ?>
    <input type="hidden" value="dozent_register_teacher" name="dozent_action"/>

    <?php
    $errors = apply_filters('dozent_teacher_register_validation_errors', array());
    if (is_array($errors) && count($errors)){
        echo '<div class="dozent-alert-warning"><ul class="dozent-required-fields">';
        foreach ($errors as $error_key => $error_value){
            echo "<li>{$error_value}</li>";
        }
        echo '</ul></div>';
    }
    ?>

    <div class="dozent-form-row">
        <div class="dozent-form-col-4">
            <div class="dozent-form-group">
                <label>
					<?php _e('First Name', 'dozent'); ?>
                </label>

                <input type="text" name="first_name" value="<?php echo dozent_utils()->input_old('first_name'); ?>" placeholder="<?php _e('First Name', 'dozent'); ?>">
            </div>
        </div>

        <div class="dozent-form-col-4">
            <div class="dozent-form-group">
                <label>
					<?php _e('Last Name', 'dozent'); ?>
                </label>

                <input type="text" name="last_name" value="<?php echo dozent_utils()->input_old('last_name'); ?>" placeholder="<?php _e('Last Name', 'dozent'); ?>">
            </div>
        </div>

        <div class="dozent-form-col-4">
            <div class="dozent-form-group">
                <label>
                    <?php _e('User Name', 'dozent'); ?>
                </label>

                <input type="text" name="user_login" class="dozent_user_name" value="<?php echo dozent_utils()->input_old('user_login'); ?>" placeholder="<?php _e('User Name', 'dozent'); ?>">
            </div>
        </div>
    </div>

    <div class="dozent-form-row">
        <div class="dozent-form-col-6">
            <div class="dozent-form-group">
                <label>
					<?php _e('E-Mail', 'dozent'); ?>
                </label>

                <input type="text" name="email" value="<?php echo dozent_utils()->input_old('email'); ?>" placeholder="<?php _e('E-Mail', 'dozent'); ?>">
            </div>
        </div>


        <div class="dozent-form-col-6">
            <div class="dozent-form-group">
                <label>
				    <?php _e('Phone Number', 'dozent'); ?>
                </label>

                <input type="text" name="phone_number" value="<?php echo dozent_utils()->input_old('phone_number'); ?>" placeholder="<?php _e('Phone Number', 'dozent'); ?>">
            </div>
        </div>

    </div>

    <div class="dozent-form-row">
        <div class="dozent-form-col-6">
            <div class="dozent-form-group">
                <label>
					<?php _e('Password', 'dozent'); ?>
                </label>

                <input type="password" name="password" value="<?php echo dozent_utils()->input_old('password'); ?>" placeholder="<?php _e('Password', 'dozent'); ?>">
            </div>
        </div>

        <div class="dozent-form-col-6">
            <div class="dozent-form-group">
                <label>
					<?php _e('Password confirmation', 'dozent'); ?>
                </label>

                <input type="password" name="password_confirmation" value="<?php echo dozent_utils()->input_old('password_confirmation'); ?>" placeholder="<?php _e('Password Confirmation', 'dozent'); ?>">
            </div>
        </div>
    </div>

    <div class="dozent-form-row">
        <div class="dozent-form-col-12">
            <div class="dozent-form-group">
                <label>
					<?php _e('Bio', 'dozent'); ?>
                </label>

                <textarea name="dozent_profile_bio"><?php echo dozent_utils()->input_old('dozent_profile_bio'); ?></textarea>
            </div>
        </div>
    </div>

    <div class="dozent-form-row">
        <div class="dozent-form-col-12">
            <div class="dozent-form-group dozent-reg-form-btn-wrap">
                <button type="submit" name="dozent_register_teacher_btn" value="register" class="dozent-button"><?php _e('Become a teacher', 'dozent'); ?></button>
            </div>
        </div>
    </div>

</form>