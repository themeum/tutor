


<?php
$quiz_id = get_the_ID();
$questions = tutor_utils()->get_questions_by_quiz($quiz_id)


?>


<div class="tutor-quiz-questions-wrap">

	<?php
	/*
	echo '<pre>';
	print_r($questions);
	echo '</pre>';*/


	if ($questions){
		foreach ($questions as $question){
			?>

			<div class="single-question-item" data-question-id="<?php echo $question->ID; ?>">


				<div class="tutor-question-item-head">

					<div class="question-short">
						<a href=""><i class="dashicons dashicons-move"></i> </a>
					</div>
					<div class="question-title">
						<?php echo $question->post_title; ?>
					</div>
					<div class="question-type">
						<?php $question_type = get_post_meta($question->ID, '_question_type', true);
						echo tutor_utils()->get_question_types($question_type);
						?>
					</div>

					<div class="question-actions-wrap">
						<a href="javascript:;" class="question-action-btn change_type"><i class="dashicons dashicons-randomize"></i> </a>
						<a href="javascript:;" class="question-action-btn edit"><i class="dashicons dashicons-edit"></i> </a>
						<a href="javascript:;" class="question-action-btn trash"><i class="dashicons dashicons-trash"></i> </a>
						<a href="javascript:;" class="question-action-btn down"><i class="dashicons dashicons-arrow-down-alt2"></i> </a>
					</div>

				</div>



				<div class="quiz-question-form-wrap">

					<div class="question-details">

						<div class="quiz-question-field tutor-flex-row">

							<div class="tutor-flex-col">
								<p>
									<label><?php _e('Question Type', 'tutor'); ?></label>
								</p>

								<select class="question_type_field" name="tutor_question[<?php echo $question->ID; ?>][question_type]">
									<?php
									$question_types = tutor_utils()->get_question_types();
									foreach ($question_types as $type_key => $type_value){
										echo "<option value='{$type_key}'>{$type_value}</option>";
									}
									?>
								</select>


							</div>


							<div class="tutor-flex-col">
								<p>
									<label><?php _e('Mark for this question', 'tutor'); ?></label>
								</p>
								<input type="number" name="tutor_question[<?php echo $question->ID; ?>][question_mark]" value="1">
								<p class="desc">
									<?php _e('When students choose right answer, how mark should he get.'); ?>
								</p>
							</div>


						</div>


						<div class="quiz-question-field">
							<p>
								<label><?php _e('Question', 'tutor'); ?></label>
							</p>
							<input type="text" class="question_field_title" name="tutor_question[<?php echo $question->ID; ?>][question_title]" value="<?php echo $question->post_title; ?>">

							<p class="desc">
								<?php _e('Title of the question.'); ?>
							</p>
						</div>

						<div class="quiz-question-field">
							<p>
								<label><?php _e('Description', 'tutor'); ?></label>
							</p>
							<textarea name="tutor_question[<?php echo $question->ID; ?>][question_description]"><?php echo $question->post_content;?></textarea>

							<p class="desc">
								<?php _e('Write about this question in details. '); ?>
							</p>
						</div>

						<div class="quiz-question-field">
							<p>
								<label><?php _e('Question Hint', 'tutor'); ?></label>
							</p>
							<textarea name="tutor_question[<?php echo $question->ID; ?>][question_hints]"><?php echo get_post_meta($question->ID, '_question_hints', true); ?></textarea>
							<p class="desc">
								<?php _e(sprintf('An instruction for the students to select the write answer. This will be show when students click to %s button', '<strong>hints</strong>'), 'tutor'); ?>
							</p>
						</div>

					</div>

					<div class="answer-details">

						<div class="answer-entry-wrap">

							<table class="multi-answers-options">
								<tbody>

								<tr>
									<th></th>
									<th><?php _e('Answer text', 'tutor'); ?></th>
									<th><?php _e('Right', 'tutor'); ?></th>
									<th>#</th>
								</tr>

								<tr>
									<td></td>
									<td> <input type="text" value="Which computer are you using?"> </td>
									<td><input name="question_answer[]" type="checkbox" value="correct"></td>
									<td></td>
								</tr>


								<tr>
									<td></td>
									<td> <input type="text" value="Which computer are you using?"> </td>
									<td><input name="question_answer[]" type="checkbox" value="correct"></td>
									<td></td>
								</tr>


								<tr>
									<td></td>
									<td><input type="text" value="Which computer are you using?"> </td>
									<td><input name="question_answer[]" type="checkbox" value="correct"></td>
									<td></td>
								</tr>


								<tr>
									<td></td>
									<td> <input type="text" value="Which computer are you using?"> </td>
									<td><input name="question_answer[]" type="checkbox" value="correct"></td>
									<td></td>
								</tr>


								<tr>
									<td></td>
									<td> <input type="text" value="Which computer are you using?"> </td>
									<td><input name="question_answer[]" type="checkbox" value="correct"></td>
									<td></td>
								</tr>


								</tbody>

							</table>


							<div class="add_answer_option_wrap">
								<button type="button" class="button add_answer_option_btn"> <?php _e('Add an option', 'tutor'); ?></button>
							</div>



						</div>




					</div>



				</div>






			</div> <!-- .single-question-item -->

			<?php
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