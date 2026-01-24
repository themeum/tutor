<?php
/**
 * Comment Card Template
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Avatar;
use Tutor\Components\Constants\Size;
use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

/**
 * Variables available:
 *
 * @var $comment_item  Object  The comment object
 * @var $lesson_id     Int     The lesson ID
 * @var $user_id       Int     The current user ID
 * @var $is_reply      Bool    Whether this is a reply (default false)
 */

$is_reply  = $is_reply ?? false;
$id_prefix = $is_reply ? 'tutor-comment-reply-' : 'tutor-comment-';
$class     = $is_reply ? 'tutor-comment-reply-item' : 'tutor-comment-item';
?>

<div class="<?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $id_prefix . $comment_item->comment_ID ); ?>" x-data="{showEditForm: false}">
	<div 
		class="tutor-comment"
		x-data="{showReplyForm: false, repliesExpanded: false}"
		x-show="!showEditForm"
		<?php if ( ! $is_reply ) : ?>
			@tutor:comment:replied.window="if ($event.detail.parentId === <?php echo (int) $comment_item->comment_ID; ?>) { repliesExpanded = true; showReplyForm = false; }"
		<?php endif; ?>
	>
		<?php Avatar::make()->user( $comment_item->user_id )->size( Size::SIZE_40 )->render(); ?>

		<div class="tutor-comment-content tutor-flex-1">
			<div class="tutor-flex tutor-justify-between">
				<div>
					<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
						<span class="tutor-discussion-card-author">
							<?php echo esc_html( $comment_item->comment_author ); ?>
						</span> 
						<span class="tutor-text-subdued">
							<?php
								// Translators: %s is the time of comment.
								echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $comment_item->comment_date_gmt ) ) ) );
							?>
						</span>
					</div>
					<div class="tutor-p2 tutor-text-secondary">
						<?php echo wp_kses_post( $comment_item->comment_content ); ?>
					</div>
				</div>

				<?php if ( $user_id === (int) $comment_item->user_id ) : ?>
				<div x-data="tutorPopover({ placement: 'bottom-end' })">
					<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
						<?php tutor_utils()->render_svg_icon( Icon::ELLIPSES, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ); ?>
					</button>
					<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
						<div class="tutor-popover-menu" style="min-width: 104px;">
							<button class="tutor-popover-menu-item" @click="showEditForm = true; hide()">
								<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
								<?php esc_html_e( 'Edit', 'tutor' ); ?>
							</button>
							<button class="tutor-popover-menu-item" @click="handleDeleteComment({commentId: <?php echo esc_html( $comment_item->comment_ID ); ?>}); hide()">
								<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?>
								<?php esc_html_e( 'Delete', 'tutor' ); ?>
							</button>
						</div>
					</div>
				</div>
				<?php endif; ?>
			</div>

			<?php if ( ! $is_reply ) : ?>
				<div class="tutor-mt-6">
					<button class="tutor-comment-action-btn tutor-comment-action-btn-reply" @click="showReplyForm = !showReplyForm">
						<?php tutor_utils()->render_svg_icon( Icon::COMMENTS ); ?>
						<?php esc_html_e( 'Reply', 'tutor' ); ?>
					</button>
				</div>

				<?php
				tutor_load_template(
					'learning-area.lesson.comment-form',
					array(
						'form_id'        => 'lesson-comment-reply-form-' . (int) $comment_item->comment_ID,
						'placeholder'    => __( 'Write your reply', 'tutor' ),
						'submit_handler' => 'replyCommentMutation?.mutate({ ...data, comment_post_ID: ' . (int) $lesson_id . ', comment_parent: ' . (int) $comment_item->comment_ID . ', order: currentOrder })',
						'cancel_handler' => 'reset(); showReplyForm = false',
						'is_pending'     => 'replyCommentMutation?.isPending',
						'class'          => 'tutor-mt-6',
						'x_show'         => 'showReplyForm',
					)
				);
				?>

				<!-- Display Comment Replies -->
				<?php
				tutor_load_template(
					'learning-area.lesson.comment-replies',
					array(
						'lesson_id'    => $lesson_id,
						'comment_item' => $comment_item,
						'user_id'      => $user_id,
					)
				);
				?>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $user_id === (int) $comment_item->user_id ) : ?>
		<?php
		tutor_load_template(
			'learning-area.lesson.comment-form',
			array(
				'form_id'        => $id_prefix . 'edit-form-' . (int) $comment_item->comment_ID,
				'placeholder'    => $is_reply ? __( 'Write your reply', 'tutor' ) : __( 'Write your comment', 'tutor' ),
				'submit_handler' => 'editCommentMutation?.mutate({ ...data, comment_id: ' . (int) $comment_item->comment_ID . ' })',
				'cancel_handler' => 'reset(); showEditForm = false',
				'is_pending'     => 'editCommentMutation?.isPending',
				'class'          => $is_reply ? 'tutor-comment-edit-form tutor-mt-6' : 'tutor-comment-edit-form',
				'x_show'         => 'showEditForm',
				'default_values' => array( 'comment' => $comment_item->comment_content ),
			)
		);
		?>
	<?php endif; ?>
</div>
