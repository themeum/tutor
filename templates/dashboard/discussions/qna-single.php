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

defined( 'ABSPATH' ) || exit;


$question = tutor_utils()->get_qa_question( (int) $discussion_id );
if ( ! $question ) {
	EmptyState::make()->render();
	return;
}

$use_id        = get_current_user_id();
$is_user_asker = $use_id === (int) $question->user_id;

$replies_order = Input::get( 'order', '' );
$replies       = tutor_utils()->get_qa_answer_by_question( $discussion_id, $replies_order, 'frontend' );

$is_solved    = (int) tutor_utils()->array_get( 'tutor_qna_solved', $question->meta, 0 );
$is_important = (int) tutor_utils()->array_get( 'tutor_qna_important', $question->meta, 0 );
$is_archived  = (int) tutor_utils()->array_get( 'tutor_qna_archived', $question->meta, 0 );

?>
<div class="tutor-qna-single">
	<div class="tutor-flex tutor-justify-between tutor-p-6 tutor-border-b">
		<button type="button" class="tutor-btn tutor-btn-secondary tutor-btn-small tutor-gap-2">
			<?php tutor_utils()->render_svg_icon( Icon::ARROW_LEFT_2 ); ?>
			<?php esc_html_e( 'Back', 'tutor' ); ?>
		</button>
		<?php if ( ! $is_user_asker ) : ?>
		<div class="tutor-flex tutor-gap-2">
			<button 
				class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-gap-2 tutor-text-subdued"
				@click="handleQnASingleAction(<?php echo (int) $question->comment_ID; ?>, 'solved')"
				:class="{ 'tutor-btn-loading': qnaSingleActionMutation?.isPending && currentAction === 'solved' }"
				:disabled="qnaSingleActionMutation?.isPending"
			>
				<?php tutor_utils()->render_svg_icon( $is_solved ? Icon::COMPLETED_FILL : Icon::COMPLETED_CIRCLE, 16, 16, array( 'class' => $is_solved ? 'tutor-icon-success-primary' : 'tutor-icon-secondary' ) ); ?>
				<?php esc_html_e( 'Solved', 'tutor' ); ?>
			</button>

			<button 
				class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-gap-2 tutor-text-subdued"
				@click="handleQnASingleAction(<?php echo (int) $question->comment_ID; ?>, 'important')"
				:class="{ 'tutor-btn-loading': qnaSingleActionMutation?.isPending && currentAction === 'important' }"
				:disabled="qnaSingleActionMutation?.isPending"
			>
				<?php tutor_utils()->render_svg_icon( $is_important ? Icon::BOOKMARK_FILL : Icon::BOOKMARK, 16, 16, array( 'class' => $is_important ? 'tutor-icon-exception4' : 'tutor-icon-secondary' ) ); ?>
				<?php esc_html_e( 'Important', 'tutor' ); ?>
			</button>
		</div>
		<?php endif; ?>
	</div>
	<div class="tutor-qna-single-body tutor-p-6 tutor-border-b">
		<div class="tutor-flex tutor-gap-5 tutor-mb-5">
			<?php Avatar::make()->size( 40 )->render(); ?>
			<div>
				<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-small">
					<span class="tutor-qna-card-author"><?php echo esc_html( $question->comment_author ); ?></span> 
					<span class="tutor-text-secondary">
						<?php
							// Translators: %s is the time of comment.
							echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $question->comment_date_gmt ) ) ) );
						?>
					</span>
				</div>
				<div class="tutor-tiny">
					<span class="tutor-text-subdued">asked in</span> 
					<?php PreviewTrigger::make()->id( $question->course_id )->render(); ?>
				</div>
			</div>
			<div class="tutor-ml-auto">
				<?php if ( ! $is_user_asker ) : ?>
				<div x-data="tutorPopover({ placement: 'bottom-end', offset: 4 })" class="tutor-quiz-item-result-more">
					<button class="tutor-btn tutor-btn-ghost tutor-btn-icon tutor-btn-x-small" x-ref="trigger" @click="toggle()">
						<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL ); ?>
					</button>

					<div 
						x-ref="content"
						x-show="open"
						x-cloak
						@click.outside="handleClickOutside()"
						class="tutor-popover"
					>
						<div class="tutor-popover-menu" style="min-width: 120px;">
							<button 
								class="tutor-popover-menu-item"
								@click="handleQnASingleAction(<?php echo (int) $question->comment_ID; ?>, 'archived')"
								:disabled="qnaSingleActionMutation?.isPending"
							>
								<?php tutor_utils()->render_svg_icon( Icon::ARCHIVE_2 ); ?> 
								<?php $is_archived ? esc_html_e( 'Un-Archive', 'tutor' ) : esc_html_e( 'Archive', 'tutor' ); ?>
							</button>
							<button 
								class="tutor-popover-menu-item"
								@click="hide(); TutorCore.modal.showModal('tutor-qna-delete-modal', { questionId: <?php echo esc_html( (int) $question->comment_ID ); ?> });"
							>
								<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?> <?php esc_html_e( 'Delete', 'tutor' ); ?>
							</button>
						</div>
					</div>
				</div>
				<?php else : ?>
				<button
					class="tutor-btn tutor-btn-secondary tutor-btn-icon tutor-btn-x-small"
					@click="TutorCore.modal.showModal('tutor-qna-delete-modal', { questionId: <?php echo esc_html( (int) $question->comment_ID ); ?> });"
				>
					<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?>
				</button>
				<?php endif; ?>
			</div>
		</div>
		<div class="tutor-p1 tutor-font-medium tutor-text-secondary">
			<?php echo wp_kses_post( $question->comment_content ); ?>
		</div>
	</div>
	<?php $qna_form_id = 'qna-reply-form-' . $question->comment_ID; ?>
	<form 
		class="tutor-qna-single-reply-form tutor-p-6 tutor-border-b" 
		x-data="{ ...tutorForm({ id: '<?php echo esc_attr( $qna_form_id ); ?>' }), focused: false }"
		x-bind="getFormBindings()"
		@submit.prevent="handleSubmit((data) => handleSaveQnAReply({ ...data, course_id: <?php echo esc_html( $question->course_id ); ?>, question_id: <?php echo esc_html( $question->comment_ID ); ?> }))($event)"
	>
		<?php
		InputField::make()
			->type( InputType::TEXTAREA )
			->name( 'answer' )
			->label( __( 'Reply', 'tutor' ) )
			->placeholder( __( 'Just drop your response here!', 'tutor' ) )
			->attr( 'x-bind', "register('answer', { required: '" . esc_js( __( 'Please enter a response', 'tutor' ) ) . "' })" )
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
						->attr( ':disabled', 'createUpdateQnAMutation?.isPending' )
						->render();

					Button::make()
						->label( __( 'Save', 'tutor' ) )
						->variant( Variant::PRIMARY_SOFT )
						->size( Size::X_SMALL )
						->attr( 'type', 'submit' )
						->attr( ':disabled', 'createUpdateQnAMutation?.isPending' )
						->attr( ':class', "{ 'tutor-btn-loading': createUpdateQnAMutation?.isPending }" )
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
		->id( 'tutor-qna-delete-modal' )
		->title( __( 'Delete This Question?', 'tutor' ) )
		->message( __( 'Are you sure you want to delete this question permanently? Please confirm your choice.', 'tutor' ) )
		->confirm_text( __( 'Yes, Delete This', 'tutor' ) )
		->confirm_handler( 'handleDeleteQnA(payload?.questionId)' )
		->mutation_state( 'deleteQnAMutation' )
		->render();
	?>
</div>

