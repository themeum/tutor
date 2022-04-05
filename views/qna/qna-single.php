<?php
	extract( $data ); // $question_id

	// QNA data
	$question = tutor_utils()->get_qa_question( $question_id );
	if (property_exists($question, 'meta')) {
		$meta     = $question->meta;
	}
	$answers  = tutor_utils()->get_qa_answer_by_question( $question_id );
	$back_url = isset( $back_url ) ? $back_url : remove_query_arg( 'question_id', tutor()->current_url );

	// Badges data
	$_user_id	   = get_current_user_id();
	if (property_exists($question, 'user_id')) {
		$is_user_asker = $question->user_id == $_user_id;
	}
	$id_slug 	   = $is_user_asker ? '_'.$_user_id : '';
	$is_solved     = (int) tutor_utils()->array_get( 'tutor_qna_solved'.$id_slug, $meta, 0 );
	$is_important  = (int) tutor_utils()->array_get( 'tutor_qna_important'.$id_slug, $meta, 0 );
	$is_archived   = (int) tutor_utils()->array_get( 'tutor_qna_archived'.$id_slug, $meta, 0 );
	$is_read       = (int) tutor_utils()->array_get( 'tutor_qna_read'.$id_slug, $meta, 0 );

	$modal_id     = 'tutor_qna_delete_single_' . $question_id;
	$reply_hidden = ! wp_doing_ajax() ? 'display:none;' : 0;

	// At first set this as read
	update_comment_meta( $question_id, 'tutor_qna_read'.$id_slug, 1 );
?>

