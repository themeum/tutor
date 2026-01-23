<?php
/**
 * Lesson comment list template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Avatar;
use Tutor\Components\Button;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\InputField;
use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

if ( empty( $comment_list ) ) {
	return;
}
?>
<?php foreach ( $comment_list as $comment_item ) : ?>
<div class="tutor-comment-item" x-data="{showEditForm: false}">
	<div class="tutor-comment" x-data="{showReplyForm: false, repliesExpanded: false}" x-show="!showEditForm">
		<?php Avatar::make()->user( $comment_item->user_id )->size( Size::SIZE_40 )->render(); ?>
		<div class="tutor-flex-1">
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
			<div class="tutor-mt-6">
				<button class="tutor-comment-action-btn tutor-comment-action-btn-reply" @click="showReplyForm = !showReplyForm">
					<?php tutor_utils()->render_svg_icon( Icon::COMMENTS ); ?>
					<?php esc_html_e( 'Reply', 'tutor' ); ?>
				</button>
			</div>
			<form 
				class="tutor-comment-reply-form tutor-mt-6" 
				x-show="showReplyForm" 
				x-collapse
				x-data="{ ...tutorForm({ id: 'lesson-comment-reply-form', mode: 'onChange' }), focused: false }"
				x-bind="getFormBindings()"
				@submit.prevent="handleSubmit((data) => replyCommentMutation?.mutate({ ...data, comment_post_ID: <?php echo esc_html( $lesson_id ); ?>, comment_parent: <?php echo esc_html( $comment_item->comment_ID ); ?>, order: currentOrder }))($event)"
			>
				<?php
				InputField::make()
					->type( InputType::TEXTAREA )
					->name( 'comment' )
					->placeholder( __( 'Write your reply', 'tutor' ) )
					->attr( 'x-bind', "register('comment', { required: '" . esc_js( __( 'Please enter a reply', 'tutor' ) ) . "' })" )
					->attr( '@focus', 'focused = true' )
					->attr( '@keydown', 'handleKeydown($event)' )
					->render();
				?>
				<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mt-5" x-cloak :class="{ 'tutor-hidden': !focused }">
					<div class="tutor-tiny tutor-text-subdued tutor-flex tutor-items-center tutor-gap-2">
						<?php tutor_utils()->render_svg_icon( Icon::COMMAND, 12, 12 ); ?> 
						<?php esc_html_e( 'Cmd/Ctrl +', 'tutor' ); ?>
						<?php tutor_utils()->render_svg_icon( Icon::ENTER, 12, 12 ); ?> 
						<?php esc_html_e( 'Enter to Save	', 'tutor' ); ?>
					</div>
					<div class="tutor-flex tutor-items-center tutor-gap-4">
						<?php
						Button::make()
							->label( __( 'Cancel', 'tutor' ) )
							->variant( Variant::GHOST )
							->size( Size::X_SMALL )
							->attr( 'type', 'button' )
							->attr( '@click', 'reset(); focused = false; showReplyForm = false' )
							->attr( ':disabled', 'replyCommentMutation?.isPending' )
							->render();

						Button::make()
							->label( __( 'Save', 'tutor' ) )
							->variant( Variant::PRIMARY_SOFT )
							->size( Size::X_SMALL )
							->attr( 'type', 'submit' )
							->attr( ':disabled', 'replyCommentMutation?.isPending' )
							->attr( ':class', "{ 'tutor-btn-loading': replyCommentMutation?.isPending }" )
							->render();
						?>
					</div>
				</div>
			</form>

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
		</div>
	</div>
	<?php if ( $user_id === (int) $comment_item->user_id ) : ?>
	<form 
		class="tutor-comment-edit-form" 
		x-show="showEditForm" 
		x-collapse
		x-data="tutorForm({ id: 'lesson-comment-edit-form', mode: 'onChange', defaultValues: { comment: '<?php echo esc_js( $comment_item->comment_content ); ?>' } })"
		x-bind="getFormBindings()"
		@submit.prevent="handleSubmit((data) => editCommentMutation?.mutate({ ...data, comment_id: <?php echo esc_html( $comment_item->comment_ID ); ?> }))($event)"
	>
		<?php
		InputField::make()
			->type( InputType::TEXTAREA )
			->name( 'comment' )
			->placeholder( __( 'Write your comment', 'tutor' ) )
			->attr( 'x-bind', "register('comment', { required: '" . esc_js( __( 'Please enter a comment', 'tutor' ) ) . "' })" )
			->attr( '@keydown', 'handleKeydown($event)' )
			->render();
		?>
		<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mt-5">
			<div class="tutor-tiny tutor-text-subdued tutor-flex tutor-items-center tutor-gap-2">
				<?php tutor_utils()->render_svg_icon( Icon::COMMAND, 12, 12 ); ?> 
				<?php esc_html_e( 'Cmd/Ctrl +', 'tutor' ); ?>
				<?php tutor_utils()->render_svg_icon( Icon::ENTER, 12, 12 ); ?> 
				<?php esc_html_e( 'Enter to Save	', 'tutor' ); ?>
			</div>
			<div class="tutor-flex tutor-items-center tutor-gap-4">
				<?php
				Button::make()
					->label( __( 'Cancel', 'tutor' ) )
					->variant( Variant::GHOST )
					->size( Size::X_SMALL )
					->attr( 'type', 'button' )
					->attr( '@click', 'reset(); showEditForm = false' )
					->attr( ':disabled', 'editCommentMutation?.isPending' )
					->render();

				Button::make()
					->label( __( 'Save', 'tutor' ) )
					->variant( Variant::PRIMARY_SOFT )
					->size( Size::X_SMALL )
					->attr( 'type', 'submit' )
					->attr( ':disabled', 'editCommentMutation?.isPending' )
					->attr( ':class', "{ 'tutor-btn-loading': editCommentMutation?.isPending }" )
					->render();
				?>
			</div>
		</div>
	</form>
	<?php endif; ?>
</div>
<?php endforeach; ?>
