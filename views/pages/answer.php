<?php
$question_id = 0;
if (isset($_GET['question_id'])){
	$question_id = (int) $_GET['question_id'];
}

$question = dozent_utils()->get_qa_question($question_id);
?>

<div class="wrap">
    <h2><?php _e('Answer', 'dozent'); ?></h2>

    <div class="dozent-qanda-wrap">
        <form action="<?php echo admin_url('admin-post.php') ?>" id="dozent_admin_answer_form" method="post">
			<?php wp_nonce_field( dozent()->nonce_action, dozent()->nonce ); ?>
            <input type="hidden" value="dozent_place_answer" name="action"/>
            <input type="hidden" value="<?php echo $question_id; ?>" name="question_id"/>

            <div class="dozent-option-field-row">
                <div class="dozent-option-field">
					<?php
					$settings = array(
						'teeny' => true,
						'media_buttons' => false,
						'quicktags' => false,
						'editor_height' => 200,
					);
					wp_editor(null, 'answer', $settings);
					?>

                    <p class="desc"><?php _e('Write an answer here'); ?></p>
                </div>

                <div class="dozent-option-field">
                    <button type="submit" name="dozent_answer_submit_btn" class="button button-primary"><?php _e('Place answer', 'dozent'); ?></button>
                </div>
            </div>
        </form>
    </div>

    <div class="dozent-admin-individual-question">
        <div class="dozent_original_question dozent-bg-white ">
            <div class="question-left">
				<?php
                echo dozent_utils()->get_dozent_avatar($question->user_id); ?>
            </div>

            <div class="question-right">

                <div class="question-top-meta">
                    <p class="review-meta">
						<?php echo $question->display_name; ?> -
                        <span class="text-muted">
							<?php _e(sprintf('%s ago', human_time_diff(strtotime($question->comment_date))), 'dozent'); ?>
						</span>
                    </p>
                </div>

                <div class="dozent_question_area">
                    <p>
                        <strong><?php echo $question->question_title; ?> </strong>

                        <span class="text-muted">
							<?php _e('on', 'dozent'); ?> <?php echo $question->post_title; ?>
						</span>
                    </p>
					<?php echo wpautop(stripslashes($question->comment_content)); ?>
                </div>

            </div>
        </div>

		<?php
		$answers = dozent_utils()->get_qa_answer_by_question($question_id);
		?>

        <div class="dozent_admin_answers_list_wrap">
			<?php
			if (is_array($answers) && count($answers)){
				foreach ($answers as $answer){
					?>
                    <div class="dozent_original_question <?php echo ($question->user_id == $answer->user_id) ? 'dozent-bg-white' : 'dozent-bg-light'
					?> ">
                        <div class="question-left">
							<?php
                            echo dozent_utils()->get_dozent_avatar($answer->user_id); ?>
                        </div>

                        <div class="question-right">
                            <div class="question-top-meta">
                                <p class="review-meta">
									<?php echo $answer->display_name; ?> -
                                    <span class="text-muted">
										<?php _e(sprintf('%s ago', human_time_diff(strtotime($answer->comment_date))), 'dozent'); ?>
									</span>
                                </p>
                            </div>

                            <div class="dozent_question_area">
								<?php echo wpautop(stripslashes($answer->comment_content)); ?>
                            </div>
                        </div>
                    </div>
					<?php
				}
			}
			?>
        </div>
    </div>
</div>