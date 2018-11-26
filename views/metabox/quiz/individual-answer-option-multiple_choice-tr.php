<?php
$answer_option = json_decode($quiz_answer_option->comment_content, true);
?>

<tr class="answer-option-row" data-answer-option-id="<?php echo $quiz_answer_option->comment_ID; ?>">
	<td></td>

	<td>
		<input name="tutor_question[<?php echo $question_id; ?>][answer_option][<?php echo $quiz_answer_option->comment_ID; ?>]" type="text" value="<?php echo tutor_utils()->avalue_dot('answer_option_text', $answer_option); ?>">
	</td>

	<td>
		<input name="tutor_question[<?php echo $question_id; ?>][answer_option_is_correct][<?php echo $quiz_answer_option->comment_ID; ?>]" type="checkbox" value="1" <?php checked('1', tutor_utils()->avalue_dot('is_correct', $answer_option)); ?>>
	</td>

	<td>
		<a href="javascript:;" class="quiz-answer-option-delete-btn"><i class="dashicons dashicons-trash"></i> </a>
	</td>
</tr>