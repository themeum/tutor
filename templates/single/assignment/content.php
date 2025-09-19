<?php
/**
 * Template for assignment content.
 *
 * @package Tutor\Templates
 * @subpackage Single\Assignment
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

use TUTOR\Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;
global $wpdb;
global $next_id;
global $assignment_submitted_id;

$is_submitting = tutor_utils()->is_assignment_submitting( get_the_ID() );

// Get the comment.
$post_id                   = get_the_ID(); //phpcs:ignore
$user_id                   = get_current_user_id();
$user_data                 = get_userdata( $user_id );
$assignment_comment        = tutor_utils()->get_single_comment_user_post_id( $post_id, $user_id );
$submitted_assignment      = tutor_utils()->is_assignment_submitted( get_the_ID() );
$is_reviewed_by_instructor = false;

if ( $submitted_assignment ) {
	$is_reviewed_by_instructor = get_comment_meta( $submitted_assignment[0]->comment_ID, 'evaluate_time', true );
}

// Get the ID of this content and the corresponding course.
$course_content_id = get_the_ID();
$course_id         = tutor_utils()->get_course_id_by_subcontent( $course_content_id );

// Get total content count.
$course_stats = tutor_utils()->get_course_completed_percent( $course_id, 0, true );

// Get enrolled data.
$enrolled_info   = tutor_utils()->is_enrolled( $course_id, $user_id );
$enrollment_time = apply_filters( 'tutor_content_drip_assignment_deadline', strtotime( $enrolled_info->post_date_gmt ?? '' ), $course_id, $post_id );

/**
 * Convert assignment time
 *
 * @todo move to utils
 *
 * @param integer $seconds seconds.
 * @return string
 */
function tutor_assignment_convert_seconds( $seconds ) {
	$dt1 = new DateTime( '@0' );
	$dt2 = new DateTime( "@$seconds" );

	$diff    = $dt1->diff( $dt2 );
	$days    = $diff->days;
	$hours   = $diff->h;
	$minutes = $diff->i;

	return $days . ' ' . _n( 'Day', 'Days', $days, 'tutor' ) . ', ' . $hours . ' ' . _n( 'Hour', 'Hours', $hours, 'tutor' ) . ', ' . $minutes . ' ' . _n( 'Minute', 'Minutes', $minutes, 'tutor' );
}

$next_prev_content_id = tutor_utils()->get_course_prev_next_contents_by_id( $post_id );
$content              = get_the_content();
$s_content            = $content;
$allow_to_upload      = (int) tutor_utils()->get_assignment_option( $post_id, 'upload_files_limit' );
$feedback_mode        = tutor_utils()->get_assignment_option( $post_id, 'feedback_mode' );
$attempts_allowed     = (int) tutor_utils()->get_assignment_option( $post_id, 'attempts_allowed' );
$course_id            = tutor_utils()->get_course_id_by( 'lesson', get_the_ID() );

$upload_dir     = wp_get_upload_dir();
$upload_baseurl = trailingslashit( $upload_dir['baseurl'] ?? '' );
$upload_basedir = trailingslashit( $upload_dir['basedir'] ?? '' );

$total_mark   = tutor_utils()->get_assignment_option( get_the_ID(), 'total_mark' );
$pass_mark    = (int) tutor_utils()->get_assignment_option( get_the_ID(), 'pass_mark' );
$earned_marks = 0; // @TODO: Get the earned marks based on assignment grading.

$is_single_attempt = Input::get( 'view_assignment_attempt_id', 0 );

$time_duration = tutor_utils()->get_assignment_option(
	get_the_ID(),
	'time_duration',
	array(
		'time'  => '',
		'value' => 0,
	)
);

if ( ! is_array( $time_duration ) ) {
	$time_duration = array();
}

$time_unit  = $time_duration['time'] ?? '';
$time_value = isset( $time_duration['value'] ) ? (int) $time_duration['value'] : 0;

$file_upload_limit       = tutor_utils()->get_assignment_option( get_the_ID(), 'upload_file_size_limit' );
$deadline_from_start     = (bool) tutor_utils()->get_assignment_option( get_the_ID(), 'deadline_from_start' );
$assignment_created_time = strtotime( $post->post_date_gmt );
$deadline_time           = $enrollment_time < $assignment_created_time ? $assignment_created_time : $enrollment_time;

$start_assignment_date = null;

if ( $assignment_comment && isset( $assignment_comment->comment_date_gmt ) ) {
	$start_assignment_date = $assignment_comment->comment_date_gmt;
}

if ( $deadline_from_start && $start_assignment_date ) {
	$deadline_time = strtotime( $start_assignment_date );
}

$time_duration_in_sec = 0;

