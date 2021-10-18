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

$assignment_id = (int) sanitize_text_field(tutor_utils()->array_get('assignment', $_GET));
$assignment_submitted_id = (int) sanitize_text_field(tutor_utils()->array_get('view_assignment', $_GET));
$submitted_url = tutor_utils()->get_tutor_dashboard_page_permalink('assignments/submitted');

if(!$assignment_submitted_id){
	echo _e("Sorry, but you are looking for something that isn't here." , 'tutor');
	return;
}
?>

<div class="tutor-dashboard-content-inner tutor-dashboard-assignment-review">
    <?php
        $submitted_assignment = tutor_utils()->get_assignment_submit_info($assignment_submitted_id);
        if ( $submitted_assignment){

            $max_mark = tutor_utils()->get_assignment_option($submitted_assignment->comment_post_ID, 'total_mark');

            $given_mark = get_comment_meta($assignment_submitted_id, 'assignment_mark', true);
            $instructor_note = get_comment_meta($assignment_submitted_id, 'instructor_note', true);
            $comment_author = get_user_by('login', $submitted_assignment->comment_author)
            ?>

            <div class="submitted-assignment-title tutor-mb-15">
                <a class="prev-btn" href="<?php echo esc_url($submitted_url . '?assignment=' . $assignment_id); ?>">
                    <span>&leftarrow;</span><?php _e('Back', 'tutor'); ?>
                </a>
            </div>
 
            <div class="tutor-assignment-review-header">
                <table class="tutor-ui-table-no-border tutor-is-lefty tutor-is-flexible">
                    <tbody>
                        <tr>
                            <td><?php _e('Course' , 'tutor'); ?>:</td>
                            <td>
                                <a href="<?php echo get_the_permalink($submitted_assignment->comment_parent); ?>" target="_blank">
                                    <?php echo get_the_title($submitted_assignment->comment_parent); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e('Student' , 'tutor'); ?>:</td>
                            <td>
                                <span>
                                    <?php echo $comment_author->display_name. ' ('.$comment_author->user_email.')'; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e('Submitted Date' , 'tutor'); ?>:</td>
                            <td>
                                <span>
                                    <?php echo date('j M, Y, h:i a', strtotime($submitted_assignment->comment_date)); ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <hr>

            <div class="tutor-dashboard-assignment-submitted-content tutor-mt-15 tutor-mb-15">
                <h4><?php _e('Assignment Description:', 'tutor'); ?></h4>
                <p><?php echo nl2br(stripslashes($submitted_assignment->comment_content)); ?></p>
                
                <h5 class="tutor-mt-15"><?php _e('Attach assignment file(s)', 'tutor'); ?></h5>
                <div class="tutor-attachment-cards">
                    <?php
                        $attached_files = get_comment_meta($submitted_assignment->comment_ID, 'uploaded_attachments', true);
                        if($attached_files){
                            $attached_files = json_decode($attached_files, true);
                            if (tutor_utils()->count($attached_files)){
                                $upload_dir = wp_get_upload_dir();
                                $upload_baseurl = trailingslashit(tutor_utils()->array_get('baseurl', $upload_dir));
                                foreach ($attached_files as $attached_file){
                                    ?>
                                    <div>
                                        <div>
                                            <a href="<?php echo $upload_baseurl . tutor_utils()->array_get('uploaded_path', $attached_file) ?>" target="_blank">
                                                <?php echo tutor_utils()->array_get('name', $attached_file); ?>
                                            </a>
                                            <span class="filesize"><?php _e('Size', 'tutor'); ?>: 2MB</span>
                                        </div>
                                        <div>
                                            <a href="<?php echo $upload_baseurl . tutor_utils()->array_get('uploaded_path', $attached_file) ?>" target="_blank">
                                                <span class="ttr ttr-download-line"></span>
                                            </a>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                        }
                    ?>
                </div>
            </div>
            
            <div class="tutor-dashboard-assignment-review-area tutor-mt-30">
                <h3><?php _e('Evaluation', 'tutor'); ?></h3>
                <form action="" method="post" class="tutor-bs-row tutor-form-submit-through-ajax" data-toast_success_message="<?php _e('Assignment evaluated', 'tutor'); ?>">
                    <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
                    <input type="hidden" value="tutor_evaluate_assignment_submission" name="tutor_action"/>
                    <input type="hidden" value="<?php echo $assignment_submitted_id; ?>" name="assignment_submitted_id"/>
                    
                    <div class="tutor-bs-col-12 tutor-bs-col-sm-4 tutor-bs-col-md-12 tutor-bs-col-lg-3">
                        <label for=""><?php _e('Your Points', 'tutor'); ?></label>
                    </div>
                    <div class="tutor-bs-col-12 tutor-bs-col-sm-8 tutor-bs-col-md-12 tutor-bs-col-lg-9">
                        <input class="tutor-form-control" type="number" name="evaluate_assignment[assignment_mark]" value="<?php echo $given_mark ? $given_mark : 0; ?>">
                        <p class="desc"><?php echo sprintf(__('Evaluate this assignment out of %s', 'tutor'), "<code>{$max_mark}</code>" ); ?></p>
                    </div>
                    
                    <div class="tutor-bs-col-12 tutor-bs-col-sm-4 tutor-bs-col-md-12 tutor-bs-col-lg-3">
                        <label for=""><?php _e('Write a note', 'tutor'); ?></label>
                    </div>
                    <div class="tutor-bs-col-12 tutor-bs-col-sm-8 tutor-bs-col-md-12 tutor-bs-col-lg-9">
                        <textarea class="tutor-form-control" name="evaluate_assignment[instructor_note]"><?php echo $instructor_note; ?></textarea>
                    </div>
                    
                    <div class="tutor-bs-col-12 tutor-bs-col-sm-4 tutor-bs-col-md-12 tutor-bs-col-lg-3"></div>
                    <div class="tutor-bs-col-12 tutor-bs-col-sm-8 tutor-bs-col-md-12 tutor-bs-col-lg-9">
                        <button type="submit" class="tutor-btn tutor-mt-15">
                            <?php _e('Evaluate this submission', 'tutor'); ?>
                        </button>
                    </div>
                </form>
            </div>

        <?php }else{
            _e('Assignments submission not found or not completed', 'tutor');
        } 
    ?>
</div>