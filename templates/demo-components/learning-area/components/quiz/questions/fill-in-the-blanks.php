<?php
/**
 * Fill In The Blanks
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

// {
// 	"4": {
// 		"question_id": "4",
// 		"content_id": null,
// 		"quiz_id": "21",
// 		"question_title": "Fill in the blanks",
// 		"question_description": "<p>This is description</p>",
// 		"answer_explanation": "",
// 		"question_type": "fill_in_the_blank",
// 		"question_mark": "1.00",
// 		"question_settings": {
// 			"answer_required": "0",
// 			"question_mark": "1",
// 			"question_type": "fill_in_the_blank",
// 			"randomize_question": "0",
// 			"show_question_mark": "0"
// 		},
// 		"question_order": "5",
// 		"question_answers": [
// 			{
// 				"answer_id": "5",
// 				"belongs_question_id": "4",
// 				"belongs_question_type": "fill_in_the_blank",
// 				"answer_title": "Please make sure to use the variable {dash} in your question title to show the blanks in your question. You can use multiple {dash} variables in one question.",
// 				"is_correct": "0",
// 				"image_id": "0",
// 				"answer_two_gap_match": "dasg | dash",
// 				"answer_view_format": "text",
// 				"answer_settings": null,
// 				"answer_order": "1"
// 			}
// 		]
// 	}
// }

$question = array(
	'index'           => 1,
	'question_id'     => 1,
	'question_title'  => __( 'Fill In The Blanks', 'tutor' ),
	'question_type'   => 'fill_in_the_blank',
	'answer_required' => true,
	'question_mark'          => 10,
	'question_settings' => array(
		'answer_required' => '0',
		'question_mark' => '1',
		'question_type' => 'fill_in_the_blank',
		'randomize_question' => '0',
		'show_question_mark' => '1',
		'is_image_matching' => '0'
	),
	'question_answers'         => array(
		array(
			'answer_title' => 'Please make sure to use the variable {dash} in your question title to show the blanks in your question. You can use multiple {dash} variables in one question.',
			"answer_two_gap_match" => "dash | dash",
			"answer_order" => 1,
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
			'question_mark'      => $question['question_mark'],
			'show_question_mark' => $question['question_settings']['show_question_mark'],
		)
	);
	?>

	<div class="tutor-quiz-question-options" data-image-matching="<?php echo esc_attr( $question['question_settings']['is_image_matching'] ); ?>">
		<?php foreach ( $question['question_answers'] as $answer ) : ?>
			<div class="tutor-quiz-question-option">
				<?php
          $title = $answer['answer_title'];
          $title = str_replace( '{dash}', '<input type="text" class="tutor-quiz-question-input" placeholder="Type your answer here" />', $title );
          echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
		<?php endforeach; ?>
	</div>
</div>