if ( $time_value && ! empty( $time_unit ) ) {
	switch ( $time_unit ) {
		case 'hours':
			$time_duration_in_sec = 3600;
			break;
		case 'days':
			$time_duration_in_sec = 86400;
			break;
		case 'weeks':
			$time_duration_in_sec = 7 * 86400;
			break;
		default:
			$time_duration_in_sec = 0;
			break;
	}
}

$time_duration_in_sec = $time_duration_in_sec * $time_value;
$remaining_time       = $deadline_time + $time_duration_in_sec;
$now                  = time();
$remaining            = $now - $remaining_time;
$is_expired           = false;

$time_map = array(
	'days'  => _n( 'Day', 'Days', $time_value, 'tutor' ),
	'hours' => _n( 'Hour', 'Hours', $time_value, 'tutor' ),
	'weeks' => _n( 'Week', 'Weeks', $time_value, 'tutor' ),
);

$duration = $time_value ? $time_value . ' ' . $time_map[ $time_unit ] : __( 'No limit', 'tutor' );
$deadline = null;

if ( $time_value ) {
	if ( $deadline_from_start && ! $start_assignment_date ) {
		$deadline = sprintf(
		// translators: %1$s is the number value (e.g., 3), %2$s is the time unit (e.g., days).
			esc_html__( '%1$s %2$s after you start the assignment', 'tutor' ),
			esc_html( $time_value ),
			esc_html( strtolower( $time_map[ $time_unit ] ) )
		);
	} elseif ( $now > $remaining_time ) {
			$is_expired = true;
			$deadline   = __( 'Expired', 'tutor' );
	} else {
		$deadline = esc_html( tutor_assignment_convert_seconds( $remaining ) );
	}
} else {
	$deadline = __( 'N\\A', 'tutor' );
}
?>

<?php do_action( 'tutor_assignment/single/before/content' ); ?>

<?php tutor_load_template( 'single.common.header', array( 'course_id' => $course_id ) ); ?>

<?php tutor_load_template_from_custom_path( tutor()->path . 'templates/single/assignment/navigation.php', array( 'next_prev_content_id' => $next_prev_content_id ) ); ?>

