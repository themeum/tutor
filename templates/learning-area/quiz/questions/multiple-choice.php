<?php
/**
 * Multiple Choice
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

$question = array(
	'index'             => 1,
	'question_id'       => 1,
	'question_title'    => __( 'What is the capital of France?', 'tutor' ),
	'question_type'     => 'multiple_choice',
	'answer_required'   => true,
	'question_mark'     => 10,
	'question_settings' => array(
		'answer_required'             => '0',
		'question_mark'               => '1',
		'question_type'               => 'multiple_choice',
		'randomize_question'          => '0',
		'has_multiple_correct_answer' => '1',
		'show_question_mark'          => '1',
	),
	'question_answers'  => array(
		array(
			'answer_title' => __( 'Paris', 'tutor' ),
			'is_correct'   => true,
		),
		array(
			'answer_title' => __( 'London', 'tutor' ),
			'is_correct'   => false,
		),
		array(
			'answer_title' => __( 'Berlin', 'tutor' ),
			'is_correct'   => '',
		),
		array(
			'answer_title' => __( 'Rome', 'tutor' ),
			'is_correct'   => false,
		),
	),
);

/** Check if current answer is correct
 *
 * @param array $answer Answer data.
 *
 * @return string Correct, Incorrect or Empty string.
 */
$is_correct = function ( $answer ) {
	if ( ! array_key_exists( 'is_correct', $answer ) ) {
		return '';
	}

	$value = $answer['is_correct'];

	// values that should return an empty string.
	$empty_values = array( null, '' );

	if ( in_array( $value, $empty_values, true ) ) {
		return '';
	}

	// map boolean values to their labels.
	$map = array(
		true  => 'correct',
		false => 'incorrect',
	);

	return $map[ $value ] ?? '';
};

/**
 * Check if current answer has a thumb
 *
 * @param array $answer Answer data.
 *
 * @return bool
 */
$has_image = function ( $answer ) {
	return array_key_exists( 'image_url', $answer );
}

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

	<div class="tutor-quiz-question-options">
		<?php foreach ( $question['question_answers'] as $answer ) : ?>
			<div class="tutor-quiz-question-option" data-option="<?php echo esc_attr( $is_correct( $answer ) ); ?>">
				<div class="tutor-input-field <?php echo $has_image( $answer ) ? 'tutor-hidden' : ''; ?>">
					<div class="tutor-input-wrapper">
						<!-- @TODO: Disable checkbox when viewing quiz attempt -->
						<input 
							type="<?php echo esc_attr( '1' === $question['question_settings']['has_multiple_correct_answer'] ? 'checkbox' : 'radio' ); ?>"
							id="<?php echo esc_attr( $question['question_id'] ); ?>"
							placeholder="Enter your full name"
							class="tutor-checkbox"
							checked
							<?php if ( $is_correct( $answer ) ) : ?>
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
				<?php if ( $has_image( $answer ) ) : ?>
					<img src="<?php echo esc_url( $answer['image_url'] ); ?>" alt="<?php echo esc_attr( $answer['answer_title'] ); ?>">
					<div data-title>
						<?php echo esc_html( $answer['answer_title'] ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>