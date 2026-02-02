<?php
/**
 * Tutor dashboard lesson comment card.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Constants\Size;
use TUTOR\Icon;
use Tutor\Components\Avatar;
use Tutor\Components\PreviewTrigger;
use Tutor\Helpers\UrlHelper;
use TUTOR\Lesson;

// Comment read-unread feature does not exist currently, will be added in future.
$is_unread  = 0;
$course     = get_post( tutor_utils()->get_course_id_by( 'lesson', $lesson_comment->comment_post_ID ) );
$last_reply = null;
$replies    = Lesson::get_comments(
	array(
		'parent' => $lesson_comment->comment_ID,
		'order'  => 'DESC',
	)
);

if ( ! empty( $replies ) ) {
	$last_reply = $replies[0];
}

$single_url = UrlHelper::add_query_params(
	$discussion_url,
	array(
		'tab' => 'lesson-comments',
		'id'  => $lesson_comment->comment_ID,
	)
);
?>
<div class="tutor-discussion-card <?php echo esc_attr( $is_unread ? 'unread' : '' ); ?>">
	<div class="tutor-flex tutor-gap-4 tutor-w-full" x-show="editingId !== <?php echo (int) $lesson_comment->comment_ID; ?>">
		<?php Avatar::make()->user( $lesson_comment->user_id )->size( Size::SIZE_32 )->render(); ?>
		<div class="tutor-discussion-card-content">
			<div class="tutor-discussion-card-top">
				<div class="tutor-discussion-card-author"><?php echo esc_html( $lesson_comment->comment_author ); ?></div>
				<div>
					<span class="tutor-text-subdued"><?php esc_html_e( 'comment on', 'tutor' ); ?></span> 
					<?php PreviewTrigger::make()->id( $lesson_comment->comment_post_ID )->render(); ?>
					<span class="tutor-text-subdued"><?php esc_html_e( 'in', 'tutor' ); ?></span> 
					<?php PreviewTrigger::make()->id( $course->ID )->render(); ?>
				</div>
			</div>
			<h6 class="tutor-discussion-card-title" id="tutor-lesson-comment-text-<?php echo (int) $lesson_comment->comment_ID; ?>"><?php echo wp_kses_post( $lesson_comment->comment_content ); ?></h6>
			<div class="tutor-discussion-card-meta">
				<button class="tutor-discussion-card-meta-reply-button">
					<?php esc_html_e( 'Reply', 'tutor' ); ?>
				</button>
				<div class="tutor-flex tutor-items-center tutor-gap-2">
					<?php tutor_utils()->render_svg_icon( Icon::COMMENTS, 20, 20 ); ?>
					<?php echo esc_html( count( $replies ) ); ?>
				</div>

				<?php if ( $last_reply ) { ?>
				<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-sm-ml-2">
					<?php Avatar::make()->user( $last_reply->user_id )->size( Size::SIZE_20 )->render(); ?>
					<div class="tutor-text-small"><?php echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $last_reply->comment_date_gmt ) ) ) ); //phpcs:ignore ?></div>
				</div>
				<?php } else { ?>
					<div class="tutor-text-small"><?php echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $lesson_comment->comment_date_gmt ) ) ) ); //phpcs:ignore ?></div>
				<?php } ?>
			</div>
		</div>
		<div class="tutor-discussion-card-actions">
			<a href="<?php echo esc_url( $single_url ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-sm-hidden">
				<?php esc_html_e( 'Reply', 'tutor' ); ?>
			</a>
			<?php if ( get_current_user_id() === (int) $lesson_comment->user_id ) : ?>
				<div x-data="tutorPopover({ placement: 'bottom-end' })">
					<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
						<?php tutor_utils()->render_svg_icon( Icon::ELLIPSES, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ); ?>
					</button>
					<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
						<div class="tutor-popover-menu" style="min-width: 104px;">
							<button class="tutor-popover-menu-item" @click="setEditing(<?php echo (int) $lesson_comment->comment_ID; ?>); hide()">
								<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
								<?php esc_html_e( 'Edit', 'tutor' ); ?>
							</button>
							<button class="tutor-popover-menu-item" @click="TutorCore.modal.showModal('tutor-comment-delete-modal', { commentId: <?php echo esc_html( $lesson_comment->comment_ID ); ?> }); hide()">
								<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?>
								<?php esc_html_e( 'Delete', 'tutor' ); ?>
							</button>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( get_current_user_id() === (int) $lesson_comment->user_id ) : ?>
		<div x-show="editingId === <?php echo (int) $lesson_comment->comment_ID; ?>" x-cloak class="tutor-w-full">
			<?php
				tutor_load_template(
					'dashboard.discussions.comment-form',
					array(
						'form_id'        => 'lesson-comment-edit-' . (int) $lesson_comment->comment_ID,
						'default_value'  => $lesson_comment->comment_content,
						'submit_handler' => '(data) => handleEditComment(data, ' . (int) $lesson_comment->comment_ID . ')',
						'cancel_handler' => 'reset(); editingId = null; focused = false',
						'is_pending'     => 'editCommentMutation?.isPending',
						'placeholder'    => __( 'Write your comment', 'tutor' ),
					)
				);
			?>
		</div>
	<?php endif; ?>
</div>
