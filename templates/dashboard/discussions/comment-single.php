<?php
/**
 * Lesson comment replies template.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Avatar;
use Tutor\Components\Button;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\Size;
use Tutor\Components\EmptyState;
use Tutor\Components\PreviewTrigger;
use Tutor\Helpers\UrlHelper;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
use Tutor\Components\Constants\Variant;
use TUTOR\Input;
use TUTOR\Lesson;

$lesson_comment = get_comment( $discussion_id );
if ( ! $lesson_comment ) {
	EmptyState::make()
		->icon( tutor_utils()->get_themed_svg( 'images/illustrations/comments-empty.svg' ) )
		->title( __( 'No Comments Found', 'tutor' ) )
		->render();
	return;
}

$user_id                 = get_current_user_id();
$comment_delete_modal_id = 'tutor-comment-delete-modal';

$replies_order = Input::get( 'order', 'DESC' );
$replies       = Lesson::get_comment_replies( $discussion_id, $replies_order );

$course = get_post( tutor_utils()->get_course_id_by( 'lesson', $lesson_comment->comment_post_ID ) );

?>
<div class="tutor-discussion-single">
	<div class="tutor-flex tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
		<a 
			href="<?php echo esc_url( UrlHelper::add_query_params( $discussion_url, array( 'tab' => 'lesson-comments' ) ) ); ?>" 
			class="tutor-btn tutor-btn-secondary tutor-btn-small tutor-gap-2"
		>
			<?php SvgIcon::make()->name( Icon::ARROW_LEFT_2 )->flip_rtl()->render(); ?>
			<?php esc_html_e( 'Back', 'tutor' ); ?>
		</a>
	</div>
	<div class="tutor-discussion-single-body tutor-p-6 tutor-border-b">
		<div class="tutor-flex tutor-gap-5 tutor-mb-5">
			<?php Avatar::make()->user( $lesson_comment->user_id )->size( Size::SIZE_40 )->render(); ?>
			<div class="tutor-min-w-0 tutor-flex-1">
				<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-small">
					<span class="tutor-discussion-card-author tutor-flex-shrink-0"><?php echo esc_html( $lesson_comment->comment_author ); ?></span> 
					<span class="tutor-text-secondary tutor-flex-shrink-0">
						<?php
							/* translators: %s human-readable time difference. */
							echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $lesson_comment->comment_date_gmt ) ) ) );
						?>
					</span>
				</div>
				<div class="tutor-tiny tutor-flex tutor-items-center tutor-gap-2 tutor-overflow-hidden">
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
			<div class="tutor-ml-auto">
				<?php if ( $user_id === (int) $lesson_comment->user_id ) : ?>
				<div x-data="tutorPopover({ placement: 'bottom-end' })">
					<?php
					Button::make()
						->label( __( 'More options', 'tutor' ) )
						->variant( Variant::GHOST )
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
							<button class="tutor-popover-menu-item tutor-gap-5" @click="TutorCore.modal.showModal('<?php echo esc_js( $comment_delete_modal_id ); ?>', { commentId: <?php echo esc_html( $lesson_comment->comment_ID ); ?> }); hide()">
								<?php SvgIcon::make()->name( Icon::DELETE_2 )->size( 20 )->render(); ?>
								<?php esc_html_e( 'Delete', 'tutor' ); ?>
							</button>
						</div>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<div x-show="editingId !== <?php echo (int) $lesson_comment->comment_ID; ?>">
			<div class="tutor-p1 tutor-font-medium tutor-text-secondary" id="tutor-lesson-comment-text-<?php echo (int) $lesson_comment->comment_ID; ?>">
				<?php echo wp_kses_post( $lesson_comment->comment_content ); ?>
			</div>
		</div>

		<?php if ( $user_id === (int) $lesson_comment->user_id ) : ?>
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
	<?php
	tutor_load_template(
		'dashboard.discussions.comment-form',
		array(
			'form_id'        => 'lesson-comment-reply-form-' . $lesson_comment->comment_ID,
			'submit_handler' => '(data) => handleReplyComment(data, ' . (int) $lesson_comment->comment_ID . ', ' . (int) $lesson_comment->comment_post_ID . ', "single")',
			'cancel_handler' => 'reset(); focused = false',
			'is_pending'     => 'replyCommentMutation?.isPending',
			'placeholder'    => __( 'Just drop your response here!', 'tutor' ),
			'label'          => __( 'Reply', 'tutor' ),
			'submit_label'   => __( 'Save', 'tutor' ),
			'form_class'     => 'tutor-discussion-single-reply-form tutor-p-6',
		)
	);
	?>
	<div id="tutor-discussion-replies-list">
		<?php
		tutor_load_template(
			'dashboard.discussions.comment-replies',
			array(
				'replies'       => $replies,
				'replies_order' => $replies_order,
				'user_id'       => $user_id,
			)
		);
		?>
	</div>

	<?php
	ConfirmationModal::make()
		->id( $comment_delete_modal_id )
		->title( __( 'Delete This Comment?', 'tutor' ) )
		->message( __( 'Are you sure you want to delete this comment permanently? Please confirm your choice.', 'tutor' ) )
		->confirm_text( __( 'Yes, Delete This', 'tutor' ) )
		->confirm_handler( 'deleteCommentMutation?.mutate({ comment_id: payload?.commentId, is_reply: payload?.isReply })' )
		->mutation_state( 'deleteCommentMutation' )
		->render();
	?>
</div>
