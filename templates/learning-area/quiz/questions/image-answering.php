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

$default_question = array(
	'index'                => 1,
	'question_id'          => 0,
	'content_id'           => null,
	'quiz_id'              => 0,
	'question_title'       => '',
	'question_description' => '',
	'answer_explanation'   => '',
	'question_type'        => 'image_answering',
	'question_mark'        => '1.00',
	'question_settings'    => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'image_answering',
		'randomize_question' => '0',
		'show_question_mark' => '0',
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
			'points'               => $question['question_mark'],
			'show_question_mark'   => $question['question_settings']['show_question_mark'],
		)
	);
	?>

	<div class="tutor-quiz-question-options">
		<?php foreach ( $question['question_answers'] as $answer ) : ?>
			<div class="tutor-quiz-question-option">
				<img src="<?php echo esc_url( wp_get_attachment_image_url( $answer['image_id'], 'full' ) ); ?>" alt="<?php echo esc_attr( $answer['answer_title'] ); ?>">
				<div class="tutor-input-field">
					<input 
						type="text"
						id="<?php echo esc_attr( $question['question_id'] ); ?>"
						placeholder="Enter answer"
						class="tutor-input"
					>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
