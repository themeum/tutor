<?php
/**
 * Attempt details True/False (read-only).
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\AttemptDetails
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Models\QuizModel;

if ( ! isset( $question ) || ! is_object( $question ) ) {
	return;
}

$question_answers = QuizModel::get_answers_by_quiz_question( (int) $question->question_id );
$question_answers = is_array( $question_answers ) ? $question_answers : array();

$given_ids = array();
if ( isset( $question->given_answer ) ) {
	$given_value = maybe_unserialize( $question->given_answer );
	$given_ids   = is_array( $given_value ) ? array_values( $given_value ) : array( $given_value );
}
$given_ids = array_map( 'intval', array_filter( $given_ids ) );
?>

<div class="tutor-quiz-question-options">
	<?php foreach ( $question_answers as $answer ) : ?>
		<?php
		$is_selected = in_array( (int) $answer->answer_id, $given_ids, true );
		$is_correct  = (bool) ( $answer->is_correct ?? false );
		$option_attr = '';
		if ( $is_selected && $is_correct ) {
			$option_attr = 'correct';
		} elseif ( $is_selected && ! $is_correct ) {
			$option_attr = 'incorrect';
		} elseif ( ! $is_selected && $is_correct ) {
			$option_attr = 'correct';
		}
		?>
		<div class="tutor-quiz-question-option" data-option="<?php echo esc_attr( $option_attr ); ?>" data-readonly="true">
			<?php SvgIcon::make()->name( ! empty( $answer->is_correct ) ? Icon::CHECK_2 : Icon::CROSS )->size( 20 )->render(); ?>
			<?php echo esc_html( $answer->answer_title ?? '' ); ?>
		</div>
	<?php endforeach; ?>
</div>
