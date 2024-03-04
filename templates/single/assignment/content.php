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

// Get the comment.
$post_id              = get_the_ID(); //phpcs:ignore
$user_id              = get_current_user_id();
$user_data            = get_userdata( $user_id );
$assignment_comment   = tutor_utils()->get_single_comment_user_post_id( $post_id, $user_id );
$submitted_assignment = tutor_utils()->is_assignment_submitted( get_the_ID() );

if ( false != $assignment_comment ) {
	$submitted                                = $assignment_comment->comment_approved;
	'submitted' == $submitted ? $is_submitted = true : '';
}

// Get the ID of this content and the corresponding course.
$course_content_id = get_the_ID();
$course_id         = tutor_utils()->get_course_id_by_subcontent( $course_content_id );

// Get total content count.
$course_stats = tutor_utils()->get_course_completed_percent( $course_id, 0, true );

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

	$diff  = $dt1->diff( $dt2 );
	$days  = $diff->days;
	$hours = $diff->h;

	return $days . ' ' . __( 'Days', 'tutor' ) . ', ' . $hours . ' ' . __( 'Hours', 'tutor' );
}

$next_prev_content_id = tutor_utils()->get_course_prev_next_contents_by_id( $post_id );
$content              = get_the_content();
$s_content            = $content;
$allow_to_upload      = (int) tutor_utils()->get_assignment_option( $post_id, 'upload_files_limit' );
$course_id            = tutor_utils()->get_course_id_by( 'lesson', get_the_ID() );

$upload_dir     = wp_get_upload_dir();
$upload_baseurl = trailingslashit( $upload_dir['baseurl'] ?? '' );
$upload_basedir = trailingslashit( $upload_dir['basedir'] ?? '' );
?>

<?php do_action( 'tutor_assignment/single/before/content' ); ?>

<?php tutor_load_template( 'single.common.header', array( 'course_id' => $course_id ) ); ?>

