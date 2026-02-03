<?php
/**
 * Matching
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;

global $tutor_is_started_quiz;

$default_question = array(
	'index'             => 1,
	'question_id'       => 0,
	'question_title'    => '',
	'question_type'     => 'matching',
	'answer_required'   => true,
	'question_mark'     => 10,
	'question_settings' => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'matching',
		'randomize_question' => '0',
		'show_question_mark' => '1',
		'is_image_matching'  => '0',
	),
);

$question = wp_parse_args( $question, $default_question );

?>

<div 
	class="tutor-quiz-question" 
	data-question="<?php echo esc_attr( $question['question_type'] ); ?>"
	x-data="tutorQuestionMatching('question-<?php echo esc_attr( $question['question_id'] ); ?>')"
>
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

	<div class="tutor-quiz-question-options" data-image-matching="<?php echo esc_attr( $question['question_settings']['is_image_matching'] ); ?>">
		<?php foreach ( $question['question_answers'] as $answer ) : ?>
			<div class="tutor-quiz-question-option">
				<?php if ( $question['question_settings']['is_image_matching'] && ! empty( $answer['image_id'] ) ) : ?>
					<img src="<?php echo esc_url( wp_get_attachment_image_url( $answer['image_id'], 'full' ) ); ?>" alt="<?php echo esc_attr( $answer['answer_title'] ); ?>">
				<?php else : ?>
					<div data-title>
						<div class="tutor-quiz-question-option-number">
							<?php echo esc_html( $answer['answer_order'] ); ?>
						</div>
						<?php echo esc_html( $answer['answer_title'] ); ?>
					</div>
				<?php endif; ?>
				<div class="tutor-quiz-question-option-drop-zone">
					<input
						class="tutor-hidden"
						name="attempt[<?php echo esc_attr( $tutor_is_started_quiz->attempt_id ); ?>][quiz_question][<?php echo esc_attr( $question['question_id'] ); ?>][answers][]"
						value="<?php echo esc_attr( $answer['answer_id'] ); ?>"
						x-bind="register('attempt[<?php echo esc_attr( $tutor_is_started_quiz->attempt_id ); ?>][quiz_question][<?php echo esc_attr( $question['question_id'] ); ?>][answers][]')"
					>
					<span data-drop-placeholder class="tutor-text-subdued">
						<?php esc_html_e( 'Drop here', 'tutor' ); ?>
					</span>
					<button type="button" class="tutor-hidden tutor-btn tutor-btn-icon tutor-btn-ghost tutor-btn-x-small">
						<?php tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ); ?>
					</button>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="tutor-quiz-question-draggable">
		<div class="tutor-quiz-question-draggable-header">
			<?php tutor_utils()->render_svg_icon( Icon::DRAG, 20, 20, array( 'class' => 'tutor-icon-secondary' ) ); ?>
			<span class="tutor-text-small tutor-font-medium"><?php esc_html_e( 'Drag from here', 'tutor' ); ?></span>
		</div>
		<div class="tutor-quiz-question-options">
			<?php foreach ( $question['question_answers'] as $answer ) : ?>
				<div class="tutor-quiz-question-option" data-option="draggable" data-id="<?php echo esc_attr( $answer['answer_id'] ); ?>">
					<div data-title>
						<?php echo esc_html( $answer['answer_two_gap_match'] ); ?>
					</div>
					<button type="button" data-grab-handle>
						<?php tutor_utils()->render_svg_icon( Icon::GRAB_HANDLE, 24, 24 ); ?>
					</button>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
