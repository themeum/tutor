<?php 

$contexts =  array(
    'attempt-table' => array(
        'columns' => array(
            'checkbox'          => '<div class="d-flex"><input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input" /></div>',
            'quiz_info'         => __('Quiz Info', 'tutor'),
            'course'            => __('Course', 'tutor'),
            'question'          => __('Question', 'tutor'),
            'total_marks'       => __('Total Marks', 'tutor'),
            'correct_answer'    => __('Correct Answer', 'tutor'),
            'incorrect_answer'  => __('Incorrect Answer', 'tutor'),
            'earned_marks'      => __('Earned Marks', 'tutor'),
            'result'            => __('Result', 'tutor'),
            // 'details'           => __('Details', 'tutor')
        ),
        'contexts' => array(
            'frontend-dashboard-my-attempts' => array(
                'quiz_info',
                'question',
                'total_marks',
                'correct_answer',
                'incorrect_answer',
                'earned_marks',
                'result',
                'details'
            ),
            'frontend-dashboard-students-attempts' => 'frontend-dashboard-my-attempts',
            'course-single-previous-attempts' => 'frontend-dashboard-my-attempts',
            'backend-dashboard-students-attempts' => true,
        )
    ),
    'attempt-details-summary' => array(
        'columns' => array(
            'user'              => __('Attempt By', 'tutor'),
            'date'              => __('Date', 'tutor'),
            'qeustion_count'    => __('Question', 'tutor'),
            'quiz_time'         => __('Quiz Time', 'tutor'),
            'attempt_time'      => __('Attempt Time', 'tutor'),
            'total_marks'       => __('Total Marks', 'tutor'),
            'pass_marks'        => __('Pass Marks', 'tutor'),
            'correct_answer'    => __('Correct Answer', 'tutor'),
            'incorrect_answer'  => __('Incorrect Answer', 'tutor'),
            'earned_marks'      => __('Earned Marks', 'tutor'),
            'result'            => __('Result', 'tutor')
        ),
        'contexts' => array(
            'frontend-dashboard-my-attempts' => array(
                'date',
                'qeustion_count',
                'total_marks',
                'pass_marks',
                'correct_answer',
                'incorrect_answer',
                'earned_marks',
                'result'
            ),
            'frontend-dashboard-students-attempts' => 'frontend-dashboard-my-attempts',
            'course-single-previous-attempts' => 'frontend-dashboard-my-attempts',
            'backend-dashboard-students-attempts' => true,
        )
    ),
    'attempt-details-answers' => array(
        'columns' => array(
            'no'              => __('No', 'tutor'),
            'type'            => __('Type', 'tutor'),
            'questions'       => __('Questions', 'tutor'),
            'given_answer'    => __('Given Answer', 'tutor'),
            'correct_answer'  => __('Correct Answer', 'tutor'),
            'answer'          => __('Answer', 'tutor'),
            'manual_review'   => __('Review', 'tutor')
        ),
        'contexts' => array(
            'frontend-dashboard-my-attempts' => array(
                'no',
                'type',
                'questions',
                'given_answer',
                'correct_answer',
                'answer',
            ),
            'frontend-dashboard-students-attempts' => array(
                'no',
                'type',
                'questions',
                'given_answer',
                'correct_answer',
                'answer',
                'manual_review'
            ),
            'backend-dashboard-students-attempts' => 'frontend-dashboard-students-attempts',
            'course-single-previous-attempts' => 'frontend-dashboard-my-attempts',
        )
    )
);

$fields = array();
$columns = apply_filters( 'tutor/quiz/attempts/table/column', $contexts[$page_key]['columns'] );
$allowed = $contexts[$page_key]['contexts'][$context];
is_string($allowed) ? $allowed=$contexts[$page_key]['contexts'][$allowed] : 0; // By reference

if($allowed===true) {
    $fields=$columns;
} else {
    foreach($columns as $key=>$column) {
        in_array($key, $allowed) ? $fields[$key]=$column : 0;
    }
}

return $fields;