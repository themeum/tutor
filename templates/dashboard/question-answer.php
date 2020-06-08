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
    <div class="responsive-table-wrap">
        <table>
            <tr>
                <th><?php _e('Question', 'tutor'); ?></th>
                <th><?php _e('Student', 'tutor'); ?></th>
                <th><?php _e('Course', 'tutor'); ?></th>
                <th><?php _e('Answer', 'tutor'); ?></th>
                <th></th>
            </tr>
            <?php
            foreach ($questions as $question) { ?>
                <tr>
                    <td><a href="<?php echo tutils()->get_tutor_dashboard_page_permalink('question-answer/answers?question_id='.$question->comment_ID); ?>"><?php echo $question->question_title; ?></a></td>
                    <td><?php echo $question->display_name; ?></td>
                    <td><?php echo $question->post_title; ?></td>
                    <td><?php echo $question->answer_count; ?></td>
                    <td>Delete</a></td>
                </tr>
            <?php
            }
            ?>
        </table>
    </div>
<?php
} else {
    echo _e('No question is available', 'tutor');
}

?>