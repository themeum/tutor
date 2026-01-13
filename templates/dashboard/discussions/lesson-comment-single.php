<?php
/**
 * Q&A replies template.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

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
use TUTOR\Icon;
use TUTOR\Input;
use TUTOR\Lesson;

defined( 'ABSPATH' ) || exit;


$lesson_comment = Lesson::get_single_comment( (int) $discussion_id );
if ( ! $lesson_comment ) {
	EmptyState::make()->render();
	return;
}

$replies_order = Input::get( 'order', '' );
$replies       = Lesson::get_comment_replies( $discussion_id, $replies_order );

$course = get_post( tutor_utils()->get_course_id_by( 'lesson', $lesson_comment->comment_post_ID ) );

?>
<div class="tutor-qna-single">
	<div class="tutor-flex tutor-justify-between tutor-p-6 tutor-border-b">
		<a href="<?php echo esc_url( $discussion_url ); ?>" class="tutor-btn tutor-btn-secondary tutor-btn-small tutor-gap-2">
			<?php tutor_utils()->render_svg_icon( Icon::ARROW_LEFT_2 ); ?>
			<?php esc_html_e( 'Back', 'tutor' ); ?>
		</a>
	</div>
	<div class="tutor-qna-single-body tutor-p-6 tutor-border-b">
		<div class="tutor-flex tutor-gap-5 tutor-mb-5">
			<?php Avatar::make()->size( Size::SIZE_40 )->render(); ?>
			<div>
				<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-small">
					<span class="tutor-qna-card-author"><?php echo esc_html( $lesson_comment->comment_author ); ?></span> 
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
				<button
					class="tutor-btn tutor-btn-secondary tutor-btn-icon tutor-btn-x-small"
					@click="TutorCore.modal.showModal('tutor-comment-delete-modal', { commentId: <?php echo esc_html( $lesson_comment->comment_ID ); ?> });"
				>
					<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?>
				</button>
			</div>
		</div>
		<div class="tutor-p1 tutor-font-medium tutor-text-secondary">
			<?php echo wp_kses_post( $lesson_comment->comment_content ); ?>
		</div>
	</div>
	<?php $lesson_comment_form_id = 'lesson-comment-reply-form-' . $lesson_comment->comment_ID; ?>
	<form 
		class="tutor-qna-single-reply-form tutor-p-6 tutor-border-b" 
		x-data="{ ...tutorForm({ id: '<?php echo esc_attr( $lesson_comment_form_id ); ?>' }), focused: false }"
		x-bind="getFormBindings()"
		@submit.prevent="handleSubmit((data) => replyCommentMutation?.mutate({ ...data, comment_post_ID: <?php echo esc_html( $course->ID ); ?>, comment_parent: <?php echo esc_html( $lesson_comment->comment_ID ); ?> }))($event)"
	>
		<?php
		InputField::make()
			->type( InputType::TEXTAREA )
			->name( 'comment' )
			->label( __( 'Reply', 'tutor' ) )
			->placeholder( __( 'Just drop your response here!', 'tutor' ) )
			->attr( 'x-bind', "register('comment', { required: '" . esc_js( __( 'Please enter a response', 'tutor' ) ) . "' })" )
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
						->attr( '@click', 'reset(); focused = false' )
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
	<div class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5">
		<div class="tutor-small tutor-text-secondary">
			<?php esc_html_e( 'Replies', 'tutor' ); ?>
			<span class="tutor-text-primary tutor-font-medium">(<?php echo count( $replies ); ?>)</span>
		</div>
		<?php Sorting::make()->order( $replies_order )->render(); ?>
	</div>
	<?php if ( ! empty( $replies ) ) : ?>
	<div class="tutor-qna-single-reply-list tutor-border-t">
		<?php foreach ( $replies as $reply ) : ?>
			<div class="tutor-qna-reply-list-item">
				<?php Avatar::make()->size( 40 )->render(); ?>
				<div>
					<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
						<span class="tutor-qna-card-author">
							<?php echo esc_html( $reply->comment_author ); ?>
						</span> 
						<span class="tutor-text-secondary">
							<?php
								// Translators: %s is the time of comment.
								echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $reply->comment_date_gmt ) ) ) );
							?>
						</span>
					</div>
					<div class="tutor-p2 tutor-text-secondary">
						<?php echo wp_kses_post( $reply->comment_content ); ?>
					</div>
				</div>
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

