<?php
/**
 * Quiz body
 *
 * @package Tutor\Templates
 * @subpackage Single\Quiz
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.5.7
 */

use Tutor\Models\CourseModel;
use TUTOR\Quiz;

global $post;

$currentPost     = $post;//phpcs:ignore
$course          = CourseModel::get_course_by_quiz( get_the_ID() );
$quiz_id         = get_the_ID();
$quiz_details    = tutor_utils()->get_quiz_option( $quiz_id );
$quiz_time_limit = ( isset( $quiz_details['time_limit'] ) && is_array( $quiz_details['time_limit'] ) ) ? $quiz_details['time_limit'] : array();
$quiz_time_value = isset( $quiz_time_limit['time_value'] ) ? $quiz_time_limit['time_value'] : 0;
$quiz_time_type  = isset( $quiz_time_limit['time_type'] ) ? $quiz_time_limit['time_type'] : 'minutes';

$is_started_quiz             = tutor_utils()->is_started_quiz();
$previous_attempts           = tutor_utils()->quiz_attempts();
$attempted_count             = is_array( $previous_attempts ) ? count( $previous_attempts ) : 0;
$questions_order             = $quiz_details['questions_order'] ?? 'rand';
$limit_attempts_allowed      = '1' === (string) ( $quiz_details['limit_attempts_allowed'] ?? '0' );
$configured_attempts_allowed = (int) ( $quiz_details['attempts_allowed'] ?? 0 );
$attempts_allowed            = Quiz::get_effective_attempts_allowed( $limit_attempts_allowed, $configured_attempts_allowed );
$passing_grade               = (int) ( $quiz_details['passing_grade'] ?? 0 );
$can_retry_quiz              = Quiz::can_retry_quiz( $limit_attempts_allowed, $configured_attempts_allowed, $attempted_count );

$attempt_remaining = (int) $attempts_allowed - (int) $attempted_count;
$quiz_answers      = array();

if ( 0 !== $attempted_count ) {
	?>
		<?php // @todo: need to change the ID ?>
		<div id="tutor-quiz-image-matching-choice" class="tutor-quiz-wrap tutor-quiz-wrap-<?php the_ID(); ?>">
		<?php
			do_action( 'tutor_quiz/body/before', $quiz_id, $quiz_details );

		if ( $is_started_quiz ) {
			$quiz_attempt_info                  = tutor_utils()->quiz_attempt_info( $is_started_quiz->attempt_info );
			$quiz_attempt_info['date_time_now']             = date( 'Y-m-d H:i:s', tutor_time() );//phpcs:ignore
			$time_limit_seconds                 = tutor_utils()->avalue_dot( 'time_limit.time_limit_seconds', $quiz_attempt_info );
			$question_layout_view               = $quiz_details['question_layout_view'] ?? 'single_question';
			$enable_pagination                  = '1' === (string) ( $quiz_details['enable_pagination'] ?? '0' );
			$pagination_type                    = $quiz_details['pagination_type'] ?? 'shape';
			$enable_answer_reveal               = '1' === (string) ( $quiz_details['enable_answer_reveal'] ?? '0' );
			$answers_reveal_duration            = (int) ( $quiz_details['answers_reveal_duration'] ?? 0 );
			$hide_previous_button               = '1' === (string) ( $quiz_details['hide_previous_button'] ?? '0' );
			$hide_quiz_time_display             = (bool) ( $quiz_details['hide_quiz_time_display'] ?? false );
			$hide_question_number_overview      = (bool) ( $quiz_details['hide_question_number_overview'] ?? false );

			$remaining_time_secs = ( strtotime( $is_started_quiz->attempt_started_at ) + $time_limit_seconds ) - strtotime( $quiz_attempt_info['date_time_now'] );

			$remaining_time_context = tutor_utils()->seconds_to_time_context( $remaining_time_secs );
			$questions              = tutor_utils()->get_random_questions_by_quiz();

			/* Quiz Meta */
			require __DIR__ . '/parts/meta.php';

			/* Quiz Question & Answer */
			if ( is_array( $questions ) && count( $questions ) ) {
				require __DIR__ . '/parts/question.php';
			} else {
				?>
					<div class="start-quiz-wrap">
						<form id="tutor-finish-quiz" method="post">
							<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

							<input type="hidden" value="<?php echo esc_attr( $quiz_id ); ?>" name="quiz_id"/>
							<input type="hidden" value="tutor_finish_quiz_attempt" name="tutor_action"/>

							<button type="submit" class="tutor-btn" name="finish_quiz_btn" value="finish_quiz">
								<i class="icon-floppy"></i> <?php esc_html_e( 'Finish', 'tutor' ); ?>
							</button>
						</form>
					</div>
				<?php
			}
		} elseif ( $previous_attempts ) {
			do_action( 'tutor_quiz/previous_attempts_html/before', $previous_attempts, $quiz_id );

			ob_start();
			tutor_load_template( 'single.quiz.previous-attempts', compact( 'previous_attempts', 'quiz_id' ) );
			$previous_attempts_html = ob_get_clean();
			echo $previous_attempts_html;//phpcs:ignore

			do_action( 'tutor_quiz/previous_attempts/after', $previous_attempts, $quiz_id );
		}

		do_action( 'tutor_quiz/body/after', $quiz_id );
		?>
		</div>
		<?php
}
?>

<script>
	window.tutor_quiz_context = '<?php echo strrev( json_encode( $quiz_answers ) ); //phpcs:ignore ?>';
</script>
