<?php 
    extract($data); // $qna_list, $context
    
    $page_key = 'qna-table';
    $table_columns = include __DIR__ . '/contexts.php';
?>
<table class="tutor-ui-table tutor-ui-table-responsive qna-list-table">
    <thead>
        <tr>
            <?php 
                foreach($table_columns as $key=>$column) {
                    echo '<th><span class="text-regular-small color-text-subsued">'. $column . '</span></th>';
                }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php 
            if(is_array($qna_list) && count($qna)) {
                foreach($qna_list as $qna) {
                    $id_string_delete = 'tutor_delete_qna_' . $qna->comment_ID;
                    $row_id = 'tutor_qna_row_' . $qna->comment_ID;
                    $menu_id =  'tutor_qna_menu_id_' . $qna->comment_ID;

                    $meta = $qna->meta;
                    $is_solved = (int)tutor_utils()->array_get('tutor_qna_solved', $meta, 0);
                    $is_important = (int)tutor_utils()->array_get('tutor_qna_important', $meta, 0);
                    $is_archived = (int)tutor_utils()->array_get('tutor_qna_archived', $meta, 0);
                    $is_read = (int)tutor_utils()->array_get('tutor_qna_read', $meta, 0);
                    ?>
                    <tr id="<?php echo $row_id; ?>" data-question_id="<?php echo $qna->comment_ID; ?>" class="<?php echo $is_read ? 'is-qna-read' : ''; ?>">
                        <?php 
                            foreach($table_columns as $key => $column) {
                                switch($key) {
                                    case 'checkbox' :
                                        ?>
                                        <td data-th="<?php _e('Mark', 'tutor'); ?>" class="tutor-shrink">
                                            <div class="td-checkbox tutor-bs-d-flex tutor-bs-align-items-center">
                                                <input id="tutor-admin-list-<?php echo $qna->comment_ID; ?>" type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo $qna->comment_ID; ?>" style="margin-top:0"/>
                                            </div>
                                        </td>
                                        <?php
                                        break;
                                        
                                    case 'student' :
                                        ?>
                                        <td data-th="<?php echo $column; ?>" class="tutor-qna-badges">
                                            <div class="td-avatar">
                                                <i data-state-class-0="ttr-msg-important-filled" data-state-class-1="ttr-msg-important-fill-filled" class="<?php echo $is_important ? 'ttr-msg-important-fill-filled' : 'ttr-msg-important-filled'; ?> tutor-icon-20 tutor-mr-10 tutor-cursor-pointer" data-action="important"></i>
                                                <img src="<?php echo esc_url(get_avatar_url($qna->user_id)); ?>" alt="<?php echo esc_attr($qna->display_name); ?> - <?php _e('Profile Picture', 'tutor'); ?>"/>
                                                <span class="text-medium-body color-text-primary">
                                                    <?php echo $qna->display_name; ?>
                                                </span>
                                                <a href="#" class="btn-text btn-detail-link color-design-dark">
                                                    <span class="ttr-detail-link-filled"></span>
                                                </a>
                                            </div>
                                        </td>
                                        <?php
                                        break;

                                    case 'question' :
                                        $content = htmlspecialchars( strip_tags($qna->comment_content) );
                                        ?>
                                        <td data-th="<?php echo $column; ?>" title="<?php echo $content; ?>">
                                            <span class="tutor-qna-title">
                                                <?php echo $content;?>
                                            </span>
                                            <small class="tutor-text-nowrap">
                                                <?php _e('Course'); ?>: <?php echo $qna->post_title; ?>
                                            </small>
                                        </td>
                                        <?php
                                        break;

                                    case 'reply' :
                                        ?>
                                        <td data-th="<?php echo $column; ?>">
                                            <span class="text-medium-caption color-text-primary">
                                                <?php echo $qna->answer_count; ?>
                                            </span>
                                        </td>
                                        <?php
                                        break;

                                    case 'waiting_since' :
                                        ?>
                                        <td data-th="<?php echo $column; ?>">
                                            <?php echo human_time_diff(strtotime($qna->comment_date)); ?>
                                        </td>
                                        <?php
                                        break;

                                    case 'status' :
                                        ?>
                                        <td data-th="<?php echo $column; ?>">
                                            <div class="tooltip-wrap">
                                                <i class="ttr-tick-circle-outline-filled tutor-font-size-24 <?php echo $is_solved ? 'tutor-text-success' : ''; ?>"></i>
                                                <span class="tooltip-txt tooltip-bottom">
                                                    <?php $is_solved ? _e('Solved', 'tutor') : _e('Unresolved Yet', 'tutor'); ?>
                                                </span>
                                            </div>
                                        </td>
                                        <?php
                                        break;

                                    case 'action' :
                                        ?>
                                        <td data-th="<?php echo $column; ?>">
                                            <div class="inline-flex-center td-action-btns">
                                                <a href="<?php echo add_query_arg( array( 'question_id'=>$qna->comment_ID ), tutor()->current_url ); ?>" class="btn-outline tutor-btn">
                                                    <?php _e( 'Details', 'tutor-pro' ); ?>
                                                </a>

                                                <!-- ToolTip Action -->
                                                <div class="tutor-popup-opener">
                                                    <button type="button" class="popup-btn" data-tutor-popup-target="<?php echo $menu_id; ?>">
                                                        <span class="toggle-icon"></span>
                                                    </button>
                                                    <ul id="<?php echo $menu_id; ?>" class="popup-menu">
                                                        <?php if($context!='frontend-dashboard-qna-table-student'): ?>
                                                            <li class="tutor-qna-badges">
                                                                <a href="#" data-action="archived" data-state-text-selector=".text-regular-body" data-state-class-selector=".color-design-white"  data-state-text-0="<?php _e('Archvie', 'tutor'); ?>" data-state-text-1="<?php _e('Un-archive', 'tutor'); ?>">
                                                                    <span class="ttr-msg-archive-filled color-design-white tutor-font-size-24 tutor-mr-5"></span>
                                                                    <span class="text-regular-body color-text-white">
                                                                        <?php $is_archived ?  _e('Un-archive', 'tutor') : _e('Archive', 'tutor'); ?>
                                                                    </span>
                                                                </a>
                                                            </li>
                                                            <li class="tutor-qna-badges">
                                                                <a href="#" data-action="read" data-state-text-selector=".text-regular-body" data-state-class-selector=".color-design-white" data-state-text-0="<?php _e('Mark as Read', 'tutor'); ?>" data-state-text-1="<?php _e('Mark as Unread', 'tutor'); ?>">
                                                                    <span class="ttr-envelope-filled color-design-white tutor-font-size-24 tutor-mr-5"></span>
                                                                    <span class="text-regular-body color-text-white" >
                                                                        <?php $is_read ? _e('Mark as Unread', 'tutor') :  _e('Mark as read', 'tutor'); ?>
                                                                    </span>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <li>
                                                            <a href="#" data-tutor-modal-target="<?php echo $id_string_delete; ?>">
                                                                <span class="ttr-delete-fill-filled color-design-white tutor-font-size-24 tutor-mr-5"></span>
                                                                <span class="text-regular-body color-text-white"><?php _e('Delete', 'tutor'); ?></span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                
                                                <!-- Delete confirmation modal -->
                                                <div id="<?php echo $id_string_delete; ?>" class="tutor-modal">
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
                                                                    <button class="tutor-btn tutor-list-ajax-action" data-request_data='{"question_id":<?php echo $qna->comment_ID;?>,"action":"tutor_delete_dashboard_question"}' data-delete_element_id="<?php echo $row_id; ?>">
                                                                        <?php esc_html_e('Yes, Delete This', 'tutor'); ?>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <?php
                                        break;
                                }
                            }
                        ?>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
					<td colspan="100%">
						<?php tutor_utils()->tutor_empty_state( __( 'No Q&A found', 'tutor' ) ); ?>
					</td>
                </tr>
                <?php
            }
        ?>
    </tbody>
</table>