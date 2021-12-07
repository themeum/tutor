<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if (!defined('ABSPATH'))
	exit;
global $post;
global $wpdb;
global $next_id;
global $assignment_submitted_id;
$is_submitted = false;
$is_submitting = tutor_utils()->is_assignment_submitting(get_the_ID());
//get the comment
$post_id = get_the_ID();
$user_id = get_current_user_id();
$assignment_comment = tutor_utils()->get_single_comment_user_post_id($post_id, $user_id);
//$submitted_assignment = tutor_utils()->get_assignment_submit_info( $assignment_submitted_id );
$submitted_assignment = tutor_utils()->is_assignment_submitted(get_the_ID());
if ($assignment_comment != false) {
	$submitted = $assignment_comment->comment_approved;
	$submitted == 'submitted' ? $is_submitted = true : '';
}

// Get the ID of this content and the corresponding course
$course_content_id = get_the_ID();
$course_id = tutor_utils()->get_course_id_by_subcontent($course_content_id);

// Get total content count
$course_stats = tutor_utils()->get_course_completed_percent($course_id, 0, true);

function tutor_assignment_convert_seconds($seconds){
	$dt1 = new DateTime("@0");
	$dt2 = new DateTime("@$seconds");
	return $dt1->diff($dt2)->format('%a Days, %h Hours');
}
?>

<?php do_action('tutor_assignment/single/before/content'); ?>

<div class="tutor-single-page-top-bar d-flex justify-content-between">
    <div class="tutor-topbar-left-item d-flex"> 
        <div class="tutor-topbar-item tutor-topbar-sidebar-toggle tutor-hide-sidebar-bar flex-center">
            <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">
                <span class="ttr-icon-light-left-line color-text-white flex-center"></span>
            </a>
        </div>
        <div class="tutor-topbar-item tutor-topbar-content-title-wrap flex-center">
			<span class="ttr-assignment-filled color-text-white tutor-mr-5"></span>
			<span class="text-regular-caption color-design-white">
				<?php 
					esc_html_e( 'Assignment: ', 'tutor' );
					the_title();
				?>
			</span>
        </div>
    </div>
    <div class="tutor-topbar-right-item d-flex align-items-center">
        <div class="tutor-topbar-assignment-details d-flex align-items-center">
            <?php
                do_action('tutor_course/single/enrolled/before/lead_info/progress_bar');
            ?>
            <div class="text-regular-caption color-design-white">
                <span class="tutor-progress-content color-primary-60">
                    <?php _e('Your Progress:', 'tutor'); ?>
                </span>
                <span class="text-bold-caption">
                    <?php echo $course_stats['completed_count']; ?>
                </span> 
                <?php _e('of ', 'tutor'); ?>
                <span class="text-bold-caption">
                    <?php echo $course_stats['total_count']; ?>
                </span>
                (<?php echo $course_stats['completed_percent'] .'%'; ?>)
            </div>
            <?php
                do_action('tutor_course/single/enrolled/after/lead_info/progress_bar');
            ?>
        </div>
        <div class="tutor-topbar-cross-icon flex-center">
            <?php $course_id = tutor_utils()->get_course_id_by('lesson', get_the_ID()); ?>
            <a href="<?php echo get_the_permalink($course_id); ?>">
                <span class="ttr-line-cross-line color-text-white flex-center"></span>
            </a>
        </div>
    </div>
</div>

<div class="tutor-mobile-top-navigation tutor-bs-d-block tutor-bs-d-sm-none tutor-my-20 tutor-mx-10">
    <div class="tutor-mobile-top-nav d-grid">
        <a href="<?php echo esc_url( get_the_permalink( isset( $previous_id ) ? $previous_id : '' ) ); ?>">
            <span class="tutor-top-nav-icon ttr-previous-line design-lightgrey"></span>
        </a>
        <div class="tutor-top-nav-title text-regular-body color-text-primary">
            <?php 
                the_title();
            ?>
        </div>
    </div>
