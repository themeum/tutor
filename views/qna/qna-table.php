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

defined( 'ABSPATH' ) || exit;

$qna_list       = $data['qna_list'] ?? $qna_list ?? array();
$context        = $data['context'] ?? $context ?? '';
$qna_pagination = $data['qna_pagination'] ?? $qna_pagination ?? array();
$view_as        = $data['view_as'] ?? $view_as ?? ( is_admin() ? 'instructor' : 'student' );

$page_key      = 'qna-table';
$table_columns = include __DIR__ . '/contexts.php';
?>
<?php if ( is_array( $qna_list ) && count( $qna_list ) ) : ?>
	<div class="tutor-table-responsive">
		<table data-qna_context="<?php echo esc_attr( $context ); ?>" class="frontend-dashboard-qna-table-<?php echo esc_attr( $view_as ); ?> tutor-table tutor-table-middle qna-list-table">
			<thead>
				<tr>
					<?php foreach ( $table_columns as $key => $column ) : ?>
						<th style="<?php echo esc_attr( 'question' === $key ? 'width: 40%;' : '' ); ?>">
							<?php echo ( 'action' !== $key ? $column : '' ); //phpcs:ignore -- contain safe data ?>
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
					$is_self          = (int) $current_user_id === (int) $qna->user_id;
					$key_slug         = 'frontend-dashboard-qna-table-student' === $context ? '_' . $current_user_id : '';

					$meta         = is_array( $qna->meta ?? null ) ? $qna->meta : array();
					$is_solved    = (int) tutor_utils()->array_get( 'tutor_qna_solved' . $key_slug, $meta, 0 );
					$is_important = (int) tutor_utils()->array_get( 'tutor_qna_important' . $key_slug, $meta, 0 );
					$is_archived  = (int) tutor_utils()->array_get( 'tutor_qna_archived' . $key_slug, $meta, 0 );
					$is_read      = (int) tutor_utils()->array_get( 'tutor_qna_read' . $key_slug, $meta, 0 );
					?>
					<tr id="<?php echo esc_attr( $row_id ); ?>" data-question_id="<?php echo esc_attr( (string) $qna->comment_ID ); ?>" class="<?php echo $is_read ? 'is-qna-read' : ''; ?>">
					<?php foreach ( $table_columns as $key => $column ) : ?>
							<td>
								<?php if ( 'checkbox' === $key ) : ?>
									<div class="tutor-d-flex tutor-align-center">
										<input id="tutor-admin-list-<?php echo esc_attr( (string) $qna->comment_ID ); ?>" type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( (string) $qna->comment_ID ); ?>" />
									</div>
								<?php elseif ( 'student' === $key ) : ?>
									<div class="tutor-d-flex tutor-align-center tutor-gap-2">
										<div class="tooltip-wrap tooltip-icon-custom tutor-qna-badges-wrapper tutor-mt-4">
											<span
												data-action="solved"
												data-question_id="<?php echo esc_attr( (string) $qna->comment_ID ); ?>"
												data-state-class-selector="i"
												data-state-class-0="tutor-icon-circle-mark-line tutor-color-muted"
												data-state-class-1="tutor-icon-circle-mark tutor-color-success"
												role="button">
												<i class="tutor-fs-6 <?php echo $is_solved ? 'tutor-icon-circle-mark tutor-color-success' : 'tutor-icon-circle-mark-line tutor-color-muted'; ?>"></i>
											</span>
											<span class="tooltip-txt tooltip-top">
												<?php $is_solved ? esc_html_e( 'Solved', 'tutor' ) : esc_html_e( 'Mark as solved', 'tutor' ); ?>
											</span>
										</div>

										<div class="tutor-d-flex tutor-align-center">
											<?php
												echo wp_kses(
													tutor_utils()->get_tutor_avatar( $qna->user_id, 'sm' ),
													tutor_utils()->allowed_avatar_tags()
												);
											?>
											<div class="tutor-ml-12">
												<div class="tutor-fs-7 tutor-fw-medium tutor-color-black">
													<?php echo esc_html( $qna->comment_author ); ?>
												</div>
											</div>
										</div>
									</div>
								<?php elseif ( 'question' === $key ) : ?>
									<?php $content = ( stripslashes( $qna->comment_content ) ); ?>
									<a href="<?php echo esc_url( add_query_arg( array( 'question_id' => $qna->comment_ID ), tutor()->current_url ) ); ?>">
										<div class="tutor-form-feedback tutor-qna-question-col <?php echo $is_read ? 'is-read' : ''; ?>">
											<div class="tutor-qna-question-title tutor-fs-7">
												<div>
													<?php
														$limit   = 60;
														$content = strlen( $content ) > $limit ? substr( $content, 0, $limit ) . '...' : $content;

														echo esc_html( $content );
													?>
												</div>
												<div class="tutor-fs-7 tutor-color-secondary">
													<?php echo esc_html( $qna->post_title ); ?>
												</div>
											</div>
										</div>
									</a>
								<?php elseif ( 'reply' === $key ) : ?>
									<?php echo esc_html( $qna->answer_count ); ?>
								<?php elseif ( 'waiting_since' === $key ) : ?>
									<?php echo esc_html( human_time_diff( strtotime( $qna->comment_date ) ) ); ?>
								<?php elseif ( 'status' === $key ) : ?>
									<div class="tooltip-wrap tooltip-icon-custom" >
										<i class="tutor-fs-4 <?php echo $is_solved ? 'tutor-icon-circle-mark tutor-color-success' : 'tutor-icon-circle-mark-line tutor-color-muted'; ?> "></i>
										<span class="tooltip-txt tooltip-top">
											<?php $is_solved ? esc_html_e( 'Solved', 'tutor' ) : esc_html_e( 'Unresolved', 'tutor' ); ?>
										</span>
									</div>
								<?php elseif ( 'action' === $key ) : ?>
									<div class="tutor-d-flex tutor-align-center tutor-justify-end tutor-gap-1">
										<?php
											$query_args = array( 'question_id' => $qna->comment_ID );
											$view_url   = add_query_arg( $query_args, tutor()->current_url );
										?>
										<a class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" href="<?php echo esc_url( $view_url ); ?>">
											<?php esc_html_e( 'View', 'tutor' ); ?>
										</a>
										<div class="tutor-dropdown-parent">
											<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
												<span class="tutor-icon-kebab-menu" aria-hidden="true"></span>
											</button>
											<ul class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
												<?php if ( 'frontend-dashboard-qna-table-student' !== $context ) : ?>
													<li class="tutor-qna-badges tutor-qna-badges-wrapper">
														<a class="tutor-dropdown-item" href="#" data-action="archived" data-state-text-selector="[data-state-text]" data-state-class-selector="[data-state-class]" data-state-text-0="<?php esc_attr_e( 'Archive', 'tutor' ); ?>" data-state-text-1="<?php esc_attr_e( 'Un-archive', 'tutor' ); ?>">
															<span class="tutor-icon-archive tutor-mr-8" data-state-class></span>
															<span data-state-text><?php $is_archived ? esc_html_e( 'Un-archive', 'tutor' ) : esc_html_e( 'Archive', 'tutor' ); ?></span>
														</a>
													</li>
												<?php endif; ?>
												<li>
													<a class="tutor-dropdown-item" href="#" data-tutor-modal-target="<?php echo esc_attr( $id_string_delete ); ?>">
														<i class="tutor-icon-trash-can-bold tutor-mr-8" aria-hidden="true"></i>
														<span><?php esc_html_e( 'Delete', 'tutor' ); ?></span>
													</a>
												</li>
											</ul>
										</div>
									</div>
									<div id="<?php echo esc_attr( $id_string_delete ); ?>" class="tutor-modal tutor-modal-primary">
										<div class="tutor-modal-overlay"></div>
										<div class="tutor-modal-window">
											<div class="tutor-modal-content">
												<div class="tutor-modal-body tutor-text-center">
													<div class="tutor-modal-icon">
														<img src="<?php echo esc_url( tutor()->url . 'assets/images/icon-trash.svg' ); ?>" />
													</div>
													<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12">
														<?php esc_html_e( 'Do You Want to Delete This Question?', 'tutor' ); ?>
													</div>
													<div class="tutor-fs-6 tutor-color-muted">
														<?php esc_html_e( 'All the replies also will be deleted.', 'tutor' ); ?>
													</div>
													<div class="tutor-d-flex tutor-justify-center tutor-mt-48 tutor-mb-24 tutor-modal-actions">
														<div>
															<button data-tutor-modal-close class="tutor-btn tutor-btn-outline-primary">
																<?php esc_html_e( 'Cancel', 'tutor' ); ?>
															</button>
															<button class="tutor-btn tutor-btn-primary tutor-list-ajax-action tutor-ml-20" data-request_data='{"question_id":<?php echo esc_attr( (string) $qna->comment_ID ); ?>,"action":"tutor_delete_dashboard_question"}' data-delete_element_id="<?php echo esc_attr( $row_id ); ?>">
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

		<?php if ( isset( $qna_pagination['total_items'], $qna_pagination['per_page'] ) && $qna_pagination['total_items'] > $qna_pagination['per_page'] ) : ?>
			<div class="tutor-mt-32">
				<?php
					$pagination_data     = array(
						'base'        => ! empty( $qna_pagination['base'] ) ? $qna_pagination['base'] : null,
						'total_items' => $qna_pagination['total_items'],
						'per_page'    => $qna_pagination['per_page'],
						'paged'       => isset( $qna_pagination['paged'] ) ? $qna_pagination['paged'] : 1,
					);
					$pagination_template = tutor()->path . 'views/elements/pagination.php';
					tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
					?>
			</div>
		<?php endif; ?>
	</div>
<?php else : ?>
	<?php tutor_utils()->tutor_empty_state(); ?>
<?php endif; ?>
