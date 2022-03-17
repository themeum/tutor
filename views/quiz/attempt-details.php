<?php

if ( ! defined( 'ABSPATH' ) ){
    exit;
}
extract($data); // $user_id, $attempt_id, $attempt_data(nullable), $context(nullable)

!isset($attempt_data) ? $attempt_data = tutor_utils()->get_attempt($attempt_id) : 0;
!isset($context) ? $context=null : 0;

if (!$attempt_id || !$attempt_data || $user_id!=$attempt_data->user_id){
    echo '<p>'.__('Attempt not found or access permission denied', 'tutor').'</p>';
	return;
}

if ( isset( $user_id ) && $user_id > 0 ) {
    $user = get_userdata( $user_id );
    if ( ! $user ) {
        return;
    }
}

function tutor_render_answer_list( $answers= array(), $dump_data=false ){
    if(!empty($answers)){

		echo '<div class="correct-answer-wrap">';

            $multi_texts = array();
            foreach ($answers as $key => $ans) {
                $type = isset($ans->answer_view_format) ? $ans->answer_view_format : 'text_image';

                if (isset($ans->answer_two_gap_match)) {
                    echo '<div class="matching-type">';
                }

                switch ($type) {
                    case 'text_image':
                        echo '<div class="text-image-type tutor-mb-4">';
                            if(isset($ans->image_id)){
                                $img_url = wp_get_attachment_image_url($ans->image_id);
                                if($img_url){
                                    echo '<span class="image"><img src="'.$img_url.'" /></span>';
                                }
                            }
                            if(isset($ans->answer_title)) {
                                echo '<span class="caption">'.stripslashes($ans->answer_title).'</span>';
                            }
                        echo '</div>';
                        break;

                    case 'text':
                        $ans_string = '<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">'
                                .esc_html( $ans->answer_title ).
                            '</span>';

                        if(isset($ans->answer_title) && !isset($ans->answer_two_gap_match)) {
                            $multi_texts[$ans->answer_title] = $ans_string;
                        } else {
                            echo $ans_string;
                        }
                        break;

                    case 'image':
                        echo '<div class="image-type">';
                            if(isset($ans->image_id)){
                                $img_url = wp_get_attachment_image_url($ans->image_id);
                                if($img_url){
                                    echo '<span class="image"><img src="'.$img_url.'" />'.'</span>';
                                }
                            }
                        echo '</div>';
                        break;
                }
                

                if (isset($ans->answer_two_gap_match)) {
                        // echo '<div class="matching-separator">&nbsp;-&nbsp;</div>';
                        echo '<div class="image-match">'.stripslashes($ans->answer_two_gap_match).'</div>';
                    echo '</div>';
                }
            }
            echo count($multi_texts) ? implode(', ', $multi_texts) : '';

		echo '</div>';
    }
}

function tutor_render_fill_in_the_blank_answer($get_db_answers_by_question, $answer_titles) {

    $spaces = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

    // Loop through the answers
    foreach ($get_db_answers_by_question as $db_answer){
        $count_dash_fields = substr_count($db_answer->answer_title, '{dash}');

        if ($count_dash_fields){
            $dash_string = array();
            $input_data = array();
            for($i=0; $i<$count_dash_fields; $i++){
                $ans_title = (!empty($answer_titles[$i]) && !ctype_space($answer_titles[$i])) ? $answer_titles[$i] : null;
                $input_data[] = $ans_title ? "<span class='filled_dash_unser'>{$ans_title}</span>" : $spaces;
            }

            $answer_title = $db_answer->answer_title;
            
            foreach($input_data as $index=>$replace){
                $replace = '<span style="text-decoration:underline;">'.$replace.'</span>';
                $answer_title = preg_replace('/{dash}/i', $replace, $answer_title, 1);
            }
            echo str_replace('{dash}', "<span class='filled_dash_unser'>{$spaces}</span>", stripslashes($answer_title));
        }
    }
}

