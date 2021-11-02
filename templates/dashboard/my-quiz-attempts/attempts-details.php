<?php

/**
 * Quiz Attempts Details
 *
 * @since v.1.6.4
 *
 * @author Themeum
 * @url https://themeum.com
 *
 *
 * @package TutorLMS/Templates
 * @version 1.6.4
 */

if ( ! defined( 'ABSPATH' ) )
exit;

$attempt_id = (int) sanitize_text_field($_GET['attempt_id']);
$attempt = tutor_utils()->get_attempt($attempt_id);

if ( ! $attempt){
	?>
    <h1><?php esc_html_e('Attempt not found', 'tutor'); ?></h1>
	<?php
	return;
}

function show_correct_answer( $answers= array() ){
    if(!empty($answers)){

		echo wp_kses_post('<div class="correct-answer-wrap">');
        foreach ($answers as $key => $ans) {
            $type = isset($ans->answer_view_format) ? $ans->answer_view_format : 'text_image';
            if (isset($ans->answer_two_gap_match)) { echo wp_kses_post('<div class="matching-type">'); }
            switch ($type) {
				case 'text_image':
					echo wp_kses_post('<div class="text-image-type">');
                        if(isset($ans->image_id)){
                            $img_url = wp_get_attachment_image_url($ans->image_id);
                            if($img_url){
                                echo wp_kses_post('<span class="image"><img src="'.$img_url.'" /></span>');
                            }
                        }
                        if(isset($ans->answer_title)) {
                            echo wp_kses_post('<span class="caption">'.stripslashes($ans->answer_title).'</span>');
                        }
					echo wp_kses_post('</div>');
                    break;
                    
				case 'text':
                    if(isset($ans->answer_title)) {
                        echo wp_kses_post('<span class="text-medium-caption color-text-primary">'
                            .stripslashes($ans->answer_title).
                        '</span>');
                    }
                    break;

				case 'image':
					echo wp_kses_post('<div class="image-type">');
                        if(isset($ans->image_id)){
                            $img_url = wp_get_attachment_image_url($ans->image_id);
                            if($img_url){
                                echo wp_kses_post('<span class="image"><img src="'.$img_url.'" />'.'</span>');
                            }
                        }
                    echo wp_kses_post('</div>');
                    break;
                default:
                    break;
            }
            if (isset($ans->answer_two_gap_match)) {
                echo wp_kses_post('<div class="matching-separator">&nbsp;-&nbsp;</div>');
                echo wp_kses_post('<div class="image-match">'.stripslashes($ans->answer_two_gap_match).'</div>');
                echo wp_kses_post('</div>');
			}
        }
		echo wp_kses_post('</div>');
    }
}


if (!$attempt_id){
    ?>
    <h1><?php esc_html_e('Attempt not found', 'tutor'); ?></h1>
    <?php
    return;
}

$user_id = tutor_utils()->get_user_id();
$attempt_data = tutor_utils()->get_attempt($attempt_id);
if ( $user_id != $attempt_data->user_id ){
    ?>
    <h1><?php esc_html_e('You have no access.', 'tutor'); ?></h1>
    <?php
    return;
}
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
?>


<div>
    <?php $attempts_page = tutor_utils()->get_tutor_dashboard_page_permalink('my-quiz-attempts'); ?>
    <a class="prev-btn" href="<?php echo esc_url($attempts_page); ?>"><span>&leftarrow;</span><?php esc_html_e('Back to Attempt List', 'tutor'); ?></a>
</div>

