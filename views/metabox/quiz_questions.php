<?php
$quiz_id = get_the_ID();

$questions = tutor_utils()->get_questions_by_quiz($quiz_id)
?>

<div class="tutor-quiz-questions-wrap">
	<?php
	if ($questions){
		foreach ($questions as $question){
			include tutor()->path."views/metabox/quiz/single-question-item.php";
		}
	}
	?>
</div>

<!-- add new question -->
<div class="tutor-add-question-wrap">
	<input type="text" name="new_question_title" value="" placeholder="<?php _e('Write your question here', 'tutor'); ?>">

	<select name="new_question_type">
		<option value="true_false"><?php _e('True/False'); ?></option>
		<option value="multiple_choice"><?php _e('Multiple Choice'); ?></option>
		<option value="single_choice"><?php _e('Single Choice'); ?></option>
	</select>

	<button type="button" class="button add_question_btn"> <?php _e('Add Question', 'tutor'); ?></button>

</div>
<!-- #End add new question -->