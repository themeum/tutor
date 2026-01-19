<?php
/**
 * Tutor learning area Q&A.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Components\Constants\InputType;
use Tutor\Components\InputField;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\Sorting;

// Get course ID from global variable set in learning-area/index.php .
global $tutor_course_id;

// Pagination setup.
$question_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
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
	<div class="tutor-discussion-search tutor-p-6 tutor-border-b">
		<form method="get" action="">
			<?php
			// Preserve existing query parameters .
			foreach ( $_GET as $key => $value ) { //phpcs:ignore
				if ( 'search' !== $key && 'current_page' !== $key && 'paged' !== $key ) {
					echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
				}
			}
			?>
			<div class="tutor-input-field">
				<div class="tutor-input-wrapper">
					<input 
						type="text"
						name="search"
						value="<?php echo esc_attr( $search_query ); ?>"
						placeholder="<?php esc_attr_e( 'Search questions, topics...', 'tutor' ); ?>"
						class="tutor-input tutor-input-content-left tutor-input-content-clear"
					>
					<div class="tutor-input-content tutor-input-content-left">
						<?php tutor_utils()->render_svg_icon( Icon::SEARCH_2, 20, 20 ); ?>
					</div>
					<button 
						type="button"
						class="tutor-input-clear-button"
						aria-label="<?php esc_attr_e( 'Clear input', 'tutor' ); ?>"
						onclick="this.previousElementSibling.previousElementSibling.value=''; this.form.submit();"
					>
						<?php tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ); ?>
					</button>
				</div>
			</div>
		</form>
	</div>

	<form 
		class="tutor-discussion-form tutor-p-6 tutor-border-b tutor-qna-form" 
		x-data="{ ...tutorForm({ id: '<?php echo esc_attr( $tutor_course_id ); ?>' }), focused : false }"
		@submit.prevent="handleSubmit((data) => createQnAMutation?.mutate({...data, course_id: <?php echo esc_html( $tutor_course_id ); ?> }))($event)"
		data-course_id="<?php echo esc_attr( $tutor_course_id ); ?>"
	>
		<div class="tutor-input-field">
			<label for="tutor-qna-question" class="tutor-block tutor-medium tutor-font-semibold tutor-mb-4"><?php esc_html_e( 'Question & Answer', 'tutor' ); ?></label>
			<div class="tutor-input-wrapper">
				<?php
				InputField::make()
					->type( InputType::TEXTAREA )
					->name( 'answer' )
					->placeholder( 'Ask a question...' )
					->clearable()
					->attr( '@focus', 'focused = true' )
					->attr( 'x-bind', "register('answer', { required: '" . esc_html( __( 'Please enter a response', 'tutor' ) ) . "' })" )
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
					->attr( ':disabled', 'createQnAMutation?.isPending' )
					->render();
				Button::make()
					->label( __( 'Save', 'tutor' ) )
					->variant( Variant::PRIMARY_SOFT )
					->size( Size::X_SMALL )
					->attr( 'type', 'submit' )
					->attr( ':disabled', 'createQnAMutation?.isPending' )
					->attr( ':class', "{ 'tutor-btn-loading': createQnAMutation?.isPending }" )
					->render();
				?>
			</div>
		</div>
	</form>

	<div class="tutor-flex tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
		<div class="tutor-small tutor-text-secondary">
			<?php esc_html_e( 'Questions', 'tutor' ); ?>
			<span class="tutor-text-primary tutor-font-medium">(<?php echo esc_html( $total_items ); ?>)</span>
		</div>
		<div>
			<?php
			Sorting::make()
				->order( Input::get( 'order', 'DESC' ) )
				->label_asc( __( 'Newest First', 'tutor' ) )
				->label_desc( __( 'Oldest First', 'tutor' ) )
				->render();
			?>
		</div>
	</div>

	<?php if ( is_array( $questions ) && count( $questions ) ) : ?>
		<div class="tutor-discussion-list tutor-flex tutor-flex-column tutor-gap-4 tutor-p-6">
			<?php foreach ( $questions as $question ) : ?>
				<?php
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
					<div class="tutor-avatar tutor-avatar-32">
						<img src="<?php echo esc_url( get_avatar_url( $question->user_id ) ); ?>" alt="<?php echo esc_attr( $question->display_name ); ?>" class="tutor-avatar-image">
					</div>
					<div class="tutor-discussion-card-content">
						<div class="tutor-discussion-card-top">
							<div class="tutor-discussion-card-author"><?php echo esc_html( $question->display_name ); ?></div>
							<div class="tutor-text-subdued"><?php echo esc_html( human_time_diff( strtotime( $question->comment_date ) ) . ' ' . __( 'ago', 'tutor' ) ); ?></div>
						</div>
						<a href="<?php echo esc_url( $question_url ); ?>">
							<h6 class="tutor-discussion-card-title"><?php echo esc_html( $content ); ?></h6>
						</a>
						<div class="tutor-discussion-card-meta">
							<a href="<?php echo esc_url( $question_url ); ?>" class="tutor-discussion-card-meta-reply-button">
								<?php esc_html_e( 'Reply', 'tutor' ); ?>
							</a>
							<div class="tutor-flex tutor-items-center tutor-gap-2">
								<?php tutor_utils()->render_svg_icon( Icon::COMMENTS, 20, 20 ); ?> 
								<?php echo esc_html( $question->answer_count ); ?>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php
		Pagination::make()
			->current( $current_page )
			->total( (int) $total_items )
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
