<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

use \TUTOR_ASSIGNMENTS\Assignments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
global $wpdb;
global $next_id;
global $assignment_submitted_id;
$is_submitted  = false;
$is_submitting = tutor_utils()->is_assignment_submitting( get_the_ID() );
// get the comment
$post_id            = get_the_ID();
$user_id            = get_current_user_id();
$user_data          = get_userdata( $user_id );
$assignment_comment = tutor_utils()->get_single_comment_user_post_id( $post_id, $user_id );
// $submitted_assignment = tutor_utils()->get_assignment_submit_info( $assignment_submitted_id );
$submitted_assignment = tutor_utils()->is_assignment_submitted( get_the_ID() );
if ( $assignment_comment != false ) {
	$submitted                                = $assignment_comment->comment_approved;
	$submitted == 'submitted' ? $is_submitted = true : '';
}

// Get the ID of this content and the corresponding course
$course_content_id = get_the_ID();
$course_id         = tutor_utils()->get_course_id_by_subcontent( $course_content_id );

// Get total content count
$course_stats = tutor_utils()->get_course_completed_percent( $course_id, 0, true );

function tutor_assignment_convert_seconds( $seconds ) {
	$dt1 = new DateTime( '@0' );
	$dt2 = new DateTime( "@$seconds" );
	return $dt1->diff( $dt2 )->format( '%a Days, %h Hours' );
}
$next_prev_content_id = tutor_utils()->get_course_prev_next_contents_by_id( $post_id );
$content              = get_the_content();
$s_content            = $content;
$allow_to_upload      = (int) tutor_utils()->get_assignment_option( $post_id, 'upload_files_limit' );
?>

<?php do_action( 'tutor_assignment/single/before/content' ); ?>

<div class="tutor-single-page-top-bar tutor-d-flex tutor-justify-content-between">
	<div class="tutor-topbar-left-item tutor-d-flex">
		<div class="tutor-topbar-item tutor-topbar-sidebar-toggle tutor-hide-sidebar-bar flex-center tutor-d-none tutor-d-xl-flex">
			<a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">
				<span class="tutor-icon-icon-light-left-line tutor-color-white flex-center"></span>
			</a>
		</div>
		<div class="tutor-topbar-item tutor-topbar-content-title-wrap flex-center">
			<span class="tutor-icon-assignment-filled tutor-icon-24 tutor-color-white tutor-mr-4"></span>
			<span class="tutor-fs-7 tutor-color-design-white">
				<?php
					esc_html_e( 'Assignment: ', 'tutor' );
					the_title();
				?>
			</span>
		</div>
	</div>
	<div class="tutor-topbar-right-item tutor-d-flex">
		<div class="tutor-topbar-assignment-details tutor-d-flex tutor-align-items-center">
			<?php
				do_action( 'tutor_course/single/enrolled/before/lead_info/progress_bar' );
			?>
			<div class="tutor-fs-7 tutor-color-design-white">
				<?php if ( true == get_tutor_option( 'enable_course_progress_bar' ) ) : ?>
					<span class="tutor-progress-content tutor-color-primary-60">
						<?php _e( 'Your Progress:', 'tutor' ); ?>
					</span>
					<span class="tutor-fs-7 tutor-fw-bold">
						<?php echo $course_stats['completed_count']; ?>
					</span>
					<?php _e( 'of ', 'tutor' ); ?>
					<span class="tutor-fs-7 tutor-fw-bold">
						<?php echo $course_stats['total_count']; ?>
					</span>
					(<?php echo $course_stats['completed_percent'] . '%'; ?>)
				<?php endif; ?>
			</div>
			<?php
				do_action( 'tutor_course/single/enrolled/after/lead_info/progress_bar' );
			?>
		</div>
		<div class="tutor-topbar-cross-icon tutor-ml-16 flex-center">
			<?php $course_id = tutor_utils()->get_course_id_by( 'lesson', get_the_ID() ); ?>
			<a href="<?php echo esc_url( get_the_permalink( $course_id ) ); ?>">
				<span class="tutor-icon-line-cross-line tutor-color-white flex-center"></span>
			</a>
		</div>
	</div>
</div>

