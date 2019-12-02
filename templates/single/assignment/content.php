<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) )
	exit;
global $wpdb;
?>

<?php do_action('tutor_assignment/single/before/content'); ?>

    <div class="tutor-single-page-top-bar">
        <div class="tutor-topbar-item tutor-hide-sidebar-bar">
            <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar"><i class="tutor-icon-angle-left"></i> </a>
			<?php $course_id = get_post_meta(get_the_ID(), '_tutor_course_id_for_assignments', true); ?>
            <a href="<?php echo get_the_permalink($course_id); ?>" class="tutor-topbar-home-btn">
                <i class="tutor-icon-home"></i> <?php echo __('Go to Course Home', 'tutor') ; ?>
            </a>
        </div>
        <div class="tutor-topbar-item tutor-topbar-content-title-wrap">
			<?php
			tutor_utils()->get_lesson_type_icon(get_the_ID(), true, true);
			the_title(); ?>
        </div>
    </div>

    <div class="tutor-lesson-content-area">
        <div class="tutor-assignment-title">
            <h2><?php the_title(); ?></h2>
        </div>

        <div class="tutor-assignment-information">
			<?php
			$time_duration = tutor_utils()->get_assignment_option(get_the_ID(), 'time_duration');
			$total_mark = tutor_utils()->get_assignment_option(get_the_ID(), 'total_mark');
			$pass_mark = tutor_utils()->get_assignment_option(get_the_ID(), 'pass_mark');
			?>

            <ul>
                <li>
					<?php _e('Time Duration : ', 'tutor') ?>
                    <strong><?php echo $time_duration["value"] ? $time_duration["value"] . ' ' .$time_duration["time"] : __('No limit', 'tutor'); ?></strong>
                </li>
                <!--<li>
                    <?php /*_e('Time Remaining : ') */?>
                    <strong><?php /*echo "7 Days, 12 Hour"; */?></strong>
                </li>-->
                <li>
					<?php _e('Total Points : ', 'tutor') ?>
                    <strong><?php echo $total_mark; ?></strong>
                </li>
                <li>
					<?php _e('Minimum Pass Points : ', 'tutor') ?>
                    <strong><?php echo $pass_mark; ?></strong>
                </li>
            </ul>
        </div>

        <hr />

        <div class="tutor-assignment-content">
            <h2><?php _e('Description', 'tutor'); ?></h2>

			<?php the_content(); ?>
        </div>

		<?php
		$assignment_attachments = maybe_unserialize(get_post_meta(get_the_ID(),'_tutor_assignment_attachments', true));
		if (tutor_utils()->count($assignment_attachments)){
			?>
            <div class="tutor-assignment-attachments">
                <h2><?php _e('Attachments', 'tutor'); ?></h2>
				<?php
				foreach ($assignment_attachments as $attachment_id){
					if ($attachment_id) {

						$attachment_name =  get_post_meta( $attachment_id, '_wp_attached_file', true );
						$attachment_name = substr($attachment_name, strrpos($attachment_name, '/')+1);
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

		$is_submitting = tutor_utils()->is_assignment_submitting(get_the_ID());
		if ($is_submitting){
			?>

            <div class="tutor-assignment-submit-form-wrap">
                <h2><?php _e('Assignment answer form', 'tutor'); ?></h2>

                <form action="" method="post" id="tutor_assignment_submit_form" enctype="multipart/form-data">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
                    <input type="hidden" value="tutor_assignment_submit" name="tutor_action"/>
                    <input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">

					<?php $allowd_upload_files = (int) tutor_utils()->get_assignment_option(get_the_ID(), 'upload_files_limit'); ?>

                    <div class="tutor-form-group">
                        <p><?php _e('Write your answer briefly', 'tutor'); ?></p>
                        <textarea name="assignment_answer"></textarea>
                    </div>

                    <div id="form_validation_response"></div>

					<?php if ($allowd_upload_files){ ?>
                        <p><?php _e('Attach assignment files', 'tutor'); ?></p>
                        <div class="tutor-assignment-attachment-upload-wrap">
							<?php
							for ($item = 1; $item <= $allowd_upload_files; $item++){
								?>
                                <div class="tutor-form-group">
                                    <label for="tutor-assignment-input-<?php echo $item; ?>"><i class="tutor-icon-upload-file"></i><span><?php _e('Upload file', 'tutor'); ?></span></label>
                                    <input class="tutor-assignment-file-upload"  id="tutor-assignment-input-<?php echo $item; ?>" type="file" name="attached_assignment_files[]">
                                </div>
								<?php
							}
							?>
                        </div>
						<?php
					}
					?>
                    <div class="tutor-assignment-submit-btn-wrap">
                        <button type="submit" class="tutor-button tutor-success" id="tutor_assignment_submit_btn"> <?php _e('Submit Assignment', 'tutor');
							?> </button>
                    </div>
                </form>

            </div>

			<?php
		}else{

			$submitted_assignment = tutor_utils()->is_assignment_submitted(get_the_ID());
			if ($submitted_assignment){
				$is_reviewed_by_instructor = get_comment_meta($submitted_assignment->comment_ID, 'evaluate_time', true);

				if ($is_reviewed_by_instructor){
					$assignment_id = $submitted_assignment->comment_post_ID;
					$submit_id = $submitted_assignment->comment_ID;

					$max_mark = tutor_utils()->get_assignment_option($submitted_assignment->comment_post_ID, 'total_mark');
					$pass_mark = tutor_utils()->get_assignment_option($submitted_assignment->comment_post_ID, 'pass_mark');
					$given_mark = get_comment_meta($submitted_assignment->comment_ID, 'assignment_mark', true);
					?>

					<?php ob_start(); ?>

                    <div class="assignment-result-wrap">
                        <h4><?php echo sprintf(__('You received %s points out of %s', 'tutor'), "<span class='received-marks'>{$given_mark}</span>", "<span class='out-of-marks'>{$max_mark}</span>") ?></h4>
                        <h4 class="submitted-assignment-grade">
							<?php _e('Your Grade is ', 'tutor'); ?>
							<?php if ($given_mark >= $pass_mark){
								?>
                                <span class="submitted-assignment-grade-pass">
                                    <?php _e('Passed', 'tutor'); ?>
                                </span>
								<?php
							}else{
								?>
                                <span class="submitted-assignment-grade-failed">
                                    <?php _e('Failed', 'tutor'); ?>
                                </span>
								<?php
							} ?>
                        </h4>
                    </div>

					<?php echo apply_filters('tutor_assignment/single/results/after', ob_get_clean(), $submit_id, $assignment_id ); ?>

				<?php } ?>


                <div class="tutor-assignments-submitted-answers-wrap">

                    <h2><?php _e('Your Answers', 'tutor'); ?></h2>

					<?php echo nl2br(stripslashes($submitted_assignment->comment_content));

					$attached_files = get_comment_meta($submitted_assignment->comment_ID, 'uploaded_attachments', true);
					if ($attached_files){
						$attached_files = json_decode($attached_files, true);

						if (tutor_utils()->count($attached_files)){

							?>
                            <h2><?php _e('Your uploaded file(s)', 'tutor'); ?></h2>

							<?php

							$upload_dir = wp_get_upload_dir();
							$upload_baseurl = trailingslashit(tutor_utils()->array_get('baseurl', $upload_dir));

							foreach ($attached_files as $attached_file){
								?>
                                <div class="uploaded-files">
                                    <a href="<?php echo $upload_baseurl.tutor_utils()->array_get('uploaded_path', $attached_file) ?>" target="_blank"><?php echo tutor_utils()->array_get('name', $attached_file); ?>
                                    </a>
                                </div>
								<?php
							}
						}
					}

					if ($is_reviewed_by_instructor){
						?>

                        <div class="instructor-note-wrap">
                            <h2><?php _e('Instructor Note', 'tutor'); ?></h2>
                            <p><?php echo nl2br(get_comment_meta($submitted_assignment->comment_ID,'instructor_note', true)) ?></p>
                        </div>
						<?php
					}
					?>
                </div>

				<?php
			}else {
				?>

                <div class="tutor-assignment-start-btn-wrap">
                    <form action="" method="post" id="tutor_assignment_start_form">
						<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
                        <input type="hidden" value="tutor_assignment_start_submit" name="tutor_action"/>
                        <input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">

                        <button type="submit" class="tutor-button" id="tutor_assignment_start_btn"> <?php _e( 'Start assignment submit', 'tutor' ); ?> </button>
                    </form>
                </div>
				<?php
			}
		}
		?>

	    <?php tutor_next_previous_pagination(); ?>

    </div>



<?php do_action('tutor_assignment/single/after/content'); ?>