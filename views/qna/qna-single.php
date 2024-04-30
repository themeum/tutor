<?php
/**
 * Tutor Q&A single page
 *
 * @package Tutor\Views
 * @subpackage Tutor\Q&A
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

use TUTOR\Input;

extract( $data ); // $question_id

// QNA data.
	$question = tutor_utils()->get_qa_question( $question_id );
if ( ! $question ) {
	tutor_utils()->tutor_empty_state();
	return;
}

if ( property_exists( $question, 'meta' ) ) {
	$meta = $question->meta;
}

	$answers  = tutor_utils()->get_qa_answer_by_question( $question_id );
	$back_url = isset( $back_url ) ? $back_url : remove_query_arg( 'question_id', tutor()->current_url );

	// Badges data.
	$_user_id = get_current_user_id();
if ( property_exists( $question, 'user_id' ) ) {
	$is_user_asker = $question->user_id == $_user_id;
}
	$id_slug      = $is_user_asker ? '_' . $_user_id : '';
	$is_solved    = (int) tutor_utils()->array_get( 'tutor_qna_solved' . $id_slug, $meta, 0 );
	$is_important = (int) tutor_utils()->array_get( 'tutor_qna_important' . $id_slug, $meta, 0 );
	$is_archived  = (int) tutor_utils()->array_get( 'tutor_qna_archived' . $id_slug, $meta, 0 );
	$is_read      = (int) tutor_utils()->array_get( 'tutor_qna_read' . $id_slug, $meta, 0 );

	$modal_id     = 'tutor_qna_delete_single_' . $question_id;
	$reply_hidden = ! wp_doing_ajax() ? 'display:none;' : 0;

	// At first set this as read.
	update_comment_meta( $question_id, 'tutor_qna_read' . $id_slug, 1 );
?>

<div class="tutor-qna-single-question<?php echo is_admin() ? ' tutor-admin-wrap' : ''; ?>" data-course_id="<?php echo esc_attr( $question->course_id ); ?>" data-question_id="<?php echo esc_attr( $question_id ); ?>" data-context="<?php echo esc_attr( $context ); ?>">
	<?php if ( in_array( $context, array( 'backend-dashboard-qna-single', 'frontend-dashboard-qna-single' ) ) ) : ?>
		<div class="<?php echo is_admin() ? 'tutor-wp-dashboard-header tutor-px-24 tutor-mb-24' : 'tutor-qa-sticky-bar'; ?>">
			<div class="tutor-row tutor-align-lg-center">
				<div class="tutor-col-lg">
					<div class="tutor-d-lg-flex tutor-align-lg-center tutor-px-12 tutor-py-16">
						<a class="tutor-btn tutor-btn-ghost" href="<?php echo esc_url( $back_url ); ?>">
							<span class="tutor-icon-previous tutor-mr-8" area-hidden="true"></span>
							<?php esc_html_e( 'Back', 'tutor' ); ?>
						</a>
					</div>
				</div>

				<div class="tutor-col-lg-auto">
					<div class="tutor-qna-badges tutor-qna-badges-wrapper">
						<?php if ( ! $is_user_asker ) : ?>
							<span class="tutor-btn tutor-btn-ghost tutor-mr-16" data-action="solved" data-state-class-selector="i" data-state-class-0="tutor-icon-circle-mark-line" data-state-class-1="tutor-icon-circle-mark tutor-color-success" role="button">
								<i class="<?php echo $is_solved ? 'tutor-icon-circle-mark tutor-color-success active' : 'tutor-icon-circle-mark-line'; ?> tutor-mr-8"></i>
								<span><?php esc_html_e( 'Solved', 'tutor' ); ?></span>
							</span>
							
							<span class="tutor-btn tutor-btn-ghost tutor-mr-16" data-action="important" data-state-class-selector="i" data-state-class-0="tutor-icon-important-line" data-state-class-1="tutor-icon-important-bold">
								<i class="<?php echo $is_important ? 'tutor-icon-important-bold active' : 'tutor-icon-important-line'; ?> tutor-mr-8"></i>
								<span><?php esc_html_e( 'Important', 'tutor' ); ?></span>
							</span>

							<span class="tutor-btn tutor-btn-ghost tutor-mr-16" data-action="archived" data-state-text-selector="span" data-state-text-0="<?php esc_html_e( 'Archive', 'tutor' ); ?>" data-state-text-1="<?php esc_html_e( 'Un-Archive', 'tutor' ); ?>" data-state-class-selector="i" data-state-class-0="tutor-icon-archive" data-state-class-1="tutor-icon-archive">
								<i class="<?php echo $is_archived ? 'tutor-icon-archive active' : 'tutor-icon-archive'; ?> tutor-mr-8"></i>
								<span><?php $is_archived ? esc_html_e( 'Un-Archive', 'tutor' ) : esc_html_e( 'Archive', 'tutor' ); ?></span>
							</span>
						<?php endif; ?>
						<span class="tutor-btn tutor-btn-ghost" data-tutor-modal-target="<?php echo esc_attr( $modal_id ); ?>">
							<i class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></i>
							<?php esc_html_e( 'Delete', 'tutor' ); ?>
						</span>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="<?php echo is_admin() ? 'tutor-admin-container' : ''; ?>">
		<div class="tutor-qna-course-title tutor-color-black tutor-fs-6 tutor-fw-bold tutor-mb-32<?php echo is_single_course( true ) || ( Input::has( 'action' ) ) ? ' tutor-d-none' : ''; ?>">
			<?php echo esc_html( $question->post_title ); ?>
			<div class="tutor-hr tutor-mt-20" area-hidden="true"></div>
		</div>
		<div class="tutor-qna-single-wrapper">
			<div class="tutor-qa-reply-wrapper tutor-mt-20">
				<div class="tutor-qa-chatlist">
					<?php
						$current_user_id = get_current_user_id();
						$avatar_url      = array();
						$is_single       = in_array( $context, array( 'course-single-qna-sidebar', 'course-single-qna-single' ) );

					if ( is_array( $answers ) && count( $answers ) ) {
						$reply_count = count( $answers ) - 1;
						foreach ( $answers as $answer ) {
							if ( ! isset( $avatar_url[ $answer->user_id ] ) ) {
								// Get avatar url if not already got.
								$avatar_url[ $answer->user_id ] = get_avatar_url( $answer->user_id );
							}

							$css_class   = ( $current_user_id != $answer->user_id || 0 == $answer->comment_parent ) ? 'tutor-qna-left' : 'tutor-qna-right';
							$css_style   = ( $is_single && $answer->comment_parent != 0 ) ? 'margin-left:14%;' . $reply_hidden : '';
							$reply_class = ( $is_single && $answer->comment_parent != 0 ) ? 'tutor-reply-msg' : '';
							?>
								<div class="tutor-qna-chat <?php echo esc_attr( $css_class . ' ' . $reply_class ); ?>" style="<?php echo esc_attr( $css_style ); ?>">
									<div class="tutor-qna-user">
										<div>
											<img src="<?php echo wp_kses( get_avatar_url( $answer->user_id ), tutor_utils()->allowed_avatar_tags() ); ?>" />
										</div>

										<div>
											<div class="tutor-fs-6 tutor-fw-medium tutor-color-secondary">
												<?php echo esc_html( $answer->display_name ); ?>
											</div>
											<div class="tutor-fs-7 tutor-color-muted">
												<?php
													$date     = human_time_diff( strtotime( $answer->comment_date_gmt ) );
													$time_ago = __( 'ago', 'tutor' );
													echo esc_html( $date . ' ' . $time_ago );
												?>
											</div>
										</div>
									</div>

									<div class="tutor-qna-text tutor-fs-7">
										<?php
											$content = stripslashes( $answer->comment_content );
											echo tutor()->has_pro ? wp_kses_post( $content ) : esc_textarea( $content );
										?>
									</div>

								<?php if ( $is_single && 0 == $answer->comment_parent ) : ?>
									<div class="tutor-toggle-reply">
										<span>
											<?php esc_html_e( 'Reply', 'tutor' ); ?> 
											<?php echo esc_html( $reply_count ? '(' . $reply_count . ')' : '' ); ?>
										</span>
									</div>
								<?php endif; ?>
								</div>
								<?php
						}
					}
					?>
				</div>
				<div class="tutor-qa-reply tutor-mt-12 tutor-mb-24 tutor-qna-reply-editor" data-context="<?php echo esc_attr( $context ); ?>" style="<?php echo esc_attr( $is_single ? $reply_hidden : '' ); ?>">
					<?php if ( function_exists( 'tutor_pro' ) ) : ?>
						<?php
							wp_editor(
								'',
								'tutor_qna_reply_editor_' . $question_id,
								tutor_utils()->text_editor_config(
									array(
										'plugins' => 'codesample',
										'tinymce' => array(
											'toolbar1' => 'bold,italic,underline,link,unlink,removeformat,image,bullist,codesample',
											'toolbar2' => '',
											'toolbar3' => '',
										),
									)
								)
							);
						?>
						<?php else : ?>
						<textarea class="tutor-form-control" placeholder="<?php esc_html_e( 'Write here...', 'tutor' ); ?>"></textarea>
					<?php endif; ?>

					<div class="tutor-d-flex tutor-align-center tutor-mt-12">
						<button data-back_url="<?php echo esc_url( $back_url ); ?>" type="submit" class="tutor-btn tutor-btn-primary tutor-btn-sm">
							<?php esc_html_e( 'Reply', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
	// Delete modal.
	tutor_load_template(
		'modal.confirm',
		array(
			'id'      => $modal_id,
			'image'   => 'icon-trash.svg',
			'title'   => __( 'Do You Want to Delete This Question?', 'tutor' ),
			'content' => __( 'All the replies also will be deleted.', 'tutor' ),
			'yes'     => array(
				'text'  => __( 'Yes, Delete This', 'tutor' ),
				'class' => 'tutor-list-ajax-action',
				'attr'  => array( 'data-request_data=\'{"action":"tutor_delete_dashboard_question", "question_id":"' . $question_id . '"}\'', 'data-redirect_to="' . $back_url . '"' ),
			),
		)
	);
	?>
