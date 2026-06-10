<?php
/**
 * Tutor learning area quiz.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Helpers\UrlHelper;
use TUTOR\Quiz;
use TUTOR\Models\QuizModel;

global $tutor_current_post, $tutor_course_id;

$quiz = $quiz ?? null;
if ( ! $quiz || ! is_a( $quiz, 'WP_Post' ) ) {
	return;
}

$quiz_id            = $quiz->ID;
$total_questions    = (int) tutor_utils()->total_questions_for_student_by_quiz( $quiz_id );
$quiz_options       = tutor_utils()->get_quiz_option( $quiz_id );
$total_marks        = Quiz::get_quiz_total_marks( $quiz_id );
$passing_grade      = (int) ( $quiz_options['passing_grade'] ?? 0 );
$quiz_time          = $quiz_options['time_limit'] ?? null;
$has_time_limit     = is_array( $quiz_time ) && ! empty( $quiz_time['time_value'] ) && (int) $quiz_time['time_value'] > 0;
$time_units         = Quiz::quiz_time_units();
$quiz_item_readable = $has_time_limit ? $quiz_time['time_value'] . ' ' . $time_units[ $quiz_time['time_type'] ] : null;
$quiz_attempt       = ( new QuizModel() )->get_quiz_attempt( $quiz_id, $user_id ?? get_current_user_id() );
$earned_marks       = 0;

if ( $quiz_attempt && $total_marks > 0 ) {
	$earned_marks = (float) $quiz_attempt->earned_marks;
	$total        = (float) $total_marks;
	$earned_marks = number_format( ( $earned_marks / $total_marks ) * 100, 2 );
}
$limit_attempts   = (int) $quiz_options['limit_attempts_allowed'] ?? 0;
$allowed_attempts = $limit_attempts ? $quiz_options['attempts_allowed'] ?? '' : '1';
?>
<div class="tutor-quiz">
	<?php ob_start(); ?>
	<div class="tutor-quiz-intro">
		<div class="tutor-quiz-intro-overview">
			<!-- Quiz Icon -->
			<div class="tutor-quiz-intro-icon">
				<?php tutor_utils()->render_themed_svg( 'images/illustrations/quiz-intro.svg' ); ?>
			</div>

			<!-- Quiz Title -->
			<h3 class="tutor-quiz-intro-title">
				<?php echo esc_html( $quiz->post_title ); ?>		
			</h3>

			<?php if ( $quiz->post_content ) : ?>
				<!-- Quiz Description -->
				<p class="tutor-quiz-intro-description">
					<?php echo wp_kses_post( $quiz->post_content ); ?>	
				</p>
			<?php endif; ?>
		</div>

		<!-- Quiz Parameters Table -->
		<div class="tutor-table-wrapper tutor-table-bordered tutor-table-column-borders tutor-quiz-intro-params tutor-mb-8 tutor-sm-mb-5">
			<?php
				Quiz::render_quiz_summary( $total_questions, $quiz_item_readable, $total_marks, $passing_grade, $earned_marks, (int) $allowed_attempts );
			?>
		</div>

		<!-- Past Attempts Section -->
		<?php Quiz::render_quiz_attempts( $quiz_id ); ?>

		<!-- Action Buttons -->
		<?php Quiz::render_quiz_actions( $quiz_id ); ?>
	</div>
	<?php echo apply_filters( 'tutor_learning_area_content', ob_get_clean() ); //phpcs:ignore --already escaped ?>
</div>
