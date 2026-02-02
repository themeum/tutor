<?php
/**
 * Fill In The Blanks
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

global $tutor_is_started_quiz;

$default_question = array(
	'index'             => 1,
	'question_id'       => 0,
	'question_title'    => '',
	'question_type'     => 'fill_in_the_blank',
	'answer_required'   => true,
	'question_mark'     => 10,
	'question_settings' => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'fill_in_the_blank',
		'randomize_question' => '0',
		'show_question_mark' => '1',
		'is_image_matching'  => '0',
	),
);

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
		<?php foreach ( $question['question_answers'] as $answer ) : ?>
			<div class="tutor-quiz-question-option">
				<?php
				$answer_title = $answer['answer_title'];
				$dash_fields  = substr_count( $answer_title, '{dash}' );
				if ( $dash_fields ) {
					$answer_title = str_replace( '{dash}', '<input type="text" class="tutor-quiz-question-input" placeholder="Type your answer here" name="attempt[' . esc_attr( $tutor_is_started_quiz->attempt_id ) . '][quiz_question][' . esc_attr( $question['question_id'] ) . '][]" />', $answer_title );
				}
				echo wp_kses(
					$answer_title,
					array(
						'input' => array(
							'type'        => true,
							'class'       => true,
							'name'        => true,
							'placeholder' => true,
						),
					)
				);
				?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
