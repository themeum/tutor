<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */


if (!defined('ABSPATH'))
    exit;

global $post;

get_tutor_header(true);
do_action('tutor_load_template_before', 'dashboard.create-course', null);

$course_id = get_the_ID();
$can_publish_course = (bool) tutor_utils()->get_option('instructor_can_publish_course') || current_user_can('administrator');
?>

<?php
if (!tutor_utils()->can_user_edit_course(get_current_user_id(), $course_id)) {
    $args = array(
        'headline' => __( 'Permission Denied', 'tutor' ),
        'message' =>  __( 'You don\'t have the right to edit this course', 'tutor' ),
        'description' => __('Please make sure you are logged in to correct account', 'tutor'),
        'button' => array(
            'url' => get_permalink( $course_id ),
            'text' =>  __( 'View Course', 'tutor' )
        )
    );

    tutor_load_template('permission-denied', $args);
    return;
}
?>

<?php do_action('tutor/dashboard_course_builder_before'); ?>
<form action="" id="tutor-frontend-course-builder" method="post" enctype="multipart/form-data">
    <?php wp_nonce_field(tutor()->nonce_action, tutor()->nonce); ?>

    <!-- Sticky header with course action buttons -->
    <header class="tutor-dashboard-builder-header tutor-mb-30">
        <div class="tutor-bs-container-fluid">
            <div class="tutor-bs-row tutor-bs-align-items-center">
                <div class="tutor-bs-col-auto">
                    <div class="tutor-dashboard-builder-header-left">
                        <div class="tutor-dashboard-builder-logo">
                            <?php $tutor_course_builder_logo_src = apply_filters('tutor_course_builder_logo_src', tutor()->url . 'assets/images/tutor-logo.png'); ?>
                            <img src="<?php echo esc_url($tutor_course_builder_logo_src); ?>" alt="">
                        </div>
                        <button type="submit" class="tutor-dashboard-builder-draft-btn" name="course_submit_btn" value="save_course_as_draft">
                            <!-- @TODO: Icon must be chenged -->
                            <i class="tutor-icon-save"></i>
                            <span><?php _e('Save', 'tutor'); ?></span>
                        </button>
                    </div>
                </div>
                <div class="tutor-bs-col tutor-mt-10 tutor-mb-10">
                    <div class="tutor-dashboard-builder-header-right tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-end">
                        <a class="tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-sm" href="<?php the_permalink($course_id); ?>" target="_blank">
                            <?php _e('Preview', 'tutor'); ?>
                        </a>
                        <?php
                        if ($can_publish_course) {
                            ?>
                                <button class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-ml-15" type="submit" name="course_submit_btn" value="publish_course">
                                    <?php _e('Publish', 'tutor'); ?>
                                </button>
                            <?php
                        } else {
                            ?>
                                <button class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-ml-15" type="submit" name="course_submit_btn" value="submit_for_review" title="<?php _e('Submit for Review', 'tutor'); ?>">
                                    <?php _e('Submit', 'tutor'); ?>
                                </button>
                            <?php
                        }
                        ?>
                        <a href="<?php echo tutor_utils()->tutor_dashboard_url(); ?>" class="ttr-line-cross-line tutor-font-size-24 tutor-ml-15" title="<?php _e('Exit', 'tutor'); ?>"></a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Course builder body -->
    <div class="tutor-bs-container">
        <div class="tutor-bs-row">
            <div class="tutor-bs-col-12 tutor-bs-col-lg-8 tutor-mb-30">
                <input type="hidden" value="tutor_add_course_builder" name="tutor_action" />
                <input type="hidden" name="course_ID" id="course_ID" value="<?php echo get_the_ID(); ?>">
                <input type="hidden" name="post_ID" id="post_ID" value="<?php echo get_the_ID(); ?>">

                <!--since 1.8.0 alert message -->
                <?php
                    $user_id = get_current_user_id();
                    $expires = get_user_meta( $user_id, 'tutor_frontend_course_message_expires', true );
                    $message = get_user_meta( $user_id, 'tutor_frontend_course_action_message', true );

                    if($message && $expires && $expires>time()) {
                        $show_modal = $message['show_modal'];
                        $message = $message['message'];

                        if(!$show_modal) {
                            ?>
                            <div class="tutor-alert tutor-alert-info">
                                <?php echo $message; ?>
                            </div>
                            <?php
                        }
                        else {
                            ?>
                            <div id="modal-course-save-feedback" class="tutor-modal tutor-is-active">
                                <span class="tutor-modal-overlay"></span>
                                <button data-tutor-modal-close class="tutor-modal-close">
                                    <span class="las la-times"></span>
                                </button>
                                <div class="tutor-modal-root">
                                    <div class="tutor-modal-inner">
                                        <div class="tutor-modal-body tutor-text-center">
                                            <div class="tutor-modal-icon">
                                                <img src="<?php echo tutor()->url; ?>/assets/images/icon-cup.svg" alt="" />
                                            </div>
                                            <div class="tutor-modal-text-wrap">
                                                <h3 class="tutor-modal-title"><?php _e('Thank You!', 'tutor'); ?></h3>
                                                <p><?php echo $message; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }

                    if($message || $expires) {
                        delete_user_meta( $user_id, 'tutor_frontend_course_message_expires' );
                        delete_user_meta( $user_id, 'tutor_frontend_course_action_message' );
                    }
                ?>
                <!--alert message end -->
                <?php do_action('tutor/dashboard_course_builder_form_field_before'); ?>

                <div class="tutor-course-builder-section tutor-course-builder-info">
                    <div class="tutor-course-builder-section-title">
                        <h3>
                            <i class="ttr-angle-down-filled"></i>
                            <span><?php esc_html_e('Course Info', 'tutor'); ?></span>
                        </h3>
                    </div>
                    <!--.tutor-course-builder-section-title-->
                    <div class="tutor-course-builder-section-content">
                        <div class="tutor-mb-30">
                            <label class="tutor-course-field-label"><?php _e('Course Title', 'tutor'); ?></label>
                            <div class="tooltip-wrap tutor-bs-d-block">
                                <span class="tooltip-txt tooltip-right">
                                    <?php _e('60', 'tutor'); ?>
                                </span>
                                <input type="text" name="title" class="tutor-form-control" value="<?php echo get_the_title(); ?>" placeholder="<?php _e('ex. Learn photoshop CS6 from scratch', 'tutor'); ?>">
                            </div>
                        </div>
                        
                        <div class="tutor-mb-30">
                            <label class="tutor-course-field-label"><?php _e('Description', 'tutor'); ?></label>
                            <div class="tutor-input-group tutor-mb-15">
                                <?php
                                    $editor_settings = array(
                                        'media_buttons' => false,
                                        'quicktags'     => false,
                                        'editor_height' => 150,
                                        'textarea_name' => 'content',
                                        'statusbar'     => false
                                    );
                                    wp_editor($post->post_content, 'course_description', $editor_settings);
                                ?>
                            </div>
                        </div>
                        
                        <?php do_action('tutor/frontend_course_edit/after/description', $post) ?>

                        <div class="tutor-frontend-builder-item-scope">
                            <div class="tutor-form-group">
                                <label>
                                    <?php _e('Choose a category', 'tutor'); ?>
                                </label>
                                <div class="tutor-form-field-course-categories">
                                    <?php //echo tutor_course_categories_checkbox($course_id);
                                    echo tutor_course_categories_dropdown($course_id, array('classes' => 'tutor_select2'));
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php do_action('tutor/frontend_course_edit/after/category', $post) ?>

                        <?php
                            $monetize_by = tutils()->get_option('monetize_by');
                            if ($monetize_by === 'wc' || $monetize_by === 'edd') {
                                $course_price = tutor_utils()->get_raw_course_price(get_the_ID());
                                $currency_symbol = tutor_utils()->currency_symbol();

                                $_tutor_course_price_type = tutils()->price_type();
                                ?>
                                <div class="tutor-bs-row tutor-bs-align-items-center">
                                    <div class="tutor-bs-col-6 tutor-bs-col-sm-5 tutor-bs-col-lg-4">
                                        <div class="tutor-form-check tutor-mb-15">
                                            <input type="radio" id="tutor_price_paid" class="tutor-form-check-input" name="tutor_course_price_type"  value="paid" <?php checked($_tutor_course_price_type, 'paid'); ?>/>
                                            <label for="tutor_price_paid" class="tutor-amount-field">
                                                <span class="tutor-input-prepand">
                                                    <?php echo $currency_symbol; ?>
                                                </span>
                                                <input type="number" name="course_price" value="<?php echo $course_price->regular_price; ?>" placeholder="<?php _e('Set course price', 'tutor'); ?>">
                                            </label>
                                        </div>
                                    </div>
                                    <div class="tutor-bs-col-6 tutor-bs-col-sm-5 tutor-bs-col-lg-4">
                                        <div class="tutor-form-check tutor-mb-15">
                                            <input type="radio" id="tutor_price_free" class="tutor-form-check-input" name="tutor_course_price_type" value="free" <?php $_tutor_course_price_type ? checked($_tutor_course_price_type, 'free') : checked('true', 'true'); ?>/>
                                            <label for="tutor_price_free">
                                                <?php _e('Free', "tutor") ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                            } 
                        ?>

                        <div class="tutor-mb-30">
                            <label class="tutor-course-field-label"><?php _e('Course Thumbnail', 'tutor'); ?></label>
                            <div class="tutor-input-group tutor-mb-15">
                                <?php 
                                    tutor_load_template_from_custom_path(tutor()->path.'/views/fragments/thumbnail-uploader.php', array(
                                        'media_id' => get_post_thumbnail_id($course_id),
                                        'input_name' => 'tutor_course_thumbnail_id'
                                    ), false);
                                ?>
                            </div>
                        </div>
                        <?php do_action('tutor/frontend_course_edit/after/thumbnail', $post); ?>
                    </div>
                </div>
                
                <?php do_action('tutor/dashboard_course_builder_form_field_after', $post); ?>

                <div class="tutor-form-row">
                    <div class="tutor-form-col-12">
                        <div class="tutor-form-group">
                            <div class="tutor-form-field tutor-course-builder-btn-group">
                                <button type="submit" class="tutor-btn" name="course_submit_btn" value="save_course_as_draft"><?php _e('Save course as draft', 'tutor'); ?></button>
                                <?php if ($can_publish_course) { ?>
                                    <button class="tutor-btn" type="submit" name="course_submit_btn" value="publish_course"><?php _e('Publish Course', 'tutor'); ?></button>
                                <?php } else { ?>
                                    <button class="tutor-btn" type="submit" name="course_submit_btn" value="submit_for_review"><?php _e('Submit for Review', 'tutor'); ?></button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Course builder tips right sidebar -->
            <div class="tutor-bs-col-12 tutor-bs-col-lg-4 tutor-mb-30">
                <div class="tutor-course-builder-upload-tips">
                    <h3 class="text-regular-body tutor-mb-20">
                        <strong>
                            <?php _e('Course Upload Tips', 'tutor') ?>
                        </strong>
                    </h3>
                    <ul>
                        <li class="tutor-mb-20"><?php _e("Set the Course Price option or make it free.", 'tutor'); ?></li>
                        <li class="tutor-mb-20"><?php _e("Standard size for the course thumbnail is 700x430.", 'tutor'); ?></li>
                        <li class="tutor-mb-20"><?php _e("Video section controls the course overview video.", 'tutor'); ?></li>
                        <li class="tutor-mb-20"><?php _e("Course Builder is where you create & organize a course.", 'tutor'); ?></li>
                        <li class="tutor-mb-20"><?php _e("Add Topics in the Course Builder section to create lessons, quizzes, and assignments.", 'tutor'); ?></li>
                        <li class="tutor-mb-20"><?php _e("Prerequisites refers to the fundamental courses to complete before taking this particular course.", 'tutor'); ?></li>
                        <li class="tutor-mb-20"><?php _e("Information from the Additional Data section shows up on the course single page.", 'tutor'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
<?php do_action('tutor/dashboard_course_builder_after'); ?>


<?php
do_action('tutor_load_template_after', 'dashboard.create-course', null);
get_tutor_footer(true); ?>