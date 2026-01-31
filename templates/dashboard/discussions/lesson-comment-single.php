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
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\EmptyState;
use Tutor\Components\InputField;
use Tutor\Components\PreviewTrigger;
use Tutor\Components\Sorting;
use Tutor\Helpers\UrlHelper;
use TUTOR\Icon;
use TUTOR\Input;
use TUTOR\Lesson;

$lesson_comment = get_comment( $discussion_id );
if ( ! $lesson_comment ) {
	EmptyState::make()->render();
	return;
}

$user_id = get_current_user_id();

$replies_order = Input::get( 'order', 'DESC' );
$replies       = Lesson::get_comment_replies( $discussion_id, $replies_order );

$course = get_post( tutor_utils()->get_course_id_by( 'lesson', $lesson_comment->comment_post_ID ) );

?>
<div class="tutor-discussion-single">
	<div class="tutor-flex tutor-justify-between tutor-p-6 tutor-border-b">
		<a 
			href="<?php echo esc_url( UrlHelper::add_query_params( $discussion_url, array( 'tab' => 'lesson-comments' ) ) ); ?>" 
			class="tutor-btn tutor-btn-secondary tutor-btn-small tutor-gap-2"
		>
			<?php tutor_utils()->render_svg_icon( Icon::ARROW_LEFT_2 ); ?>
			<?php esc_html_e( 'Back', 'tutor' ); ?>
		</a>
	</div>
	<div class="tutor-discussion-single-body tutor-p-6 tutor-border-b">
		<div class="tutor-flex tutor-gap-5 tutor-mb-5">
			<?php Avatar::make()->user( $lesson_comment->user_id )->size( Size::SIZE_40 )->render(); ?>
			<div>
				<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-small">
					<span class="tutor-discussion-card-author"><?php echo esc_html( $lesson_comment->comment_author ); ?></span> 
					<span class="tutor-text-secondary">
						<?php
							// Translators: %s is the time of comment.
							echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $lesson_comment->comment_date_gmt ) ) ) );
						?>
					</span>
				</div>
				<div class="tutor-tiny">
					<span class="tutor-text-subdued"><?php esc_html_e( 'comment on', 'tutor' ); ?></span> 
					<?php PreviewTrigger::make()->id( $lesson_comment->comment_post_ID )->render(); ?>
					<span class="tutor-text-subdued"><?php esc_html_e( 'in', 'tutor' ); ?></span> 
					<?php PreviewTrigger::make()->id( $course->ID )->render(); ?>
				</div>
			</div>
			<div class="tutor-ml-auto">
				<?php if ( $user_id === (int) $lesson_comment->user_id ) : ?>
				<div x-data="tutorPopover({ placement: 'bottom-end' })">
					<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
						<?php tutor_utils()->render_svg_icon( Icon::ELLIPSES, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ); ?>
					</button>
					<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
						<div class="tutor-popover-menu" style="min-width: 104px;">
							<button class="tutor-popover-menu-item" @click="editingId = <?php echo (int) $lesson_comment->comment_ID; ?>; hide()">
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
							'form_id'         => 'lesson-comment-edit-' . (int) $lesson_comment->comment_ID,
							'default_value'   => $lesson_comment->comment_content,
							'submit_handler'  => '(data) => handleEditComment(data, ' . (int) $lesson_comment->comment_ID . ')',
							'cancel_handler'  => 'reset(); editingId = null',
							'is_pending_prop' => 'editCommentMutation?.isPending',
							'placeholder'     => __( 'Write your comment', 'tutor' ),
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
			'form_id'            => 'lesson-comment-reply-form-' . $lesson_comment->comment_ID,
			'submit_handler'     => '(data) => replyCommentMutation?.mutate({ ...data, comment_post_ID: ' . (int) $course->ID . ', comment_parent: ' . (int) $lesson_comment->comment_ID . ' })',
			'cancel_handler'     => 'reset(); focused = false',
			'is_pending_prop'    => 'replyCommentMutation?.isPending',
			'placeholder'        => __( 'Just drop your response here!', 'tutor' ),
			'label'              => __( 'Reply', 'tutor' ),
			'submit_label'       => __( 'Save', 'tutor' ),
			'form_class'         => 'tutor-discussion-single-reply-form tutor-p-6 tutor-border-b',
			'show_shortcut_info' => true,
			'is_collapsible'     => true,
		)
	);
	?>
	<div class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5">
		<div class="tutor-small tutor-text-secondary">
			<?php esc_html_e( 'Replies', 'tutor' ); ?>
			<span class="tutor-text-primary tutor-font-medium">(<?php echo count( $replies ); ?>)</span>
		</div>
		<?php Sorting::make()->order( $replies_order )->render(); ?>
	</div>
	<?php if ( ! empty( $replies ) ) : ?>
	<div class="tutor-discussion-single-reply-list tutor-border-t">
		<?php foreach ( $replies as $reply ) : ?>
			<div class="tutor-discussion-reply-list-item">
				<div class="tutor-flex tutor-gap-5 tutor-w-full" x-show="editingId !== <?php echo (int) $reply->comment_ID; ?>">
					<?php Avatar::make()->user( $reply->user_id )->size( Size::SIZE_40 )->render(); ?>
					<div class="tutor-flex-1">
						<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
							<span class="tutor-discussion-card-author">
								<?php echo esc_html( $reply->comment_author ); ?>
							</span> 
							<span class="tutor-text-secondary">
								<?php
									// Translators: %s is the time of comment.
									echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $reply->comment_date_gmt ) ) ) );
								?>
							</span>
						</div>
						<div class="tutor-p2 tutor-text-secondary" id="tutor-lesson-comment-text-<?php echo (int) $reply->comment_ID; ?>">
							<?php echo wp_kses_post( $reply->comment_content ); ?>
						</div>
					</div>
					<?php if ( $user_id === (int) $reply->user_id ) : ?>
					<div x-data="tutorPopover({ placement: 'bottom-end' })" class="tutor-ml-auto">
						<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
							<?php tutor_utils()->render_svg_icon( Icon::ELLIPSES, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ); ?>
						</button>
						<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
							<div class="tutor-popover-menu" style="min-width: 104px;">
								<button class="tutor-popover-menu-item" @click="editingId = <?php echo (int) $reply->comment_ID; ?>; hide()">
									<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
									<?php esc_html_e( 'Edit', 'tutor' ); ?>
								</button>
								<button class="tutor-popover-menu-item" @click="TutorCore.modal.showModal('tutor-comment-delete-modal', { commentId: <?php echo esc_html( $reply->comment_ID ); ?> }); hide()">
									<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?>
									<?php esc_html_e( 'Delete', 'tutor' ); ?>
								</button>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</div>

				<?php if ( $user_id === (int) $reply->user_id ) : ?>
					<div x-show="editingId === <?php echo (int) $reply->comment_ID; ?>" x-cloak class="tutor-mt-5 tutor-w-full">
						<?php
							tutor_load_template(
								'dashboard.discussions.comment-form',
								array(
									'form_id'         => 'lesson-comment-edit-' . (int) $reply->comment_ID,
									'default_value'   => $reply->comment_content,
									'submit_handler'  => '(data) => handleEditComment(data, ' . (int) $reply->comment_ID . ')',
									'cancel_handler'  => 'reset(); editingId = null',
									'is_pending_prop' => 'editCommentMutation?.isPending',
									'placeholder'     => __( 'Write your comment', 'tutor' ),
								)
							);
						?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php
	ConfirmationModal::make()
		->id( 'tutor-comment-delete-modal' )
		->title( __( 'Delete This Comment?', 'tutor' ) )
		->message( __( 'Are you sure you want to delete this comment permanently? Please confirm your choice.', 'tutor' ) )
		->confirm_text( __( 'Yes, Delete This', 'tutor' ) )
		->confirm_handler( 'deleteCommentMutation?.mutate({ comment_id: payload?.commentId })' )
		->mutation_state( 'deleteCommentMutation' )
		->render();
	?>
</div>
