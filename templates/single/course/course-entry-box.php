<?php 
    $is_enrolled           = tutor_utils()->is_enrolled();
    $lesson_url            = tutor_utils()->get_course_first_lesson();
    $is_administrator      = tutor_utils()->has_user_role('administrator');
    $is_instructor         = tutor_utils()->is_instructor_of_this_course();
    $course_content_access = (bool) get_tutor_option('course_content_access_for_ia');
    $is_privileged_user    = $course_content_access && ($is_administrator || $is_instructor);

    $sidebar_meta = apply_filters( 'tutor/course/single/sidebar/metadata', array(
        array(
            'icon_class' => 'ttr-level-line', 
            'label' => __('Level', 'tutor'), 
            'value' => get_tutor_course_level(get_the_ID())
        ),
        array(
            'icon_class' => 'ttr-student-line-1', 
            'label' => __('Total Enrolled', 'tutor'), 
            'value' => !get_tutor_option('disable_course_total_enrolled') ? tutor_utils()->count_enrolled_users_by_course() : null
        ),
        array(
            'icon_class' => 'ttr-clock-filled', 
            'label' => __('Duration', 'tutor'), 
            'value' => !get_tutor_option('disable_course_duration') ? get_tutor_course_duration_context() : null
        ),
        array(
            'icon_class' => 'ttr-student-line-1', 
            'label' => __('Last Updated', 'tutor'), 
            'value' => !get_tutor_option('disable_course_update_date') ? get_tutor_course_duration_context() : null
        ),
    ), get_the_ID());
?>

<div class="tutor-course-sidebar-card tutor-before-enroll">
    <!-- Course Entry -->
    <div class="tutor-course-sidebar-card-body tutor-p-30">
        <?php 
            if($is_enrolled) {
                // The user is enrolled anyway. No matter manual, free, purchased, woocommerce, edd, membership
                do_action('tutor_course/single/actions_btn_group/before');
                
                // Course Info
                $completed_lessons  = tutor_utils()->get_completed_lesson_count_by_course();
                $completed_percent  = tutor_utils()->get_course_completed_percent();
                $is_completed_course= tutor_utils()->is_completed_course();
                $retake_course      = tutor_utils()->get_option('course_retake_feature', false) && ($is_completed_course || $completed_percent >= 100);

                // Show Start/Continue/Retake Button
                if ( $lesson_url ) { 
                    $button_class = 'tutor-mt-5 tutor-mb-5 tutor-is-fullwidth tutor-btn '.($retake_course ? 'tutor-btn-tertiary tutor-is-outline tutor-btn-lg tutor-btn-full' : '').' tutor-is-fullwidth tutor-pr-0 tutor-pl-0 ' . ($retake_course ? ' tutor-course-retake-button' : '');
                    ?>
                    <a href="<?php echo $lesson_url; ?>" class="<?php echo $button_class; ?>" data-course_id="<?php echo get_the_ID(); ?>">
                        <?php
                            if(is_single_course() && $retake_course) {
                                _e( 'Retake This Course', 'tutor' );
                            } else if( $completed_percent <= 0 ){
                                _e( 'Start Learning', 'tutor' );
                            } else {
                                _e( 'Continue Learning', 'tutor' );
                            }
                        ?>
                    </a>
                    <?php 
                } else {
                    // Show Only enrolled message if there is no content to start from
                    ?>
                    <div class="text-regular-caption color-text-hints tutor-mt-12 tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-center">
                        <span class="tutor-icon-26 color-success ttr-purchase-filled tutor-mr-6 "></span> &nbsp;
                        <?php echo sprintf(__('You enrolled this course on %s', 'tutor'), '<span class="text-bold-small color-success tutor-ml-3">'.date_format($is_enrolled->post_date, get_option( 'date_format' )).'</span>'); ?>
                    </div>
                    <?php
                }

                // Show Course Completion Button
                if ( ! $is_completed_course) {
                    ?>
                    <div class="tutor-course-complete-form-wrap">
                        <form method="post">
                            <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

                            <input type="hidden" value="<?php echo get_the_ID(); ?>" name="course_id"/>
                            <input type="hidden" value="tutor_complete_course" name="tutor_action"/>

                            <button type="submit" class="tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-lg tutor-btn-full" name="complete_course_btn" value="complete_course">
                                <?php _e( 'Complete Course', 'tutor' ); ?>
                            </button>
                        </form>
                    </div>
                    <?php
                }
                do_action('tutor_course/single/actions_btn_group/after'); 

            } else if($is_privileged_user) {
                // The user is not enrolled but privileged to access course content
                if($lesson_url) {
                    ?>
                    <a href="<?php echo $lesson_url; ?>" class="tutor-mt-5 tutor-mb-5 tutor-is-fullwidth tutor-btn">
                        <?php _e('Continue Lesson', 'tutor'); ?>
                    </a>
                    <?php
                } else {
                    echo _e('No Content to Access', 'tutor');
                }
            } else {
                // The course enroll options like purchase or free enrolment
                $is_purchasable = tutor_utils()->is_course_purchasable();
                $price = apply_filters('get_tutor_course_price', null, get_the_ID());

                if ($is_purchasable && $price){
                    tutor_single_course_add_to_cart(); 
                } else {
                    ?>
                    <div class="tutor-course-sidebar-card-pricing tutor-bs-d-flex align-items-end tutor-bs-justify-content-between">
                        <div>
                            <span class="text-bold-h4 color-text-primary"><?php _e('Free', 'tutor'); ?></span>
                        </div>
                    </div>
                    <div class="tutor-course-sidebar-card-btns">
                        <form class="<?php echo implode( ' ', $tutor_form_class ); ?>" method="post">
                            <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
                            <input type="hidden" name="tutor_course_id" value="<?php echo get_the_ID(); ?>">
                            <input type="hidden" name="tutor_course_action" value="_tutor_course_enroll_now">
                            <button type="submit" class="tutor-btn tutor-btn-icon- tutor-btn-primary tutor-btn-lg tutor-btn-full tutor-mt-24">
                                <span><?php _e('Enroll Course', 'tutor'); ?></span>
                            </button>
                        </form>
                    </div>
                    <div class="text-regular-caption color-text-hints tutor-mt-12 tutor-text-center">
                        <?php _e('Free acess this course', 'tutor'); ?>
                    </div>
                    <?php
                }
            }
        ?>
    </div>

    <!-- Course Info -->
    <div class="tutor-course-sidebar-card-footer tutor-p-30">
        <ul class="tutor-course-sidebar-card-meta-list tutor-m-0">
            <?php foreach($sidebar_meta as $meta): ?>
                <?php if(!$meta['value']) continue; ?>
                <li class="tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-between">
                    <div class="flex-center">
                        <span class="tutor-icon-24 <?php echo $meta['icon_class']; ?> color-text-primary"></span>
                        <span class="text-regular-caption color-text-hints tutor-ml-5">
                            <?php echo $meta['label']; ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-medium-caption color-text-primary">
                            <?php echo $meta['value']; ?>
                        </span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>