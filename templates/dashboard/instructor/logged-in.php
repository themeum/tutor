<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$is_instructor = tutor_utils()->is_instructor();
if ( $is_instructor ) {
    $user_id = get_current_user_id();
    $instructor_status = get_user_meta( $user_id, '_tutor_instructor_status', true );
?>
    <div class="tutor-bs-container">
        <div class="tutor-instructor-application-process tutor-pt-50 tutor-pb-50">
            <div class="tutor-app-process-alert">
                <div style="border:1px solid var(--tutor-color-brand);" class="tutor-primary tutor-py-12 tutor-px-20 tutor-radius-6">
                    <div class="tutor-alert-text tutor-bs-d-flex tutor-bs-align-items-center">
                    <span class="ttr-circle-outline-info-filled tutor-mr-10 tutor-h4 color-brand"></span>
                    <span>
                    <?php
                        if ( $instructor_status == 'pending' ) {
                            esc_html_e( 'Your application will be reviewed and the results will be sent to you by email.', 'tutor' );
                        } elseif ( $instructor_status == 'approved' ) {
                            esc_html_e( 'Your application has been accepted. Further necessary details have been sent to your registered email account.', 'tutor' );
                        } elseif ( $instructor_status == 'blocked' ) {
                            esc_html_e( 'You have been blocked from being an instructor.', 'tutor' );
                        }
                    ?>
                    </span>
                    </div>
                </div>
            </div>
            <div class="tutor-app-process-image tutor-bs-m-auto tutor-pt-30 tutor-pb-45 tutor-bs-d-flex tutor-bs-justify-content-center tutor-bs-align-items-center">
                <span class="tutor-app-process-img">
                    <img
                    src="<?php echo esc_url( tutor()->url . 'assets/images/instructor-application-received.png' ); ?>"
                    alt="<?php esc_attr_e( 'Instructor Application Received', 'tutor' ); ?>"
                    />
                </span>
            </div>
            <div class="tutor-instructor-application-body">
                <div class="tutor-ins-app-title tutor-bs-m-auto tutor-text-center">
                    <span class="tutor-app-process-title text-medium-h3 color-text-primary">
                    <?php
                        if ( $instructor_status == 'pending' ) {
                            esc_html_e( 'Thank you for registering as an instructor! ', 'tutor' );
                        } elseif ( $instructor_status == 'approved' ) {
                            esc_html_e( 'Congratulations! You are now registered as an instructor.', 'tutor' );
                        } elseif ( $instructor_status == 'blocked' ) {
                            esc_html_e( 'Unfortunately, your instructor status has been removed.', 'tutor' );
                        }
                    ?>
                    </span>
                </div>
                <div class="tutor-ins-app-subtitle tutor-bs-m-auto tutor-text-center tutor-pt-25 tutor-pb-50">
                    <span class="tutor-app-process-subtitle text-regular-h6 color-text-subsued">
                    <?php
                        if ( $instructor_status == 'pending' ) {
                            esc_html_e( 'We\'ve received your application, and we will review it soon. Please hang tight!', 'tutor' );
                        } elseif ( $instructor_status == 'approved' ) {
                            esc_html_e( 'Start building your first course today and let your eLearning journey begin.', 'tutor' );
                        } elseif ( $instructor_status == 'blocked' ) {
                            esc_html_e( 'Please contact the site administrator for further information.', 'tutor' );
                        }
                    ?>
                    </span>
                </div>
                <div class="tutor-instructor-apply-button tutor-text-center">
                    <a style="text-decoration:none;" class="tutor-bg-primary color-text-white tutor-py-15 tutor-px-30 tutor-radius-6" href="<?php echo esc_url( tutor_utils()->tutor_dashboard_url() ) ?>">
                        <?php esc_html_e( 'Go to Dashboard', 'tutor' ); ?>
                    </a>
                </div>
            </div>
        </div>
	</div>

<?php } else {
    tutor_load_template( 'dashboard.instructor.apply_for_instructor' );
} ?>