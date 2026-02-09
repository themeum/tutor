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

use Tutor\Components\Button;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Models\QuizModel;
use TUTOR\Quiz;

global $tutor_current_post, $tutor_course_id;

$quiz = $quiz ?? null;
if ( ! $quiz || ! is_a( $quiz, 'WP_Post' ) ) {
	return;
}

$quiz_id            = $quiz->ID;
$total_questions    = (int) tutor_utils()->total_questions_for_student_by_quiz( $quiz_id );
$quiz_options       = get_post_meta( $quiz_id, 'tutor_quiz_option', true );
$total_marks        = Quiz::get_quiz_total_marks( $quiz_id );
$passing_grade      = (int) $quiz_options['passing_grade'] ?? 0;
$quiz_time          = $quiz_options['time_limit'] ?? null;
$has_time_limit     = is_array( $quiz_time ) && ! empty( $quiz_time['time_value'] ) && (int) $quiz_time['time_value'] > 0;
$quiz_item_readable = $has_time_limit ? $quiz_time['time_value'] . ' ' . $quiz_time['time_type'] : null;

?>
<div class="tutor-quiz-intro">
	<div class="tutor-card">
		<!-- Quiz Icon -->
		<div class="tutor-quiz-intro-icon tutor-mb-8">
			<img src="<?php echo esc_url( tutor()->url . 'assets/images/quiz-intro.svg' ); ?>" alt="<?php esc_attr_e( 'Quiz', 'tutor' ); ?>">
		</div>

		<!-- Quiz Title -->
		<h1 class="tutor-quiz-intro-title tutor-mb-5">
			<?php echo esc_html( $quiz->post_title ); ?>		
		</h1>

		<!-- Quiz Description -->
		<p class="tutor-quiz-intro-description tutor-mb-8">
			<?php echo wp_kses_post( $quiz->post_content ); ?>	
		</p>

		<!-- Quiz Parameters Table -->
		<div class="tutor-table-wrapper tutor-table-bordered tutor-table-column-borders tutor-quiz-intro-params tutor-mb-8">
			<?php
				Quiz::render_quiz_summary( $total_questions, $quiz_item_readable, $total_marks, $passing_grade );
			?>
		</div>

		<!-- Past Attempts Section -->
		<?php Quiz::render_quiz_attempts( $quiz_id ); ?>
		
		<!-- Action Buttons -->
		<?php Quiz::render_quiz_actions( $quiz_id ); ?>
	</div>
</div>
