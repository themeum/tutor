<?php
/**
 * Tutor learning area Q&A single.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Avatar;
use Tutor\Components\Button;
use Tutor\Components\ConfirmationModal;
use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\EmptyState;
use Tutor\Components\Popover;
use Tutor\Components\Sorting;

// Get course ID and question ID.
global $tutor_course_id;
$question_id = Input::get( 'question_id', 0, Input::TYPE_INT );
$order_by    = Input::get( 'order', 'DESC' );

// Get question data.
$question = tutor_utils()->get_qa_question( $question_id );

// Get answers.
$answers         = tutor_utils()->get_qa_answer_by_question( $question_id, $order_by, 'frontend' );
$current_user_id = get_current_user_id();
$back_url        = remove_query_arg( 'question_id' );

?>
<div class="tutor-discussion-single"  x-data="tutorQna()">
	<div class="tutor-discussion-single-header tutor-p-6 tutor-border-b">
		<?php
		Button::make()
			->label( __( 'Back', 'tutor' ) )
			->tag( 'a' )
			->variant( 'secondary' )
			->size( Size::SMALL )
			->icon( Icon::ARROW_LEFT_2 )
			->attr( 'href', esc_url( $back_url ) )
			->attr( 'class', 'tutor-gap-2' )
			->render();
		?>
	</div>
	<?php
	if ( ! $question ) {
		EmptyState::make()->title( 'Question not found!' )->render();
		return;
	}
	?>

	<div class="tutor-discussion-single-body tutor-p-6 tutor-border-b">
		<div class="tutor-flex tutor-justify-between tutor-gap-6 tutor-mb-5">
			<?php
			Avatar::make()
				->src( esc_url( get_avatar_url( $question->user_id ) ) )
				->size( Size::SIZE_32 )
				->attr( 'alt', $question->display_name )
				->render();
			?>
			<div>
				<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
					<span class="tutor-discussion-card-author"><?php echo esc_html( $question->display_name ); ?></span> 
					<span class="tutor-text-secondary"> <?php echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $question->comment_date ) ) ) ); //phpcs:ignore ?></span>
				</div>
			</div>
			<?php if ( $current_user_id == $question->user_id || current_user_can( 'manage_tutor' ) ) : ?>
				<div class="tutor-ml-auto">
					<?php
					Popover::make()
						->placement( 'bottom-end' )
						->trigger(
							Button::make()
								->variant( Variant::GHOST )
								->icon( tutor_utils()->get_svg_icon( Icon::THREE_DOTS_VERTICAL, 16, 16 ) )
								->size( Size::SMALL )
								->attr( 'x-ref', 'trigger' )
								->attr( '@click', 'toggle()' )
								->get()
						)
						->menu_item(
							array(
								'tag'     => 'button',
								'content' => esc_html__( 'Delete', 'tutor' ),
								'icon'    => tutor_utils()->get_svg_icon( Icon::DELETE_2 ),
								'attr'    => array(
									'@click' => "hide(); TutorCore.modal.showModal('tutor-qna-delete-modal', { questionId: " . esc_html( $question->comment_ID ) . ", context: 'question' });",
								),
							)
						)
						->render();
					?>
				</div>
			<?php endif; ?>
		</div>
		<div class="tutor-p1 tutor-font-medium tutor-text-secondary">
			<?php echo wp_kses_post( stripslashes( $question->comment_content ) ); ?>
		</div>
	</div>

	<div class="tutor-qa-reply tutor-p-6 tutor-border-b">
		<form class="tutor-discussion-single-reply-form tutor-qna-reply-form"
			x-data="{ ...tutorForm({ id: '<?php echo esc_attr( $question_id ); ?>' }), focused : false }"
			@submit.prevent="handleSubmit((data) => replyQnaMutation?.mutate({...data, course_id: <?php echo esc_html( $tutor_course_id ); ?>, question_id: <?php echo esc_html( $question_id ); ?> }))($event)"
		>
			<div class="tutor-input-field">
				<label for="tutor-qna-reply" class="tutor-block tutor-medium tutor-font-semibold tutor-mb-4"><?php esc_html_e( 'Reply', 'tutor' ); ?></label>
				<?php
				InputField::make()
				->type( InputType::TEXTAREA )
				->name( 'response' )
				->placeholder( __( 'Just drop your response here!', 'tutor' ) )
				->clearable()
				->attr( '@focus', 'focused = true' )
				->attr( 'x-bind', "register('answer', { required: '" . esc_html( __( 'Please enter a response', 'tutor' ) ) . "' })" )
				->render();
				?>
			</div>
			<div class="tutor-flex tutor-items-center tutor-justify-end tutor-gap-2 tutor-mt-5"  x-cloak :class="{ 'tutor-hidden': !focused }">
				<?php
				Button::make()
					->label( __( 'Cancel', 'tutor' ) )
					->variant( Variant::GHOST )
					->size( Size::X_SMALL )
					->attr( 'type', 'button' )
					->attr( '@click', 'reset(); focused = false' )
					->attr( ':disabled', 'replyQnaMutation?.isPending' )
					->render();
				Button::make()
					->label( __( 'Save', 'tutor' ) )
					->variant( Variant::PRIMARY_SOFT )
					->size( Size::X_SMALL )
					->attr( 'type', 'submit' )
					->attr( ':disabled', 'replyQnaMutation?.isPending' )
					->attr( ':class', "{ 'tutor-btn-loading': replyQnaMutation?.isPending }" )
					->render();
				?>
			</div>
		</form>
	</div>

	<div class="tutor-flex tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
		<div class="tutor-small tutor-text-secondary">
			<?php esc_html_e( 'Replies', 'tutor' ); ?>
			<span class="tutor-text-primary tutor-font-medium">(<?php echo esc_html( is_array( $answers ) ? count( $answers ) : 0 ); ?>)</span>
		</div>
		<div>
			<?php
			Sorting::make()
				->order( Input::get( 'order', 'DESC' ) )
				->render();
			?>
		</div>
	</div>

	<?php if ( tutor_utils()->count( $answers ) ) : ?>
		<div class="tutor-discussion-single-reply-list">
			<?php foreach ( $answers as $answer ) : ?>
				<div class="tutor-discussion-reply-list-item" data-answer_id="<?php echo esc_attr( $answer->comment_ID ); ?>">
					<?php
						Avatar::make()
							->src( esc_url( get_avatar_url( $answer->user_id ) ) )
							->size( Size::SIZE_32 )
							->attr( 'alt', $answer->display_name )
							->render();
					?>
					<div>
						<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
							<span class="tutor-discussion-card-author"><?php echo esc_html( $answer->display_name ); ?></span> 
							<span class="tutor-text-subdued"> <?php echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $answer->comment_date ) ) ) ); // phpcs:ignore ?></span>
						</div>
						<div class="tutor-p2 tutor-text-secondary tutor-mb-6">
							<?php echo wp_kses_post( stripslashes( $answer->comment_content ) ); ?>
						</div>
					</div>
					<?php if ( $current_user_id == $answer->user_id || current_user_can( 'manage_tutor' ) ) : ?>
						<div class="tutor-ml-auto">
							<?php
							Popover::make()
								->placement( 'bottom-end' )
								->trigger(
									Button::make()
										->variant( Variant::GHOST )
										->icon( tutor_utils()->get_svg_icon( Icon::THREE_DOTS_VERTICAL, 16, 16 ) )
										->size( Size::SMALL )
										->attr( 'x-ref', 'trigger' )
										->attr( '@click', 'toggle()' )
										->get()
								)
								->menu_item(
									array(
										'tag'     => 'button',
										'content' => esc_html__( 'Delete', 'tutor' ),
										'icon'    => tutor_utils()->get_svg_icon( Icon::DELETE_2 ),
										'attr'    => array(
											'@click' => "hide(); TutorCore.modal.showModal('tutor-qna-delete-modal', { questionId: " . esc_html( $answer->comment_ID ) . ", context: 'reply' });",
										),
									)
								)
								->render();
							?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="tutor-p-6">
			<div class="tutor-text-center tutor-text-secondary">
				<?php EmptyState::make()->title( 'No replies yet. Be the first to reply!' )->render(); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php
	ConfirmationModal::make()
		->id( 'tutor-qna-delete-modal' )
		->title( __( 'Delete This Question?', 'tutor' ) )
		->message( __( 'Are you sure you want to delete this question permanently? Please confirm your choice.', 'tutor' ) )
		->confirm_text( __( 'Yes, Delete This', 'tutor' ) )
		->confirm_handler( 'deleteQnaMutation?.mutate({ question_id: payload?.questionId, context: payload?.context })' )
		->mutation_state( 'deleteQnaMutation' )
		->render();
	?>

</div>
