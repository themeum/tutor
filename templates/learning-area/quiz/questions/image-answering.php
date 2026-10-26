<?php
/**
 * Tutor quiz image answering.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Quiz;
use Tutor\Components\InputField;

$field_name         = '';
$register_rules     = '';
$question           = (array) ( $question ?? array() );
$answer_is_required = $answer_is_required ?? false;
$required_message   = $required_message ?? '';
if ( $answer_is_required ) {
	$register_rules = ", { required: '" . esc_js( $required_message ) . "' }";
}

?>

<div class="tutor-quiz-question-options">
	<?php if ( tutor_utils()->count( $question['question_answers'] ) ) : ?>
		<?php foreach ( $question['question_answers'] as $index => $answer ) : ?>
			<div class="tutor-quiz-question-option">
				<img
					src="<?php echo esc_url( wp_get_attachment_image_url( $answer['image_id'], 'full' ) ); ?>"
					alt="<?php echo esc_attr( Quiz::sanitize_quiz_content( $answer['answer_title'] ?? '' ) ); ?>"
				>
				<?php
				$input_name    = sprintf( '%s[answer_id][%d]', $question_field_name_base ?? '', (int) $answer['answer_id'] );
				$rules_suffix  = $register_rules;
				$register_attr = "register('{$input_name}'{$rules_suffix})";

				if ( 0 === $index ) {
					$field_name = $input_name;
				}

				InputField::make()
					->name( $input_name )
					->clearable()
					->attr( 'x-bind', $register_attr )
					->placeholder( __( 'Write your answer here', 'tutor' ) )
					->render();
				?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
