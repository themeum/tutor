<?php 
    $answers = tutor_utils()->get_qa_answer_by_question($_GET['question_id']);
?>

<div class="tutor-qna-single-wrapper">
    <div class="tutor-qa-sticky-bar">
        <div>
            <a href="#"><i class=""></i> <?php _e('Back', 'tutor'); ?></a>
        </div>
        <div>
            <span><i class="ttr-tick-circle-outline-filled"></i> <?php _e('Solved', 'tutor'); ?></span>
            <span><i class="ttr-msg-important-filled"></i> <?php _e('Important', 'tutor'); ?></span>
            <span><i class="ttr-msg-archive-filled"></i> <?php _e('Archive', 'tutor'); ?></span>
            <span><i class="ttr-msg-unread-filled"></i> <?php _e('Mark as Unread', 'tutor'); ?></span>
            <span><i class="ttr-delete-fill-filled"></i> <?php _e('Delete', 'tutor'); ?></span>
        </div>
    </div>
    <div class="tutor-qa-chatlist">

    <?php
		
		?>

        <div class="tutor_admin_answers_list_wrap">
			<?php
			if (is_array($answers) && count($answers)){
				foreach ($answers as $answer){
					?>
                    <div class="tutor_original_question <?php echo ($question->user_id == $answer->user_id) ? 'tutor-bg-white' : 'tutor-bg-light'
					?> ">
                        <div class="question-left">
							<?php
                            echo tutor_utils()->get_tutor_avatar($answer->user_id); ?>
                        </div>

                        <div class="question-right">
                            <div class="question-top-meta">
                                <p class="review-meta">
									<?php echo $answer->display_name; ?> -
                                    <span class="text-muted">
										<?php echo sprintf(__('%s ago', 'tutor'), human_time_diff(strtotime($answer->comment_date))); ?>
									</span>
                                </p>
                            </div>

                            <div class="tutor_question_area">
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
    <div class="tutor-qa-reply">

    </div>
</div>


<?php 
    if(!is_admin()){
        // Right sidebar should be loaded at backend dashboard only due to spacing issue at frontend
        return;
    }
?>
<div class="tutor-qna-admin-sidebar">

</div>