// Prepare student data
if(!isset($user_data)) {
    $user_data = get_userdata( $user_id );
}

// Prepare atttempt meta info
extract(tutor_utils()->get_quiz_attempt_timing($attempt_data)); // $attempt_duration, $attempt_duration_taken;

// Prepare the correct/incorrect answer count for the first summary table
$answers = tutor_utils()->get_quiz_answers_by_attempt_id($attempt_id);
$correct = 0;
$incorrect = 0;
if(is_array($answers) && count($answers) > 0) {
    foreach ($answers as $answer){
        if ( (bool) isset( $answer->is_correct ) ? $answer->is_correct : '' ) {
            $correct++;
        } else {
            if ($answer->question_type === 'open_ended' || $answer->question_type === 'short_answer'){
            } else {
                $incorrect++;
            }
        }
    }
}

// Prepare the column list for the first summary table
$page_key ='attempt-details-summary';
$table_1_columns = include __DIR__ . '/contexts.php';

// Prepare the column list for the second table (eery single answer list)
$page_key = 'attempt-details-answers';
$table_2_columns = include __DIR__ . '/contexts.php';

include __DIR__ . '/header.php';

$attempt_info = @unserialize( $attempt_data->attempt_info );

if ( is_array( $attempt_info ) ) {
    $attempt_type = '';
	// Allowed duration
	if ( isset( $attempt_info['time_limit'] ) ) {
		$attempt_duration = tutor_utils()->second_to_formated_time( $attempt_info['time_limit']['time_limit_seconds'], $attempt_info['time_limit']['time_type'] );
	}
	if ( 'days' == $attempt_info['time_limit']['time_type'] ) {
		$attempt_type = 'hours';
	}
	if ( 'hours' == $attempt_info['time_limit']['time_type'] ) {
		$attempt_type = 'minutes';
	}
	if ( 'minutes' == $attempt_info['time_limit']['time_type'] ) {
		$attempt_type = 'minutes';
	}

	// Taken duration
	$seconds                = strtotime( $attempt_data->attempt_ended_at ) - strtotime( $attempt_data->attempt_started_at );
	$attempt_duration_taken = tutor_utils()->second_to_formated_time( $seconds, $attempt_type );
}


?>

