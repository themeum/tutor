<?php
/**
 * Shared quiz attempt details summary view.
 *
 * @package Tutor\Templates
 * @subpackage Shared
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use TUTOR\Quiz_Attempts_List;
use TUTOR\Input;
use Tutor\Components\EmptyState;
use Tutor\Models\QuizModel;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

global $tutor_current_post;

$is_quiz_details_hidden = Quiz_Attempts_List::is_attempt_details_hidden();

if ( $is_quiz_details_hidden ) {
	return;
}

$quiz_id              = (int) ( $quiz_id ?? ( $tutor_current_post->ID ?? 0 ) );
$user_id              = (int) ( $user_id ?? get_current_user_id() );
$attempt_id           = (int) ( $attempt_id ?? Input::get( 'attempt_id', 0, Input::TYPE_INT ) );
$attempt_data         = $attempt_data ?? null;
$back_url             = $back_url ?? get_permalink( $quiz_id );
$context              = (string) ( $context ?? '' );
$is_instructor_review = ! empty( $is_instructor_review );

if ( $attempt_id > 0 && ! $attempt_data ) {
	$attempt_data = tutor_utils()->get_attempt( $attempt_id );
}

$render_attempt_not_found = static function ( string $title ) {
	EmptyState::make()
		->title( $title )
		->icon( tutor_utils()->get_themed_svg( 'images/illustrations/quiz-empty.svg' ) )
		->render();
};

if ( ! $attempt_data || empty( $attempt_data->attempt_id ) ) {
	$render_attempt_not_found( __( 'Attempt not found', 'tutor' ) );
	return;
}

if ( $is_instructor_review ) {
	if ( ! tutor_utils()->can_user_manage( 'attempt', (int) $attempt_data->attempt_id ) ) {
		$render_attempt_not_found( __( 'Attempt not found or access permission denied', 'tutor' ) );
		return;
	}
} elseif ( $user_id > 0 && (int) $attempt_data->user_id !== $user_id ) {
	$render_attempt_not_found( __( 'Attempt not found or access permission denied', 'tutor' ) );
	return;
}

if ( ! $quiz_id ) {
	$quiz_id = (int) ( $attempt_data->quiz_id ?? 0 );
}

$questions = QuizModel::get_quiz_answers_by_attempt_id( (int) $attempt_data->attempt_id );
$questions = QuizModel::filter_attempt_answers_for_details( $questions, $is_instructor_review );

$course_contents = tutor_utils()->get_course_prev_next_contents_by_id( $quiz_id );
?>
<div class="tutor-quiz-summary-page">
	<div class="tutor-quiz-summary-header">
		<div class="tutor-quiz-summary-header-inner">
			<div class="tutor-flex tutor-items-center tutor-gap-4">
				<?php
					Button::make()
						->icon( Icon::ARROW_LEFT_2 )
						->flip_rtl()
						->tag( 'a' )
						->attr( 'href', $back_url )
						->variant( Variant::GHOST )
						->size( Size::X_SMALL )
						->icon_only()
						->render();
				?>
				<h5 class="tutor-h5 tutor-font-semibold"><?php esc_html_e( 'Quiz Summary', 'tutor' ); ?></h5>
			</div>

			<?php
				Button::make()
					->icon( Icon::CROSS )
					->tag( 'a' )
					->attr( 'href', $back_url )
					->attr( 'type', 'button' )
					->variant( Variant::GHOST )
					->size( Size::X_SMALL )
					->icon_only()
					->render();
			?>
		</div>
	</div>

	<div class="tutor-surface-l1">
		<?php
		tutor_load_template(
			'shared.components.quiz.attempt-details.summary',
			array(
				'attempt_data'         => $attempt_data,
				'answers'              => $questions, // $questions holds quiz answers data, mapped to 'answers' key for summary template
				'is_instructor_review' => $is_instructor_review,
			)
		);
		?>
	</div>
	<?php if ( tutor_utils()->count( $questions ) ) : ?>
	<div class="tutor-quiz-summary-body">
		<?php
		tutor_load_template(
			'shared.components.quiz.attempt-details.questions-sidebar',
			array(
				'quiz_id'              => $quiz_id,
				'attempt_data'         => $attempt_data,
				'questions'            => $questions,
				'is_instructor_review' => $is_instructor_review,
			)
		);
		?>
		<div class="tutor-quiz-summary-content">
			<h3 class="tutor-h3 tutor-sm-text-h5 tutor-text-subdued tutor-mb-10 tutor-sm-mb-5">
				<?php esc_html_e( 'Review your answers', 'tutor' ); ?>
			</h3>
			<?php
			tutor_load_template(
				'shared.components.quiz.attempt-details.review-answers',
				array(
					'questions'            => is_array( $questions ) ? $questions : array(),
					'attempt_data'         => $attempt_data,
					'back_url'             => $back_url,
					'context'              => $context,
					'is_instructor_review' => $is_instructor_review,
				)
			);
			?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ( ! empty( $is_learning_area ) && $course_contents->next_id ) : ?>
	<div class="tutor-quiz-summary-footer">
		<div class="tutor-quiz-summary-footer-inner">
			<?php
				Button::make()
				->tag( 'a' )
				->label( __( 'Continue Lesson', 'tutor' ) )
				->variant( Variant::PRIMARY )
				->size( Size::LARGE )
				->attr( 'href', esc_url( get_the_permalink( $course_contents->next_id ) ) )
				->render();
			?>
		</div>
	</div>
	<?php endif; ?>
</div>
