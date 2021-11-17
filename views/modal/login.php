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
	        <?php do_action("tutor_before_login_form");?>

            <div class="tutor-modal-body">
                <h3 class="tutor-modal-title tutor-mb-30">Hi, Welcome back!</h3>
                <form action="#">
                    <div class="tutor-input-group tutor-form-control-has-icon-right tutor-mb-20">
                        <input type="text" class="tutor-form-control" placeholder="<?php echo esc_html( $args['label_username'] )?>" name="log" value="<?php echo esc_attr( $args['value_username'] )?>" size="20"/>
                    </div>
                    <div class="tutor-input-group tutor-form-control-has-icon-right tutor-mb-30">
                        <input type="password" class="tutor-form-control" placeholder="<?php echo esc_html( $args['label_password'] )?>" name="pwd" value="" size="20"/>
                    </div>
                    <?php 
                        do_action("tutor_login_form_middle");
                        do_action("login_form");
                        apply_filters("login_form_middle",'','');
                    ?>
                    <div class="row align-items-center tutor-mb-30">
                        <div class="col">
                        <div class="tutor-form-check">
                            <input id="login-agmnt-1" type="checkbox" class="tutor-form-check-input" name="rememberme" value="forever"/>
                            <label htmlFor="login-agmnt-1">Keep me signed in</label>
                        </div>
                        </div>
                        <div class="col-auto">
                            <a href="<?php echo esc_url($args['wp_lostpassword_url'])?>">
                                <?php echo esc_html($args['wp_lostpassword_label']);?>
                            </a>
                        </div>
                    </div>

		            <?php do_action("tutor_login_form_end");?>
			        <input type="hidden" name="redirect_to" value="<?php echo tutor()->current_url; ?>" />

                    <button type="submit" class="tutor-btn is-primary tutor-is-block">
                        Sign In
                    </button>
                    <?php if(get_option( 'users_can_register', false )): ?>
                        <div class="tutor-text-center tutor-mt-15">
                            Don’t have an account? <a href="#">Registration Now</a>
                        </div>
                    <?php endif; ?>
                </form>

                <?php do_action("tutor_after_login_form"); ?>
            </div>
        </div>
    </div>
</div>