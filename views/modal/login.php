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
                <div class="tutor-fs-5 tutor-color-black tutor-mb-32">
                    <?php _e('Hi, Welcome back!', 'tutor'); ?>
                </div>
                <?php
                    // load form template.
                    $login_form = trailingslashit( tutor()->path ) . 'templates/login-form.php';
                    tutor_load_template_from_custom_path(
                        $login_form,
                        false
                    );
                ?>
                <?php do_action("tutor_after_login_form"); ?>
            </div>
        </div>
    </div>
</div>