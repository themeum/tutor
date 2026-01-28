<?php
/**
 * Tutor quiz image answering.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

$question = array(
	'index'                => 1,
	'question_id'          => '7',
	'content_id'           => null,
	'quiz_id'              => '21',
	'question_title'       => 'Image Answering',
	'question_description' => '<p>This is description</p>',
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
	'question_order'       => '7',
	'question_answers'     => array(
		array(
			'answer_id'             => '8',
			'belongs_question_id'   => '7',
			'belongs_question_type' => 'image_answering',
			'answer_title'          => 'Option 1 Answer',
			'is_correct'            => '0',
			'image_id'              => '20',
			'answer_two_gap_match'  => '',
			'answer_view_format'    => 'text_image',
			'answer_settings'       => null,
			'answer_order'          => '1',
			'image_url'             => 'https://placehold.co/600x400',
		),
		array(
			'answer_id'             => '9',
			'belongs_question_id'   => '7',
			'belongs_question_type' => 'image_answering',
			'answer_title'          => 'Option 2 Answer',
			'is_correct'            => '0',
			'image_id'              => '18',
			'answer_two_gap_match'  => '',
			'answer_view_format'    => 'text_image',
			'answer_settings'       => null,
			'answer_order'          => '2',
			'image_url'             => 'https://placehold.co/600x400',
		),
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
			'points'             => $question['question_mark'],
			'show_question_mark' => $question['question_settings']['show_question_mark'],
		)
	);
	?>

	<div class="tutor-quiz-question-options">
		<?php foreach ( $question['question_answers'] as $answer ) : ?>
			<div class="tutor-quiz-question-option">
				<img src="<?php echo esc_url( $answer['image_url'] ); ?>" alt="<?php echo esc_attr( $answer['answer_title'] ); ?>">
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
