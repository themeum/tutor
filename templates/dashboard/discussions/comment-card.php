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
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
use Tutor\Components\Avatar;
use Tutor\Components\Button;
use Tutor\Components\Constants\Variant;
use Tutor\Components\PreviewTrigger;
use Tutor\Helpers\UrlHelper;
use TUTOR\Lesson;

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
<div class="tutor-discussion-card" data-comment-id="<?php echo esc_attr( (int) $lesson_comment->comment_ID ); ?>" x-data="tutorPopover({ placement: 'bottom-end' })" :class="{ 'active': open }">
	<div class="tutor-flex tutor-gap-4 tutor-w-full" x-show="editingId !== <?php echo (int) $lesson_comment->comment_ID; ?>">
		<?php Avatar::make()->user( $lesson_comment->user_id )->size( Size::SIZE_32 )->render(); ?>
		<div class="tutor-discussion-card-content">
			<div class="tutor-discussion-card-top">
				<div class="tutor-discussion-card-author tutor-flex-shrink-0"><?php echo esc_html( $lesson_comment->comment_author ); ?></div>
				<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-overflow-hidden">
					<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-min-w-0">
						<span class="tutor-text-subdued tutor-flex-shrink-0"><?php esc_html_e( 'comment on', 'tutor' ); ?></span> 
						<div class="tutor-min-w-0 tutor-flex-1">
							<?php PreviewTrigger::make()->id( $lesson_comment->comment_post_ID )->render(); ?>
						</div>
					</div>
					<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-min-w-0">
						<span class="tutor-text-subdued tutor-flex-shrink-0"><?php esc_html_e( 'in', 'tutor' ); ?></span> 
						<div class="tutor-min-w-0 tutor-flex-1">
							<?php PreviewTrigger::make()->id( $course->ID )->render(); ?>
						</div>
					</div>
				</div>
			</div>
			<a href="<?php echo esc_url( $single_url ); ?>" class="tutor-discussion-card-title tutor-break-words" id="<?php echo esc_attr( 'tutor-lesson-comment-text-' . (int) $lesson_comment->comment_ID ); ?>"><?php echo wp_kses_post( $lesson_comment->comment_content ); ?></a>
			<div class="tutor-discussion-card-meta tutor-sm-mt-4">
				<button 
					@click="toggleCommentReply(<?php echo (int) $lesson_comment->comment_ID; ?>)"
					class="tutor-discussion-card-meta-reply-button"
					type="button"
				>
					<?php esc_html_e( 'Reply', 'tutor' ); ?>
				</button>

				<a href="<?php echo esc_url( $single_url ); ?>" class="tutor-flex tutor-items-center tutor-gap-2">
					<?php SvgIcon::make()->name( Icon::COMMENTS )->size( 20 )->render(); ?>
					<span class="tutor-discussion-card-reply-count tutor-text-subdued"><?php echo esc_html( count( $replies ) ); ?></span>
				</a>

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
		<div class="tutor-discussion-card-actions" x-show="replyingCommentId !== <?php echo (int) $lesson_comment->comment_ID; ?>">
			<?php
			Button::make()
				->variant( Variant::PRIMARY )
				->size( Size::X_SMALL )
				->label( __( 'Reply', 'tutor' ) )
				->attr( '@click', 'toggleCommentReply(' . (int) $lesson_comment->comment_ID . ')' )
				->attr( 'class', 'tutor-force-sm-hidden' )
				->attr( 'type', 'button' )
				->size( Size::X_SMALL )
				->render();
			?>
			<?php if ( get_current_user_id() === (int) $lesson_comment->user_id ) : ?>
				<div class="tutor-flex">
					<?php
					Button::make()
						->label( __( 'More options', 'tutor' ) )
						->variant( Variant::SECONDARY )
						->size( Size::X_SMALL )
						->icon( Icon::ELLIPSES, 'left', Size::SIZE_16, Color::SECONDARY )
						->icon_only()
						->attr( 'x-ref', 'trigger' )
						->attr( '@click', 'toggle()' )
						->render();
					?>
					<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
						<div class="tutor-popover-menu" style="min-width: 104px;">
							<button class="tutor-popover-menu-item tutor-gap-5" @click="setEditing(<?php echo (int) $lesson_comment->comment_ID; ?>); hide()">
								<?php SvgIcon::make()->name( Icon::EDIT_2 )->size( 20 )->render(); ?>
								<?php esc_html_e( 'Edit', 'tutor' ); ?>
							</button>
							<button class="tutor-popover-menu-item tutor-gap-5" @click="TutorCore.modal.showModal('tutor-comment-delete-modal', { commentId: <?php echo esc_html( $lesson_comment->comment_ID ); ?> }); hide()">
								<?php SvgIcon::make()->name( Icon::DELETE_2 )->size( 20 )->render(); ?>
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

	<div x-show="replyingCommentId === <?php echo (int) $lesson_comment->comment_ID; ?>" x-cloak class="tutor-card tutor-surface-l1-hover tutor-mt-4 tutor-w-full">
		<?php
			tutor_load_template(
				'dashboard.discussions.comment-form',
				array(
					'form_id'        => 'lesson-comment-reply-form-' . (int) $lesson_comment->comment_ID,
					'submit_handler' => '(data) => handleReplyComment(data, ' . (int) $lesson_comment->comment_ID . ', ' . (int) $lesson_comment->comment_post_ID . ', "list")',
					'cancel_handler' => 'setReplyingComment(null)',
					'is_pending'     => 'replyCommentMutation?.isPending',
					'placeholder'    => __( 'Write your reply', 'tutor' ),
					'label'          => __( 'Reply', 'tutor' ),
					'submit_label'   => __( 'Save', 'tutor' ),
				)
			);
			?>
	</div>
</div>
