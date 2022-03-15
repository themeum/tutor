<?php
$lost_pass = apply_filters('tutor_lostpassword_url', wp_lostpassword_url());
?>
<div class="tutor-login-modal tutor-modal tutor-is-sm tutor-modal-is-close-inside-inner">
    <span class="tutor-modal-overlay"></span>
    <div class="tutor-modal-root">
        <div class="tutor-modal-inner">
            <button data-tutor-modal-close class="tutor-modal-close">
                <span class="tutor-icon-line-cross-line tutor-icon-40"></span>
            </button>
            <?php do_action("tutor_before_login_form"); ?>

            <div class="tutor-modal-body">
                <div class="tutor-fs-5 tutor-fw-normal tutor-color-black tutor-mb-32">
                    <?php _e('Hi, Welcome back!', 'tutor'); ?>
                </div>
                <form>
                    <?php if(is_single_course()): ?>
                        <input type="hidden" name="tutor_course_enroll_attempt" value="<?php echo get_the_ID(); ?>">
                    <?php endif; ?>
                    
                    <input type="hidden" name="tutor_action" value="tutor_user_login" />
                    <input type="hidden" name="redirect_to" value="<?php echo tutor()->current_url ?>" />

                    <div class="tutor-input-group tutor-form-control-has-icon-right tutor-mb-20">
                        <input type="text" class="tutor-form-control" placeholder="<?php _e('Username or Email Address', 'tutor'); ?>" name="log" value="" size="20" />
                    </div>
                    <div class="tutor-input-group tutor-form-control-has-icon-right tutor-mb-32">
                        <input type="password" class="tutor-form-control" placeholder="<?php _e('Password', 'tutor'); ?>" name="pwd" value="" size="20" />
                    </div>
                    <div class="tutor-login-error">

                    </div>
                    <?php
                        do_action("tutor_login_form_middle");
                        do_action("login_form");
                        apply_filters("login_form_middle", '', '');
                    ?>
                    <div class="tutor-d-flex tutor-justify-content-between tutor-align-items-center tutor-mb-40">
                        <div class="tutor-form-check">
                            <input id="tutor-login-agmnt-1" type="checkbox" class="tutor-form-check-input tutor-bg-black-40" name="rememberme" value="forever" />
                            <label for="tutor-login-agmnt-1" class="tutor-fs-7 tutor-fw-normal tutor-color-muted">
                                <?php _e('Keep me signed in', 'tutor'); ?>
                            </label>
                        </div>
                        <a href="<?php echo $lost_pass; ?>" class="tutor-fs-6 tutor-fw-medium tutor-color-black-60 td-none">
                            <?php _e('Forgot?', 'tutor'); ?>
                        </a>
                    </div>

                    <?php do_action("tutor_login_form_end"); ?>
                    <button type="submit" class="tutor-btn is-primary tutor-is-block">
                        <?php _e('Sign In', 'tutor'); ?>
                    </button>
                    <?php if (get_option('users_can_register', false)) : ?>
                        <?php 
                            $url_arg = array(
                                'redirect_to' => tutor()->current_url,
                            );

                            if(is_single_course()) {
                                $url_arg['enrol_course_id'] = get_the_ID();
                            }
                        ?>
                        <div class="tutor-text-center tutor-fs-6 tutor-fw-normal tutor-color-black-60 tutor-mt-20">
                            <?php _e('Don\'t have an account?', 'tutor'); ?>&nbsp;
                            <a href="<?php echo add_query_arg($url_arg, tutor_utils()->student_register_url()); ?>" class="tutor-fweight-500 td-none tutor-color-design-brand">
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