<?php
/**
 * Template for displaying Assignments Review Form
 *
 * @since v.1.3.4
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$assignment_id           = (int) tutor_utils()->array_get( 'assignment', $_GET );
$assignment_submitted_id = (int) tutor_utils()->array_get( 'view_assignment', $_GET );
$submitted_url           = tutor_utils()->get_tutor_dashboard_page_permalink( 'assignments/submitted' );

if ( ! $assignment_submitted_id ) {
	esc_html_e( "Sorry, but you are looking for something that isn't here.", 'tutor' );
	return;
}
?>

<div class="tutor-dashboard-content-inner tutor-dashboard-assignment-review">
	<?php
		$submitted_assignment = tutor_utils()->get_assignment_submit_info( $assignment_submitted_id );
	if ( $submitted_assignment ) {

		$max_mark = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'total_mark' );

		$given_mark      = get_comment_meta( $assignment_submitted_id, 'assignment_mark', true );
		$instructor_note = get_comment_meta( $assignment_submitted_id, 'instructor_note', true );
		$comment_author  = get_user_by( 'login', $submitted_assignment->comment_author )
		?>

	<div class="submitted-assignment-title tutor-mb-16">
		<a class="tutor-btn tutor-btn-ghost" href="<?php echo esc_url( $submitted_url . '?assignment=' . $assignment_id ); ?>">
			<span class="tutor-icon-previous tutor-mr-8" area-hidden="true"></span>
			<?php esc_html_e( 'Back', 'tutor' ); ?>
		</a>
	</div>

	<div class="tutor-assignment-review-header">
		<div class="tutor-row tutor-align-center tutor-mb-16">
			<div class="tutor-col-lg-3">
				<span class="tutor-color-secondary"><?php esc_html_e( 'Course', 'tutor' ); ?>:</span>
			</div>
			<div class="tutor-col-lg tutor-mt-8 tutor-mt-lg-0">
				<a class="tutor-fw-medium tutor-color-black" href="<?php echo esc_url( get_the_permalink( $submitted_assignment->comment_parent ) ); ?>" target="_blank">
					<?php esc_html_e( get_the_title( $submitted_assignment->comment_parent ) ); ?>
				</a>
			</div>
		</div>

		<div class="tutor-row tutor-align-center tutor-mb-16">
			<div class="tutor-col-lg-3">
				<span class="tutor-color-secondary"><?php esc_html_e( 'Student', 'tutor' ); ?>:</span>
			</div>
			<div class="tutor-col-lg tutor-mt-8 tutor-mt-lg-0">
				<span class="tutor-fw-medium tutor-color-black"><?php echo esc_html( $comment_author->display_name . ' (' . $comment_author->user_email . ')' ); ?></span>
			</div>
		</div>

		<div class="tutor-row tutor-align-center">
			<div class="tutor-col-lg-3">
				<span class="tutor-color-secondary"><?php esc_html_e( 'Submitted Date', 'tutor' ); ?>:</span>
			</div>
			<div class="tutor-col-lg tutor-mt-8 tutor-mt-lg-0">
				<span class="tutor-fw-medium tutor-color-black"><?php echo esc_attr( date( 'j M, Y, h:i a', strtotime( $submitted_assignment->comment_date ) ) ); ?></span>
			</div>
		</div>
	</div>
	
	<div class="tutor-hr"></div>

	<div class="tutor-dashboard-assignment-submitted-content tutor-mt-32 tutor-mb-16">
		<h5 class="tutor-fs-6 tutor-fw-medium tutor-mb-5">
			<?php esc_html_e( 'Assignment Description:', 'tutor' ); ?>
		</h5>
		<p class="tutor-fs-6 tutor-color-secondary tutor-mb-5">
			<?php echo nl2br( stripslashes( $submitted_assignment->comment_content ) ); ?>
		</p>
		<?php
		$attached_files = get_comment_meta( $submitted_assignment->comment_ID, 'uploaded_attachments', true );
		if ( $attached_files && is_array( json_decode( $attached_files ) ) ) :
			?>
			<div class="tutor-fs-5 tutor-fw-medium tutor-mb-20">
				<?php _e( 'Attach assignment file(s)', 'tutor' ); ?>
			</div>
			<div class="tutor-attachment-cards">
				<div class="tutor-row">
					<?php
					if ( $attached_files ) {
						$attached_files = json_decode( $attached_files, true );
						if ( tutor_utils()->count( $attached_files ) ) {
							$upload_dir     = wp_get_upload_dir();
							$upload_baseurl = trailingslashit( tutor_utils()->array_get( 'baseurl', $upload_dir ) );
							foreach ( $attached_files as $attached_file ) {
								?>
									<div class="tutor-col-lg-6 tutor-mb-16 tutor-mb-lg-0">
										<div class="tutor-card tutor-d-flex tutor-align-center tutor-px-16 tutor-py-12">
											<div>
												<div class="tutor-fs-6 tutor-color-black tutor-mb-4"><?php echo esc_html( tutor_utils()->array_get( 'name', $attached_file ) ); ?></div>
												<div class="tutor-fs-7 tutor-color-muted"><?php esc_html_e( 'Size', 'tutor' ); ?><?php esc_html_e( ': 2MB', 'tutor' ); ?></div>
											</div>

											<div class="tutor-ml-auto">
												<a href="<?php echo esc_url( $upload_baseurl . tutor_utils()->array_get( 'uploaded_path', $attached_file ) ); ?>" class="tutor-iconic-btn tutor-iconic-btn-secondary tutor-iconic-btn-lg" target="_blank" rel="noopener noreferrer">
													<span class="tutor-icon-download"></span>
												</a>
											</div>
										</div>
									</div>
								<?php
							}
						}
					}
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<div class="tutor-dashboard-assignment-review-area tutor-mt-32">
		<h3><?php esc_html_e( 'Evaluation', 'tutor' ); ?></h3>
		<form action="" method="post" class="tutor-row tutor-form-submit-through-ajax" data-toast_success_message="<?php _e( 'Assignment evaluated', 'tutor' ); ?>">
			<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
			<input type="hidden" value="tutor_evaluate_assignment_submission" name="tutor_action"/>
			<input type="hidden" value="<?php echo esc_html( $assignment_submitted_id ); ?>" name="assignment_submitted_id"/>

			<div class="tutor-col-12 tutor-col-sm-4 tutor-col-md-12 tutor-col-lg-3">
				<label for=""><?php esc_html_e( 'Your Points', 'tutor' ); ?></label>
			</div>
			<div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-9 tutor-mb-32">
				<input type="number"  class="tutor-form-control" name="evaluate_assignment[assignment_mark]" value="<?php echo $given_mark ? $given_mark : 0; ?>" min="0" max="<?php echo esc_attr( $max_mark ); ?>" title="<?php esc_attr_e( 'Evaluate mark can not be greater than total mark', 'tutor' )?>">
				<p class="desc"><?php echo sprintf( __( 'Evaluate this assignment out of %s', 'tutor' ), "<code>{$max_mark}</code>" ); ?></p>
			</div>

			<div class="tutor-col-12 tutor-col-sm-4 tutor-col-md-12 tutor-col-lg-3">
				<label for=""><?php esc_html_e( 'Feedback', 'tutor' ); ?></label>
			</div>
			<div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-9 tutor-mb-20">
				<textarea class="tutor-form-control" name="evaluate_assignment[instructor_note]"><?php esc_html_e( $instructor_note ); ?></textarea>
			</div>

			<div class="tutor-col-12 tutor-col-sm-4 tutor-col-md-12 tutor-col-lg-3"></div>
			<div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-9">
				<button type="submit" class="tutor-btn tutor-btn-primary tutor-mt-16">
					<?php esc_html_e( 'Evaluate this submission', 'tutor' ); ?>
				</button>
			</div>
		</form>
	</div>

		<?php
	} else {
		esc_html_e( 'Assignments submission not found or not completed', 'tutor' );
	}
	?>
</div>
