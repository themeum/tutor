<?php
/**
 * True False
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$question = array(
	'index'           => 1,
	'question_id'     => 1,
	'question_title'  => __( 'Is the Earth round?', 'tutor' ),
	'question_type'   => 'true_false',
	'answer_required' => true,
	'points'          => 10,
	'answers'         => array(
		array(
			'answer_title' => __( 'True', 'tutor' ),
			'is_correct'   => true,
		),
		array(
			'answer_title' => __( 'False', 'tutor' ),
			'is_correct'   => false,
		),
	),
);
?>

<div class="tutor-quiz-question" data-question="<?php echo esc_attr( $question['question_type'] ); ?>">
	<?php
	tutor_load_template(
		'demo-components.learning-area.components.quiz.question-header',
		array(
			'index'          => $question['index'],
			'question_title' => $question['question_title'],
			'points'         => $question['points'],
		)
	);
	?>

	<div class="tutor-quiz-question-options">
		<?php foreach ( $question['answers'] as $answer ) : ?>
			<div class="tutor-quiz-question-option" data-option="<?php echo esc_attr( $answer['is_correct'] ? 'correct' : 'incorrect' ); ?>">
				<?php tutor_utils()->render_svg_icon( $answer['is_correct'] ? Icon::CHECK_2 : Icon::CROSS, 20, 20 ); ?>
				<?php echo esc_html( $answer['answer_title'] ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
