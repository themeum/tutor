<?php
/**
 * Tutor Q&A table
 *
 * @package Tutor\Views
 * @subpackage Tutor\Q&A
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

extract( $data ); // $qna_list, $context, $qna_pagination, $view_as

$page_key      = 'qna-table';
$table_columns = include __DIR__ . '/contexts.php';
$view_as       = isset( $view_as ) ? $view_as : ( is_admin() ? 'instructor' : 'student' );
?>
<?php if ( is_array( $qna_list ) && count( $qna_list ) ) : ?>
	<div class="tutor-table-responsive">
		<table data-qna_context="<?php echo esc_attr( $context ); ?>" class="frontend-dashboard-qna-table-<?php echo esc_attr( $view_as ); ?> tutor-table tutor-table-middle qna-list-table">
			<thead>
				<tr>
					<?php foreach ( $table_columns as $key => $column ) : ?>
						<th style="<?php echo esc_attr( 'question' == $key ? 'width: 40%;' : '' ); ?>">
							<?php echo ( 'action' != $key ? $column : '' ); //phpcs:ignore -- contain safe data ?>
						</th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<?php
					$current_user_id = get_current_user_id();
				foreach ( $qna_list as $qna ) :
					$id_string_delete = 'tutor_delete_qna_' . $qna->comment_ID;
					$row_id           = 'tutor_qna_row_' . $qna->comment_ID;
					$menu_id          = 'tutor_qna_menu_id_' . $qna->comment_ID;
					$is_self          = $current_user_id == $qna->user_id;
					$key_slug         = 'frontend-dashboard-qna-table-student' == $context ? '_' . $current_user_id : '';

					$meta         = $qna->meta;
					$is_solved    = (int) tutor_utils()->array_get( 'tutor_qna_solved' . $key_slug, $meta, 0 );
					$is_important = (int) tutor_utils()->array_get( 'tutor_qna_important' . $key_slug, $meta, 0 );
					$is_archived  = (int) tutor_utils()->array_get( 'tutor_qna_archived' . $key_slug, $meta, 0 );
					$is_read      = (int) tutor_utils()->array_get( 'tutor_qna_read' . $key_slug, $meta, 0 );
					?>
					<tr id="<?php echo esc_attr( $row_id ); ?>" data-question_id="<?php echo esc_attr( $qna->comment_ID ); ?>" class="<?php echo $is_read ? 'is-qna-read' : ''; ?>">
					<?php foreach ( $table_columns as $key => $column ) : ?>
							<td>
								<?php if ( 'checkbox' == $key ) : ?>
									<div class="tutor-d-flex tutor-align-center">
										<input id="tutor-admin-list-<?php echo esc_attr( $qna->comment_ID ); ?>" type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $qna->comment_ID ); ?>" />
									</div>
								<?php elseif ( 'student' == $key ) : ?>
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
												<?php $is_important ? esc_html_e( 'This conversation is important', 'tutor' ) : esc_html_e( 'Mark this conversation as important', 'tutor' ); ?>
											</span>
										</div>

										<?php
										echo wp_kses(
											tutor_utils()->get_tutor_avatar( $qna->user_id ),
											tutor_utils()->allowed_avatar_tags()
										);
										?>

										<div>
											<div>
												<?php echo esc_html( $qna->display_name ); ?>
											</div>
											<div class="tutor-fs-7 tutor-color-muted tutor-mt-4">
												<?php echo esc_html( human_time_diff( strtotime( $qna->comment_date ) ) ); ?>
											</div>
										</div>
									</div>
								<?php elseif ( 'question' == $key ) : ?>
									<?php $content = ( stripslashes( $qna->comment_content ) ); ?>
									<a href="<?php echo esc_url( add_query_arg( array( 'question_id' => $qna->comment_ID ), tutor()->current_url ) ); ?>">
										<div class="tutor-form-feedback tutor-qna-question-col <?php echo $is_read ? 'is-read' : ''; ?>">
											<i class="tutor-icon-bullet-point tutor-form-feedback-icon" area-hidden="true"></i>
											<div class="tutor-qna-desc">
												<div class="tutor-qna-content tutor-fs-6 tutor-fw-bold tutor-color-black">
													<?php
														$limit   = 60;
														$content = strlen( $content ) > $limit ? substr( $content, 0, $limit ) . '...' : $content;

                                                        echo $content //phpcs:ignore
													?>
												</div>
												<div class="tutor-fs-7 tutor-color-secondary">
													<span class="tutor-fw-medium"><?php esc_html_e( 'Course' ); ?>:</span>
													<span><?php echo esc_html( $qna->post_title ); ?></span>
												</div>
											</div>
										</div>
									</a>
								<?php elseif ( 'reply' == $key ) : ?>
									<?php echo esc_html( $qna->answer_count ); ?>
								<?php elseif ( 'waiting_since' == $key ) : ?>
									<?php echo esc_html( human_time_diff( strtotime( $qna->comment_date ) ) ); ?>
								<?php elseif ( 'status' == $key ) : ?>
									<div class="tooltip-wrap tooltip-icon-custom" >
										<i class="tutor-fs-4 <?php echo $is_solved ? 'tutor-icon-circle-mark tutor-color-success' : 'tutor-icon-circle-mark-line tutor-color-muted'; ?>"></i>
										<span class="tooltip-txt tooltip-bottom">
											<?php $is_solved ? esc_html_e( 'Solved', 'tutor' ) : esc_html_e( 'Unresolved Yet', 'tutor' ); ?>
										</span>
									</div>
								<?php elseif ( 'action' == $key ) : ?>
									<div class="tutor-d-flex tutor-align-center tutor-justify-end tutor-gap-1">
										<a href="<?php echo esc_url( add_query_arg( array( 'question_id' => $qna->comment_ID ), tutor()->current_url ) ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
											<?php esc_html_e( 'Reply', 'tutor' ); ?>
										</a>

										<div class="tutor-dropdown-parent">
											<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
												<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
											</button>
											<ul class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
												<?php if ( 'frontend-dashboard-qna-table-student' != $context ) : ?>
													<li class="tutor-qna-badges tutor-qna-badges-wrapper">
														<a class="tutor-dropdown-item" href="#" data-action="archived" data-state-text-selector="[data-state-text]" data-state-class-selector="[data-state-class]" data-state-text-0="<?php esc_attr_e( 'Archive', 'tutor' ); ?>" data-state-text-1="<?php esc_attr_e( 'Un-archive', 'tutor' ); ?>">
															<span class="tutor-icon-archive tutor-mr-8" data-state-class></span>
															<span data-state-text>
																<?php $is_archived ? esc_html_e( 'Un-archive', 'tutor' ) : esc_html_e( 'Archive', 'tutor' ); ?>
															</span>
														</a>
													</li>
												<?php endif; ?>
												<li class="tutor-qna-badges tutor-qna-badges-wrapper">
													<a class="tutor-dropdown-item" href="#" data-action="read" data-state-text-selector="[data-state-text]" data-state-class-selector="[data-state-class]" data-state-text-0="<?php esc_attr_e( 'Mark as Read', 'tutor' ); ?>" data-state-text-1="<?php esc_attr_e( 'Mark as Unread', 'tutor' ); ?>">
														<span class="tutor-icon-envelope tutor-mr-8" data-state-class></span>
														<span data-state-text>
															<?php $is_read ? esc_html_e( 'Mark as Unread', 'tutor' ) : esc_html_e( 'Mark as read', 'tutor' ); ?>
														</span>
													</a>
												</li>
												<li>
													<a class="tutor-dropdown-item" href="#" data-tutor-modal-target="<?php echo esc_attr( $id_string_delete ); ?>">
														<span class="tutor-icon-trash-can-bold tutor-mr-8"></span>
														<span><?php esc_html_e( 'Delete', 'tutor' ); ?></span>
													</a>
												</li>
											</ul>
										</div>

										<!-- Delete confirmation modal -->
										<div id="<?php echo esc_attr( $id_string_delete ); ?>" class="tutor-modal">
											<div class="tutor-modal-overlay"></div>
											<div class="tutor-modal-window">
												<div class="tutor-modal-content tutor-modal-content-white">
													<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
														<span class="tutor-icon-times" area-hidden="true"></span>
													</button>

													<div class="tutor-modal-body tutor-text-center">
														<div class="tutor-mt-48">
															<img class="tutor-d-inline-block" src="<?php echo esc_url( trailingslashit( tutor()->url ) . 'assets/images/icon-trash.svg' ); ?>" />
														</div>

														<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12"><?php esc_html_e( 'Delete This Question?', 'tutor' ); ?></div>
														<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'All the replies also will be deleted.', 'tutor' ); ?></div>
														
														<div class="tutor-d-flex tutor-justify-center tutor-my-48">
															<button data-tutor-modal-close class="tutor-btn tutor-btn-outline-primary">
																<?php esc_html_e( 'Cancel', 'tutor' ); ?>
															</button>
															<button class="tutor-btn tutor-btn-primary tutor-list-ajax-action tutor-ml-20" data-request_data='{"question_id":<?php echo esc_attr( $qna->comment_ID ); ?>,"action":"tutor_delete_dashboard_question"}' data-delete_element_id="<?php echo esc_attr( $row_id ); ?>">
																<?php esc_html_e( 'Yes, Delete This', 'tutor' ); ?>
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

<?php if ( $qna_pagination['total_items'] > $qna_pagination['per_page'] ) : ?>
	<div class="tutor-mt-32">
		<?php
			$pagination_data     = array(
				'base'        => ! empty( $qna_pagination['base'] ) ? $qna_pagination['base'] : null,
				'total_items' => $qna_pagination['total_items'],
				'per_page'    => $qna_pagination['per_page'],
				'paged'       => $qna_pagination['paged'],
			);
			$pagination_template = tutor()->path . 'views/elements/pagination.php';
			tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
			?>
	</div>
<?php endif; ?>
