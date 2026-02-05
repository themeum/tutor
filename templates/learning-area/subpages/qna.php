<?php
/**
 * Tutor learning area Q&A.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Avatar;
use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Components\Constants\InputType;
use Tutor\Components\InputField;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\SearchFilter;
use Tutor\Components\Sorting;
use Tutor\Helpers\UrlHelper;

$question_id = Input::get( 'question_id', 0, Input::TYPE_INT );

if ( $question_id ) {
	tutor_load_template( 'learning-area.subpages.qna-single' );
	return;
}

// Get course ID from global variable set in learning-area/index.php .
global $tutor_course_id;

// Pagination setup.
$question_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page      = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset            = ( $current_page - 1 ) * $question_per_page;
$search_query      = Input::get( 'search', '' );
$order_by          = Input::get( 'order', 'DESC' );

// Get questions for this course.
$total_items = (int) tutor_utils()->get_qa_questions(
	$offset,
	$question_per_page,
	$search_query,
	null,
	null,
	null,
	null,
	true,
	array(
		'course_id' => $tutor_course_id,
		'order'     => $order_by,
	)
);
$questions   = tutor_utils()->get_qa_questions(
	$offset,
	$question_per_page,
	$search_query,
	null,
	null,
	null,
	null,
	false,
	array(
		'course_id' => $tutor_course_id,
		'order'     => $order_by,
	)
);

?>
<div class="tutor-pt-4 tutor-pb-6">
	<div class="tutor-learning-area-qna" x-data="tutorQna()">
		<div class="tutor-discussion-search tutor-p-6 tutor-border-b">
			<?php
			SearchFilter::make()
				->form_id( 'tutor-qna-search-form' )
				->placeholder( 'Search questions, topics...' )
				->action( UrlHelper::current() )
				->hidden_inputs( array( 'subpage' => 'qna' ) )
				->size( 'large' )
				->render();
			?>
		</div>

		<form
			class="tutor-discussion-form tutor-p-6 tutor-border-b tutor-qna-form"
			x-data="{ ...tutorForm({ id: '<?php echo esc_attr( $tutor_course_id ); ?>', defaultValues: { course_id: <?php echo esc_html( $tutor_course_id ); ?> } }), focused : false }"
			@submit.prevent="handleSubmit((data) => createQnaMutation?.mutate(data))($event)"
		>
			<div class="tutor-input-field">
				<label for="tutor-qna-question" class="tutor-block tutor-medium tutor-font-semibold tutor-mb-4"><?php esc_html_e( 'Question & Answer', 'tutor' ); ?></label>
				<div class="tutor-input-wrapper">
					<?php
					InputField::make()
						->type( InputType::TEXTAREA )
						->name( 'answer' )
						->placeholder( 'Asked questions...' )
						->attr( '@focus', 'focused = true' )
						->attr( 'x-bind', "register('answer', { required: '" . esc_html( __( 'Please enter a response', 'tutor' ) ) . "' })" )
						->attr( 'rows', 4 )
						->render();
					?>
				</div>
			</div>
			<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mt-5" x-cloak :class="{ 'tutor-hidden': !focused }">
				<div class="tutor-tiny tutor-text-subdued tutor-flex tutor-items-center tutor-gap-2">
					<?php tutor_utils()->render_svg_icon( Icon::COMMAND, 12, 12 ); ?>
					<?php esc_html_e( 'Cmd/Ctrl +', 'tutor' ); ?>
					<?php tutor_utils()->render_svg_icon( Icon::ENTER, 12, 12 ); ?>
					<?php esc_html_e( 'Enter to Save', 'tutor' ); ?>
				</div>
				<div class="tutor-flex tutor-items-center tutor-gap-4">
					<?php
					Button::make()
						->label( __( 'Cancel', 'tutor' ) )
						->variant( Variant::GHOST )
						->size( Size::X_SMALL )
						->attr( 'type', 'button' )
						->attr( '@click', 'reset(); focused = false' )
						->attr( ':disabled', 'createQnaMutation?.isPending' )
						->render();
					Button::make()
						->label( __( 'Save', 'tutor' ) )
						->variant( Variant::PRIMARY_SOFT )
						->size( Size::X_SMALL )
						->attr( 'type', 'submit' )
						->attr( ':disabled', 'createQnaMutation?.isPending' )
						->attr( ':class', "{ 'tutor-btn-loading': createQnaMutation?.isPending }" )
						->render();
					?>
				</div>
			</div>
		</form>

		<div class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
			<div class="tutor-small tutor-text-secondary">
				<?php esc_html_e( 'Questions', 'tutor' ); ?>
				<span class="tutor-text-primary tutor-font-medium">(<?php echo esc_html( $total_items ); ?>)</span>
			</div>
			<div>
				<?php
				Sorting::make()
					->order( $order_by )
					->render();
				?>
			</div>
		</div>

		<?php if ( empty( $questions ) ) : ?>
			<?php EmptyState::make()->title( 'No Questions Found!' )->render(); ?>
		<?php else : ?>
			<div class="tutor-flex tutor-flex-column tutor-gap-4 tutor-sm-gap-none tutor-p-6 tutor-sm-p-none">
				<?php
				foreach ( $questions as $question ) :
					tutor_load_template(
						'learning-area.subpages.qna-card',
						array(
							'question' => $question,
						)
					);
				endforeach;
				?>
			</div>
		<?php endif; ?>

		<?php
		Pagination::make()
			->current( $current_page )
			->total( $total_items )
			->limit( $question_per_page )
			->attr( 'class', 'tutor-px-6 tutor-pb-6 tutor-sm-p-5 tutor-sm-border-t' )
			->render();
		?>
	</div>
</div>
