<?php
/**
 * Ordering
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
	'question_title'    => __( 'Ordering', 'tutor' ),
	'question_type'     => 'ordering',
	'answer_required'   => true,
	'question_mark'     => 10,
	'question_settings' => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'ordering',
		'randomize_question' => '0',
		'show_question_mark' => '1',
	),
	'question_answers'  => array(
		array(
			'answer_id'    => 1,
			'answer_title' => __( 'Option 1', 'tutor' ),
			'is_correct'   => true,
			'answer_order' => 1,
		),
		array(
			'answer_id'    => 2,
			'answer_title' => __( 'Option 2', 'tutor' ),
			'image_url'    => 'https://placehold.co/600x400',
			'is_correct'   => false,
			'answer_order' => 2,
		),
	),
);

?>

<div class="tutor-quiz-question" data-question="<?php echo esc_attr( $question['question_type'] ); ?>" data-question-id="question-<?php echo esc_attr( $question['question_id'] ); ?>" x-data="tutorQuestionOrdering('question-<?php echo esc_attr( $question['question_id'] ); ?>')">
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

	<div class="tutor-quiz-question-options">
		<?php foreach ( $question['question_answers'] as $answer ) : ?>
			<div class="tutor-quiz-question-option" data-option="draggable" data-id="<?php echo esc_attr( $answer['answer_id'] ); ?>">
				<div data-option-order="<?php echo esc_attr( $answer['answer_order'] ); ?>">
					<?php echo esc_html( $answer['answer_order'] ); ?>
				</div>
				<div data-title>
					<?php if ( ! empty( $answer['image_url'] ) ) : ?>
						<img src="<?php echo esc_url( $answer['image_url'] ); ?>" alt="<?php echo esc_attr( $answer['answer_title'] ); ?>">
					<?php endif; ?>
					<?php echo esc_html( $answer['answer_title'] ); ?>
				</div>

				<button type="button" data-grab-handle>
					<?php tutor_utils()->render_svg_icon( Icon::GRAB_HANDLE, 40, 40 ); ?>
				</button>
			</div>
		<?php endforeach; ?>
	</div>
</div>