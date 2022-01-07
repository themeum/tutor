<?php

/**
 * @package TutorLMS/Templates
 * @version 1.6.4
 */

if(isset($_GET['question_id'])) {
        tutor_load_template_from_custom_path(tutor()->path . '/views/qna/qna-single.php', array(
            'question_id' => $_GET['question_id'],
            'context' => 'frontend-dashboard-qna-single'
        ));
    return;
}

if(isset($_GET['view_as']) && in_array($_GET['view_as'], array('student', 'instructor'))) {
    update_user_meta( get_current_user_id(), 'tutor_qa_view_as', $_GET['view_as'] );
}

$is_instructor      = tutor_utils()->is_instructor();
$user_status        = get_user_meta( get_current_user_id(), '_tutor_instructor_status', true );
$view_option        = get_user_meta( get_current_user_id(), 'tutor_qa_view_as', true );
$view_as            = $is_instructor ? ($view_option ? $view_option : 'instructor') : 'student';
$as_instructor_url  = add_query_arg( array('view_as'=>'instructor'), tutor()->current_url );
$as_student_url     = add_query_arg( array('view_as'=>'student'), tutor()->current_url );
?>

<div class="tutor-bs-row tutor-bs-align-items-center tutor-mb-24">
    <div class="tutor-bs-col">
        <div class="tutor-text-medium-h5 tutor-color-text-primary"><?php _e('Question & Answer', 'tutor'); ?></div>
    </div>
    <?php if( $is_instructor && 'approved' === $user_status ): ?>
        <div class="tutor-bs-col-auto">
            <?php _e('View as', 'tutor'); ?>:
        </div>
        <div class="tutor-bs-col-auto">
            <label class="tutor-form-toggle tutor-dashboard-qna-vew-as current-view-<?php echo $view_as=='instructor' ? 'instructor' : 'student'; ?>">
                <input type="checkbox" class="tutor-form-toggle-input" <?php echo $view_as=='instructor' ? 'checked="checked"' : ''; ?> data-as_instructor_url="<?php echo $as_instructor_url; ?>" data-as_student_url="<?php echo $as_student_url; ?>" disabled="disabled"/>
                <span class="tutor-form-toggle-label tutor-form-toggle-checked"><?php _e('Student', 'tutor'); ?></span>
                <span class="tutor-form-toggle-control"></span>
                <span class="tutor-form-toggle-label tutor-form-toggle-unchecked"><?php _e('Instructor', 'tutor'); ?></span>
            </label>
        </div>
    <?php endif; ?>
</div>

<?php
    $per_page = 10;
    $current_page = max( 1, tutor_utils()->avalue_dot('current_page', $_GET) );
    $offset = ($current_page-1)*$per_page;

    $total_items = $view_as=='instructor' ? tutor_utils()->get_qa_questions($offset, $per_page, '', null, null, null, null, true) : tutor_utils()->get_qa_questions($offset, $per_page, '', null, null, get_current_user_id(), null, true );
    $questions = $view_as=='instructor' ? tutor_utils()->get_qa_questions($offset, $per_page) : tutor_utils()->get_qa_questions($offset, $per_page, '', null, null, get_current_user_id() );

    tutor_load_template_from_custom_path(tutor()->path . '/views/qna/qna-table.php', array(
        'qna_list' => $questions,
        'context' => 'frontend-dashboard-qna-table-'.$view_as,
        'qna_pagination' => array(
            'base' => '?current_page=%#%',
            'total_items' => $total_items,
            'per_page' => $per_page,
            'paged' => $current_page
        )
    ));
?>