<div class="tutor-qna-single-question" data-course_id="<?php echo $question->course_id; ?>" data-question_id="<?php echo $question_id; ?>" data-context="<?php echo $context; ?>">
	<div id="<?php echo $modal_id; ?>" class="tutor-modal">
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
						<button class="tutor-btn tutor-btn-primary tutor-list-ajax-action tutor-ml-20" data-request_data='{"question_id":<?php echo $question_id; ?>,"action":"tutor_delete_dashboard_question"}' data-redirect_to="<?php echo $back_url; ?>">
							<?php esc_html_e('Yes, Delete This', 'tutor'); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-qna-single-wrapper">
		<?php if ( in_array( $context, array( 'backend-dashboard-qna-single', 'frontend-dashboard-qna-single' ) ) ) : ?>
			<div class="tutor-qa-sticky-bar tutor-fs-6 tutor-mb-24">
				<div>
					<a class="tutor-btn tutor-btn-ghost" href="<?php echo $back_url; ?>">
						<span class="tutor-icon-previous tutor-mr-8" area-hidden="true"></span>
						<?php _e('Back', 'tutor'); ?>
					</a>
				</div>
				<div class="tutor-qna-badges tutor-qna-badges-wrapper tutor-d-lg-flex tutor-align-items-center tutor-justify-end">
					<?php if ( ! $is_user_asker ) : ?>
						<span class="tutor-btn tutor-btn-ghost tutor-mr-16" data-action="solved" data-state-class-selector="i" data-state-class-0="tutor-icon-circle-mark-line" data-state-class-1="tutor-icon-circle-mark tutor-color-success" role="button">
							<i class="<?php echo $is_solved ? 'tutor-icon-circle-mark tutor-color-success active' : 'tutor-icon-circle-mark-line'; ?> tutor-mr-8"></i>
							<span><?php _e( 'Solved', 'tutor' ); ?></span>
						</span>
						
						<span class="tutor-btn tutor-btn-ghost tutor-mr-16" data-action="important" data-state-class-selector="i" data-state-class-0="tutor-icon-important-line" data-state-class-1="tutor-icon-important-bold">
							<i class="<?php echo $is_important ? 'tutor-icon-important-bold active' : 'tutor-icon-important-line'; ?> tutor-mr-8"></i>
							<span><?php _e( 'Important', 'tutor' ); ?></span>
						</span>

						<span class="tutor-btn tutor-btn-ghost tutor-mr-16" data-action="archived" data-state-text-selector="span" data-state-text-0="<?php _e( 'Archive', 'tutor' ); ?>" data-state-text-1="<?php _e( 'Un-Archive', 'tutor' ); ?>" data-state-class-selector="i" data-state-class-0="tutor-icon-archive" data-state-class-1="tutor-icon-archive">
							<i class="<?php echo $is_archived ? 'tutor-icon-archive active' : 'tutor-icon-archive'; ?> tutor-mr-8"></i>
							<span><?php $is_archived ? _e( 'Un-Archive', 'tutor' ) : _e( 'Archive', 'tutor' ); ?></span>
						</button>
					<?php endif; ?>
					<a href="javascript:;" class="tutor-btn tutor-btn-ghost" data-tutor-modal-target="<?php echo $modal_id; ?>">
						<i class="tutor-icon-trashcan-bold tutor-mr-8" area-hidden="true"></i>
						<?php _e( 'Delete', 'tutor' ); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>

		<div class="tutor-qa-reply-wrapper tutor-mt-20">
			<div class="tutor-qa-chatlist">
				<?php
					$current_user_id = get_current_user_id();
					$avata_url       = array();
					$is_single       = in_array( $context, array( 'course-single-qna-sidebar', 'course-single-qna-single' ) );

				if ( is_array( $answers ) && count( $answers ) ) {
					$reply_count = count( $answers ) - 1;
					foreach ( $answers as $answer ) {
						if ( ! isset( $avata_url[ $answer->user_id ] ) ) {
							// Get avatar url if not already got
							$avata_url[ $answer->user_id ] = get_avatar_url( $answer->user_id );
						}

						$css_class = ( $current_user_id != $answer->user_id || $answer->comment_parent == 0 ) ? 'tutor-qna-left' : 'tutor-qna-right';
						$css_style = ( $is_single && $answer->comment_parent != 0 ) ? 'margin-left:14%;' . $reply_hidden : '';
						?>
							<div class="tutor-qna-chat <?php echo $css_class; ?> " style="<?php echo $css_style; ?>">
								<div class="tutor-qna-user">
									<img src="<?php echo get_avatar_url( $answer->user_id ); ?>" />
									<div>
										<div class="tutor-fs-6 tutor-fw-medium tutor-color-black-70">
											<?php echo $answer->display_name; ?>
										</div>
										<div class="tutor-fs-7 tutor-color-muted">
											<?php echo sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $answer->comment_date ) ) ); ?>
										</div>
									</div>
								</div>

								<div class="tutor-qna-text tutor-fs-7">
									<?php echo esc_textarea( stripslashes( $answer->comment_content ) ); ?>
								</div>

							<?php if ( $is_single && $answer->comment_parent == 0 ) : ?>
									<div class="tutor-toggle-reply">
										<span><?php _e( 'Reply', 'tutor' ); ?> <?php echo $reply_count ? '(' . $reply_count . ')' : ''; ?></span>
									</div>
								<?php endif; ?>
							</div>
							<?php
					}
				}
				?>
			</div>
			<div class="tutor-qa-reply tutor-mt-12 tutor-mb-24" data-context="<?php echo $context; ?>" style="<?php echo $is_single ? $reply_hidden : ''; ?>">
				<textarea class="tutor-form-control" placeholder="<?php _e( 'Write here...', 'tutor' ); ?>"></textarea>
				<div class="tutor-d-flex tutor-align-items-center">
					<button data-back_url="<?php echo $back_url; ?>" type="submit" class="tutor-btn tutor-btn-primary tutor-btn-sm">
						<?php esc_html_e( 'Reply', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<?php
	if ( $context == 'backend-dashboard-qna-single' ) {
		?>
			<div class="tutor-qna-admin-sidebar">
				<div class="tutor-qna-user">
					<img src="<?php echo get_avatar_url( $question->user_id ); ?>"/>
					<div class="tutor-fs-4 tutor-fw-medium tutor-color-black tutor-mt-24"><?php echo $question->display_name; ?></div>
					<div class="tutor-fs-6 tutor-fw-medium tutor-color-black-60 tutor-mt-4"><?php echo $question->user_email; ?></div>
					<div class="tutor-user-social tutor-d-flex tutor-mt-24" style="column-gap: 4px;">
						<?php
							$tutor_user_social_icons = tutor_utils()->tutor_user_social_icons();

							foreach ( $tutor_user_social_icons as $key => $social_icon ) :
								$url                                    = get_user_meta( $question->user_id, $key, true );
								$tutor_user_social_icons[ $key ]['url'] = $url;
								if ( '' === $url ) {
									continue;
								}
						?>
							<a class="tutor-iconic-btn" href="<?php echo esc_url( $url ); ?>">
								<i class="<?php echo esc_attr( $social_icon['icon_classes'] ); ?>"></i>
							</a>
						<?php endforeach;?>
					</div>
				</div>
				<table class="tutor-ui-table tutor-ui-table-responsive tutor-ui-table-data-td-target">
					<tr>
						<td class="expand-btn" data-th="Collapse" data-td-target="tutor-asked-under-course" style="background-color: #F4F6F9;">
							<div class="tutor-d-flex tutor-justify-between tutor-align-items-center">
								<span class="tutor-color-black tutor-fs-6 tutor-fw-medium tutor-pl-12">
									<?php esc_html_e( 'Asked Under', 'tutor' ); ?>
								</span>
								<div class="tutor-icon-angle-down tutor-fs-6 tutor-color-primary has-data-td-target"></div>
							</div>

						</td>
					</tr>
					<tr>
						<td class="data-td-content" id="tutor-asked-under-course">
							<div class="td-toggle-content">
								<span class="tutor-color-black tutor-fs-6 tutor-fw-bold">
									<?php echo esc_html( $question->post_title ); ?>
								</span>
							</div>
						</td>
					</tr>
				</table>

				<table class="tutor-ui-table tutor-ui-table-responsive tutor-ui-table-data-td-target">
					<tr>
						<td class="expand-btn" data-th="Collapse" data-td-target="tutor-prev-question-history" style="background-color: #F4F6F9;">
							<div class="tutor-d-flex tutor-justify-between tutor-align-items-center">
								<span class="tutor-color-black tutor-fs-6 tutor-fw-medium tutor-pl-12">
									<?php esc_html_e( 'Previous Question History', 'tutor' ); ?>
								</span>
								<div class="tutor-icon-angle-down tutor-fs-6 tutor-color-primary has-data-td-target"></div>
							</div>

						</td>
					</tr>
					<tr>
						<td class="data-td-content" id="tutor-prev-question-history">
							<div class="td-toggle-content">
								<?php
									$own_questions = tutor_utils()->get_qa_questions( 0, 10, '', null, null, $question->user_id );
								?>
								<?php if ( count( $own_questions ) ) : ?>
									<div class="qna-previous-questions">
										<?php foreach ( $own_questions as $question ) : ?>
											<div>
												<span class="tutor-color-black-60 tutor-fs-7">
													<?php echo esc_html( date_i18n( get_option( 'date_format') , strtotime( $question->comment_date ) ) ); ?>,
													<?php echo esc_html( date_i18n( get_option( 'time_format') , strtotime( $question->comment_date ) ) ); ?>
												</span>
												<span class="tutor-color-black tutor-fs-6 tutor-fw-bold">
													<?php echo esc_textarea( $question->comment_content ); ?>
												</span>
												<span class="tutor-color-black-60">
													<?php esc_html_e( 'Course', 'tutor' ); ?></strong>: <?php echo esc_html( $question->post_title ); ?>
												</span>
											</div>
										<?php endforeach; ?>
									</div>
								<?php else : ?>
									<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
								<?php endif; ?>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<?php
	}
	?>
</div>