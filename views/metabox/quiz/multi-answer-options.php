
<table class="multi-answers-options">
	<tbody>

	<tr>
		<th></th>
		<th><?php _e('Answer text', 'tutor'); ?></th>
		<th><?php _e('Is Correct', 'tutor'); ?></th>
		<th>#</th>
	</tr>

	<?php
	$quiz_answer_options = tutor_utils()->get_quiz_answer_options_by_question($question->ID);
	//$question_type = get_post_meta($question->ID, '_question_type', true);

	if ($quiz_answer_options){
		if ($question_type === 'true_false'){
			$quiz_answer_options = array_slice($quiz_answer_options, 0, 2);
		}

		$question_id = $question->ID;
		foreach ($quiz_answer_options as $quiz_answer_option){
			include tutor()->path."views/metabox/quiz/individual-answer-option-{$question_type}-tr.php";
		}
	}
	?>

	</tbody>
</table>


<div class="add_answer_option_wrap" style="display: <?php echo ($question_type === 'true_false' && count($quiz_answer_options) >= 2) ? 'none' : 'block'; ?>;" >
	<button type="button" class="button add_answer_option_btn"> <?php _e('Add an option', 'tutor'); ?></button>
</div>