<?php echo is_admin() ? '<div class="wrap">' : ''; ?>
<table class="tutor-ui-table tutor-ui-table-responsive my-quiz-attempts tutor-mb-32">
    <thead>
        <tr>
            <?php
                foreach($table_1_columns as $key => $column) {
                    $class_name = join('-', explode(' ', strtolower($column)));
                    echo '<th class="'. $class_name .'">
                            <span class="tutor-fs-7 tutor-fw-normal tutor-color-black-60">'.
                                $column.
                            '</span>
                        </th>';
                }
            ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <?php
                foreach($table_1_columns as $key => $column){
                    switch($key) {
                        case 'user':
                            ?>
                            <td data-th="<?php echo $column; ?>">
                                <div class="td-avatar">
                                    <img src="<?php echo esc_url(get_avatar_url($user_id)); ?>" alt="<?php echo esc_attr($user->display_name); ?> - <?php _e('Profile Picture', 'tutor'); ?>"/>
                                    <div class="tutor-fs-6 tutor-fw-medium  tutor-color-black tutor-text-nowrap">
                                        <?php echo $user_data ? $user_data->display_name : ''; ?>
                                    </div>
                                    <a href="<?php echo esc_url( tutor_utils()->profile_url($user_id, false) ) ?>" class="btn-text btn-detail-link tutor-color-design-dark">
                                        <span class="tutor-icon-detail-link-filled"></span>
                                    </a>
                                </div>
                            </td>
                            <?php
                            break;

                        case 'date' :
                            ?>
                            <td data-th="<?php echo $column; ?>">
                                <div class="tutor-table-date-time tutor-color-black">
                                    <?php
                                        echo date_i18n(get_option('date_format'), strtotime($attempt_data->attempt_started_at));
                                    ?>
                                </div>
                                <div class="tutor-table-date-time tutor-color-black">
                                    <?php
                                        echo date_i18n(get_option('time_format'), strtotime($attempt_data->attempt_started_at));
                                    ?>
                                </div>
                            </td>
                            <?php
                            break;

                        case 'qeustion_count' :
                            ?>
                            <td data-th="<?php echo $column; ?>">
                                <span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
                                    <?php echo $attempt_data->total_questions; ?>
                                </span>
                            </td>
                            <?php
                            break;

                        case 'quiz_time':
                            ?>
                            <td data-th="<?php echo $column; ?>">
                                <?php echo $attempt_duration; ?>
                            </td>
                            <?php
                            break;

                        case 'attempt_time':
                            ?>
                            <td data-th="<?php echo $column; ?>">
                                <?php echo $attempt_duration_taken; ?>
                            </td>
                            <?php
                            break;

                        case 'total_marks' :
                            ?>
                            <td data-th="<?php echo $column; ?>">
                                <span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
                                    <?php echo $attempt_data->total_marks; ?>
                                </span>
                            </td>
                            <?php
                            break;

                        case 'pass_marks' :
                            ?>
                            <td data-th="<?php echo $column; ?>">
                                <span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
                                    <?php
                                        $pass_marks = ($total_marks * $passing_grade) / 100;
                                        echo number_format_i18n($pass_marks, 2);

                                        $pass_mark_percent = $passing_grade; // tutor_utils()->get_quiz_option($attempt_data->quiz_id, 'passing_grade', 0);
                                        echo ' ('.$pass_mark_percent.'%)';
                                    ?>
                                </span>
                            </td>
                            <?php
                            break;

                        case 'correct_answer' :
                            ?>
                            <td data-th="<?php echo $column; ?>">
                                <span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
                                    <?php echo $correct; ?>
                                </span>
                            </td>
                            <?php
                            break;

                        case 'incorrect_answer' :
                            ?>
                            <td data-th="<?php echo $column; ?>">
                                <span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
                                    <?php echo $incorrect; ?>
                                </span>
                            </td>
                            <?php
                            break;

                        case 'earned_marks' :
                            ?>
                            <td data-th="<?php echo $column; ?>">
                                <span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
                                    <?php
                                        echo $attempt_data->earned_marks;
                                        $earned_percentage = $attempt_data->earned_marks > 0 ? ( number_format(($attempt_data->earned_marks * 100) / $attempt_data->total_marks)) : 0;
                                        echo ' ('.$earned_percentage.'%)';
                                    ?>
                                </span>
                            </td>
                            <?php
                            break;

                        case 'result':
                            ?>
                            <td data-th="<?php echo $column; ?>">
                                <span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
                                    <?php
                                        $ans_array = is_array($answers) ? $answers : array();
                                        $has_pending = count(array_filter($ans_array, function($ans){
                                            return $ans->is_correct===null;
                                        }));

                                        if($has_pending){
                                            echo '<span class="tutor-badge-label label-warning">'.__('Pending', 'tutor').'</span>';
                                        } else if ($earned_percentage >= $pass_mark_percent){
                                            echo '<span class="tutor-badge-label label-success">'.__('Pass', 'tutor').'</span>';
                                        }else{
                                            echo '<span class="tutor-badge-label label-danger">'.__('Fail', 'tutor').'</span>';
                                        }
                                    ?>
                                </span>
                            </td>
                            <?php
                            break;
                    }
                }
            ?>
        </tr>
    </tbody>