<div class="tutor-course-topic-single-body">
	<div class="tutor-quiz-wrapper tutor-d-flex tutor-justify-center tutor-mt-36 tutor-pb-80">
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

				if ( isset( $time_duration['value'] ) && isset( $time_duration['time'] ) ) {
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

				$time_duration_in_sec = $time_duration_in_sec * (int) $time_duration['value'];
				$remaining_time       = $assignment_created_time + $time_duration_in_sec;
				$now                  = time();
				$remaining            = $now - $remaining_time;
				?>

			<?php if ( ! $submitted_assignment ) : ?>
				<div class="tutor-assignment-meta-info tutor-d-flex tutor-justify-between tutor-mt-24 tutor-mt-sm-32 tutor-py-16 tutor-py-sm-24">
					<div class="tutor-assignment-detail-info tutor-d-flex">
						<div class="tutor-assignment-duration">
							<span class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Duration:', 'tutor' ); ?></span>
							<span class="tutor-fs-6 tutor-fw-medium  tutor-color-black">
								<?php echo esc_html( $time_duration['value'] ? $time_duration['value'] . ' ' . __( $time_duration['time'], 'tutor' ) : __( 'No limit', 'tutor' ) ); //phpcs:ignore ?>
							</span>
						</div>
						<div class="tutor-assignmetn-deadline">
							<span class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Deadline:', 'tutor' ); ?></span>
							<span class="tutor-fs-6 tutor-fw-medium  tutor-color-black">
								<?php
								if ( 0 != $time_duration['value'] ) {
									if ( $now > $remaining_time && false == $is_submitted ) {
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
							<span class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Total Marks:', 'tutor' ); ?></span>
							<span class="tutor-fs-6 tutor-fw-medium  tutor-color-black"><?php echo esc_html( $total_mark ); ?></span>
						</div>
						<div class="tutor-assignmetn-pass-mark">
							<span class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Passing Mark:', 'tutor' ); ?></span>
							<span class="tutor-fs-6 tutor-fw-medium  tutor-color-black"><?php echo esc_html( $pass_mark ); ?></span>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php
			/**
			 * Time_duration[value]==0 means no limit
			 * if have unlimited time then no msg should appear
			 */
			if ( ( 0 != $time_duration['value'] ) && ( $now > $remaining_time && false == $is_submitted ) ) :
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
				<div class="tutor-time-out-assignment-details tutor-assignment-border-bottom tutor-pb-48 tutor-pb-sm-72">
					<div class="tutor-to-assignment tutor-pt-32 tutor-pt-sm-40">
						<div class="tutor-to-title tutor-fs-6 tutor-fw-medium tutor-color-black">
							<?php esc_html_e( 'Description', 'tutor' ); ?>
						</div>
						<div class="tutor-to-body tutor-fs-6 tutor-color-secondary tutor-pt-12 tutor-entry-content">
							<?php the_content(); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php
				$assignment_attachments = maybe_unserialize( get_post_meta( get_the_ID(), '_tutor_assignment_attachments', true ) );
			if ( tutor_utils()->count( $assignment_attachments ) ) :
				?>
				<div class="tutor-assignment-attachments tutor-pt-40">
					<span class="tutor-fs-6 tutor-fw-medium tutor-color-black">
					<?php esc_html_e( 'Attachments', 'tutor' ); ?>
					</span>
					<div class="tutor-assignment-attachments-list tutor-pt-16">
					<?php if ( is_array( $assignment_attachments ) && count( $assignment_attachments ) ) : ?>
							<?php foreach ( $assignment_attachments as $attachment_id ) : ?>
								<?php
									$attachment_name = get_post_meta( $attachment_id, '_wp_attached_file', true );
									$attachment_name = substr( $attachment_name, strrpos( $attachment_name, '/' ) + 1 );
									$file_size       = tutor_utils()->get_readable_filesize( get_attached_file( $attachment_id ) );
								?>
								<div class="tutor-instructor-card tutor-col-sm-5 tutor-py-16 tutor-mr-12 tutor-ml-3">
									<div class="tutor-icard-content">
										<div class="tutor-fs-6 tutor-color-secondary">
											<a href="<?php echo esc_url( wp_get_attachment_url( $attachment_id ) ); ?>" target="_blank"
												download>
												<?php echo esc_html( $attachment_name ); ?>
											</a>
										</div>
										<div class="tutor-fs-7">
											<?php esc_html_e( 'Size: ', 'tutor' ); ?>
											<?php echo esc_html( $file_size ); ?>
										</div>
									</div>
									<div class="tutor-d-flex tutor-align-center">
										<a class="tutor-iconic-btn tutor-iconic-btn-outline" href="<?php echo esc_url( wp_get_attachment_url( $attachment_id ) ); ?>" target="_blank">
											<span class="tutor-icon-download" area-hidden="true"></span>
										</a>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ( $is_submitting || isset( $_GET['update-assignment'] ) ) && ( $remaining_time > $now || 0 == $time_duration['value'] ) ) : ?>
				<div class="tutor-assignment-submission tutor-assignment-border-bottom tutor-pb-48 tutor-pb-sm-72">
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

				<?php $has_show_more = strlen( $s_content ) > 500 ? true : false; ?>

				<?php if ( $s_content ) : ?>
					<div class="tutor-assignment-description-details tutor-assignment-border-bottom tutor-pb-32 tutor-pb-sm-44">
						<div id="content-section" class="tutor-pt-40 tutor-pt-sm-60<?php echo esc_attr( $has_show_more ? ' tutor-toggle-more-content tutor-toggle-more-collapsed' : '' ); ?>"<?php echo $has_show_more ? ' data-tutor-toggle-more-content data-toggle-height="300" style="height: 300px;"' : ''; ?>>
							<div class="tutor-fs-6 tutor-fw-medium tutor-color-black">
								<?php esc_html_e( 'Description', 'tutor' ); ?>
							</div>
							<div class="tutor-entry-content tutor-fs-6 tutor-color-secondary tutor-pt-12">
								<?php echo apply_filters( 'the_content', $s_content );//phpcs:ignore ?>
							</div>
						</div>

						<?php if ( $has_show_more ) : ?>
							<a href="#" class="tutor-btn-show-more tutor-btn tutor-btn-ghost tutor-mt-32" data-tutor-toggle-more=".tutor-toggle-more-content">
								<span class="tutor-toggle-btn-icon tutor-icon tutor-icon-plus tutor-mr-8" area-hidden="true"></span>
								<span class="tutor-toggle-btn-text"><?php esc_html_e( 'Show More', 'tutor' ); ?></span>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $next_prev_content_id->next_id ) : ?>
					<div class="tutor-assignment-footer tutor-d-flex tutor-justify-end tutor-pt-32 tutor-pt-sm-44">
						<a href="<?php echo esc_url( get_permalink( $next_prev_content_id->next_id ) ); ?>" class="tuttor-assignment-skip-button tutor-btn tutor-btn-ghost tutor-mt-md-0 tutor-mt-12">
							<?php esc_html_e( 'Skip To Next', 'tutor' ); ?>
						</a>
					</div>
				<?php endif; ?>
			<?php else : ?>

				<?php if ( $submitted_assignment ) : ?>
					<?php
					$is_reviewed_by_instructor = get_comment_meta( $submitted_assignment->comment_ID, 'evaluate_time', true );

					$assignment_id = $submitted_assignment->comment_post_ID;
					$submit_id     = $submitted_assignment->comment_ID;

					$max_mark   = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'total_mark' );
					$pass_mark  = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'pass_mark' );
					$given_mark = get_comment_meta( $submitted_assignment->comment_ID, 'assignment_mark', true );
					?>
				<div class="tutor-assignment-result-table tutor-mt-32 tutor-mb-40">
					<div class="tutor-table-responsive">
						<table class="tutor-table my-quiz-attempts">
							<thead>
								<tr>
									<th>
										<?php esc_html_e( 'Date', 'tutor' ); ?>
									</th>
									<th>
										<?php esc_html_e( 'Total Marks', 'tutor' ); ?>
									</th>
									<th>
										<?php esc_html_e( 'Pass Marks', 'tutor' ); ?>
									</th>
									<th>
										<?php esc_html_e( 'Earned Marks', 'tutor' ); ?>
									</th>
									<th>
										<?php esc_html_e( 'Result', 'tutor' ); ?>
									</th>
								</tr>
							</thead>

							<tbody>
								<tr>
									<td>
										<?php echo esc_html( tutor_utils()->convert_date_into_wp_timezone( $submitted_assignment->comment_date ) ); ?>
									</td>

									<td>
										<?php esc_html_e( $max_mark, 'tutor' );//phpcs:ignore ?>
									</td>

									<td>
										<?php esc_html_e( $pass_mark, 'tutor' );//phpcs:ignore ?>
									</td>

									<td>
										<?php esc_html_e( $given_mark, 'tutor' );//phpcs:ignore ?>
									</td>

									<td>
										<?php if ( $is_reviewed_by_instructor ) : ?>
											<?php if ( $given_mark >= $pass_mark ) : ?>
												<span class="tutor-badge-label label-success">
													<?php esc_html_e( 'Passed', 'tutor' ); ?>
												</span>
											<?php else : ?>
												<span class="tutor-badge-label label-danger">
													<?php esc_html_e( 'Failed', 'tutor' ); ?>
												</span>
											<?php endif; ?>
										<?php endif; ?>

										<?php if ( ! $is_reviewed_by_instructor ) : ?>
											<span class="tutor-badge-label label-warning">
												<?php esc_html_e( 'Pending', 'tutor' ); ?>
											</span>
										<?php endif; ?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

					<?php
					$instructor_note = get_comment_meta( $submitted_assignment->comment_ID, 'instructor_note', true );
					if ( ! empty( $instructor_note ) && $is_reviewed_by_instructor ) :
						?>
					<div class="tutor-instructor-note tutor-my-32 tutor-py-20 tutor-px-24 tutor-py-sm-32 tutor-px-sm-36">
						<div class="tutor-in-title tutor-fs-6 tutor-fw-medium tutor-color-black">
							<?php esc_html_e( 'Instructor Note', 'tutor' ); ?>
						</div>
						<div class="tutor-in-body tutor-fs-6 tutor-color-secondary tutor-pt-12 tutor-pt-sm-16">
							<?php echo wp_kses_post( nl2br( get_comment_meta( $submitted_assignment->comment_ID, 'instructor_note', true ) ) ); ?>
						</div>
					</div>
				<?php endif; ?>

					<?php
					/**
					 * If user not submitted assignment and assignment expired
					 * then show expire message
					 *
					 * @since 2.0.0
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
									'icon'        => ' tutor-icon-circle-times-line',
								)
							);
						}
						?>
					</div>
				<?php endif; ?>

				<div class="tutor-assignment-details tutor-assignment-border-bottom tutor-pb-48 tutor-pb-sm-72">
					<div class="tutor-ar-body tutor-pt-24 tutor-pb-40 tutor-px-16 tutor-px-md-32">
						<div class="tutor-ar-header tutor-d-flex tutor-justify-between tutor-align-center">
							<div class="tutor-ar-title tutor-fs-6 tutor-fw-medium tutor-color-black">
								<?php esc_html_e( 'Your Assignment', 'tutor' ); ?>
							</div>

							<?php
							$result = Assignments::get_assignment_result( $post_id, $user_id );
							if ( in_array( $result, array( 'pending', 'fail' ), true ) && ( $remaining_time > $now || 0 == $time_duration['value'] ) ) :
								?>
								<div class="tutor-ar-btn">
									<a href="<?php echo esc_url( add_query_arg( 'update-assignment', $submitted_assignment->comment_ID ) ); ?>"
										class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
									<?php esc_html_e( 'Edit', 'tutor' ); ?>
									</a>
								</div>
							<?php endif; ?>
						</div>

						<div class="tutor-fs-6 tutor-color-secondary tutor-pt-16 tutor-entry-content">
							<?php echo wp_kses_post( nl2br( stripslashes( $submitted_assignment->comment_content ) ) ); ?>
						</div>

						<?php
							$attached_files = get_comment_meta( $submitted_assignment->comment_ID, 'uploaded_attachments', true );
						if ( $attached_files ) :
							?>
							<?php
							$attached_files = json_decode( $attached_files, true );
							if ( tutor_utils()->count( $attached_files ) ) :
								?>
									<div class="tutor-attachment-files submited-files tutor-d-flex tutor-flex-column tutor-mt-20 tutor-mt-sm-40">
									<?php
									foreach ( $attached_files as $attached_file ) :
										?>
											<div class="tutor-instructor-card tutor-mt-12">
												<div class="tutor-icard-content">
													<div class="tutor-fs-6 tutor-color-secondary">
												<?php echo esc_html( tutor_utils()->array_get( 'name', $attached_file ) ); ?>
													</div>
													<div class="tutor-fs-7"><?php esc_html_e( 'Size', 'tutor' ); ?>:
												<?php
													echo esc_html(
														tutor_utils()->get_readable_filesize( $upload_basedir . $attached_file['uploaded_path'] )
													);
												?>
													</div>
												</div>
												<div class="tutor-d-flex tutor-align-center">
													<a class="tutor-iconic-btn tutor-iconic-btn-outline" download
														href="<?php echo esc_url( $upload_baseurl . tutor_utils()->array_get( 'uploaded_path', $attached_file ) ); ?>"
														target="_blank">
														<span class="tutor-icon-download"></span>
													</a>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>

					<?php $has_show_more = strlen( $s_content ) > 500 ? true : false; ?>

					<?php if ( $s_content ) : ?>
						<div class="tutor-assignment-description-details tutor-assignment-border-bottom tutor-pb-32 tutor-pb-sm-44">
							<div id="content-section" class="tutor-pt-40 tutor-pt-sm-60<?php echo $has_show_more ? ' tutor-toggle-more-content tutor-toggle-more-collapsed' : ''; ?>"<?php echo $has_show_more ? ' data-tutor-toggle-more-content data-toggle-height="300" style="height: 300px;"' : ''; ?>>
								<div class="tutor-fs-6 tutor-fw-medium tutor-color-black">
									<?php esc_html_e( 'Description', 'tutor' ); ?>
								</div>
								<div class="tutor-entry-content tutor-fs-6 tutor-color-secondary tutor-pt-12">
									<?php echo apply_filters( 'the_content', $s_content ); //phpcs:ignore ?>
								</div>
							</div>
							<?php if ( $has_show_more ) : ?>
								<a href="#" class="tutor-btn-show-more tutor-btn tutor-btn-ghost tutor-mt-32" data-tutor-toggle-more=".tutor-toggle-more-content">
									<span class="tutor-toggle-btn-icon tutor-icon tutor-icon-plus tutor-mr-8" area-hidden="true"></span>
									<span class="tutor-toggle-btn-text"><?php esc_html_e( 'Show More', 'tutor' ); ?></span>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>


					<?php if ( $next_prev_content_id->next_id ) : ?>
						<div class="tutor-assignment-footer tutor-pt-32 tutor-pt-sm-44">
							<a class="tutor-btn tutor-btn-primary tutor-static-loader"
								href="<?php echo esc_url( get_the_permalink( $next_prev_content_id->next_id ) ); ?>">
								<?php esc_html_e( 'Continue Lesson', 'tutor' ); ?>
							</a>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<div class="tutor-assignment-footer tutor-pt-32 tutor-pt-sm-44">
						<div class="tutor-assignment-footer-btn tutor-d-flex tutor-justify-between">
							<form action="" method="post" id="tutor_assignment_start_form">
								<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
								<input type="hidden" value="tutor_assignment_start_submit" name="tutor_action" />
								<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">
								<button type="submit" id="tutor_assignment_start_btn" class="tutor-btn tutor-btn-primary"<?php echo ( ( 0 != $time_duration['value'] ) && ( $now > $remaining_time ) ) ? ' disabled' : ''; ?>>
									<?php esc_html_e( 'Start Assignment Submit', 'tutor' ); ?>
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
