<?php
/**
 * Openended Short Answer
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;

global $tutor_is_started_quiz;

$default_question = array(
	'index'             => 1,
	'question_id'       => 1,
	'question_title'    => '',
	'question_type'     => 'open_ended',
	'answer_required'   => true,
	'question_mark'     => 10,
	'question_settings' => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'open_ended',
		'randomize_question' => '0',
		'show_question_mark' => '1',
	),
);

$question = wp_parse_args( $question, $default_question );

?>

<div class="tutor-quiz-question" data-question="<?php echo esc_attr( $question['question_type'] ); ?>">
	<?php
	tutor_load_template(
		'learning-area.quiz.question-header',
		array(
			'index'                => $question['index'],
			'question_title'       => $question['question_title'],
			'question_description' => $question['question_description'],
			'question_mark'        => $question['question_mark'],
			'show_question_mark'   => $question['question_settings']['show_question_mark'],
		)
	);
	?>

	<div class="tutor-quiz-question-options">
		<?php
			InputField::make()
				->type( InputType::TEXTAREA )
				->name( 'attempt[' . esc_attr( $tutor_is_started_quiz->attempt_id ) . '][quiz_question][' . esc_attr( $question['question_id'] ) . ']' )
				->placeholder( __( 'Type your answer here', 'tutor' ) )
				->render();
		?>
	</div>
</div>
