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

use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use TUTOR\Input;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\Size;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\SearchFilter;
use Tutor\Components\Sorting;
use Tutor\Helpers\UrlHelper;

$question_id = Input::get( 'question_id', 0, Input::TYPE_INT );

if ( $question_id ) {
	tutor_load_template( 'learning-area.subpages.qna.single' );
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
<div class="tutor-py-8">
	<h4 class="tutor-h4 tutor-mb-5 tutor-flex tutor-items-center tutor-gap-4">
		<?php SvgIcon::make()->name( Icon::QA )->size( 24 )->render(); ?>
		<?php esc_html_e( 'Q&A', 'tutor' ); ?>
	</h4>
	<div class="tutor-learning-area-qna" x-data="tutorQnA()">
		<div class="tutor-discussion-search tutor-p-6 tutor-border-b">
			<?php
				SearchFilter::make()
					->form_id( 'tutor-qna-search-form' )
					->placeholder( 'Search questions, topics...' )
					->action( UrlHelper::current() )
					->hidden_inputs( array( 'subpage' => 'qna' ) )
					->size( Size::LARGE )
					->render();
			?>
		</div>

		<div class="tutor-p-6">
			<label for="answer" class="tutor-block tutor-medium tutor-font-semibold tutor-mb-4">
				<?php esc_html_e( 'Question & Answer', 'tutor' ); ?>
			</label>
			<?php
			tutor_load_template(
				'learning-area.subpages.qna.form',
				array(
					'form_id'        => 'learning-area-qna-form',
					'submit_handler' => '(data) => createQnAMutation?.mutate({ ...data, course_id: ' . (int) $tutor_course_id . ' })',
					'cancel_handler' => 'reset(); focused = false',
					'is_pending'     => 'createQnAMutation?.isPending',
					'placeholder'    => __( 'Asked questions...', 'tutor' ),
					'submit_label'   => __( 'Save', 'tutor' ),
				)
			);
			?>
		</div>

		<div class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b tutor-border-t">
			<div class="tutor-small tutor-text-secondary">
				<?php esc_html_e( 'Questions', 'tutor' ); ?>
				<span class="tutor-text-primary tutor-font-medium">(<?php echo esc_html( $total_items ); ?>)</span>
			</div>
			<?php Sorting::make()->order( $order_by )->render(); ?>
		</div>

		<?php if ( empty( $questions ) ) : ?>
			<?php
				EmptyState::make()
					->title( 'No Questions Found!' )
					->icon( tutor_utils()->get_themed_svg( 'images/illustrations/qna-empty.svg' ) )
					->render();
			?>
		<?php else : ?>
			<div class="tutor-discussion-card-wrapper tutor-flex tutor-flex-column tutor-gap-4 tutor-sm-gap-none tutor-p-6 tutor-sm-p-none">
				<?php
				foreach ( $questions as $question ) :
					tutor_load_template(
						'learning-area.subpages.qna.card',
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

		<?php
		ConfirmationModal::make()
			->id( 'tutor-qna-delete-modal' )
			->title( __( 'Delete This Question?', 'tutor' ) )
			->message( __( 'Are you sure you want to delete this question permanently? Please confirm your choice.', 'tutor' ) )
			->confirm_text( __( 'Yes, Delete This', 'tutor' ) )
			->confirm_handler( 'deleteQnAMutation?.mutate(payload)' )
			->mutation_state( 'deleteQnAMutation' )
			->render();
		?>
	</div>
</div>
