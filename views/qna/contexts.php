<?php 

$contexts =  array(
    'qna-table' => array(
        'columns' => array(
            'checkbox'      => '<div class="d-flex"><input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input" /></div>',
            'student'       => __('Student', 'tutor'),
            'question'      => __('Question', 'tutor'),
            'reply'         => __('Reply', 'tutor'),
            'waiting_since' => __('Waiting Since', 'tutor'),
            'status'        => __('Status', 'tutor'),
            'action'        => __('Action', 'tutor'),
        ),
        'contexts' => array(
            'frontend-dashboard-qna-table' => array(
                'student',
                'question',
                'reply',
                'status',
                'action'
            ),
            'backend-dashboard-qna-table' => true,
        )
    ),
);

$fields = array();
$columns = apply_filters( 'tutor/qna/table/column', $contexts[$page_key]['columns'] );
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