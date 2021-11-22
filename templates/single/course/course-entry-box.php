<?php 
    // Utillity data
    $is_enrolled           = tutor_utils()->is_enrolled();
    $lesson_url            = tutor_utils()->get_course_first_lesson();
    $is_administrator      = tutor_utils()->has_user_role( 'administrator' );
    $is_instructor         = tutor_utils()->is_instructor_of_this_course();
    $course_content_access = (bool) get_tutor_option( 'course_content_access_for_ia' );
    $is_privileged_user    = $course_content_access && ( $is_administrator || $is_instructor );
    $tutor_course_sell_by  = apply_filters( 'tutor_course_sell_by', null );
    $is_public             = get_post_meta( get_the_ID(), '_tutor_is_public_course', true ) == 'yes';

    // Monetization info
    $monetize_by = tutor_utils()->get_option( 'monetize_by' );
    $enable_guest_course_cart = tutor_utils()->get_option( 'enable_guest_course_cart' );
    $is_purchasable = tutor_utils()->is_course_purchasable();
    
    // Get login url if 
    $is_tutor_login_disabled = tutor_utils()->get_option( 'disable_tutor_native_login' );
    $auth_url = $is_tutor_login_disabled ? isset( $_SERVER['REQUEST_SCHEME'] ) ? wp_login_url( $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) : '' : '';

    // Right sidebar meta data
    $sidebar_meta = apply_filters( 'tutor/course/single/sidebar/metadata', array(
        array(
            'icon_class' => 'ttr-level-line', 
            'label'      => __( 'Level', 'tutor' ), 
            'value'      => get_tutor_course_level( get_the_ID() )
        ),
        array(
            'icon_class' => 'ttr-student-line-1', 
            'label'      => __( 'Total Enrolled', 'tutor' ), 
            'value'      => ! get_tutor_option( 'disable_course_total_enrolled' ) ? tutor_utils()->count_enrolled_users_by_course() : null
        ),
        array(
            'icon_class' => 'ttr-clock-filled', 
            'label'      => __( 'Duration', 'tutor' ), 
            'value'      => ! get_tutor_option( 'disable_course_duration' ) ? get_tutor_course_duration_context() : null
        ),
        array(
            'icon_class' => 'ttr-update-line', 
            'label'      => __( 'Last Updated', 'tutor' ), 
            'value'      => ! get_tutor_option( 'disable_course_update_date' ) ? tutor_get_formated_date( get_option( 'date_format' ), get_the_modified_date() ) : null
        ),
    ), get_the_ID());
?>

