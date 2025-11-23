<?php
/**
 * Multiple Choice
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\LearningArea\Helper;

$question = array(
	'index'           => 1,
	'question_id'     => 1,
	'question_title'  => __( 'What is the capital of France?', 'tutor' ),
	'question_type'   => 'multiple_choice',
	'answer_required' => true,
	'points'          => 10,
	'answers'         => array(
		array(
			'answer_title' => __( 'Paris', 'tutor' ),
			'thumb'        => 'https://placehold.co/600x400',
			'is_correct'   => true,
		),
		array(
			'answer_title' => __( 'London', 'tutor' ),
			'thumb'        => 'https://placehold.co/600x400',
			'is_correct'   => false,
		),
		array(
			'answer_title' => __( 'Berlin', 'tutor' ),
			'thumb'        => 'https://placehold.co/600x400',
			'is_correct'   => '',
		),
		array(
			'answer_title' => __( 'Rome', 'tutor' ),
			'thumb'        => 'https://placehold.co/600x400',
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
			<div class="tutor-quiz-question-option" data-option="<?php echo esc_attr( Helper::is_correct( $answer ) ); ?>">
				<div class="tutor-input-field <?php echo Helper::has_thumb( $answer ) ? 'tutor-hidden' : ''; ?>">
					<div class="tutor-input-wrapper">
						<!-- @TODO: Disable checkbox when viewing quiz attempt -->
						<input 
							type="checkbox"
							id="<?php echo esc_attr( $question['question_id'] ); ?>"
							placeholder="Enter your full name"
							class="tutor-checkbox"
							checked
							<?php if ( Helper::is_correct( $answer ) ) : ?>
								disabled
							<?php endif; ?>
						>
						<label 
							for="<?php echo esc_attr( $question['question_id'] ); ?>" class="tutor-label"
						>
							<?php echo esc_html( $answer['answer_title'] ); ?>
						</label>
					</div>
				</div>
				<?php if ( Helper::has_thumb( $answer ) ) : ?>
					<img src="<?php echo esc_url( $answer['thumb'] ); ?>" alt="<?php echo esc_attr( $answer['answer_title'] ); ?>">
					<div data-title>
						<?php echo esc_html( $answer['answer_title'] ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>