<div class="tutor-mobile-top-navigation tutor-d-block tutor-d-sm-none tutor-my-20 tutor-mx-12">
	<div class="tutor-mobile-top-nav tutor-d-grid">
		<a href="<?php echo esc_url( get_the_permalink( isset( $previous_id ) ? $previous_id : '' ) ); ?>">
			<span class="tutor-top-nav-icon tutor-icon-previous-line design-lightgrey"></span>
		</a>
		<div class="tutor-top-nav-title tutor-fs-6 tutor-color-black">
			<?php
				the_title();
			?>
		</div>
	</div>
</div>
<div class="tutor-quiz-wrapper tutor-quiz-wrapper tutor-d-flex justify-content-center tutor-mt-36 tutor-pb-80">
	<div id="tutor-assignment-wrap" class="tutor-quiz-wrap tutor-course-assignment-details tutor-submit-assignment  tutor-assignment-result-pending">
		<div class="tutor-assignment-title tutor-fs-4 tutor-fw-medium tutor-color-black">
			<?php the_title(); ?>
		</div>

		<?php
			$time_duration = tutor_utils()->get_assignment_option(
				get_the_ID(),
				'time_duration',
				array(
					'time'  => '',
					'value' => 0,
				)
			);

			$total_mark        = tutor_utils()->get_assignment_option( get_the_ID(), 'total_mark' );
			$pass_mark         = tutor_utils()->get_assignment_option( get_the_ID(), 'pass_mark' );
			$file_upload_limit = tutor_utils()->get_assignment_option( get_the_ID(), 'upload_file_size_limit' );

			global $post;
			$assignment_created_time = strtotime( $post->post_date_gmt );
			$time_duration_in_sec    = 0;
			if ( isset( $time_duration['value'] ) and isset( $time_duration['time'] ) ) {
				switch ( $time_duration['time'] ) {
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

			$time_duration_in_sec = $time_duration_in_sec * $time_duration['value'];
			$remaining_time       = $assignment_created_time + $time_duration_in_sec;
			$now                  = time();
			$remaining            = $now - $remaining_time;

			?>
		<?php if ( ! $submitted_assignment ) { ?>
		<div class="tutor-assignment-meta-info tutor-d-flex tutor-justify-content-between tutor-mt-24 tutor-mt-sm-32 tutor-py-16 tutor-py-sm-24">
			<div class="tutor-assignment-detail-info tutor-d-flex">
				<div class="tutor-assignment-duration">
					<span class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Duration:', 'tutor' ); ?></span>
					<span class="tutor-fs-6 tutor-fw-medium  tutor-color-black">
						<?php echo esc_html( $time_duration['value'] ? $time_duration['value'] . ' ' . $time_duration['time'] : __( 'No limit', 'tutor' ) ); ?>
					</span>
				</div>
				<div class="tutor-assignmetn-deadline">
					<span class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Deadline:', 'tutor' ); ?></span>
					<span class="tutor-fs-6 tutor-fw-medium  tutor-color-black">
						<?php
						if ( $time_duration['value'] != 0 ) {
							if ( $now > $remaining_time and $is_submitted == false ) {
								esc_html_e( 'Expired', 'tutor' );
							} else {
								echo esc_html( tutor_assignment_convert_seconds( $remaining ) );
							}
						} else {
							esc_html_e( 'N\\A', 'tutor' );
						}
						?>
					</span>
				</div>
			</div>
			<div class="tutor-assignment-detail-info tutor-d-flex">
				<div class="tutor-assignment-marks">
					<span class="tutor-fs-6 tutor-color-muted"><?php _e( 'Total Marks:', 'tutor' ); ?></span>
					<span class="tutor-fs-6 tutor-fw-medium  tutor-color-black"><?php echo $total_mark; ?></span>
				</div>
				<div class="tutor-assignmetn-pass-mark">
					<span class="tutor-fs-6 tutor-color-muted"><?php _e( 'Passing Mark:', 'tutor' ); ?></span>
					<span class="tutor-fs-6 tutor-fw-medium  tutor-color-black"><?php echo $pass_mark; ?></span>
				</div>
			</div>
		</div>
		<?php } ?>
		<?php
		/*
		*time_duration[value]==0 means no limit
		*if have unlimited time then no msg should
		*appear
		*/
		if ( $time_duration['value'] != 0 ) :
			if ( $now > $remaining_time and $is_submitted == false ) :
				?>
			<div class="quiz-flash-message tutor-mt-24 tutor-mt-sm-32">
				<div class="tutor-quiz-warning-box time-over tutor-d-flex tutor-align-items-center tutor-justify-content-between">
					<div class="flash-info tutor-d-flex tutor-align-items-center">
						<span class="tutor-icon-cross-cricle-filled tutor-color-design-danger tutor-mr-8"></span>
						<span class="tutor-fs-7 tutor-color-danger-100">
							<?php _e( 'You have missed the submission deadline. Please contact the instructor for more information.', 'tutor' ); ?>
						</span>
					</div>
				</div>
			</div>
				<?php
			endif;
		endif;
		?>
		<?php if ( ! $is_submitting && ! $submitted_assignment ) { ?>
		<div class="tutor-time-out-assignment-details tutor-assignment-border-bottom tutor-pb-48 tutor-pb-sm-72">
			<div class="tutor-to-assignment tutor-pt-32 tutor-pt-sm-40 has-show-more">

				<div class="tutor-to-title tutor-fs-6 tutor-fw-medium tutor-color-black">
					<?php _e( 'Description', 'tutor' ); ?>
				</div>

				<div class="tutor-to-body tutor-fs-6 tutor-color-black-60 tutor-pt-12 tutor-entry-content">
					<?php the_content(); ?>
				</div>

			</div>
		</div>
		<?php } ?>
		<?php
		$assignment_attachments = maybe_unserialize( get_post_meta( get_the_ID(), '_tutor_assignment_attachments', true ) );
		if ( tutor_utils()->count( $assignment_attachments ) ) {
			?>
			<div class="tutor-assignment-attachments tutor-pt-40">
				<span class="tutor-fs-6 tutor-fw-medium tutor-color-black">
					<?php esc_html_e( 'Attachments', 'tutor' ); ?>
				</span>
				<div class="tutor-container tutor-pt-16">
					<div class="tutor-row tutor-gy-3">
					<?php if ( is_array( $assignment_attachments ) && count( $assignment_attachments ) ) : ?>
						<?php
						foreach ( $assignment_attachments as $attachment_id ) :
							$attachment_name = get_post_meta( $attachment_id, '_wp_attached_file', true );
							$attachment_name = substr( $attachment_name, strrpos( $attachment_name, '/' ) + 1 );
							$file_size       = tutor_utils()->get_attachment_file_size( $attachment_id );
							?>
							<div class="tutor-instructor-card tutor-col-sm-5 tutor-py-16 tutor-mr-12 tutor-ml-3">
								<div class="tutor-icard-content">
									<div class="tutor-fs-6 tutor-color-black-70">
									<a href="<?php echo esc_url( wp_get_attachment_url( $attachment_id ) ); ?>" target="_blank" download>
										<?php echo esc_html( $attachment_name ); ?>
									</a>
									</div>
									<div class="tutor-fs-7">
										<?php esc_html_e( 'Size: ', 'tutor' ); ?>
										<?php echo esc_html( $file_size ? $file_size . 'KB' : '' ); ?>
									</div>
								</div>
								<div class="tutor-avatar tutor-is-xs flex-center">
									<a href="<?php echo esc_url( wp_get_attachment_url( $attachment_id ) ); ?>" target="_blank" download>
										<span class="tutor-icon-download-line"></span>
									</a>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
					</div>
				</div>
			</div>
			<?php
		}

		if ( ( $is_submitting || isset( $_GET['update-assignment'] ) ) && ( $remaining_time > $now || $time_duration['value'] == 0 ) ) {

			?>

			<div class="tutor-assignment-submission tutor-assignment-border-bottom tutor-pb-48 tutor-pb-sm-72">
				<form action="" method="post" id="tutor_assignment_submit_form" enctype="multipart/form-data">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
					<input type="hidden" value="tutor_assignment_submit" name="tutor_action" />
					<input type="hidden" value="<?php echo tutor()->current_url; ?>" name="_wp_http_referer" />
					<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">

					<?php $allowed_upload_files = (int) tutor_utils()->get_assignment_option( get_the_ID(), 'upload_files_limit' ); ?>
					<div class="tutor-assignment-body tutor-pt-32 tutor-pt-sm-40 has-show-more">
						<div class="tutor-to-title tutor-fs-6 tutor-fw-medium tutor-color-black">
							<?php _e( 'Assignment Submission', 'tutor' ); ?>
						</div>
						<div class="tutor-fs-7 tutor-color-black-60 tutor-pt-16 tutor-pt-sm-32">
							<?php _e( 'Assignment answer form', 'tutor' ); ?>
						</div>
						<div class="tutor-assignment-text-area tutor-pt-20">
							<!-- <textarea  name="assignment_answer" class="tutor-form-control"></textarea> -->
							<?php
								$assignment_comment_id = isset( $_GET['update-assignment'] ) ? sanitize_text_field( $_GET['update-assignment'] ) : 0;
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

						<?php if ( $allowed_upload_files ) { ?>
							<div class="tutor-assignment-attachment tutor-mt-32 tutor-py-20 tutor-px-16 tutor-py-sm-32 tutor-px-sm-32">
								<div class="tutor-fs-7 tutor-color-black-60">
									<?php _e( "Attach assignment files (Max: $allow_to_upload file)", 'tutor' ); ?>
								</div>
								<div class="tutor-attachment-files tutor-mt-12">
									<div class="tutor-assignment-upload-btn tutor-mt-12 tutor-mt-md-0">
										<form>
											<label for="tutor-assignment-file-upload">
												<input type="file" id="tutor-assignment-file-upload" name="attached_assignment_files[]" multiple>
												<a class="tutor-btn tutor-btn-primary tutor-btn-md">
													<?php _e( 'Choose file', 'tutor' ); ?>
												</a>
											</label>
											<input type="hidden" name="tutor_assignment_upload_limit" value="<?php echo $file_upload_limit * 1000000?>">
										</form>
									</div>
									<div class="tutor-input-type-size">
										<p class="tutor-fs-7 tutor-color-black-60">
											<?php _e( 'File Support: ', 'tutor' ); ?>
											<span class="tutor-color-black">
												<?php esc_html_e( 'Any standard Image, Document, Presentation, Sheet, PDF or Text file is allowed', 'tutor' ); ?>
											</span>
										</p>
										<p class="tutor-fs-7 tutor-color-black-60 tutor-mt-7">
											<?php _e( 'Total File Size: Max', 'tutor' ); ?>
											<span class="tutor-color-black">
												<?php echo $file_upload_limit; ?>
												<?php _e( 'MB', 'tutor' ); ?>
											</span>
										</p>
									</div>
								</div>
								<!-- uploaded attachment by students -->
								<div class="tutor-container tutor-pt-16 tutor-update-assignment-attachments">
									<div class="tutor-row tutor-gy-3" id="tutor-student-assignment-edit-file-preview">
									<?php
										$submitted_attachments = get_comment_meta( $assignment_comment_id, 'uploaded_attachments' );
									if ( is_array( $submitted_attachments ) && count( $submitted_attachments ) ) :
										foreach ( $submitted_attachments as $attach ) :
											$attachments = json_decode( $attach );
											?>
											<?php foreach ( $attachments as $attachment ) : ?>
												<div class="tutor-instructor-card tutor-col-sm-5 tutor-py-16 tutor-mr-16">
													<div class="tutor-icard-content">
														<div class="tutor-fs-6 tutor-color-black-70">
															<?php echo esc_html( $attachment->name ); ?>
														</div>
														<div class="tutor-fs-7">Size: 230KB;</div>
													</div>
													<div class="tutor-attachment-file-close tutor-avatar tutor-is-xs flex-center">
														<a href="<?php echo esc_url( $attachment->url ); ?>" data-id="<?php echo esc_attr( $assignment_comment_id ); ?>" data-name="<?php echo esc_attr( $attachment->name ); ?>" target="_blank">
															<span class="tutor-icon-cross-filled color-design-brand"></span>
														</a>
													</div>
												</div>
											<?php endforeach; ?>
										<?php endforeach; ?>
										<?php endif; ?>
									</div>
								</div>
								<!-- uploaded attachment by students end -->
							</div>

						<?php } ?>
						<div class="tutor-assignment-submit-btn tutor-mt-60">
							<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-lg" id="tutor_assignment_submit_btn">
								<?php esc_html_e( 'Submit Assignment', 'tutor' ); ?>
							</button>
						</div>
					</div>
				</form>
			</div> <!-- assignment-submission -->
			<div class="tutor-assignment-description-details tutor-assignment-border-bottom tutor-pb-32 tutor-pb-sm-44">
				<div class="tutor-pt-40 tutor-pt-sm-60 <?php echo esc_attr( strlen( $s_content ) > 500 ? 'tutor-ad-body has-show-more' : '' ); ?>" id="content-section">
					<div class="tutor-fs-6 tutor-fw-medium tutor-color-black">
						<?php _e( 'Description', 'tutor' ); ?>
					</div>
					<div class="tutor-fs-6 tutor-color-black-60 tutor-pt-12 tutor-entry-content" id="short-text">
						<?php
						if ( strlen( $s_content ) > 500 ) {
							echo wp_kses_post( substr_replace( $s_content, '...', 500 ) );
						} else {
							echo wp_kses_post( $s_content );
						}
						?>
						<span id="dots"></span>
					</div>
					<?php if ( strlen( $s_content ) > 500 ) : ?>
						<div class="tutor-fs-6 tutor-color-black-60 tutor-pt-12 tutor-entry-content" id="full-text">
							<?php
								echo wp_kses_post( $s_content );
							?>
						</div>
						<div class="tutor-show-more-btn tutor-pt-12">
							<button class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-lg" id="showBtn">
								<span class="btn-icon tutor-icon-plus-filled tutor-color-design-brand" id="no-icon"></span>
								<span class="tutor-color-black"><?php esc_html_e( 'Show More', 'tutor' ); ?></span>
							</button>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( isset( $next_prev_content_id->next_id ) && '' !== $next_prev_content_id->next_id ) : ?>
			<div class="tutor-assignment-footer tutor-d-flex tutor-justify-content-end tutor-pt-32 tutor-pt-sm-44">
				<a href="<?php echo esc_url( get_permalink( $next_prev_content_id->next_id ) ); ?>" class="tutor-btn tutor-btn-disable-outline tutor-no-hover tutor-btn-lg tutor-mt-md-0 tutor-mt-12">
					<?php esc_html_e( 'Skip To Next', 'tutor' ); ?>
				</a>
			</div>
			<?php endif; ?>
			<?php
		} else {

			/**
			 * If assignment submitted
			 */
			if ( $submitted_assignment ) {
				$is_reviewed_by_instructor = get_comment_meta( $submitted_assignment->comment_ID, 'evaluate_time', true );


					$assignment_id = $submitted_assignment->comment_post_ID;
					$submit_id     = $submitted_assignment->comment_ID;

					$max_mark   = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'total_mark' );
					$pass_mark  = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'pass_mark' );
					$given_mark = get_comment_meta( $submitted_assignment->comment_ID, 'assignment_mark', true );
				?>
					<div class="tutor-assignment-result-table tutor-mt-32 tutor-mb-40">
						<div class="tutor-ui-table-wrapper">
							<table class="tutor-ui-table tutor-ui-table-responsive my-quiz-attempts">
								<thead class="tutor-fs-7 tutor-color-black-60">
									<tr>
										<th>
											<span>
												<?php _e( 'Date', 'tutor' ); ?>
											</span>
										</th>
										<th>
											<span>
												<?php _e( 'Total Marks', 'tutor' ); ?>
											</span>
										</th>
										<th>
											<span>
												<?php _e( 'Pass Marks', 'tutor' ); ?>
											</span>
										</th>
										<th>
											<span>
												<?php _e( 'Earned Marks', 'tutor' ); ?>
											</span>
										</th>
										<th>
											<span>
												<?php _e( 'Result', 'tutor' ); ?>
											</span>
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td data-th="Date" class="date">
											<div class="td-statement-info">
												<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
													<?php esc_html_e( tutor_utils()->convert_date_into_wp_timezone( $submitted_assignment->comment_date ) ) ?>
												</span>
											</div>
										</td>
										<td data-th="Total Marks" class="total-marks">
											<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
												<?php esc_html_e( $max_mark, 'tutor' ); ?>
											</span>
										</td>
										<td data-th="Pass Marks" class="pass-marks">
											<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
												<?php esc_html_e( $pass_mark, 'tutor' ); ?>
											</span>
										</td>
										<td data-th="Earned Marks" class="earned-marks">
											<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
												<?php esc_html_e( $given_mark, 'tutor' ); ?>
											</span>
										</td>
										<td data-th="Result" class="result">
											<?php
											if ( $is_reviewed_by_instructor ) {
												if ( $given_mark >= $pass_mark ) {
													?>
												<span class="tutor-badge-label label-success">
													<?php _e( 'Passed', 'tutor' ); ?>
												</span>
													<?php
												} else {
													?>
												<span class="tutor-badge-label label-warning">
													<?php _e( 'Failed', 'tutor' ); ?>
												</span>
													<?php
												}
											}
											?>
											<?php
											if ( ! $is_reviewed_by_instructor ) {
												?>
											<span class="tutor-badge-label label-danger">
												<?php _e( 'Pending', 'tutor' ); ?>
											</span>
											<?php } ?>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div> <!-- assignment-result-table -->


				<?php
				$instructor_note = get_comment_meta( $submitted_assignment->comment_ID, 'instructor_note', true );
				if ( !empty($instructor_note) && $is_reviewed_by_instructor ) {
					?>
				<div class="tutor-instructor-note tutor-my-32 tutor-py-20 tutor-px-24 tutor-py-sm-32 tutor-px-sm-36">
					<div class="tutor-in-title tutor-fs-6 tutor-fw-medium tutor-color-black">
					<?php _e( 'Instructor Note', 'tutor' ); ?>
					</div>
					<div class="tutor-in-body tutor-fs-6 tutor-color-black-60 tutor-pt-12 tutor-pt-sm-16">
					<?php echo nl2br( get_comment_meta( $submitted_assignment->comment_ID, 'instructor_note', true ) ); ?>
					</div>
				</div>
				<?php } ?>

				<?php
					/**
					 * If user not submitted assignment and assignment expired
					 * then show expire message
					 *
					 * @since v2.0.0
					 */
				if ( ! $is_submitted && 0 != $time_duration['value'] && ( $now > $remaining_time ) ) :
					?>
						<div class="tutor-mb-40">
						<?php
						$alert_template = tutor()->path . 'templates/global/alert.php';
						if ( file_exists( $alert_template ) ) {
							tutor_load_template_from_custom_path(
								$alert_template,
								array(
									'alert_class' => 'tutor-alert tutor-danger',
									'message'     => __( 'You have missed the submission deadline. Please contact the instructor for more information.', 'tutor_pro' ),
									'icon'        => ' tutor-icon-cross-circle-outline-filled',
								)
							);
						}
						?>
						</div>
					<?php endif; ?>

				<div class="tutor-assignment-details tutor-assignment-border-bottom tutor-pb-48 tutor-pb-sm-72">
					<div class="tutor-ar-body tutor-pt-24 tutor-pb-40 tutor-px-16 tutor-px-md-32">
						<div class="tutor-ar-header tutor-d-flex tutor-justify-content-between tutor-align-items-center">
							<div class="tutor-ar-title tutor-fs-6 tutor-fw-medium tutor-color-black">
								<?php esc_html_e( 'Your Assignment', 'tutor' ); ?>
							</div>
							<?php
								$evaluated = Assignments::is_evaluated( $post_id );
							if ( ! $evaluated && ( $remaining_time > $now || $time_duration['value'] == 0 ) ) :
								?>
								<div class="tutor-ar-btn">
									<a href="<?php echo esc_url( add_query_arg( 'update-assignment', $submitted_assignment->comment_ID ) ); ?>" class="tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-sm">
										<?php esc_html_e( 'Edit', 'tutor' ); ?>
									</a>
								</div>
							<?php endif; ?>
						</div>
						<div class="tutor-fs-6 tutor-color-black-60 tutor-pt-16 tutor-entry-content">
							<?php echo nl2br( stripslashes( $submitted_assignment->comment_content ) ); ?>
						</div>
						<?php
							$attached_files = get_comment_meta( $submitted_assignment->comment_ID, 'uploaded_attachments', true );
						if ( $attached_files ) {
							$attached_files = json_decode( $attached_files, true );

							if ( tutor_utils()->count( $attached_files ) ) {
								?>
									<div class="tutor-attachment-files submited-files tutor-d-flex tutor-mt-20 tutor-mt-sm-40">
									<?php
										$upload_dir     = wp_get_upload_dir();
										$upload_baseurl = trailingslashit( tutor_utils()->array_get( 'baseurl', $upload_dir ) );

									foreach ( $attached_files as $attached_file ) {
										?>
												<div class="tutor-instructor-card">
													<div class="tutor-icard-content">
														<div class="tutor-fs-6 tutor-color-black-70">
													<?php echo tutor_utils()->array_get( 'name', $attached_file ); ?>
														</div>
														<div class="tutor-fs-7">Size: <?php echo tutor_utils()->array_get( 'size', $attached_file ); ?></div>
													</div>
													<div class="tutor-avatar tutor-is-xs flex-center">
														<a download href="<?php echo $upload_baseurl . tutor_utils()->array_get( 'uploaded_path', $attached_file ); ?>" target="_blank">
															<span class="tutor-icon-download-line tutor-color-design-brand"></span>
														</a>
													</div>
												</div>
											<?php
									}
									?>
									</div>
									<?php
							}
						}
						?>
					</div>
				</div>

				<div class="tutor-assignment-description-details tutor-assignment-border-bottom tutor-pb-32 tutor-pb-sm-44">
					<div class="tutor-pt-40 tutor-pt-sm-60 <?php echo esc_attr( strlen( $s_content ) > 500 ? 'tutor-ad-body has-show-more' : '' ); ?>" id="content-section">
						<div class="tutor-fs-6 tutor-fw-medium tutor-color-black">
							<?php _e( 'Description', 'tutor' ); ?>
						</div>
						<div class="tutor-fs-6 tutor-color-black-60 tutor-pt-12 tutor-entry-content" id="short-text">
							<?php
							if ( strlen( $s_content ) > 500 ) {
								echo wp_kses_post( substr_replace( $s_content, '...', 500 ) );
							} else {
								echo wp_kses_post( $s_content );
							}
							?>
							<span id="dots"></span>
						</div>
						<?php if ( strlen( $s_content ) > 500 ) : ?>
							<div class="tutor-fs-6 tutor-color-black-60 tutor-pt-12 tutor-entry-content" id="full-text">
								<?php
									echo wp_kses_post( $s_content );
								?>
							</div>
							<div class="tutor-show-more-btn tutor-pt-12">
								<button class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-lg" id="showBtn">
									<span class="btn-icon tutor-icon-plus-filled tutor-color-design-brand" id="no-icon"></span>
									<span class="tutor-color-black"><?php esc_html_e( 'Show More', 'tutor' ); ?></span>
								</button>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php if ( isset( $next_prev_content_id->next_id ) && '' !== $next_prev_content_id->next_id ) : ?>
				<div class="tutor-assignment-footer tutor-pt-32 tutor-pt-sm-44">
					<a class="tutor-btn tutor-btn-primary tutor-btn-lg" href="<?php echo esc_url( get_the_permalink( $next_prev_content_id->next_id ) ); ?>">
						<?php esc_html_e( 'Continue Lesson', 'tutor' ); ?>
					</a>
				</div>
				<?php endif; ?>
				<?php
			} else {
				?>
				<div class="tutor-assignment-footer tutor-pt-32 tutor-pt-sm-44">
					<div class="tutor-assignment-footer-btn tutor-d-flex tutor-justify-content-between">
						<form action="" method="post" id="tutor_assignment_start_form">
						<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
						<input type="hidden" value="tutor_assignment_start_submit" name="tutor_action" />
						<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">
							<button type="submit" class="tutor-btn tutor-btn-primary
							<?php
							if ( $time_duration['value'] != 0 ) {
								if ( $now > $remaining_time ) {
									echo 'tutor-btn-disable tutor-no-hover'; }
							}
							?>
							tutor-btn-lg" id="tutor_assignment_start_btn"
				<?php
				if ( $time_duration['value'] != 0 ) {
					if ( $now > $remaining_time ) {
						echo 'disabled'; }
				}
				?>
>
								<?php esc_html_e( 'Start Assignment Submit', 'tutor' ); ?>
							</button>
						</form>

						<?php if ( isset( $next_prev_content_id->next_id ) && 0 !== $next_prev_content_id->next_id ) : ?>
							<a href="<?php echo esc_url( get_permalink( $next_prev_content_id->next_id ) ); ?>" class="tutor-btn tutor-btn-disable-outline tutor-no-hover tutor-btn-lg tutor-mt-md-0 tutor-mt-12">
							<?php esc_html_e( 'Skip To Next', 'tutor' ); ?>
						</a>
						<?php endif; ?>
					</div>
				</div>
				<?php
			}
		}
		?>
	</div>
</div>

<?php do_action( 'tutor_assignment/single/after/content' ); ?>