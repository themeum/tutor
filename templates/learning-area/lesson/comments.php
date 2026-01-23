<?php
/**
 * Lesson comments template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Button;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Sorting;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\InputField;
use TUTOR\Icon;
use TUTOR\Input;
use TUTOR\Lesson;

defined( 'ABSPATH' ) || exit;

$user_id       = get_current_user_id();
$item_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page  = max( 1, Input::post( 'current_page', 0, Input::TYPE_INT ) );
$order_filter  = Input::get( 'order' ) ?? Input::post( 'order' ) ?? 'DESC';

$comments_list_args = array(
	'post_id' => $lesson_id,
	'parent'  => 0,
	'paged'   => $current_page,
	'number'  => $item_per_page,
	'order'   => $order_filter,
);

$comment_count_args = array(
	'post_id' => $lesson_id,
	'parent'  => 0,
	'count'   => true,
);

$comments_count = Lesson::get_comments( $comment_count_args );
$comment_list   = Lesson::get_comments( $comments_list_args );
?>
<div x-show="activeTab === 'comments'" x-cloak x-data="tutorLessonComments(<?php echo esc_js( $lesson_id ); ?>, <?php echo esc_js( $comments_count ); ?>)" class="tutor-tab-panel" role="tabpanel">
	<form 
		class="tutor-p-6" 
		x-data="{ ...tutorForm({ id: 'lesson-comment-form', mode: 'onChange' }), focused: false }"
		x-bind="getFormBindings()"
		@submit.prevent="handleSubmit((data) => createCommentMutation?.mutate({ ...data, comment_post_ID: <?php echo esc_html( $lesson_id ); ?>, comment_parent: 0, order: currentOrder }))($event)"
	>
		<div class="tutor-text-medium tutor-font-semibold tutor-mb-4">
			<?php esc_html_e( 'Join The Conversation', 'tutor' ); ?>
		</div>

		<?php
		InputField::make()
			->type( InputType::TEXTAREA )
			->name( 'comment' )
			->placeholder( __( 'Write your comment...', 'tutor' ) )
			->attr( 'x-bind', "register('comment', { required: '" . esc_js( __( 'Please enter a comment', 'tutor' ) ) . "' })" )
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
					->attr( ':disabled', 'createCommentMutation?.isPending' )
					->render();

				Button::make()
					->label( __( 'Save', 'tutor' ) )
					->variant( Variant::PRIMARY_SOFT )
					->size( Size::X_SMALL )
					->attr( 'type', 'submit' )
					->attr( ':disabled', 'createCommentMutation?.isPending' )
					->attr( ':class', "{ 'tutor-btn-loading': createCommentMutation?.isPending }" )
					->render();
				?>
			</div>
		</div>
	</form>

	<?php if ( ! empty( $comment_list ) ) : ?>
		<div 
			class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-t"
			:class="{ 'tutor-loading-spinner': isReloading }"
		>
			<div class="tutor-small tutor-text-secondary">
				<?php esc_html_e( 'Comments', 'tutor' ); ?>
				<span class="tutor-text-primary tutor-font-medium" x-text="'(' + totalComments + ')'"></span>
			</div>
			<?php
			Sorting::make()
				->order( $order_filter )
				->on_change( 'handleChangeOrder' )
				->bind_active_order( 'currentOrder' )
				->render();
			?>
		</div>
		<div x-ref="commentList" class="tutor-comments-list tutor-border-t">
			<?php
			tutor_load_template(
				'learning-area.lesson.comment-list',
				compact( 'comment_list', 'lesson_id', 'user_id' )
			);
			?>
		</div>
		<?php
		ConfirmationModal::make()
			->id( 'delete-comment-modal' )
			->title( 'Delete This Item?' )
			->message( 'This action cannot be undone.' )
			->icon( Icon::DELETE_2, 80, 80 )
			->mutation_state( 'deleteCommentMutation' )
			->confirm_handler( 'deleteCommentMutation?.mutate({ comment_id: payload?.commentId })' )
			->render();
		?>
	<?php endif; ?>

	<div x-ref="loadMoreTrigger" aria-hidden="true">
		<span x-show="loading" class="tutor-loading-spinner tutor-border-t"></span>
	</div>
</div>