</div>
<div class="tutor-quiz-wrapper tutor-quiz-wrapper d-flex justify-content-center tutor-mt-100 tutor-pb-100">
	<div id="tutor-assignment-wrap" class="tutor-quiz-wrap tutor-course-assignment-details tutor-submit-assignment  tutor-assignment-result-pending">	
		<div class="tutor-assignment-title text-medium-h4 color-text-primary">
			<?php the_title(); ?>
		</div>

		<?php
			$time_duration = tutor_utils()->get_assignment_option(get_the_ID(), 'time_duration', array('time'=>'', 'value'=>0));

			$total_mark = tutor_utils()->get_assignment_option(get_the_ID(), 'total_mark');
			$pass_mark = tutor_utils()->get_assignment_option(get_the_ID(), 'pass_mark');
			$file_upload_limit = tutor_utils()->get_assignment_option(get_the_ID(), 'upload_file_size_limit');

			global $post;
			$assignment_created_time = strtotime($post->post_date_gmt);
			$time_duration_in_sec = 0;
			if (isset($time_duration['value']) and isset($time_duration['time'])) {
				switch ($time_duration['time']) {
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
			$remaining_time = $assignment_created_time + $time_duration_in_sec;
			$now = time();
			$remaining= $now - $remaining_time;

		?>
		<?php if (!$submitted_assignment) { ?>
		<div class="tutor-assignment-meta-info d-flex justify-content-between tutor-mt-25 tutor-mt-sm-35 tutor-py-15 tutor-py-sm-22">
			<div class="tutor-assignment-detail-info d-flex">
				<div class="tutor-assignment-duration">
					<span class="text-regular-body color-text-hints"><?php _e('Duration:', 'tutor'); ?></span>
					<span class="text-medium-body color-text-primary">
						<?php echo $time_duration["value"] ? $time_duration["value"] . ' ' . $time_duration["time"] : __('No limit', 'tutor'); ?>
					</span>
				</div>
				<div class="tutor-assignmetn-deadline">
					<span class="text-regular-body color-text-hints"><?php _e('Deadline:', 'tutor'); ?></span>
					<span class="text-medium-body color-text-primary">
						<?php
							if ($time_duration['value'] != 0) {
								if ($now > $remaining_time and $is_submitted == false) { 
									_e('Expired', 'tutor');
								} else {
									echo tutor_assignment_convert_seconds($remaining);
								}
							} else {
								_e('N\\A', 'tutor'); 
							}
						?>
					</span>
				</div>
			</div>
			<div class="tutor-assignment-detail-info d-flex">
				<div class="tutor-assignment-marks">
					<span class="text-regular-body color-text-hints"><?php _e('Total Marks:', 'tutor'); ?></span>
					<span class="text-medium-body color-text-primary"><?php echo $total_mark; ?></span>
				</div>
				<div class="tutor-assignmetn-pass-mark">
					<span class="text-regular-body color-text-hints"><?php _e('Passing Mark:', 'tutor'); ?></span>
					<span class="text-medium-body color-text-primary"><?php echo $pass_mark; ?></span>
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
		if ($time_duration['value'] != 0) :
			if ($now > $remaining_time and $is_submitted == false) : ?>
			<div class="quiz-flash-message tutor-mt-25 tutor-mt-sm-35">
				<div class="tutor-quiz-warning-box time-over d-flex align-items-center justify-content-between">
					<div class="flash-info d-flex align-items-center">
						<span class="ttr-cross-cricle-filled color-design-danger tutor-mr-7"></span>
						<span class="text-regular-caption color-danger-100">
							<?php _e('You have missed the submission deadline. Please contact the instructor for more information.', 'tutor'); ?>
						</span>
					</div>
				</div>
			</div>
		<?php
			endif;
		endif;
		?>
		<?php if (!$is_submitting && !$submitted_assignment){ ?>
		<div class="tutor-time-out-assignment-details tutor-assignment-border-bottom tutor-pb-50 tutor-pb-sm-70">
			<div class="tutor-to-assignment tutor-pt-30 tutor-pt-sm-40 has-show-more">

				<div class="tutor-to-title text-medium-h6 color-text-primary">
					<?php _e('Description', 'tutor'); ?>
				</div>

				<div class="tutor-to-body text-regular-body color-text-subsued tutor-pt-12">
					<?php the_content(); ?>
				</div>

			</div>
		</div>
		<?php } ?>
		<?php
		$assignment_attachments = maybe_unserialize(get_post_meta(get_the_ID(), '_tutor_assignment_attachments', true));
		if (tutor_utils()->count($assignment_attachments)) {
			?>
			<div class="tutor-assignment-attachments">
				<h2><?php _e('Attachments', 'tutor'); ?></h2>
				<?php
				foreach ($assignment_attachments as $attachment_id) {
					if ($attachment_id) {
						$attachment_name =  get_post_meta($attachment_id, '_wp_attached_file', true);
						$attachment_name = substr($attachment_name, strrpos($attachment_name, '/') + 1);

						?>
						<p class="attachment-file-name">
							<a href="<?php echo wp_get_attachment_url($attachment_id); ?>" target="_blank">
								<i class="tutor-icon-attach"></i> <?php echo $attachment_name; ?>
							</a>
						</p>
						<?php
					}
				}
				?>
			</div>
			<?php
		}

		if ($is_submitting and ($remaining_time > $now or $time_duration['value'] == 0)) { ?>

			<div class="tutor-assignment-submission tutor-assignment-border-bottom tutor-pb-50 tutor-pb-sm-70">
				<form action="" method="post" id="tutor_assignment_submit_form" enctype="multipart/form-data">
					<?php wp_nonce_field(tutor()->nonce_action, tutor()->nonce); ?>
					<input type="hidden" value="tutor_assignment_submit" name="tutor_action" />
					<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">

					<?php $allowd_upload_files = (int) tutor_utils()->get_assignment_option(get_the_ID(), 'upload_files_limit'); ?>
					<div class="tutor-assignment-body tutor-pt-30 tutor-pt-sm-40 has-show-more">
						<div class="tutor-to-title text-medium-h6 color-text-primary">
							<?php _e('Assignment Submission', 'tutor'); ?>
						</div>
						<div class="text-regular-caption color-text-subsued tutor-pt-15 tutor-pt-sm-30">
						<?php _e('Assignment answer form', 'tutor'); ?>
						</div>
						<div class="tutor-assignment-text-area tutor-pt-20">
							<textarea  name="assignment_answer" class="tutor-form-control"></textarea>
						</div>

						<?php if ($allowd_upload_files) { ?>
							<div class="tutor-assignment-attachment tutor-mt-30 tutor-py-20 tutor-px-15 tutor-py-sm-30 tutor-px-sm-30">
								<div class="text-regular-caption color-text-subsued">
									<?php _e('Attach assignment files', 'tutor'); ?>
								</div>
								<div class="tutor-attachment-files tutor-mt-12">
									<div class="tutor-assignment-upload-btn tutor-mt-10 tutor-mt-md-0">
										<form>
											<label for="tutor-assignment-file-upload">
												<input type="file" id="tutor-assignment-file-upload" name="attached_assignment_files[]" multiple>
												<a class="tutor-btn tutor-btn-primary tutor-btn-md">
													<?php _e('Choose file', 'tutor'); ?>
												</a>
											</label>
										</form>
									</div>
									<div class="tutor-input-type-size">
										<p class="text-regular-small color-text-subsued">
											<?php _e('File Support: ', 'tutor'); ?>
											<span class="color-text-primary">
												<?php _e('jpg, .jpeg,. gif, or .png.', 'tutor'); ?>
											</span>
											<?php _e(' no text on the image.', 'tutor'); ?>
										</p>
										<p class="text-regular-small color-text-subsued tutor-mt-7">
											<?php _e('Total File Size: Max', 'tutor'); ?> 
											<span class="color-text-primary">
												<?php echo $file_upload_limit; ?>
												<?php _e('MB', 'tutor'); ?>
											</span>
										</p>
									</div>
								</div>
								<div class="tutor-asisgnment-upload-file-preview d-flex tutor-mt-20 tutor-mt-sm-30">
									
								</div>
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
			<div class="tutor-assignment-description-details tutor-assignment-border-bottom tutor-pb-30 tutor-pb-sm-45">
				<div class="tutor-ad-body tutor-pt-40 tutor-pt-sm-60 has-show-more" id="content-section">
					<div class="text-medium-h6 color-text-primary">
						<?php _e('Description', 'tutor'); ?>
					</div>
					<div class="text-regular-body color-text-subsued tutor-pt-12" id="short-text">
						<?php
							$content = get_the_content();
							$s_content = $content;
							echo substr_replace($s_content, "..." , 500);
						?>
						<span id="dots"></span>
					</div>
					<div class="text-regular-body color-text-subsued tutor-pt-12" id="full-text">
						<?php
							the_content();
						?>
					</div>
					<div class="tutor-show-more-btn tutor-pt-12">
						<button class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-lg" id="showBtn">
							<span class="btn-icon ttr-plus-filled color-design-brand" id="no-icon"></span>
							<span class="color-text-primary"><?php _e('Show More', 'tutot'); ?></span>
						</button>
					</div>
				</div>
			</div>

			<div class="tutor-assignment-footer d-flex justify-content-end tutor-pt-30 tutor-pt-sm-45">
				<button class="tutor-btn tutor-btn-disable-outline tutor-no-hover tutor-btn-lg tutor-mt-md-0 tutor-mt-10">
					<?php _e('Sorry I don’t Understand', 'tutot'); ?>
				</button>
			</div>

			<?php
		} else {

			
			if ($submitted_assignment) {
				$is_reviewed_by_instructor = get_comment_meta($submitted_assignment->comment_ID, 'evaluate_time', true);

				
					$assignment_id = $submitted_assignment->comment_post_ID;
					$submit_id = $submitted_assignment->comment_ID;

					$max_mark = tutor_utils()->get_assignment_option($submitted_assignment->comment_post_ID, 'total_mark');
					$pass_mark = tutor_utils()->get_assignment_option($submitted_assignment->comment_post_ID, 'pass_mark');
					$given_mark = get_comment_meta($submitted_assignment->comment_ID, 'assignment_mark', true);
			?>
					<div class="tutor-assignment-result-table tutor-mt-30 tutor-mb-40">
						<div class="tutor-ui-table-wrapper">
							<table class="tutor-ui-table tutor-ui-table-responsive my-quiz-attempts">
								<thead>
									<tr>
										<th>
										<span class="text-regular-small color-text-subsued">
											<?php _e('Date', 'tutor'); ?>
										</span>
										</th>
										<th>
										<span class="text-regular-small color-text-subsued">
											<?php _e('Total Marks', 'tutor'); ?>
										</span>
										</th>
										<th>
										<span class="text-regular-small color-text-subsued">
											<?php _e('Pass Marks', 'tutor'); ?>
										</span>
										</th>
										<th>
										<span class="text-regular-small color-text-subsued">
											<?php _e('Earned Marks', 'tutor'); ?>
										</span>
										</th>
										<th>
										<span class="text-regular-small color-text-subsued">
											<?php _e('Result', 'tutor'); ?>	
										</span>
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td data-th="Date" class="date">
											<div class="td-statement-info">
												<span class="text-medium-small color-text-primary">
													<?php esc_html_e( date('F j Y g:i a', strtotime( $submitted_assignment->comment_date ) ), 'tutor' ); ?>
												</span>
											</div>
										</td>
										<td data-th="Total Marks" class="total-marks">
											<span class="text-medium-caption color-text-primary">
												<?php esc_html_e( $max_mark, 'tutor' ); ?>
											</span>
										</td>
										<td data-th="Pass Marks" class="pass-marks">
											<span class="text-medium-caption color-text-primary">
												<?php esc_html_e( $pass_mark, 'tutor' ); ?>
											</span>
										</td>
										<td data-th="Earned Marks" class="earned-marks">
											<span class="text-medium-caption color-text-primary">
												<?php esc_html_e( $given_mark, 'tutor' ); ?>
											</span>
										</td>
										<td data-th="Result" class="result">
											<?php 
												if ($is_reviewed_by_instructor) {
												if ($given_mark >= $pass_mark) {
											?>
												<span class="tutor-badge-label label-success">
													<?php _e('Passed', 'tutor'); ?>
												</span>
											<?php
												} else {
											?>
												<span class="tutor-badge-label label-warning">
													<?php _e('Failed', 'tutor'); ?>
												</span>
											<?php } } ?>
											<?php 
												if (!$is_reviewed_by_instructor) { 
											?>
											<span class="tutor-badge-label label-danger">
												<?php _e('Pending', 'tutor'); ?>
											</span>
											<?php } ?>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div> <!-- assignment-result-table -->

				

				<?php 
					if ($is_reviewed_by_instructor) {
				?>
				<div class="tutor-instructor-note tutor-my-30 tutor-py-20 tutor-px-25 tutor-py-sm-30 tutor-px-sm-35">
					<div class="tutor-in-title text-medium-h6 color-text-primary">
						<?php _e('Instructor Note', 'tutor'); ?>
					</div>
					<div class="tutor-in-body text-regular-body color-text-subsued tutor-pt-10 tutor-pt-sm-18">
						<?php echo nl2br(get_comment_meta($submitted_assignment->comment_ID, 'instructor_note', true)) ?>
					</div>
				</div>
				<?php } ?>

				<div class="tutor-assignment-details tutor-assignment-border-bottom tutor-pb-50 tutor-pb-sm-70">
					<div class="tutor-ar-body tutor-pt-25 tutor-pb-40 tutor-px-15 tutor-px-md-30">
						<div class="tutor-ar-header d-flex justify-content-between align-items-center">
							<div class="tutor-ar-title text-medium-h6 color-text-primary">
								<?php _e('Your Assigment', 'tutor'); ?>
							</div>
							<div class="tutor-ar-btn">
							<button class="tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-sm">Edit</button>
							</div>
						</div>
						<div class="text-regular-body color-text-subsued tutor-pt-18">
							<?php echo nl2br(stripslashes($submitted_assignment->comment_content)); ?>
						</div>
						<?php
							$attached_files = get_comment_meta($submitted_assignment->comment_ID, 'uploaded_attachments', true);
							if ($attached_files) {
								$attached_files = json_decode($attached_files, true);
		
								if (tutor_utils()->count($attached_files)) {
						?>
						<div class="tutor-attachment-files submited-files d-flex tutor-mt-20 tutor-mt-sm-40">
							<?php
								$upload_dir = wp_get_upload_dir();
								$upload_baseurl = trailingslashit(tutor_utils()->array_get('baseurl', $upload_dir));
									foreach ($attached_files as $attached_file) {
								?>
										<div class="tutor-instructor-card">
											<div class="tutor-icard-content">
												<div class="text-regular-body color-text-title">
													<?php echo tutor_utils()->array_get('name', $attached_file); ?>
												</div>
												<div class="text-regular-small">Size: <?php echo tutor_utils()->array_get('size', $attached_file); ?></div>
											</div>
											<div class="tutor-attachment-file-close tutor-avatar tutor-is-xs flex-center">
												<a href="<?php echo $upload_baseurl . tutor_utils()->array_get('uploaded_path', $attached_file) ?>" target="_blank">
													<span class="ttr-download-line color-design-brand"></span>
												</a>
											</div>
										</div>
							<?php }  ?>
						</div>
						<?php } } ?>
					</div>
				</div>

				<div class="tutor-assignment-description-details tutor-assignment-border-bottom tutor-pb-30 tutor-pb-sm-45">
					<div class="tutor-ad-body tutor-pt-40 tutor-pt-sm-60 has-show-more" id="content-section">
						<div class="text-medium-h6 color-text-primary">
							<?php _e('Description', 'tutor'); ?>
						</div>
						<div class="text-regular-body color-text-subsued tutor-pt-12" id="short-text">
							<?php
								$content = get_the_content();
								$s_content = $content;
								echo substr_replace($s_content, "..." , 500);
							?>
							<span id="dots"></span>
						</div>
						<div class="text-regular-body color-text-subsued tutor-pt-12" id="full-text">
							<?php
								the_content();
							?>
						</div>
						<?php
							$content = get_the_content();
							if (strlen($content) !== 0){
						?>
						<div class="tutor-show-more-btn tutor-pt-12">
							<button class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-lg" id="showBtn">
								<span class="btn-icon ttr-plus-filled color-design-brand" id="no-icon"></span>
								<span class="color-text-primary"><?php _e('Show More', 'tutot'); ?></span>
							</button>
						</div>
						<?php } ?>
					</div>
				</div>

				<div class="tutor-assignment-footer tutor-pt-30 tutor-pt-sm-45">
					<a class="tutor-btn tutor-btn-primary tutor-btn-lg" href="<?php echo get_the_permalink($next_id); ?>">
						<?php _e( 'Continue Lesson', 'tutor' ); ?>
					</a>
				</div>

			<?php
			} else { ?>
				<div class="tutor-assignment-footer tutor-pt-30 tutor-pt-sm-45">
					<div class="tutor-assignment-footer-btn tutor-btn-group d-flex justify-content-between">
						<form action="" method="post" id="tutor_assignment_start_form">
						<?php wp_nonce_field(tutor()->nonce_action, tutor()->nonce); ?>
						<input type="hidden" value="tutor_assignment_start_submit" name="tutor_action" />
						<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">
							<button type="submit" class="tutor-btn tutor-btn-primary <?php if ($time_duration['value'] != 0) { if ($now > $remaining_time) {echo "tutor-btn-disable tutor-no-hover"; } } ?> tutor-btn-lg" id="tutor_assignment_start_btn" <?php if ($time_duration['value'] != 0) { if ($now > $remaining_time) {echo "disabled"; } } ?>>
								<?php _e('Start Assignment Submit', 'tutor'); ?>
							</button>
						</form>
						<button class="tutor-btn tutor-btn-disable-outline tutor-no-hover tutor-btn-lg tutor-mt-md-0 tutor-mt-10">
							<?php _e('Sorry I don’t Understand', 'tutor'); ?>
						</button>
					</div>
                </div>
		<?php
			}
		}
		?>
	</div>
</div>

<?php do_action('tutor_assignment/single/after/content'); ?>