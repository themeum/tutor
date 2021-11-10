<?php 
    $question_id = isset($question_id) ? $question_id : (int)sanitize_text_field( tutor_utils()->array_get('question_id', $_GET) );
    if(!$question_id || !tutor_utils()->can_user_manage('qa_question', $question_id)){
        _e('Access Denied. Or question not found.', 'tutor');
        return;
    }

    $question = tutor_utils()->get_qa_question($question_id);
    $meta = $question->meta;
    $answers = tutor_utils()->get_qa_answer_by_question($question_id);
    $back_url = remove_query_arg( 'question_id', tutor()->current_url );

    $is_solved = (int)tutor_utils()->array_get('tutor_qna_solved', $meta, 0);
    $is_important = (int)tutor_utils()->array_get('tutor_qna_important', $meta, 0);
    $is_archived = (int)tutor_utils()->array_get('tutor_qna_archived', $meta, 0);
    $is_read = (int)tutor_utils()->array_get('tutor_qna_read', $meta, 0);
?>

<div class="tutor-qna-single-wrapper" data-question_id="<?php echo $question_id; ?>">
    <div class="tutor-qa-sticky-bar">
        <div>
            <a href="<?php echo $back_url; ?>">
                <?php _e('Back', 'tutor'); ?>
            </a>
        </div>
        <div>
            <span data-action="solved" data-value="<?php echo $is_solved ? 1 : 0; ?>"><i class="ttr-tick-circle-outline-filled"></i> <?php _e('Solved', 'tutor'); ?></span>
            <span data-action="important" data-value="<?php echo $is_important ? 1 : 0; ?>"><i class="ttr-msg-important-filled"></i> <?php _e('Important', 'tutor'); ?></span>
            <span data-action="archived" data-value="<?php echo $is_archived ? 1 : 0; ?>"><i class="ttr-msg-archive-filled"></i> <?php _e('Archive', 'tutor'); ?></span>
            <span data-action="unread" data-value="<?php echo $is_read ? 1 : 0; ?>"><i class="ttr-msg-unread-filled"></i> <?php _e('Mark as Unread', 'tutor'); ?></span>
            <span data-tutor-modal-target="tutor_qna_delete_single">
                <i class="ttr-delete-fill-filled"></i> <?php _e('Delete', 'tutor'); ?>
            </span>

            <!-- Delete modal -->
            <div id="tutor_qna_delete_single" class="tutor-modal">
                <span class="tutor-modal-overlay"></span>
                <button data-tutor-modal-close class="tutor-modal-close">
                    <span class="las la-times"></span>
                </button>
                <div class="tutor-modal-root">
                    <div class="tutor-modal-inner">
                        <div class="tutor-modal-body tutor-text-center">
                            <div class="tutor-modal-icon">
                                <img src="<?php echo tutor()->url; ?>assets/images/icon-trash.svg" />
                            </div>
                            <div class="tutor-modal-text-wrap">
                                <h3 class="tutor-modal-title">
                                    <?php esc_html_e('Delete This Question?', 'tutor'); ?>
                                </h3>
                                <p>
                                    <?php esc_html_e('All the replies also will be deleted.', 'tutor'); ?>
                                </p>
                            </div>
                            <div class="tutor-modal-btns tutor-btn-group">
                                <button data-tutor-modal-close class="tutor-btn tutor-is-outline tutor-is-default">
                                    <?php esc_html_e('Cancel', 'tutor'); ?>
                                </button>
                                <button class="tutor-btn tutor-list-ajax-action" data-request_data='{"question_id":<?php echo $question_id;?>,"action":"tutor_delete_dashboard_question"}' data-redirect_to="<?php echo $back_url; ?>">
                                    <?php esc_html_e('Yes, Delete This', 'tutor'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tutor-qa-chatlist">
        <div class="tutor_admin_answers_list_wrap">
			<?php
                $current_user_id = get_current_user_id();
                if (is_array($answers) && count($answers)){
                    foreach ($answers as $answer){
                        ?>
                        <div class="tutor_original_question <?php echo $current_user_id == $answer->user_id ? 'tutor-bg-white' : 'tutor-bg-light' ?> ">
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
        <textarea class="tutor-form-control"><?php 
            echo wpautop(stripslashes($answer->comment_content)); 
        ?></textarea>

        <button type="submit" class="tutor-btn tutor-mt-5">
            <?php esc_html_e('Reply', 'tutor'); ?> 
        </button>
    </div>
</div>

<?php 
    if(!is_admin()){
        // Right sidebar should be loaded at backend dashboard only due to spacing issue at frontend
        return;
    }
?>
<div class="tutor-qna-admin-sidebar">
    <div class="tutor-qna-user">
        <img src="<?php echo get_avatar_url($question->user_id)?>"/>
        <h2><?php echo $question->display_name; ?></h2>
        <strong><?php echo $question->user_email; ?></strong>
    </div>
    
    <div class="tutor-qna-user-details">
        <label for="qna_segment_1"><?php _e('Asked Under', 'tutor'); ?></label>
        <input id="qna_segment_1" type="radio" name="tutor_qna_segment"/>
        <div>
            <strong><?php echo $question->post_title; ?></strong>
        </div>
    </div>

    <?php 
        $own_questions = tutor_utils()->get_qa_questions( 0, 10, '', null, null, $question->user_id );
        $own_questions = array_filter($own_questions, function($question) use($question_id) {
            return true; // $question_id!=$question->comment_ID;
        });

        if(count($own_questions)) {
            ?>
            <div class="tutor-qna-user-details">
                <label for="qna_segment_2"><?php _e('Previous Question History', 'tutor'); ?></label>
                <input id="qna_segment_2" type="radio" name="tutor_qna_segment"/>
                <div class="qna-previous-questions">
                    <?php 
                        foreach($own_questions as $question) {
                            ?>
                            <div>
                                <span><?php echo $question->comment_date; ?></span>
                                <strong><?php echo htmlspecialchars( $question->comment_content ); ?></strong>
                                <small><strong><?php _e('Course', 'tutor'); ?></strong>: <?php echo $question->post_title; ?></small>
                            </div>
                            <?php
                        }
                    ?>
                </div>
            </div>
            <?php
        }
    ?>
</div>