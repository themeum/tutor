<?php
/**
 * Logged in template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Instructor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

use Tutor\Components\Alert;
use TUTOR\Icon;

$user_id           = get_current_user_id();
$is_instructor     = tutor_utils()->is_instructor( $user_id );
$instructor_status = get_user_meta( $user_id, '_tutor_instructor_status', true );

if ( 'try_again' === $instructor_status ) {
	Alert::make()
		->text( 'You have been rejected from being an instructor.' )
		->variant( Alert::WARNING )
		->icon( Icon::WARNING )
		->render();
	tutor_load_template( 'dashboard.instructor.apply_for_instructor' );
	return;
}

if ( $is_instructor ) {
	?>
	<div class="tutor-container">
		<div class="tutor-instructor-application-process tutor-pt-48 tutor-pb-48">
			<div class="tutor-app-process-alert tutor-mb-10">
				<?php
				$instructor_status_text = '';
				if ( 'pending' === $instructor_status ) {
					$instructor_status_text = 'Your application will be reviewed and the results will be sent to you by email.';
				} elseif ( 'approved' === $instructor_status ) {
					$instructor_status_text = 'Your application has been accepted. Further necessary details have been sent to your registered email account.';
				} elseif ( 'blocked' === $instructor_status ) {
					$instructor_status_text = 'You have been blocked from being an instructor.';
				}
				Alert::make()
					->text( $instructor_status_text )
					->variant( Alert::INFO )
					->icon( Icon::INFO )
					->render();
				?>
			</div>
			<div class="tutor-app-process-image tutor-m-auto tutor-flex tutor-justify-center tutor-align-center">
				<span class="tutor-app-process-img">
					<img
						src="<?php echo esc_url( tutor()->url . 'assets/images/instructor-thankyou.png' ); ?>"
						alt="<?php esc_attr_e( 'Instructor Application Received', 'tutor' ); ?>"
						style="margin: 0 auto;"
					/>
				</span>
			</div>
			<div class="tutor-instructor-application-body tutor-mt-10">
				<div class="tutor-ins-app-title tutor-m-auto tutor-text-center">
					<span class="tutor-app-process-title tutor-fs-3 tutor-fw-medium tutor-color-black">
					<?php
					if ( 'pending' === $instructor_status ) {
						esc_html_e( 'Thank you for registering as an instructor! ', 'tutor' );
					} elseif ( 'approved' === $instructor_status ) {
						esc_html_e( 'Congratulations! You are now registered as an instructor.', 'tutor' );
					} elseif ( 'blocked' === $instructor_status ) {
						esc_html_e( 'Unfortunately, your instructor status has been removed.', 'tutor' );
					}
					?>
					</span>
				</div>
				<div class="tutor-ins-app-subtitle tutor-m-auto tutor-text-center tutor-pt-24 tutor-pb-48">
					<span class="tutor-app-process-subtitle tutor-fs-6 tutor-color-secondary">
					<?php
					if ( 'pending' === $instructor_status ) {
						esc_html_e( 'We\'ve received your application, and we will review it soon. Please hang tight!', 'tutor' );
					} elseif ( 'approved' === $instructor_status ) {
						esc_html_e( 'Start building your first course today and let your eLearning journey begin.', 'tutor' );
					} elseif ( 'blocked' === $instructor_status ) {
						esc_html_e( 'Please contact the site administrator for further information.', 'tutor' );
					}
					?>
					</span>
				</div>
				<div class="tutor-instructor-apply-button tutor-text-center tutor-mt-6">
					<a style="text-decoration:none;" class="tutor-btn tutor-btn-primary" href="<?php echo esc_url( tutor_utils()->tutor_dashboard_url() ); ?>">
						<?php esc_html_e( 'Go to Dashboard', 'tutor' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>

<?php } else {
	tutor_load_template( 'dashboard.instructor.apply_for_instructor' );
} ?>
