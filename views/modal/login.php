<?php 
    $lost_pass = apply_filters('tutor_lostpassword_url', wp_lostpassword_url());
?>
<div class="tutor-login-modal tutor-modal tutor-is-sm">
    <span class="tutor-modal-overlay"></span>
    <button data-tutor-modal-close class="tutor-modal-close">
        <span class="las la-times"></span>
    </button>
    <div class="tutor-modal-root">
        <div class="tutor-modal-inner">
	        <?php do_action("tutor_before_login_form"); ?>

            <div class="tutor-modal-body">
                <h3 class="tutor-modal-title tutor-mb-30">
                    <?php _e('Hi, Welcome back!', 'tutor'); ?>
                </h3>
                <form>
                    <input type="hidden" name="tutor_course_enroll_attempt" value="<?php echo get_the_ID();?>">
                    <input type="hidden" name="tutor_action" value="tutor_user_login" />
                    <input type="hidden" name="redirect_to" value="<?php echo tutor()->current_url; ?>" />

                    <div class="tutor-input-group tutor-form-control-has-icon-right tutor-mb-20">
                        <input type="text" class="tutor-form-control" placeholder="<?php _e('Username or Email Address', 'tutor'); ?>" name="log" value="" size="20"/>
                    </div>
                    <div class="tutor-input-group tutor-form-control-has-icon-right tutor-mb-30">
                        <input type="password" class="tutor-form-control" placeholder="<?php _e('Password', 'tutor'); ?>" name="pwd" value="" size="20"/>
                    </div>
                    <div class="tutor-login-error">

                    </div>
                    <?php 
                        do_action("tutor_login_form_middle");
                        do_action("login_form");
                        apply_filters("login_form_middle",'','');
                    ?>
                    <div class="tutor-bs-row align-items-center tutor-mb-30">
                        <div class="tutor-bs-col">
                        <div class="tutor-form-check">
                            <input id="tutor-login-agmnt-1" type="checkbox" class="tutor-form-check-input" name="rememberme" value="forever"/>
                            <label for="tutor-login-agmnt-1">
                                <?php _e('Keep me signed in', 'tutor'); ?>
                            </label>
                        </div>
                        </div>
                        <div class="tutor-bs-col-auto">
                            <a href="<?php echo $lost_pass; ?>">
                                <?php _e('Forgot Password?', 'tutor'); ?>
                            </a>
                        </div>
                    </div>

		            <?php do_action("tutor_login_form_end");?>
                    <button type="submit" class="tutor-btn is-primary tutor-is-block">
                        <?php _e('Sign In', 'tutor'); ?>
                    </button>
                    <?php if(get_option( 'users_can_register', false )): ?>
                        <div class="tutor-text-center tutor-mt-15">
                            <?php _e('Don\'t have an account?', 'tutor'); ?>&nbsp;
                            <a href="<?php echo add_query_arg ( array('redirect_to'=>tutor()->current_url), tutor_utils()->student_register_url() ); ?>">
                                <?php _e('Registration Now', 'tutor'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </form>

                <?php do_action("tutor_after_login_form"); ?>
            </div>
        </div>
    </div>
</div>