<div class="tutor-course-topic-single-body">
	<div class="tutor-quiz-wrapper tutor-d-flex tutor-justify-center tutor-mt-36 tutor-pb-80">
		<div id="tutor-assignment-wrap" class="tutor-quiz-wrap tutor-course-assignment-details tutor-submit-assignment  tutor-assignment-result-pending">
			<?php
			tutor_load_template_from_custom_path(
				tutor()->path . 'templates/single/assignment/header.php',
				array(
					'total_mark'   => $total_mark,
					'pass_mark'    => $pass_mark,
					'earned_marks' => $earned_marks,
					'has_back_url' => $is_single_attempt,
					'duration'     => $duration,
					'deadline'     => $deadline,
				)
			);
			?>

			<?php
			/**
			 * Time_duration[value]==0 means no limit
			 * if have unlimited time then no msg should appear
			 */
			if ( $is_expired ) :
				?>
				<div class="quiz-flash-message tutor-mt-24 tutor-mt-sm-32">
					<div class="tutor-quiz-warning-box time-over tutor-d-flex tutor-align-center tutor-justify-between">
						<div class="flash-info tutor-d-flex tutor-align-center">
							<span class="tutor-icon-circle-times-bold tutor-color-danger tutor-mr-8"></span>
							<span class="tutor-fs-7 tutor-color-danger-100">
								<?php esc_html_e( 'You have missed the submission deadline. Please contact the instructor for more information.', 'tutor' ); ?>
							</span>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! $is_submitting && ! $submitted_assignment && get_the_content() ) : ?>
				<?php
				tutor_load_template_from_custom_path(
					tutor()->path . 'templates/single/assignment/details.php'
				);
				?>
			<?php endif; ?>

			<?php if ( ( $is_submitting || isset( $_GET['update-assignment'] ) ) && ( $remaining_time > $now || 0 === $time_value ) ) : ?>
				<div class="tutor-assignment-submission tutor-pb-48 tutor-pb-sm-72 <?php echo ! $is_single_attempt ? 'tutor-assignment-border-bottom' : ''; ?>">
					<form action="" method="post" id="tutor_assignment_submit_form" enctype="multipart/form-data">
						<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce, false ); ?>
						<input type="hidden" value="tutor_assignment_submit" name="tutor_action" />
						<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">

						<?php $allowed_upload_files = (int) tutor_utils()->get_assignment_option( get_the_ID(), 'upload_files_limit' ); ?>
						<div class="tutor-assignment-body tutor-pt-32 tutor-pt-sm-40">
							<div class="tutor-to-title tutor-fs-6 tutor-fw-medium tutor-color-black">
								<?php esc_html_e( 'Assignment Submission', 'tutor' ); ?>
							</div>

							<div class="tutor-fs-7 tutor-color-secondary tutor-pt-16 tutor-pt-sm-32">
								<?php esc_html_e( 'Assignment answer form', 'tutor' ); ?>
							</div>

							<div class="tutor-assignment-text-area tutor-pt-20">
								<?php
									$assignment_comment_id = Input::has( 'update-assignment' ) ? Input::get( 'update-assignment' ) : 0;
									$content               = $assignment_comment_id ? get_comment( $assignment_comment_id ) : '';
									$args                  = tutor_utils()->text_editor_config();
									$args['tinymce']       = array(
										'toolbar1' => 'formatselect,bold,italic,underline,forecolor,bullist,numlist,alignleft,aligncenter,alignright,alignjustify,undo,redo',
									);
									$args['editor_height'] = '140';
									$editor_args           = array(
										'content' => isset( $content->comment_content ) ? $content->comment_content : '',
										'args'    => $args,
									);
									$text_editor_template  = tutor()->path . 'templates/global/tutor-text-editor.php';
									tutor_load_template_from_custom_path( $text_editor_template, $editor_args );
									?>
							</div>

							<?php if ( $allowed_upload_files ) : ?>
								<div class="tutor-assignment-attachment tutor-mt-32 tutor-py-20 tutor-px-16 tutor-py-sm-32 tutor-px-sm-32">
									<div class="tutor-fs-7 tutor-color-secondary">
										<?php
											$attachment_text  = _x( 'Attach assignment files (Max: ', 'Assignment attachment', 'tutor' );
											$attachment_text .= $allow_to_upload . _x( ' file)', 'Assignment attachment', 'tutor' );
											echo esc_html( $attachment_text );
										?>
									</div>
									<div class="tutor-attachment-files tutor-mt-12">
										<div class="tutor-assignment-upload-btn tutor-mt-12 tutor-mt-md-0">
											<form>
												<label for="tutor-assignment-file-upload">
													<input type="file" id="tutor-assignment-file-upload"
														name="attached_assignment_files[]" multiple>
													<a class="tutor-btn tutor-btn-primary tutor-btn-md">
														<?php esc_html_e( 'Choose file', 'tutor' ); ?>
													</a>
												</label>
												<input type="hidden" name="tutor_assignment_upload_limit"
													value="<?php echo esc_attr( $file_upload_limit * 1000000 ); ?>">
											</form>
										</div>
										<div class="tutor-input-type-size">
											<p class="tutor-fs-7 tutor-color-secondary">
												<?php esc_html_e( 'File Support: ', 'tutor' ); ?>
												<span class="tutor-color-black">
													<?php esc_html_e( 'Any standard Image, Document, Presentation, Sheet, PDF or Text file is allowed', 'tutor' ); ?>
												</span>
											</p>
											<p class="tutor-fs-7 tutor-color-secondary tutor-mt-7">
												<?php esc_html_e( 'Total File Size: Max', 'tutor' ); ?>
												<span class="tutor-color-black">
													<?php echo esc_html( $file_upload_limit ); ?>
													<?php esc_html_e( 'MB', 'tutor' ); ?>
												</span>
											</p>
										</div>
									</div>

									<div class="tutor-container tutor-pt-16 tutor-update-assignment-attachments">
										<div class="tutor-row tutor-gy-3" id="tutor-student-assignment-edit-file-preview">
											<?php
												$submitted_attachments = get_comment_meta( $assignment_comment_id, 'uploaded_attachments' );
											if ( is_array( $submitted_attachments ) && count( $submitted_attachments ) ) :
												?>
												<?php
												foreach ( $submitted_attachments as $attach ) :
													$attachments = json_decode( $attach );
													?>
													<?php foreach ( $attachments as $attachment ) : ?>
														<div class="tutor-instructor-card tutor-col-sm-5 tutor-py-16 tutor-mr-16">
															<div class="tutor-icard-content">
																<div class="tutor-fs-6 tutor-color-secondary">
																	<?php echo esc_html( $attachment->name ); ?>
																</div>
																<div class="tutor-fs-7">
																	<?php echo esc_html( tutor_utils()->get_readable_filesize( $upload_basedir . $attachment->uploaded_path ) ); ?>
																</div>
															</div>
															<div
																class="tutor-attachment-file-close tutor-d-flex tutor-align-center">
																<a class="tutor-iconic-btn tutor-iconic-btn-outline" href="<?php echo esc_url( $attachment->url ); ?>"
																	data-id="<?php echo esc_attr( $assignment_comment_id ); ?>"
																	data-name="<?php echo esc_attr( $attachment->name ); ?>" target="_blank">
																	<span class="tutor-icon-times"></span>
																</a>
															</div>
														</div>
													<?php endforeach; ?>
												<?php endforeach; ?>
											<?php endif; ?>
										</div>
									</div>
								</div>
							<?php endif; ?>

							<div class="tutor-assignment-submit-btn tutor-mt-60">
								<button type="submit" id="tutor_assignment_submit_btn" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-static-loader">
									<?php esc_html_e( 'Submit Assignment', 'tutor' ); ?>
								</button>
							</div>
						</div>
					</form>
				</div>

				<?php if ( $next_prev_content_id->next_id && ! $is_single_attempt ) : ?>
					<div class="tutor-assignment-footer tutor-d-flex tutor-justify-end tutor-pt-32 tutor-pt-sm-44">
						<a href="<?php echo esc_url( get_permalink( $next_prev_content_id->next_id ) ); ?>" class="tuttor-assignment-skip-button tutor-btn tutor-btn-ghost tutor-mt-md-0 tutor-mt-12">
							<?php esc_html_e( 'Skip To Next', 'tutor' ); ?>
						</a>
					</div>
				<?php endif; ?>
			<?php else : ?>

				<?php if ( $submitted_assignment ) : ?>
					<?php if ( $is_single_attempt ) : ?>
						<?php
						tutor_load_template_from_custom_path(
							tutor()->path . 'templates/single/assignment/single-attempt.php',
							array(
								'attempt_id'     => $is_single_attempt,
								'remaining_time' => $remaining_time,
								'now'            => $now,
								'time_value'     => $time_value,
							)
						);
						return;
						?>
						<?php else : ?>
							<?php
								tutor_load_template_from_custom_path(
									tutor()->path . 'templates/single/assignment/attempts.php',
									array(
										'attempts' =>
											$submitted_assignment,
									)
								);
								// You can add your HTML here inside the else block.
							?>
						<?php endif; ?>

					<?php if ( ! $is_single_attempt ) : ?>
						<?php
							tutor_load_template_from_custom_path(
								tutor()->path . 'templates/single/assignment/details.php'
							);
						?>
					<?php endif; ?>
					
					<div class="tutor-assignment-footer tutor-pt-32 tutor-pt-sm-44">
						<!-- @TODO: need to check against the count of attempts -->
						<div class="tutor-assignment-footer-btn tutor-d-flex">
							<?php if ( 1 < $attempts_allowed && 'retry' === $feedback_mode && $earned_marks < $pass_mark && $is_reviewed_by_instructor ) : ?>
								<form action="" method="post" id="tutor_assignment_start_form">
									<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
									<input type="hidden" value="tutor_assignment_start_submit" name="tutor_action" />
									<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">
									<button type="submit" id="tutor_assignment_start_btn" class="tutor-btn tutor-btn-primary tutor-mr-16"<?php echo $is_expired ? ' disabled' : ''; ?>>
										<?php esc_html_e( 'Retry', 'tutor' ); ?>
									</button>
								</form>
							<?php endif; ?>

							<?php if ( $next_prev_content_id->next_id ) : ?>
								<a class="tutor-btn tutor-btn-primary tutor-static-loader"
								href="<?php echo esc_url( get_the_permalink( $next_prev_content_id->next_id ) ); ?>">
								<?php esc_html_e( 'Continue Lesson', 'tutor' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php else : ?>
					<div class="tutor-assignment-footer tutor-pt-32 tutor-pt-sm-44">
						<div class="tutor-assignment-footer-btn tutor-d-flex tutor-justify-between">
							<form action="" method="post" id="tutor_assignment_start_form">
								<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
								<input type="hidden" value="tutor_assignment_start_submit" name="tutor_action" />
								<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">
								<button type="submit" id="tutor_assignment_start_btn" class="tutor-btn tutor-btn-primary"<?php echo $is_expired ? ' disabled' : ''; ?>>
									<?php esc_html_e( 'Start Assignment', 'tutor' ); ?>
								</button>
							</form>

							<?php if ( $next_prev_content_id->next_id ) : ?>
								<a href="<?php echo esc_url( get_permalink( $next_prev_content_id->next_id ) ); ?>" class="tutor-btn tutor-btn-ghost tutor-mt-md-0 tutor-mt-12">
									<?php esc_html_e( 'Skip To Next', 'tutor' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php tutor_load_template( 'single.common.footer', array( 'course_id' => $course_id ) ); ?>

<?php do_action( 'tutor_assignment/single/after/content' ); ?>
