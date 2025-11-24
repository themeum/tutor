<?php
/**
 * Openended Short Answer
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$question = array(
	'index'             => 1,
	'question_id'       => 1,
	'question_title'    => __( 'Openended Short Answer', 'tutor' ),
	'question_type'     => 'open_ended',
	'answer_required'   => true,
	'question_mark'     => 10,
	'question_settings' => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'open_ended',
		'randomize_question' => '0',
		'show_question_mark' => '1',
		'is_image_matching'  => '0',
	),
);

?>

<div class="tutor-quiz-question" data-question="<?php echo esc_attr( $question['question_type'] ); ?>">
	<?php
	tutor_load_template(
		'demo-components.learning-area.components.quiz.question-header',
		array(
			'index'              => $question['index'],
			'question_title'     => $question['question_title'],
			'question_mark'      => $question['question_mark'],
			'show_question_mark' => $question['question_settings']['show_question_mark'],
		)
	);
	?>

	<div class="tutor-quiz-question-options" data-image-matching="<?php echo esc_attr( $question['question_settings']['is_image_matching'] ); ?>">
		<div class="tutor-input-field">
		<div class="tutor-input-wrapper">
		<textarea 
			type="text"
			id="name"
			placeholder="Type your answer here"
			class="tutor-input tutor-text-area tutor-input-content-clear"
		></textarea>
		</div>
		<div class="tutor-help-text">This is a helper text.</div>
	</div>
	</div>
</div>