</table>
<?php
// instructor feedback.
global $wp_query;
$query_vars = $wp_query->query_vars;
$page_name = isset( $query_vars['tutor_dashboard_page'] ) ? $query_vars['tutor_dashboard_page'] : '';
$attempt_info = unserialize( $attempt_data->attempt_info );
$feedback = is_array( $attempt_info ) && isset( $attempt_info['instructor_feedback'] ) ? $attempt_info['instructor_feedback'] : '';
// don't show on instructor quiz attempt since below already have feedback box area.
if ( '' !== $feedback && 'my-quiz-attempts' === $page_name ) {
	?>
    <div class="tutor-quiz-attempt-note tutor-instructor-note tutor-my-32 tutor-py-20 tutor-px-24 tutor-py-sm-32 tutor-px-sm-36">
        <div class="tutor-in-title tutor-fs-6 tutor-fw-medium tutor-color-black">
            <?php esc_html_e( 'Instructor Note', 'tutor' ); ?>					
        </div>
		<div class="tutor-in-body tutor-fs-6 tutor-fw-normal tutor-color-black-60 tutor-pt-12 tutor-pt-sm-16">
			<?php echo wp_kses_post( $feedback ); ?>					
        </div>
	</div>
<?php } ?>

<?php
    if (is_array($answers) && count($answers)){
        echo $context!='course-single-previous-attempts' ? '<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mt-24">'.__('Quiz Overview', 'tutor').'</div>' : '';
        ?>
        <div class="tutor-ui-table-wrapper tutor-mt-16">
            <table class="tutor-ui-table tutor-ui-table-responsive tutor-quiz-attempt-details tutor-mb-32 td-align-top">
                <thead>
                    <tr>
                        <?php
                            foreach($table_2_columns as $key => $column) {
                                $class_name = join('--', explode(' ', strtolower($column)));
                                echo '<th class="'. $class_name .'"><span class="text-regular-small tutor-color-black-60">'. $column .'</span></th>';
                            }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $answer_i = 0;
                        foreach ($answers as $answer){
                            $answer_i++;
                            $question_type = tutor_utils()->get_question_types($answer->question_type);
    
                            $answer_status = 'wrong';

                            // If already correct, then show it
                            if ((bool)$answer->is_correct) {
                                $answer_status = 'correct';
                            } 
                            
                            // Image answering also needs review since the answer texts are not meant to match exactly
                            else if (in_array($answer->question_type, array('open_ended', 'short_answer', 'image_answering'))){
                                $answer_status = $answer->is_correct===null ? 'pending' : 'wrong';
                            }
                            ?>
    
                            <tr class="<?php echo 'tutor-quiz-answer-status-'.$answer_status; ?>">
                                <?php foreach($table_2_columns as $key => $column): ?>
                                    <?php
                                        switch($key) {
                                            case 'no' :
                                                ?>
                                                <td class="no" data-th="<?php echo $column; ?>">
                                                    <span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
                                                        <?php echo $answer_i; ?>
                                                    </span>
                                                </td>
                                                <?php
                                                break;
    
                                            case 'type' :
                                                ?>
                                                <td class="type" data-th="<?php echo $column; ?>">
                                                    <?php $type = tutor_utils()->get_question_types( $answer->question_type ); ?>
                                                    <div class="tooltip-wrap tooltip-icon tutor-d-flex">
                                                        <?php echo $question_type['icon']; ?>
                                                        <span class="tooltip-txt tooltip-top">
                                                            <?php echo $type['name']; ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <?php
                                                break;
    
                                            case 'questions' :
                                                ?>
                                                <td class="questions" data-th="<?php echo $column; ?>">
                                                    <span class="text-medium-small tutor-d-flex tutor-align-items-center">
                                                        <?php echo stripslashes($answer->question_title); ?>
                                                    </span>
                                                </td>
                                                <?php
                                                break;
    
                                            case 'given_answer' :
                                                ?>
                                                <td class="given-answer" data-th="<?php echo $column; ?>">
                                                    <?php
                                                        // Single choice
                                                        if ( $answer->question_type === 'single_choice' ) {
                                                            $get_answers = tutor_utils()->get_answer_by_id($answer->given_answer);
                                                            tutor_render_answer_list($get_answers);
                                                        }
    

                                                        // True false or single choise
                                                        if ($answer->question_type === 'true_false'){
                                                            $get_answers = tutor_utils()->get_answer_by_id($answer->given_answer);
                                                            $answer_titles = wp_list_pluck($get_answers, 'answer_title');
                                                            $answer_titles = array_map('stripslashes', $answer_titles);
    
                                                            echo '<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">'.
                                                                    implode('</p><p>', $answer_titles).
                                                                '</span>';
                                                        }
    
                                                        // Multiple choice
                                                        elseif ($answer->question_type === 'multiple_choice'){
                                                            $get_answers = tutor_utils()->get_answer_by_id(maybe_unserialize($answer->given_answer));
                                                            tutor_render_answer_list($get_answers);
                                                        }
    
                                                        // Fill in the blank
                                                        elseif ($answer->question_type === 'fill_in_the_blank'){
                                                            $answer_titles = maybe_unserialize($answer->given_answer);
                                                            $get_db_answers_by_question = tutor_utils()->get_answers_by_quiz_question($answer->question_id);
    
                                                            echo tutor_render_fill_in_the_blank_answer($get_db_answers_by_question, $answer_titles);
                                                        }
    
                                                        // Open ended or short answer
                                                        elseif ($answer->question_type === 'open_ended' || $answer->question_type === 'short_answer'){
                                                            if ($answer->given_answer){
                                                                echo wpautop(stripslashes($answer->given_answer));
                                                            }
                                                        }
    
                                                        // Ordering
                                                        elseif ($answer->question_type === 'ordering'){
                                                            $ordering_ids = maybe_unserialize($answer->given_answer);
                                                            foreach ($ordering_ids as $ordering_id){
                                                                $get_answers = tutor_utils()->get_answer_by_id($ordering_id);
                                                                tutor_render_answer_list($get_answers);
                                                            }
                                                        }
    
                                                        // Matching
                                                        elseif ($answer->question_type === 'matching'){
    
                                                            $ordering_ids = maybe_unserialize($answer->given_answer);
                                                            $original_saved_answers = tutor_utils()->get_answers_by_quiz_question($answer->question_id);
    
                                                            $answers = array();

                                                            foreach ($original_saved_answers as $key => $original_saved_answer){
                                                                $provided_answer_order_id = isset($ordering_ids[$key]) ? $ordering_ids[$key] : 0;
                                                                $provided_answer_order = tutor_utils()->get_answer_by_id($provided_answer_order_id);
                                                                if(tutor_utils()->count($provided_answer_order)){
                                                                    foreach ($provided_answer_order as $provided_answer_order);
                                                                    $original_saved_answer->answer_two_gap_match = $provided_answer_order->answer_two_gap_match;
                                                                    $answers[] = $original_saved_answer;
                                                                }
                                                            }

                                                            tutor_render_answer_list( $answers );
                                                        }
    
                                                        elseif ($answer->question_type === 'image_matching'){
        
                                                            $ordering_ids = maybe_unserialize($answer->given_answer);
                                                            $original_saved_answers = tutor_utils()->get_answers_by_quiz_question($answer->question_id);
        
                                                            $answers = array();

                                                            foreach ($original_saved_answers as $key => $original_saved_answer){
                                                                $provided_answer_order_id = isset($ordering_ids[$key]) ? $ordering_ids[$key] : 0;
                                                                $provided_answer_order = tutor_utils()->get_answer_by_id($provided_answer_order_id);
                                                                foreach ($provided_answer_order as $provided_answer_order);
                                                                
                                                                if($provided_answer_order->answer_title) {
                                                                    $original_saved_answer->answer_view_format = 'text_image';
                                                                    $original_saved_answer->answer_title = $provided_answer_order->answer_title;
                                                                    $answers[] = $original_saved_answer;
                                                                }
                                                            }

                                                            tutor_render_answer_list( $answers );
                                                        }
                                                        
                                                        elseif ($answer->question_type === 'image_answering'){
        
                                                            $ordering_ids = maybe_unserialize($answer->given_answer);

                                                            $answers = array();
        
                                                            foreach ($ordering_ids as $answer_id => $image_answer){
                                                                $db_answers = tutor_utils()->get_answer_by_id($answer_id);
                                                                foreach ($db_answers as $db_answer);
                                                                $db_answer->answer_title = $image_answer;
                                                                $db_answer->answer_view_format = 'text_image';
                                                                $answers[] = $db_answer;
                                                                
                                                            }

                                                            tutor_render_answer_list( $answers );
                                                        }
                                                    ?>
                                                </td>
                                                <?php
                                                break;
    
                                            case 'correct_answer' :
                                                ?>
                                                <td class="correct-answer" data-th="<?php echo $column; ?>">
                                                    <?php
                                                    if (($answer->question_type != 'open_ended' && $answer->question_type != 'short_answer')) {
    
                                                        global $wpdb;
    
                                                        // True false
                                                        if ( $answer->question_type === 'true_false' ) {
                                                            $correct_answer = $wpdb->get_var( $wpdb->prepare(
                                                                "SELECT answer_title FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='true_false'
                                                                    AND is_correct = 1",
                                                                $answer->question_id
                                                            ) );

                                                            echo '<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">' . 
                                                                    $correct_answer . 
                                                                '</span>';
                                                        }
    
                                                        // Single choice
                                                        elseif ( $answer->question_type === 'single_choice' ) {
                                                            $correct_answer = $wpdb->get_results( $wpdb->prepare(
                                                                "SELECT answer_title, image_id, answer_view_format
                                                                FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='single_choice' AND 
                                                                    is_correct = 1",
                                                                    $answer->question_id
                                                                ) );
    
                                                            tutor_render_answer_list($correct_answer);
                                                        }
    
                                                        // Multiple choice
                                                        elseif ( $answer->question_type === 'multiple_choice' ) {
                                                            $correct_answer = $wpdb->get_results( $wpdb->prepare(
                                                                "SELECT answer_title, image_id, answer_view_format
                                                                FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='multiple_choice'
                                                                    AND is_correct = 1 ;",
                                                                $answer->question_id
                                                            ) );
    
                                                            tutor_render_answer_list($correct_answer);
                                                        }
    
                                                        // Fill in the blanks
                                                        elseif ( $answer->question_type === 'fill_in_the_blank' ) {
                                                            $correct_answer = $wpdb->get_var( $wpdb->prepare(
                                                                "SELECT answer_two_gap_match FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='fill_in_the_blank'",
                                                                $answer->question_id
                                                            ) );
    
                                                            $answer_titles = explode('|', stripslashes($correct_answer));
                                                            $get_db_answers_by_question = tutor_utils()->get_answers_by_quiz_question($answer->question_id);
    
                                                            echo tutor_render_fill_in_the_blank_answer($get_db_answers_by_question, $answer_titles);
                                                        }
    
                                                        // Ordering
                                                        elseif ( $answer->question_type === 'ordering' ) {
                                                            $correct_answer = $wpdb->get_results( $wpdb->prepare(
                                                                "SELECT answer_title, image_id, answer_view_format
                                                                FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='ordering'
                                                                ORDER BY answer_order ASC;",
                                                                $answer->question_id
                                                            ) );
    
                                                            foreach($correct_answer as $ans) {
                                                                tutor_render_answer_list(array($ans));
                                                            }
                                                        }
    
                                                        // Matching
                                                        elseif( $answer->question_type === 'matching' ){
                                                            $correct_answer = $wpdb->get_results( $wpdb->prepare(
                                                                "SELECT answer_title, image_id, answer_two_gap_match, answer_view_format
                                                                FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='matching'
                                                                ORDER BY answer_order ASC;",
                                                                $answer->question_id
                                                            ) );
    
                                                            tutor_render_answer_list($correct_answer);
                                                        }
    
                                                        // Image matching
                                                        elseif( $answer->question_type === 'image_matching' ) {
                                                            $correct_answer = $wpdb->get_results( $wpdb->prepare(
                                                                "SELECT answer_title, image_id, answer_two_gap_match
                                                                FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='image_matching'
                                                                ORDER BY answer_order ASC;",
                                                                $answer->question_id
                                                            ) );
    
                                                            tutor_render_answer_list($correct_answer, true);
                                                        }

                                                        // Image Answering
                                                        elseif ($answer->question_type === 'image_answering'){
    
                                                            $correct_answer = $wpdb->get_results( $wpdb->prepare(
                                                                "SELECT answer_title, image_id, answer_two_gap_match
                                                                FROM {$wpdb->prefix}tutor_quiz_question_answers
                                                                WHERE belongs_question_id = %d
                                                                    AND belongs_question_type='image_answering'
                                                                ORDER BY answer_order ASC;",
                                                                $answer->question_id
                                                            ) );

                                                            !is_array($correct_answer) ? $correct_answer=array() : 0;
                                                            
                                                            echo '<div class="answer-image-matched-wrap">';
                                                            foreach ($correct_answer as $image_answer){
                                                                ?>
                                                                    <div class="image-matching-item">
                                                                        <p class="dragged-img-rap"><img src="<?php echo wp_get_attachment_image_url( $image_answer->image_id); ?>" /> </p>
                                                                        <p class="dragged-caption"><?php echo $image_answer->answer_title; ?></p>
                                                                    </div>
                                                                <?php
                                                            }
                                                            echo '</div>';
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <?php
                                                break;
    
                                            case 'answer' :
                                                ?>
                                                <td class="answer" data-th="<?php echo $column; ?>">
                                                    <?php
                                                        switch($answer_status) {
                                                            case 'correct' :
                                                                echo '<span class="tutor-badge-label label-success">'.__('Correct', 'tutor').'</span>';
                                                                break;
    
                                                            case 'pending' :
                                                                echo '<span class="tutor-badge-label label-warning">'.__('Pending', 'tutor').'</span>';
                                                                break;
    
                                                            case 'wrong' :
                                                                echo '<span class="tutor-badge-label label-danger">'.__('Incorrect', 'tutor').'</span>';
                                                                break;
                                                        }
                                                    ?>
                                                </td>
                                                <?php
                                                break;
    
                                            case 'manual_review' :
                                                ?>
                                                <td data-th="<?php echo $column; ?>" class="tutor-text-center tutor-bg-gray-10 tutor-text-nowrap">
                                                    <a href="javascript:;" data-back-url="<?php echo $back_url; ?>" data-attempt-id="<?php echo $attempt_id; ?>" data-attempt-answer-id="<?php echo $answer->attempt_answer_id; ?>" data-mark-as="correct" data-context="<?php echo $context; ?>" title="<?php _e('Mark as correct', 'tutor'); ?>" class="quiz-manual-review-action tutor-mr-12 tutor-icon-rounded tutor-text-success">
                                                        <i class="tutor-icon-mark-filled tutor-icon-24"></i>
                                                    </a>
                                                    <a href="javascript:;" data-back-url="<?php echo $back_url; ?>" data-attempt-id="<?php echo $attempt_id; ?>" data-attempt-answer-id="<?php echo $answer->attempt_answer_id; ?>" data-mark-as="incorrect" data-context="<?php echo $context; ?>" title="<?php _e('Mark as In correct', 'tutor'); ?>" class="quiz-manual-review-action tutor-icon-rounded tutor-text-danger">
                                                        <i class="tutor-icon-line-cross-line tutor-icon-24"></i>
                                                    </a>
                                                </td>
                                                <?php
                                        }
                                    ?>
                                <?php endforeach; ?>
                            </tr>
                            <?php
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }
?>

<?php echo is_admin() ? '</div>' : ''; ?>