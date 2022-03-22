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
		<a class="tutor-back-btn tutor-color-design-dark" href="<?php echo esc_url( $submitted_url . '?assignment=' . $assignment_id ); ?>">
			<!-- <span class="assignment-back-icon">&leftarrow;</span><?php esc_html_e( 'Back', 'tutor' ); ?> -->
			<span class="tutor-color-black assignment-back-icon tutor-icon-previous-line tutor-icon-30 tutor-mr-12"></span>
			<span class="tutor-color-black-60"><?php esc_html_e( 'Back', 'tutor' ); ?></span>
		</a>
		<!-- <a class="tutor-back-btn tutor-color-design-dark" href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'assignments' ) ); ?>"></a> -->
	</div>

	<div class="tutor-assignment-review-header">
		<table class="tutor-ui-table-no-border tutor-is-lefty tutor-is-flexible">
			<tbody>
				<tr>
					<td class="tutor-color-black-60"><?php esc_html_e( 'Course', 'tutor' ); ?></td>
					<td>:
						<a href="<?php echo esc_url( get_the_permalink( $submitted_assignment->comment_parent ) ); ?>" target="_blank">
						<?php esc_html_e( get_the_title( $submitted_assignment->comment_parent ) ); ?>
						</a>
					</td>
				</tr>
				<tr>
					<td class="tutor-color-black-60"><?php esc_html_e( 'Student', 'tutor' ); ?></td>
					<td>:
						<span>
						<?php echo esc_html( $comment_author->display_name . ' (' . $comment_author->user_email . ')' ); ?>
						</span>
					</td>
				</tr>
				<tr>
					<td class="tutor-color-black-60"><?php esc_html_e( 'Submitted Date', 'tutor' ); ?></td>
					<td>:
						<span>
						<?php echo esc_attr( date( 'j M, Y, h:i a', strtotime( $submitted_assignment->comment_date ) ) ); ?>
						</span>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<hr>

	<div class="tutor-dashboard-assignment-submitted-content tutor-mt-32 tutor-mb-16">
		<h5 class="tutor-fs-6 tutor-fw-medium tutor-mb-5">
			<?php esc_html_e( 'Assignment Description:', 'tutor' ); ?>
		</h5>
		<p class="tutor-fs-6 tutor-color-black-60 tutor-mb-5">
			<?php echo nl2br( stripslashes( $submitted_assignment->comment_content ) ); ?>
		</p>
		<?php
		$attached_files = get_comment_meta( $submitted_assignment->comment_ID, 'uploaded_attachments', true );
		if ( $attached_files && is_array( json_decode( $attached_files ) ) ) :
			?>
			<h5 class="tutor-fs-6 tutor-fw-medium tutor-mb-12 tutor-mt-20"><?php _e( 'Attach assignment file(s)', 'tutor' ); ?></h5>
			<div class="tutor-attachment-cards">
				<?php
				if ( $attached_files ) {
					$attached_files = json_decode( $attached_files, true );
					if ( tutor_utils()->count( $attached_files ) ) {
						$upload_dir     = wp_get_upload_dir();
						$upload_baseurl = trailingslashit( tutor_utils()->array_get( 'baseurl', $upload_dir ) );
						foreach ( $attached_files as $attached_file ) {
							?>
									<div>
										<div>
											<a href="<?php echo esc_url( $upload_baseurl . tutor_utils()->array_get( 'uploaded_path', $attached_file ) ); ?>" target="_blank">
										<?php echo esc_html( tutor_utils()->array_get( 'name', $attached_file ) ); ?>
											</a>
											<span class="filesize"><?php esc_html_e( 'Size', 'tutor' ); ?><?php esc_html_e( ': 2MB', 'tutor' ); ?></span>
										</div>
										<div>
											<a href="<?php echo esc_url( $upload_baseurl . tutor_utils()->array_get( 'uploaded_path', $attached_file ) ); ?>" class="tutor-mt-4" target="_blank">
												<span class="tutor-icon-download-line"></span>
											</a>
										</div>
									</div>
							<?php
						}
					}
				}
				?>
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
				<input class="tutor-form-control" type="number" name="evaluate_assignment[assignment_mark]" value="<?php echo $given_mark ? $given_mark : 0; ?>" min="0">
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
				<button type="submit" class="tutor-btn tutor-mt-16">
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
