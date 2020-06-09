<?php

/**
 * @package TutorLMS/Templates
 * @version 1.6.4
 */

?>

<h2><?php _e('Question & Answer', 'tutor'); ?></h2>

<?php
$questions = tutils()->get_qa_questions();

/* echo "<pre>";
print_r($questions); */

if (tutor_utils()->count($questions)) {
?>
    <div class="tutor-dashboard-info-table-wrap">
        <table class="tutor-dashboard-info-table">
            <tr>
                <th><?php _e('Question', 'tutor'); ?></th>
                <th><?php _e('Student', 'tutor'); ?></th>
                <th><?php _e('Course', 'tutor'); ?></th>
                <th><?php _e('Answer', 'tutor'); ?></th>
                <th></th>
            </tr>
            <?php
            foreach ($questions as $question) { ?>
                <tr id="tutor-dashboard-question-<?php echo $question->comment_ID; ?>">
                    <td><a href="<?php echo tutils()->get_tutor_dashboard_page_permalink('question-answer/answers?question_id='.$question->comment_ID); ?>"><?php echo $question->question_title; ?></a></td>
                    <td><?php echo $question->display_name; ?></td>
                    <td><?php echo $question->post_title; ?></td>
                    <td><?php echo $question->answer_count; ?></td>
                    <td>
                        <a href="#tutor-question-delete" class="tutor-dashboard-element-delete-btn" data-id="<?php echo $question->comment_ID; ?>">
                            <i class="tutor-icon-garbage"></i> <?php _e('Delete', 'tutor') ?>
                        </a>
                    </td>
                </tr>
            <?php
            }
            ?>
        </table>
        <div class="tutor-frontend-modal" data-popup-rel="#tutor-question-delete" style="display: none">
            <div class="tutor-frontend-modal-overlay"></div>
            <div class="tutor-frontend-modal-content">
                <button class="tm-close tutor-icon-line-cross"></button>

                <div class="tutor-modal-body tutor-course-delete-popup">
                    <img src="<?php echo tutor()->url . 'assets/images/delete-icon.png' ?>" alt="">
                    <h3><?php _e('Delete This Question?', 'tutor'); ?></h3>
                    <p><?php _e("You are going to delete this question, it can't be undone", 'tutor'); ?></p>
                    <div class="tutor-modal-button-group">
                        <form action="" id="tutor-dashboard-delete-element-form">
                            <input type="hidden" name="action" value="tutor_delete_dashboard_question">
                            <input type="hidden" name="question_id" id="tutor-dashboard-delete-element-id" value="">
                            <button type="button" class="tutor-modal-btn-cancel"><?php _e('Cancel', 'tutor') ?></button>
                            <button type="submit" class="tutor-danger tutor-modal-element-delete-btn"><?php _e('Yes, Delete Question', 'tutor') ?></button>
                        </form>
                    </div>
                </div>
                
            </div> <!-- tutor-frontend-modal-content -->
        </div> <!-- tutor-frontend-modal -->
    </div>
<?php
} else {
    echo _e('No question is available', 'tutor');
}

?>