<div class="tutor-course-sidebar-card">
    <!-- Course Entry -->
    <div class="tutor-course-sidebar-card-body tutor-p-30 <?php echo ! is_user_logged_in() ? 'tutor-course-entry-box-login' : ''; ?>">
        <?php 
            if ( $is_enrolled ) {
                // The user is enrolled anyway. No matter manual, free, purchased, woocommerce, edd, membership
                do_action( 'tutor_course/single/actions_btn_group/before' );
                
                // Course Info
                $completed_lessons   = tutor_utils()->get_completed_lesson_count_by_course();
                $completed_percent   = tutor_utils()->get_course_completed_percent();
                $is_completed_course = tutor_utils()->is_completed_course();
                $retake_course       = tutor_utils()->get_option( 'course_retake_feature', false ) && ( $is_completed_course || $completed_percent >= 100 );

                // Show Start/Continue/Retake Button
                if ( $lesson_url ) { 
                    $button_class = 'tutor-is-fullwidth tutor-btn ' . ( $retake_course ? 'tutor-btn-tertiary tutor-is-outline tutor-btn-lg tutor-btn-full' : '' ) . ' tutor-is-fullwidth tutor-pr-0 tutor-pl-0 ' . ( $retake_course ? ' tutor-course-retake-button' : '' );
                    ?>
                    <a href="<?php echo esc_url( $lesson_url ); ?>" class="<?php echo esc_attr( $button_class ); ?>" data-course_id="<?php echo esc_attr( get_the_ID() ); ?>">
                        <?php
                            if ( is_single_course() && $retake_course ) {
                                esc_html_e( 'Retake This Course', 'tutor' );
                            } elseif ( $completed_percent <= 0 ) {
                                esc_html_e( 'Start Learning', 'tutor' );
                            } else {
                                esc_html_e( 'Continue Learning', 'tutor' );
                            }
                        ?>
                    </a>
                    <?php 
                } else {
                    // Show Only enrolled message if there is no content to start from
                    ?>
                    <div class="text-regular-caption color-text-hints tutor-mt-12 tutor-bs-d-flex tutor-bs-justify-content-center">
                        <span class="tutor-icon-26 color-success ttr-purchase-filled tutor-mr-6"></span>
                        <?php echo sprintf( __( 'You enrolled this course on %s', 'tutor' ), '<span class="text-bold-small color-success tutor-ml-3">' . tutor_get_formated_date( get_option( 'date_format' ), $is_enrolled->post_date ) . '</span>' ); ?>
                    </div>
                    <?php
                }

                // Show Course Completion Button
                if ( ! $is_completed_course ) {
                    ?>
                    <form method="post">
                        <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

                        <input type="hidden" value="<?php echo esc_attr( get_the_ID() ); ?>" name="course_id"/>
                        <input type="hidden" value="tutor_complete_course" name="tutor_action"/>

                        <button type="submit" class="tutor-mt-25 tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-lg tutor-btn-full" name="complete_course_btn" value="complete_course">
                            <?php esc_html_e( 'Complete Course', 'tutor' ); ?>
                        </button>
                    </form>
                    <?php
                }
                do_action( 'tutor_course/single/actions_btn_group/after' ); 

            } elseif ( $is_privileged_user ) {
                // The user is not enrolled but privileged to access course content
                if ( $lesson_url ) {
                    ?>
                    <a href="<?php echo esc_url( $lesson_url ); ?>" class="tutor-mt-5 tutor-mb-5 tutor-is-fullwidth tutor-btn">
                        <?php esc_html_e( 'Continue Lesson', 'tutor' ); ?>
                    </a>
                    <?php
                } else {
                    esc_html_e( 'No Content to Access', 'tutor' );
                }
            } else {
                // The course enroll options like purchase or free enrolment
                $price = apply_filters( 'get_tutor_course_price', null, get_the_ID() );

                if ( tutor_utils()->is_course_fully_booked( null ) ) {
                    ?>
                    <div class="tutor-alert tutor-warning tutor-mt-28">
                        <div class="tutor-alert-text">
                            <span class="tutor-alert-icon tutor-icon-34 ttr-circle-outline-info-filled tutor-mr-10"></span>
                            <span>
                                <?php esc_html_e( 'This course is full right now. We limit the number of students to create an optimized and productive group dynamic.', 'tutor' ); ?>
                            </span>
                        </div>
                    </div>
                    <?php
                } elseif ( $is_purchasable && $price ) {
                    if ( $tutor_course_sell_by ) {
                        // Load template based on monetization option
                        tutor_load_template( 'single.course.add-to-cart-' . $tutor_course_sell_by );
                    } elseif ( $is_public ) {
                        // Get the first content url
                        $first_lesson_url = tutor_utils()->get_course_first_lesson( get_the_ID(), tutor()->lesson_post_type );
                        ! $first_lesson_url ? $first_lesson_url = tutor_utils()->get_course_first_lesson( get_the_ID() ) : 0;
            
                        ?>
                        <a href="<?php echo esc_url( $first_lesson_url ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-full">
                            <?php esc_html_e( 'Start Learning', 'tutor' ); ?>
                        </a>
                        <?php
                    } 
                } else {
                    ob_start();
                    ?>
                    <div class="tutor-course-sidebar-card-pricing tutor-bs-d-flex align-items-end tutor-bs-justify-content-between">
                        <div>
                            <span class="text-bold-h4 color-text-primary"><?php esc_html_e( 'Free', 'tutor' ); ?></span>
                        </div>
                    </div>
                    <div class="tutor-course-sidebar-card-btns">
                        <form class="tutor-enrol-course-form" method="post">
                            <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
                            <input type="hidden" name="tutor_course_id" value="<?php echo esc_attr( get_the_ID() ); ?>">
                            <input type="hidden" name="tutor_course_action" value="_tutor_course_enroll_now">
                            <button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-full tutor-mt-24">
                                <?php esc_html_e( 'Enroll Course', 'tutor' ); ?>
                            </button>
                        </form>
                    </div>
                    <div class="text-regular-caption color-text-hints tutor-mt-12 tutor-text-center">
                        <?php esc_html_e( 'Free acess this course', 'tutor' ); ?>
                    </div>
                    <?php
                    echo apply_filters( 'tutor/course/single/entry-box/free', ob_get_clean(), get_the_ID() );
                }
            }
        ?>
    </div>

    <!-- Course Info -->
    <div class="tutor-course-sidebar-card-footer tutor-p-30">
        <ul class="tutor-course-sidebar-card-meta-list tutor-m-0 tutor-pl-0">
            <?php foreach ( $sidebar_meta as $meta ) : ?>
                <?php if ( ! $meta['value'] ) continue; ?>
                <li class="tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-between">
                    <div class="flex-center">
                        <span class="tutor-icon-24 <?php echo $meta['icon_class']; ?> color-text-primary"></span>
                        <span class="text-regular-caption color-text-hints tutor-ml-5">
                            <?php echo esc_html( $meta['label'] ); ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-medium-caption color-text-primary">
                            <?php echo wp_kses_post( $meta['value'] ); ?>
                        </span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<?php 
    if ( ! is_user_logged_in() ) {
        tutor_load_template_from_custom_path( tutor()->path . '/views/modal/login.php' );
    }
?>