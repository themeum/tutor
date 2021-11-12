<?php

/**
 * @package TutorLMS/Templates
 * @version 1.6.4
 */

if(isset($_GET['question_id'])) {
    ?>
    <h2><?php _e('Answer', 'tutor'); ?></h2>
    <?php 
        tutor_load_template_from_custom_path(tutor()->path . '/views/qna/qna-single.php', array(
            'question_id' => $_GET['question_id'],
            'context' => 'frontend-dashboard-qna-single'
        ));
    ?>
    <?php
    return;
}
?>

<h2><?php _e('Question & Answer', 'tutor'); ?></h2>

<?php
$per_page = 10;
$current_page = max( 1, tutor_utils()->avalue_dot('current_page', $_GET) );
$offset = ($current_page-1)*$per_page;

$total_items = tutor_utils()->get_total_qa_question();
$questions = tutor_utils()->get_qa_questions($offset, $per_page);

if (tutor_utils()->count($questions)) {
    tutor_load_template_from_custom_path(tutor()->path . '/views/qna/qna-table.php', array(
        'qna_list' => $questions,
        'context' => 'frontend-dashboard-qna-table'
    ));
} else {
    echo 'No Question Yet';
}
?>
<div class="tutor-dashboard-info-table-wrap tutor-dashboard-q-and-a">
    <div class="tutor-pagination">
        <?php
            echo paginate_links( array(
                'format' => '?current_page=%#%',
                'current' => $current_page,
                'total' => ceil($total_items/$per_page)
            ) );
        ?>
    </div>
</div>