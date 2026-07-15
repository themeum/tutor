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

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Avatar;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Color;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;

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

<div class="<?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $id_prefix . $comment_item->comment_ID ); ?>">
	<div 
		class="tutor-comment"
		x-data="{ repliesExpanded: false }"
		x-show="editingId !== <?php echo (int) $comment_item->comment_ID; ?>"
		x-cloak
		<?php if ( ! $is_reply ) : ?>
			@tutor:comment:replied.window="if ($event.detail.parentId === <?php echo (int) $comment_item->comment_ID; ?>) { repliesExpanded = true; replyingId = null; }"
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
								/* translators: %s human-readable time difference. */
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
						<?php SvgIcon::make()->name( Icon::ELLIPSES )->size( 16 )->color( Color::SECONDARY )->render(); ?>
					</button>
					<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
						<div class="tutor-popover-menu" style="min-width: 104px;">
							<button class="tutor-popover-menu-item" @click="editingId = <?php echo (int) $comment_item->comment_ID; ?>; $nextTick(() => $dispatch('tutor-focus-form-<?php echo esc_attr( $id_prefix . 'edit-form-' . (int) $comment_item->comment_ID ); ?>')); hide()">
								<?php SvgIcon::make()->name( Icon::EDIT_2 )->render(); ?>
								<?php esc_html_e( 'Edit', 'tutor' ); ?>
							</button>
							<button class="tutor-popover-menu-item" @click="handleDeleteComment({commentId: <?php echo esc_html( $comment_item->comment_ID ); ?>}); hide()">
								<?php SvgIcon::make()->name( Icon::DELETE_2 )->render(); ?>
								<?php esc_html_e( 'Delete', 'tutor' ); ?>
							</button>
						</div>
					</div>
				</div>
				<?php endif; ?>
			</div>

			<?php if ( ! $is_reply ) : ?>
				<div class="tutor-mt-6">
					<button class="tutor-comment-action-btn tutor-comment-action-btn-reply" @click="replyingId = replyingId === <?php echo (int) $comment_item->comment_ID; ?> ? null : <?php echo (int) $comment_item->comment_ID; ?>; if (replyingId) $nextTick(() => $dispatch('tutor-focus-form-lesson-comment-reply-form-<?php echo (int) $comment_item->comment_ID; ?>'))">
						<?php SvgIcon::make()->name( Icon::COMMENTS )->render(); ?>
						<?php esc_html_e( 'Reply', 'tutor' ); ?>
					</button>
				</div>

				<?php
				tutor_load_template(
					'learning-area.lesson.comment-form',
					array(
						'form_id'        => 'lesson-comment-reply-form-' . (int) $comment_item->comment_ID,
						'placeholder'    => __( 'Write your reply', 'tutor' ),
						'submit_handler' => 'handleReplyComment(data, ' . (int) $comment_item->comment_ID . ')',
						'cancel_handler' => 'reset(); replyingId = null',
						'is_pending'     => 'replyCommentMutation?.isPending',
						'class'          => 'tutor-comment-reply-form tutor-mt-6',
						'x_show'         => 'replyingId === ' . (int) $comment_item->comment_ID,
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
				'submit_handler' => 'handleEditComment(data,' . (int) $comment_item->comment_ID . ')',
				'cancel_handler' => 'reset(); editingId = null',
				'is_pending'     => 'editCommentMutation?.isPending',
				'class'          => 'tutor-comment-edit-form',
				'x_show'         => 'editingId === ' . (int) $comment_item->comment_ID,
				'default_value'  => $comment_item->comment_content,
			)
		);
		?>
	<?php endif; ?>
</div>
