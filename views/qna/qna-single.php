<?php 
    extract($data); // $question_id

    // At first set this as read
    update_comment_meta( $question_id, 'tutor_qna_read', 1 );
    
    // QNA data
    $question       = tutor_utils()->get_qa_question($question_id);
    $meta           = $question->meta;
    $answers        = tutor_utils()->get_qa_answer_by_question($question_id);
    $back_url       = remove_query_arg( 'question_id', tutor()->current_url );

    // Badges data
    $is_solved      = (int)tutor_utils()->array_get('tutor_qna_solved', $meta, 0);
    $is_important   = (int)tutor_utils()->array_get('tutor_qna_important', $meta, 0);
    $is_archived    = (int)tutor_utils()->array_get('tutor_qna_archived', $meta, 0);
    $is_read        = (int)tutor_utils()->array_get('tutor_qna_read', $meta, 0);
    $is_user_asker  = $question->user_id==get_current_user_id();

    $modal_id       = 'tutor_qna_delete_single_' . $question_id;
    $reply_hidden   = !wp_doing_ajax() ? 'display:none;' : 0;
?>

<div class="tutor-qna-single-question" data-course_id="<?php echo $question->course_id; ?>" data-question_id="<?php echo $question_id; ?>" data-context="<?php echo $context; ?>">
    <!-- Delete modal -->
    <div id="<?php echo $modal_id; ?>" class="tutor-modal">
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

    <div class="tutor-qna-single-wrapper">

        <!-- Show header action bar if it is single question in backend/frontend dashboard -->
        <?php if(in_array($context, array('backend-dashboard-qna-single', 'frontend-dashboard-qna-single'))): ?>
            <div class="tutor-qa-sticky-bar">
                <div>
                    <a class="qna-back-button" href="<?php echo $back_url; ?>">
                        <i class="ttr-previous-line"></i> <?php _e('Back', 'tutor'); ?>
                    </a>
                </div>
                <div class="tutor-qna-badges">

                    <!-- Show meta data actions if it is instructor view -->
                    <?php if(!$is_user_asker): ?>
                        <span data-action="solved" data-state-class-selector="i" data-state-class-0="ttr-tick-circle-outline-filled" data-state-class-1="ttr-mark-cricle tutor-text-success">
                            <i class="<?php echo $is_solved ? 'ttr-mark-cricle tutor-text-success' : 'ttr-tick-circle-outline-filled'; ?>"></i> 
                            <span><?php _e('Solved', 'tutor'); ?></span>
                        </span>
                        <span data-action="important" data-state-class-selector="i" data-state-class-0="ttr-msg-important-filled" data-state-class-1="ttr-msg-important-fill-filled">
                            <i class="<?php echo $is_important ? 'ttr-msg-important-fill-filled' : 'ttr-msg-important-filled'; ?>"></i> 
                            <span><?php _e('Important', 'tutor'); ?></span>
                        </span>
                        <span data-action="archived" data-state-class-selector="i" data-state-class-0="ttr-msg-archive-filled" data-state-class-1="ttr-msg-important-fill-filled">
                            <i class="<?php echo $is_archived ? 'ttr-msg-archive-filled' : 'ttr-msg-archive-filled'; ?>"></i> 
                            <span><?php _e('Archive', 'tutor'); ?></span>
                        </span>
                        <span data-action="unread" data-state-class-selector="i" data-state-class-0="ttr-msg-unread-filled" data-state-class-1="ttr-msg-read-filled">
                            <i class="<?php echo $is_read ? 'ttr-msg-read-filled' : 'ttr-msg-unread-filled'; ?>"></i> 
                            <span><?php _e('Mark as Unread', 'tutor'); ?></span>
                        </span>
                    <?php endif; ?>
                    <span data-tutor-modal-target="<?php echo $modal_id; ?>">
                        <i class="ttr-delete-fill-filled"></i> 
                        <span><?php _e('Delete', 'tutor'); ?></span>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Show  question anaswer. Both the root level question and reply will go in single loop. Just first one will be considered as root questoin. -->
        <div class="tutor-qa-chatlist">
            <?php
                $current_user_id = get_current_user_id();
                $avata_url = array();
                $is_single = in_array($context, array('course-single-qna-sidebar', 'course-single-qna-single'));
                
                if (is_array($answers) && count($answers)){
                    $reply_count = count($answers)-1;

                    foreach ($answers as $answer){
                        if(!isset($avata_url[$answer->user_id])) {
                            // Get avatar url if not already got
                            $avata_url[$answer->user_id] = get_avatar_url($answer->user_id);
                        }

                        $css_class = ($current_user_id!=$answer->user_id || $answer->comment_parent==0) ? 'tutor-qna-left' : 'tutor-qna-right';
                        $css_style = ($is_single && $answer->comment_parent!=0) ? 'margin-left:14%;'.$reply_hidden : '';
                        ?>
                        <div class="tutor-qna-chat <?php echo $css_class; ?> " style="<?php echo $css_style; ?>">
                            <div class="tutor-qna-user">
                                <img src="<?php echo get_avatar_url($answer->user_id); ?>" />
                                <div>
                                    <strong><?php echo $answer->display_name; ?></strong>
                                    <small class="text-muted">
                                        <?php echo sprintf(__('%s ago', 'tutor'), human_time_diff(strtotime($answer->comment_date))); ?>
                                    </small>
                                </div>
                            </div>

                            <div class="tutor-qna-text">
                                <?php echo htmlspecialchars(strip_tags( $answer->comment_content )); ?>
                            </div>

                            <?php if($is_single && $answer->comment_parent==0): ?>
                                <div class="tutor-toggle-reply">
                                    <span><?php _e('Reply', 'tutor'); ?> <?php echo $reply_count ? '('.$reply_count.')' : ''; ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                }
            ?>
        </div>
        <div class="tutor-qa-reply" data-context="<?php echo $context; ?>" style="<?php echo $is_single ? $reply_hidden : ''; ?>">
            <textarea class="tutor-form-control" placeholder="<?php _e('Write here...', 'tutor'); ?>"></textarea>
            <div class="tutor-bs-d-flex tutor-bs-justify-content-end tutor-bs-align-items-center">
                <button type="submit" class="<?php echo is_admin() ? 'tutor-btn-wordpress' : ''; ?> tutor-btn tutor-is-xs tutor-ml-15">
                    <?php esc_html_e('Reply', 'tutor'); ?> 
                </button>
            </div>
        </div>
    </div>

    <?php 
        if($context=='backend-dashboard-qna-single'){
            // Right sidebar should be loaded at backend dashboard only
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
            <?php
        }
    ?>
</div>