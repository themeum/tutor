
<?php 
    // Load header based on context
    if($context && file_exists($file_path=__DIR__ . '/header-context/'.$context.'.php')) {
        // Prepare header data
        $course_title   = get_the_title( $attempt_data->course_id );
        $course_url     = get_permalink( $attempt_data->course_id );
        
        $quiz_title     = get_the_title( $attempt_data->quiz_id );
        $quiz_url       = get_permalink( $attempt_data->quiz_id );
        
        $user_data      = get_userdata( $attempt_data->user_id );
        $student_name   = $user_data->display_name;
        $student_url    = '#';

        extract(tutor_utils()->get_quiz_attempt_timing($attempt_data)); // $attempt_duration, $attempt_duration_taken;
        $quiz_time      = $attempt_duration;
        $attempt_time   = $attempt_duration_taken;

        $question_count = $attempt_data->total_questions;
        $total_marks    = $attempt_data->total_marks;
        $pass_marks     = '';
        
        $back_url       = remove_query_arg( 'view_quiz_attempt_id', tutor()->current_url );

        include $file_path;
    }
?>