<div class="tutor-quiz-attempt-review-wrap">
    <div class="attempt-answers-header">
        <div class="attempt-header-course"><?php echo __('Course:','tutor')." <a href='" .get_permalink($attempt_data->course_id)."'>".get_the_title($attempt_data->course_id)."</a>"; ?></div>
        <div class="attempt-header-quiz"><?php echo "<a href='" .get_permalink($attempt_data->quiz_id)."'>".get_the_title($attempt_data->quiz_id)."</a>"; ?></div>
    </div>
        
    <table class="tutor-ui-table tutor-ui-table-responsive my-quiz-attempts">
        <thead>
            <tr>
                <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('Date', 'tutor'); ?></span></th>
                <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('Question', 'tutor'); ?></span></th>
                <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('Total Marks', 'tutor'); ?></span></th>
                <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('Pass Marks', 'tutor'); ?></span></th>
                <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('Correct', 'tutor'); ?></span></th>
                <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('Incorrect', 'tutor'); ?></span></th>
                <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('Earned Marks', 'tutor'); ?></span></th>
                <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('Result', 'tutor'); ?></span></th>
            </tr>
        </thead>
        <tbody>
        <tr>
            <td data-th="<?php esc_html_e('Date', 'tutor'); ?>">
                <span class="text-medium-caption color-text-primary">
                    <?php
                        echo date_i18n(get_option('date_format'), strtotime($attempt_data->attempt_started_at)).' '.date_i18n(get_option('time_format'), strtotime($attempt_data->attempt_started_at));
                    ?>
                </span>
            </td>
            <td data-th="<?php esc_html_e('Question', 'tutor'); ?>">
                <span class="text-medium-caption color-text-primary">
                    <?php echo $attempt_data->total_questions; ?>
                </span>
            </td>
            <td data-th="<?php esc_html_e('Total Marks', 'tutor'); ?>">
                <span class="text-medium-caption color-text-primary">
                    <?php echo $attempt_data->total_marks; ?>
                </span>
            </td>
            <td data-th="<?php esc_html_e('Pass Marks', 'tutor'); ?>">
                <span class="text-medium-caption color-text-primary">
                    <?php 
                        $pass_mark_percent = tutor_utils()->get_quiz_option($attempt_data->quiz_id, 'passing_grade', 0);
                        echo $pass_mark_percent.'%';
                    ?>
                </span>
            </td>
            <td data-th="<?php esc_html_e('Correct', 'tutor'); ?>">
                <span class="text-medium-caption color-text-primary">
                    <?php echo $correct; ?>
                </span>
            </td>
            <td data-th="<?php esc_html_e('Incorrect', 'tutor'); ?>">
                <span class="text-medium-caption color-text-primary">
                    <?php echo $incorrect; ?>
                </span>
            </td>
            <td data-th="<?php esc_html_e('Earned Marks', 'tutor'); ?>">
                <span class="text-medium-caption color-text-primary">
                    <?php 
                        echo $attempt_data->earned_marks; 
                        $earned_percentage = $attempt_data->earned_marks > 0 ? ( number_format(($attempt_data->earned_marks * 100) / $attempt_data->total_marks)) : 0;
                        echo '('.$earned_percentage.'%)';
                    ?>
                </span>
            </td>
            <td data-th="<?php esc_html_e('Result', 'tutor'); ?>">
                <span class="text-medium-caption color-text-primary">
                    <?php 
                        if ($earned_percentage >= $pass_mark_percent){
                            echo wp_kses_post('<span class="tutor-badge-label label-success">'.__('Pass', 'tutor').'</span>') ;
                        }else{
                            echo wp_kses_post('<span class="tutor-badge-label label-danger">'.__('Fail', 'tutor').'</span>');
                        }
                    ?>
                </span>
            </td>
        </tr>
        </tbody>
    </table>
</div>


<?php $feedback = get_post_meta($attempt_id ,'instructor_feedback', true); ?>
<?php if($feedback){ ?>
    <div class="tutor-quiz-attempt-review-wrap">
        <div class="quiz-attempt-answers-wrap">
            <div class="attempt-answers-header">
                <div class="attempt-header-quiz"><?php esc_html_e('Instructor Feedback', 'tutor'); ?></div>
            </div>
            <div class="instructor-feedback-content">
                <p><?php echo $feedback; ?></p>
            </div>
        </div>
    </div>
<?php } ?>


