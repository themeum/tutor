<?php
$is_multiple_questions_loop = ! (isset($is_question_edit_page)) || ! $is_question_edit_page;
?>

<div class="single-question-item" data-question-id="<?php echo $question->ID; ?>">
	<div class="tutor-question-item-head">
		<?php if ($is_multiple_questions_loop){ ?>
        <div class="question-short">
			<a href=""><i class="dashicons dashicons-move"></i> </a>
		</div>
		<?php } ?>


        <div class="question-title">
			<?php echo $question->post_title; ?>
		</div>
		<div class="question-type">
			<?php $question_type = get_post_meta($question->ID, '_question_type', true);
			if ($question_type){
				echo tutor_utils()->get_question_types($question_type);
			}
			?>
		</div>

        <?php if ($is_multiple_questions_loop){ ?>
		<div class="question-actions-wrap">
			<span class="tutor-loading-icon-wrap button"></span>
			<a href="javascript:;" class="question-action-btn trash"><i class="dashicons dashicons-trash"></i> </a>
			<a href="javascript:;" class="question-action-btn down"><i class="dashicons dashicons-arrow-down-alt2"></i> </a>
		</div>
        <?php } ?>
	</div>

	<div class="quiz-question-form-wrap" style="display: <?php echo $is_multiple_questions_loop ? 'none' : 'block'; ?>;">

		<div class="quiz-question-flex-wrap">
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
								echo "<option value='{$type_key}' ".selected($type_key, $question_type)." >{$type_value}</option>";
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
					<?php
					include tutor()->path."views/metabox/quiz/multi-answer-options.php";
					?>
                </div>
            </div>

        </div>

	</div>

</div> <!-- .single-question-item -->
