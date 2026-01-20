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
$question_per_page = 1;
$current_page      = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset            = ( $current_page - 1 ) * $question_per_page;
$search_query      = Input::get( 'search', '' );
$order_by          = Input::get( 'order', 'DESC' );

// Get questions for this course.
$total_items = tutor_utils()->get_qa_questions(
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
<div class="tutor-learning-area-qna tutor-mb-9" x-data="tutorQna()">

	<!-- Question Search  -->
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

	<!-- Question submission form  -->
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

	<!-- Question sorting -->
	<div class="tutor-flex tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
		<div class="tutor-small tutor-text-secondary">
			<?php esc_html_e( 'Questions', 'tutor' ); ?>
			<span class="tutor-text-primary tutor-font-medium">(<?php echo esc_html( $total_items ); ?>)</span>
		</div>
		<div>
			<?php
			Sorting::make()
				->order( Input::get( 'order', 'DESC' ) )
				->render();
			?>
		</div>
	</div>

	<!-- Question Listing -->
	<?php if ( tutor_utils()->count( $questions ) ) : ?>
		<div class="tutor-discussion-list tutor-flex tutor-flex-column tutor-gap-4 tutor-p-6">
			<?php
			foreach ( $questions as $question ) :
				$meta         = $question->meta;
				$is_important = (int) tutor_utils()->array_get( 'tutor_qna_important', $meta, 0 );
				$question_url = add_query_arg(
					array(
						'subpage'     => 'qna',
						'question_id' => $question->comment_ID,
					),
					get_permalink( $tutor_course_id )
				);
				$content      = wp_strip_all_tags( stripslashes( $question->comment_content ) );
				$content      = strlen( $content ) > 100 ? substr( $content, 0, 100 ) . '...' : $content;
				?>
				<div class="tutor-discussion-card" @click="window.location.href = '<?php echo esc_url( $question_url ); ?>'" style="cursor: pointer;">
					<?php
					Avatar::make()
						->src( esc_url( get_avatar_url( $question->user_id ) ) )
						->size( Size::SIZE_32 )
						->attr( 'alt', $question->display_name )
						->render();
					?>
					<div class="tutor-discussion-card-content">
						<div class="tutor-discussion-card-top">
							<div class="tutor-discussion-card-author"><?php echo esc_html( $question->display_name ); ?></div>
							<div class="tutor-text-subdued"><?php echo esc_html( human_time_diff( strtotime( $question->comment_date ) ) . ' ' . __( 'ago', 'tutor' ) ); ?></div>
						</div>
						<a href="<?php echo esc_url( $question_url ); ?>">
							<h6 class="tutor-discussion-card-title"><?php echo esc_html( $content ); ?></h6>
						</a>
						<div class="tutor-discussion-card-meta">
							<div class="tutor-flex tutor-items-center tutor-gap-2">
								<?php tutor_utils()->render_svg_icon( Icon::COMMENTS, 16, 16 ); ?> 
								<?php echo esc_html( $question->answer_count ); ?>
							</div>
						</div>
					</div>
					<?php if ( $is_important ) : ?>
						<span class="tutor-flex tutor-absolute tutor-right-6 tutor-top-0" style="top: -4px;">
							<?php tutor_utils()->render_svg_icon( Icon::BOOKMARK_FILL, 25, 25, array( 'class' => 'tutor-icon-exception4' ) ); ?>
						</span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php
		Pagination::make()
			->current( $current_page )
			->total( $total_items )
			->limit( $question_per_page )
			->attr( 'class', 'tutor-px-6 tutor-pb-6 tutor-sm-p-5 tutor-sm-border-t' )
			->render();
	else :
		?>
		<div class="tutor-p-6">
			<?php EmptyState::make()->title( 'No Questions Found!' )->render(); ?>
		</div>
	<?php endif; ?>
</div>
