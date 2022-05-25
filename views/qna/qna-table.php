<?php
extract($data); // $qna_list, $context, $qna_pagination, $view_as

$page_key = 'qna-table';
$table_columns = include __DIR__ . '/contexts.php';
$view_as = isset($view_as) ? $view_as : (is_admin() ? 'instructor' : 'student');
?>
<?php if (is_array($qna_list) && count($qna_list)) : ?>
    <div class="tutor-table-responsive">
        <table data-qna_context="<?php echo $context; ?>" class="frontend-dashboard-qna-table-<?php echo $view_as; ?> tutor-table tutor-table-middle qna-list-table">
            <thead>
                <tr>
                    <?php foreach ($table_columns as $key => $column) : ?>
                        <th style="<?php echo $key == 'question' ? 'width: 40%;' : ''; ?>"><?php echo $key != 'action' ? $column : ''; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>
                <?php
                    $current_user_id = get_current_user_id();
                    foreach ( $qna_list as $qna ) :
                        $id_string_delete   = 'tutor_delete_qna_' . $qna->comment_ID;
                        $row_id             = 'tutor_qna_row_' . $qna->comment_ID;
                        $menu_id            = 'tutor_qna_menu_id_' . $qna->comment_ID;
                        $is_self            = $current_user_id == $qna->user_id;
                        $key_slug           = $context == 'frontend-dashboard-qna-table-student' ? '_' . $current_user_id : '';

                        $meta               = $qna->meta;
                        $is_solved          = (int)tutor_utils()->array_get('tutor_qna_solved' . $key_slug, $meta, 0);
                        $is_important       = (int)tutor_utils()->array_get('tutor_qna_important' . $key_slug, $meta, 0);
                        $is_archived        = (int)tutor_utils()->array_get('tutor_qna_archived' . $key_slug, $meta, 0);
                        $is_read            = (int)tutor_utils()->array_get('tutor_qna_read' . $key_slug, $meta, 0);
                ?>
                    <tr id="<?php echo $row_id; ?>" data-question_id="<?php echo $qna->comment_ID; ?>" class="<?php echo $is_read ? 'is-qna-read' : ''; ?>">
                        <?php foreach ($table_columns as $key => $column) : ?>
                            <td>
                                <?php if ( $key == 'checkbox' ) : ?>
                                    <div class="tutor-d-flex tutor-align-center">
                                        <input id="tutor-admin-list-<?php echo $qna->comment_ID; ?>" type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo $qna->comment_ID; ?>" />
                                    </div>
                                <?php elseif ( $key == 'student' ) : ?>
                                    <div class="tutor-d-flex tutor-align-center tutor-gap-2">
                                        <div class="tooltip-wrap tooltip-icon-custom tutor-qna-badges-wrapper tutor-mt-4">
                                            <span
                                                data-state-class-0="tutor-icon-important-line"
                                                data-state-class-1="tutor-icon-important-bold"
                                                data-action="important"
                                                data-state-class-selector="i"
                                            >
                                                <i class="<?php echo $is_important ? 'tutor-icon-important-bold' : 'tutor-icon-important-line'; ?>  tutor-cursor-pointer" area-hidden="true"></i>
                                            </span>

                                            <span class="tooltip-txt tooltip-bottom">
                                                <?php $is_important ? _e('This conversation is important', 'tutor') : _e('Mark this conversation as important', 'tutor'); ?>
                                            </span>
                                        </div>

                                        <?php echo tutor_utils()->get_tutor_avatar( $qna->user_id ); ?>

                                        <div>
                                            <div>
                                                <?php echo $qna->display_name; ?>
                                            </div>
                                            <div class="tutor-fs-7 tutor-color-muted tutor-mt-4">
                                                <?php echo human_time_diff(strtotime($qna->comment_date)); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif ( $key == 'question' ) : ?>
                                    <?php $content = ( stripslashes( $qna->comment_content ) ); ?>
                                    <a href="<?php echo add_query_arg(array('question_id' => $qna->comment_ID), tutor()->current_url); ?>">
                                        <div class="tutor-form-feedback tutor-qna-question-col <?php echo $is_read ? 'is-read' : ''; ?>">
                                            <i class="tutor-icon-bullet-point tutor-form-feedback-icon" area-hidden="true"></i>
                                            <div class="tutor-qna-desc">
                                                <div class="tutor-qna-content tutor-fs-6 tutor-fw-bold tutor-color-black">
                                                    <?php
                                                        $limit = 60;
                                                        $content = strlen($content) > $limit ? substr($content, 0, $limit) . '...' : $content;
                                                        echo esc_html($content);
                                                    ?>
                                                </div>
                                                <div class="tutor-fs-7 tutor-color-secondary">
                                                    <span class="tutor-fw-medium"><?php _e('Course'); ?>:</span>
                                                    <span><?php echo $qna->post_title; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                <?php elseif ( $key == 'reply' ) : ?>
                                    <?php echo $qna->answer_count; ?>
                                <?php elseif ( $key == 'waiting_since' ) : ?>
                                    <?php echo human_time_diff(strtotime($qna->comment_date)); ?>
                                <?php elseif ( $key == 'status' ) : ?>
                                    <div class="tooltip-wrap tooltip-icon-custom" >
                                        <i class="tutor-fs-4 <?php echo $is_solved ? 'tutor-icon-circle-mark tutor-color-success' : 'tutor-icon-circle-mark-line tutor-color-muted'; ?>"></i>
                                        <span class="tooltip-txt tooltip-bottom">
                                            <?php $is_solved ? _e('Solved', 'tutor') : _e('Unresolved Yet', 'tutor'); ?>
                                        </span>
                                    </div>
                                <?php elseif ( $key == 'action' ) : ?>
                                    <div class="tutor-d-flex tutor-align-center tutor-justify-end tutor-gap-1">
                                        <a href="<?php echo add_query_arg(array('question_id' => $qna->comment_ID), tutor()->current_url); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
                                            <?php _e('Reply', 'tutor-pro'); ?>
                                        </a>

                                        <div class="tutor-dropdown-parent">
                                            <button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
                                                <span class="tutor-icon-kebab-menu" area-hidden="true"></span>
                                            </button>
                                            <ul class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
                                                <?php if ($context != 'frontend-dashboard-qna-table-student') : ?>
                                                    <li class="tutor-qna-badges tutor-qna-badges-wrapper">
                                                        <a class="tutor-dropdown-item" href="#" data-action="archived" data-state-text-selector="[data-state-text]" data-state-class-selector="[data-state-class]" data-state-text-0="<?php _e('Archvie', 'tutor'); ?>" data-state-text-1="<?php _e('Un-archive', 'tutor'); ?>">
                                                            <span class="tutor-icon-archive tutor-mr-8" data-state-class></span>
                                                            <span data-state-text>
                                                                <?php $is_archived ?  _e('Un-archive', 'tutor') : _e('Archive', 'tutor'); ?>
                                                            </span>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                <li class="tutor-qna-badges tutor-qna-badges-wrapper">
                                                    <a class="tutor-dropdown-item" href="#" data-action="read" data-state-text-selector="[data-state-text]" data-state-class-selector="[data-state-class]" data-state-text-0="<?php _e('Mark as Read', 'tutor'); ?>" data-state-text-1="<?php _e('Mark as Unread', 'tutor'); ?>">
                                                        <span class="tutor-icon-envelope tutor-mr-8" data-state-class></span>
                                                        <span data-state-text>
                                                            <?php $is_read ? _e('Mark as Unread', 'tutor') :  _e('Mark as read', 'tutor'); ?>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="tutor-dropdown-item" href="#" data-tutor-modal-target="<?php echo $id_string_delete; ?>">
                                                        <span class="tutor-icon-trash-can-bold tutor-mr-8"></span>
                                                        <span><?php _e('Delete', 'tutor'); ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Delete confirmation modal -->
                                        <div id="<?php echo $id_string_delete; ?>" class="tutor-modal">
                                            <div class="tutor-modal-overlay"></div>
                                            <div class="tutor-modal-window">
                                                <div class="tutor-modal-content tutor-modal-content-white">
                                                    <button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
                                                        <span class="tutor-icon-times" area-hidden="true"></span>
                                                    </button>

                                                    <div class="tutor-modal-body tutor-text-center">
                                                        <div class="tutor-mt-48">
                                                            <img class="tutor-d-inline-block" src="<?php echo tutor()->url; ?>assets/images/icon-trash.svg" />
                                                        </div>

                                                        <div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12"><?php esc_html_e('Delete This Question?', 'tutor'); ?></div>
                                                        <div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e('All the replies also will be deleted.', 'tutor'); ?></div>
                                                        
                                                        <div class="tutor-d-flex tutor-justify-center tutor-my-48">
                                                            <button data-tutor-modal-close class="tutor-btn tutor-btn-outline-primary">
                                                                <?php esc_html_e('Cancel', 'tutor'); ?>
                                                            </button>
                                                            <button class="tutor-btn tutor-btn-primary tutor-list-ajax-action tutor-ml-20" data-request_data='{"question_id":<?php echo $qna->comment_ID; ?>,"action":"tutor_delete_dashboard_question"}' data-delete_element_id="<?php echo $row_id; ?>">
                                                                <?php esc_html_e('Yes, Delete This', 'tutor'); ?>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
<?php endif; ?>

<?php if ($qna_pagination['total_items'] > $qna_pagination['per_page']) : ?>
    <div class="tutor-mt-32">
        <?php
            $pagination_data = array(
                'base'        => !empty($qna_pagination['base']) ? $qna_pagination['base'] : null,
                'total_items' => $qna_pagination['total_items'],
                'per_page'    => $qna_pagination['per_page'],
                'paged'       => $qna_pagination['paged'],
            );
            $pagination_template = tutor()->path . 'views/elements/pagination.php';
            tutor_load_template_from_custom_path($pagination_template, $pagination_data);
        ?>
    </div>
<?php endif; ?>