<div class="tutor-quiz-attempt-review-wrap">
    <?php
    if (is_array($answers) && count($answers)){
        ?>
        <div class="quiz-attempt-answers-wrap">
            <div class="attempt-answers-header">
                <div class="attempt-header-quiz"><?php esc_html_e('Quiz Overview', 'tutor'); ?></div>
            </div>

            <table class="tutor-ui-table tutor-ui-table-responsive">
                <thead>
                    <tr>
                        <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('No.', 'tutor'); ?></span></th>
                        <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('Type', 'tutor'); ?></span></th>
                        <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('Question', 'tutor'); ?></span></th>
                        <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('Given Answers', 'tutor'); ?></span></th>
                        <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('Correct Answers', 'tutor'); ?></span></th>
                        <th><span class="text-regular-small color-text-subsued"><?php esc_html_e('Answer', 'tutor'); ?></span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $answer_i = 0;
                        foreach ($answers as $answer){
                            $answer_i++;
                            $question_type = tutor_utils()->get_question_types($answer->question_type);
                            ?>
                            
                            <tr>
                                <td data-th="<?php esc_html_e('No.', 'tutor'); ?>">
                                    <span class="text-medium-caption color-text-primary">
                                        <?php echo $answer_i; ?>
                                    </span>
                                </td>
                                <td data-th="<?php esc_html_e('Type', 'tutor'); ?>">
                                    <?php $type = tutor_utils()->get_question_types( $answer->question_type ); ?>
                                    <div class="tooltip-wrap tooltip-icon-">
                                        <?php echo $question_type['icon']; ?>
                                        <span class="tooltip-txt tooltip-top">
                                            <?php echo $type['name']; ?>
                                        </span>
                                    </div>
                                </td>
                                <td data-th="<?php esc_html_e('Question', 'tutor'); ?>">
                                    <span class="text-medium-small">
                                        <?php echo stripslashes($answer->question_title); ?>
                                    </span>
                                </td>
                                <td data-th="<?php esc_html_e('Given Answers', 'tutor'); ?>">
                                    <?php
                                        // True false or single choise
                                        if ($answer->question_type === 'true_false' || $answer->question_type === 'single_choice' ){
                                            $get_answers = tutor_utils()->get_answer_by_id($answer->given_answer);
                                            $answer_titles = wp_list_pluck($get_answers, 'answer_title');
                                            $answer_titles = array_map('stripslashes', $answer_titles);
                                            
                                            echo wp_kses_post('<span class="text-medium-caption color-text-primary">'.implode('</p><p>', $answer_titles).'</span>');
                                        } 
                                    
                                        // Multiple choice
                                        elseif ($answer->question_type === 'multiple_choice'){
                                            $get_answers = tutor_utils()->get_answer_by_id(maybe_unserialize($answer->given_answer));
                                            $answer_titles = wp_list_pluck($get_answers, 'answer_title');
                                            $answer_titles = array_map('stripslashes', $answer_titles);

                                            echo wp_kses_post('<p class="text-medium-caption color-text-primary">'.implode('</p><p>', $answer_titles).'</p>');
                                        }
                                    
                                        // Fill in the blank
                                        elseif ($answer->question_type === 'fill_in_the_blank'){
                                            $answer_titles = maybe_unserialize($answer->given_answer);
                                            $get_db_answers_by_question = tutor_utils()->get_answers_by_quiz_question($answer->question_id);

                                            // Loop through the answers
                                            foreach ($get_db_answers_by_question as $db_answer){
                                                $count_dash_fields = substr_count($db_answer->answer_title, '{dash}');
                                                
                                                if ($count_dash_fields){
                                                    $dash_string = array();
                                                    $input_data = array();
                                                    for($i=0; $i<$count_dash_fields; $i++){
                                                        $input_data[] =  isset($answer_titles[$i]) ? "<span class='filled_dash_unser'>{$answer_titles[$i]}</span>" : "______";
                                                    }
                                                    $answer_title = $db_answer->answer_title;
                                                    foreach($input_data as $replace){
                                                        $answer_title = preg_replace('/{dash}/i', $replace, $answer_title, 1);
                                                    }
                                                    echo str_replace('{dash}', '_____', stripslashes($answer_title));
                                                }
                                            }
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
                                                $answer_titles = wp_list_pluck($get_answers, 'answer_title');
                                                $answer_titles = array_map('stripslashes', $answer_titles);
                                                echo wp_kses_post('<p>'.implode('</p><p>', $answer_titles).'</p>');
                                            }
                                        }
                                    
                                        // Matching
                                        elseif ($answer->question_type === 'matching'){

                                            $ordering_ids = maybe_unserialize($answer->given_answer);
                                            $original_saved_answers = tutor_utils()->get_answers_by_quiz_question($answer->question_id);

                                            foreach ($original_saved_answers as $key => $original_saved_answer){
                                                $provided_answer_order_id = isset($ordering_ids[$key]) ? $ordering_ids[$key] : 0;
                                                $provided_answer_order = tutor_utils()->get_answer_by_id($provided_answer_order_id);
                                                if(tutor_utils()->count($provided_answer_order)){
                                                    foreach ($provided_answer_order as $provided_answer_order);
                                                    echo stripslashes($original_saved_answer->answer_title)  .' - '. stripslashes($provided_answer_order->answer_two_gap_match).'<br />';
                                                }
                                            }
                                        }
                                    
                                    elseif ($answer->question_type === 'image_matching'){

                                        $ordering_ids = maybe_unserialize($answer->given_answer);
                                        $original_saved_answers = tutor_utils()->get_answers_by_quiz_question($answer->question_id);

                                        echo wp_kses_post('<div class="answer-image-matched-wrap">');
                                        foreach ($original_saved_answers as $key => $original_saved_answer){
                                            $provided_answer_order_id = isset($ordering_ids[$key]) ? $ordering_ids[$key] : 0;
                                            $provided_answer_order = tutor_utils()->get_answer_by_id($provided_answer_order_id);
                                            foreach ($provided_answer_order as $provided_answer_order);
                                            ?>
                                            <div class="image-matching-item">
                                                <p class="dragged-img-rap"><img src="<?php echo wp_get_attachment_image_url( $original_saved_answer->image_id); ?>" /> </p>
                                                <p class="dragged-caption"><?php echo stripslashes($provided_answer_order->answer_title); ?></p>
                                            </div>
                                            <?php
                                        }
                                        echo wp_kses_post('</div>');
                                    }elseif ($answer->question_type === 'image_answering'){

                                        $ordering_ids = maybe_unserialize($answer->given_answer);

                                        echo wp_kses_post('<div class="answer-image-matched-wrap">');
                                        foreach ($ordering_ids as $answer_id => $image_answer){
                                            $db_answers = tutor_utils()->get_answer_by_id($answer_id);
                                            foreach ($db_answers as $db_answer);
                                            ?>
                                            <div class="image-matching-item">
                                                <p class="dragged-img-rap"><img src="<?php echo wp_get_attachment_image_url( $db_answer->image_id); ?>" /> </p>
                                                <p class="dragged-caption"><?php echo $image_answer; ?></p>
                                            </div>
                                            <?php
                                        }
                                        echo wp_kses_post('</div>');
                                    }

                                    ?>
                                </td>


                                <td>
                                    <?php
                                    if (($answer->question_type != 'open_ended' && $answer->question_type != 'short_answer')) {

                                        global $wpdb;
                                        if ( $answer->question_type === 'true_false' ) {
                                            $correct_answer = $wpdb->get_var( $wpdb->prepare( 
                                                "SELECT answer_title FROM {$wpdb->prefix}tutor_quiz_question_answers 
                                                WHERE belongs_question_id = %d AND is_correct = 1", 
                                                $answer->question_id 
                                            ) );

                                            echo wp_kses_post('<span class="text-medium-caption color-text-primary">' . $correct_answer . '</span>');
                                        } 
                                        
                                        elseif ( $answer->question_type === 'single_choice' ) {
                                            $correct_answer = $wpdb->get_results( $wpdb->prepare( 
                                                "SELECT answer_title, image_id, answer_view_format 
                                                FROM {$wpdb->prefix}tutor_quiz_question_answers 
                                                WHERE belongs_question_id = %d AND 
                                                    is_correct = 1", 
                                                    $answer->question_id 
                                                ) );

                                            show_correct_answer($correct_answer);
                                        } 
                                        
                                        elseif ( $answer->question_type === 'multiple_choice' ) {
                                            $correct_answer = $wpdb->get_results( $wpdb->prepare( "SELECT answer_title, image_id, answer_view_format FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE belongs_question_id = %d AND is_correct = 1 ;", $answer->question_id ) );
                                            show_correct_answer($correct_answer);

                                        } elseif ( $answer->question_type === 'fill_in_the_blank' ) {
                                            $correct_answer = $wpdb->get_var( $wpdb->prepare( "SELECT answer_two_gap_match FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE belongs_question_id = %d", $answer->question_id ) );
                                            if($correct_answer){
                                                echo implode(', ', explode('|', stripslashes($correct_answer)));
                                            }

                                        } elseif ( $answer->question_type === 'ordering' ) {
                                            $correct_answer = $wpdb->get_results( $wpdb->prepare( "SELECT answer_title, image_id, answer_view_format FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE belongs_question_id = %d ORDER BY answer_order ASC;", $answer->question_id ) );
                                            show_correct_answer($correct_answer);

                                        } elseif( $answer->question_type === 'matching' ){
                                            $correct_answer = $wpdb->get_results( $wpdb->prepare( "SELECT answer_title, image_id, answer_two_gap_match, answer_view_format FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE belongs_question_id = %d ORDER BY answer_order ASC;", $answer->question_id ) );
                                            show_correct_answer($correct_answer);

                                        } elseif( $answer->question_type === 'image_matching' ) {
                                            $correct_answer = $wpdb->get_results( $wpdb->prepare( "SELECT answer_title, image_id, answer_two_gap_match FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE belongs_question_id = %d ORDER BY answer_order ASC;", $answer->question_id ) );
                                            show_correct_answer($correct_answer);
                                        }
                                    }
                                    ?>
                                </td>

                                <td>
                                    <?php

                                    if ( (bool) isset( $answer->is_correct ) ? $answer->is_correct : '' ) {
                                        echo wp_kses_post('<span class="tutor-badge-label label-success">'.__('Correct', 'tutor').'</span>');
                                    } else {
                                        if ($answer->question_type === 'open_ended' || $answer->question_type === 'short_answer'){
                                            if ( (bool) $attempt->is_manually_reviewed && (!isset( $answer->is_correct ) || $answer->is_correct == 0 )) {
                                                echo wp_kses_post('<span class="tutor-badge-label label-danger">'.__('Wrong', 'tutor').'</span>');
                                            } else {
                                                echo wp_kses_post('<span class="tutor-badge-label label-warning">'.__('Pending', 'tutor').'</span>');
                                            }
                                        } else {
                                            echo wp_kses_post('<span class="tutor-badge-label label-danger">'.__('Wrong', 'tutor').'</span>');
                                        }
                                    }
                                    ?>
                                </td>

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
</div>