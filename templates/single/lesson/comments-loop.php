<?php
/**
 * Lesson comments loop
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.6
 */

use TUTOR\Lesson;

?>
<?php if ( is_array( $comments ) && count( $comments ) ) : ?>
	<?php
	foreach ( $comments as $comment ) :
		$author_name = tutor_utils()->display_name( $comment->user_id );
		?>

		<div class="tutor-comments-list tutor-parent-comment tutor-mt-32" id="lesson-comment-<?php echo esc_attr( $comment->comment_ID ); ?>">
			<div class="comment-avatar">
				<img src="<?php echo esc_url( get_avatar_url( $comment->user_id ) ); ?>" alt="">
			</div>
			<div class="tutor-single-comment">
				<div class="tutor-actual-comment tutor-mb-12">
					<div class="tutor-comment-author">
						<span class="tutor-fs-6 tutor-fw-bold"><?php echo esc_html( $author_name ); ?></span>
						<span class="tutor-fs-7 tutor-ml-12 tutor-ml-sm-10">
						<?php echo esc_html( human_time_diff( strtotime( $comment->comment_date ), tutor_time() ) . __( ' ago', 'tutor' ) ); ?>
						</span>
					</div>
					<div class="tutor-comment-text tutor-fs-6 tutor-mt-4">
					<?php echo esc_textarea( $comment->comment_content ); ?>
					</div>
				</div>
				<div class="tutor-comment-actions tutor-ml-24">
					<span class="tutor-fs-6 tutor-color-secondary"><?php esc_html_e( 'Reply', 'tutor' ); ?></span>
				</div>

			<?php
			$replies = Lesson::get_comments(
				array(
					'post_id' => $lesson_id,
					'parent'  => $comment->comment_ID,
				)
			);
			?>
			<?php if ( is_array( $replies ) && count( $replies ) ) : ?>
					<?php foreach ( $replies as $reply ) : ?>
						<?php $replier_name = tutor_utils()->display_name( $reply->user_id ); ?>
						<div class="tutor-comments-list tutor-child-comment tutor-mt-32" id="lesson-comment-<?php echo esc_attr( $reply->comment_ID ); ?>">
							<div class="comment-avatar">
								<img src="<?php echo esc_url( get_avatar_url( $reply->user_id ) ); ?>" alt="">
							</div>
							<div class="tutor-single-comment">
								<div class="tutor-actual-comment tutor-mb-12">
									<div class="tutor-comment-author">
										<span class="tutor-fs-6 tutor-fw-bold">
											<?php echo esc_html( $replier_name ); ?>
										</span>
										<span class="tutor-fs-7 tutor-ml-0 tutor-ml-sm-10">
											<?php echo esc_html( human_time_diff( strtotime( $reply->comment_date ), tutor_time() ) . __( ' ago', 'tutor' ) ); ?>
										</span>
									</div>
									<div class="tutor-comment-text tutor-fs-6 tutor-mt-4">
										<?php echo esc_textarea( $reply->comment_content ); ?>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
				<form class="tutor-comment-box tutor-reply-box tutor-mt-20" method="post" tutor-comment-reply>
				<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
					<input type="hidden" name="action" value="tutor_reply_lesson_comment">
					<input type="hidden" name="is_lesson_comment" value="true">
					<div class="comment-avatar">
						<img src="<?php echo esc_url( get_avatar_url( get_current_user_id() ) ); ?>" alt="">
					</div>
					<div class="tutor-comment-textarea">
						<textarea placeholder="<?php esc_html_e( 'Write your comment here…', 'tutor' ); ?>" name="comment" class="tutor-form-control"></textarea>
						<input type="hidden" name="comment_post_ID" value="<?php echo esc_attr( $lesson_id ); ?>" />
						<input type="hidden" name="comment_parent" value="<?php echo esc_attr( $comment->comment_ID ); ?>" />
					</div>
					<div class="tutor-comment-submit-btn">
						<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-lesson-comment-reply">
						<?php esc_html_e( 'Reply', 'tutor' ); ?>
						</button>
					</div>
				</form>
			</div>
			<span class="tutor-comment-line"></span>
		</div>
	<?php endforeach; ?>
<?php endif; ?>
