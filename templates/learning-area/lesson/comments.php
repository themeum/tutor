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
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\InputField;
use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

?>
<div x-show="activeTab === 'comments'" x-cloak x-data="tutorLessonComments(<?php echo esc_js( $lesson_id ); ?>)" class="tutor-tab-panel" role="tabpanel">
	<form 
		class="tutor-p-6" 
		x-data="{ ...tutorForm({ id: 'lesson-comment-form' }), focused: false }"
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

	<?php tutor_load_template( 'learning-area.lesson.comment-list' ); ?>

	<div x-ref="loadMoreTrigger" aria-hidden="true">
		<span x-show="loading" class="tutor-loading-spinner tutor-border-t"></span>
	</div>
</div>
