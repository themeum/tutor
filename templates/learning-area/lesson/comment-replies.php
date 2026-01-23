<?php
/**
 * Lesson comment replies template.
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
use TUTOR\Lesson;

defined( 'ABSPATH' ) || exit;

$replies     = Lesson::get_comments(
	array(
		'post_id' => $lesson_id,
		'parent'  => $comment_item->comment_ID,
	)
);
$reply_count = is_array( $replies ) ? count( $replies ) : 0;
?>
<?php if ( $reply_count > 0 ) : ?>
<div id="tutor-comment-replies-<?php echo esc_attr( $comment_item->comment_ID ); ?>" class="tutor-replies-wrapper">
	<div class="tutor-comment-replies" x-show="repliesExpanded" x-collapse>
		<?php foreach ( $replies as $reply_item ) : ?>
			<div class="tutor-comment-reply-item" id="tutor-comment-reply-<?php echo esc_attr( $reply_item->comment_ID ); ?>" x-data="{showReplyEditForm: false}">
				<div class="tutor-flex tutor-gap-5" x-show="!showReplyEditForm">
					<?php Avatar::make()->user( $reply_item->user_id )->size( Size::SIZE_40 )->render(); ?>
					<div class="tutor-flex-1">
						<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
							<span class="tutor-discussion-card-author">
								<?php echo esc_html( $reply_item->comment_author ); ?>
							</span> 
							<span class="tutor-text-subdued">
								<?php
									// Translators: %s is the time of comment.
									echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $reply_item->comment_date_gmt ) ) ) );
								?>
							</span>
						</div>
						<div class="tutor-p2 tutor-text-secondary">
							<?php echo wp_kses_post( $reply_item->comment_content ); ?>
						</div>
					</div>
					<?php if ( $user_id === (int) $reply_item->user_id ) : ?>
					<div x-data="tutorPopover({ placement: 'bottom-end' })">
						<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
							<?php tutor_utils()->render_svg_icon( Icon::ELLIPSES, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ); ?>
						</button>
						<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
							<div class="tutor-popover-menu" style="min-width: 104px;">
								<button class="tutor-popover-menu-item" @click="showReplyEditForm = true; hide()">
									<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
									<?php esc_html_e( 'Edit', 'tutor' ); ?>
								</button>
								<button class="tutor-popover-menu-item" @click="handleDeleteComment({commentId: <?php echo esc_html( $reply_item->comment_ID ); ?>}); hide()">
									<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?>
									<?php esc_html_e( 'Delete', 'tutor' ); ?>
								</button>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</div>
				<?php if ( $user_id === (int) $reply_item->user_id ) : ?>
				<form 
					class="tutor-comment-edit-form tutor-mt-6" 
					x-show="showReplyEditForm" 
					x-collapse
					x-data="tutorForm({ id: 'lesson-comment-edit-form', mode: 'onChange', defaultValues: { comment: '<?php echo esc_js( $reply_item->comment_content ); ?>' } })"
					x-bind="getFormBindings()"
					@submit.prevent="handleSubmit((data) => editCommentMutation?.mutate({ ...data, comment_id: <?php echo esc_html( $reply_item->comment_ID ); ?> }))($event)"
				>
					<?php
					InputField::make()
						->type( InputType::TEXTAREA )
						->name( 'comment' )
						->placeholder( __( 'Write your reply', 'tutor' ) )
						->attr( 'x-bind', "register('comment', { required: '" . esc_js( __( 'Please enter a reply', 'tutor' ) ) . "' })" )
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
								->attr( '@click', 'reset(); showReplyEditForm = false' )
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
	</div>
	<button 
		class="tutor-comment-replies-toggle"
		@click="repliesExpanded = !repliesExpanded"
		x-ref="repliesToggle"
	>
		<span x-show="repliesExpanded" x-cloak>
			<?php echo esc_html__( 'Collapse all replies', 'tutor' ); ?>
		</span>
		<span x-show="!repliesExpanded">
			<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of replies */
						_n( '%d more reply', '%d more replies', $reply_count, 'tutor' ),
						$reply_count
					)
				);
			?>
		</span>
	</button>
</div>
<?php endif; ?>
