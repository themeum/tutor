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
	'index'                => 1,
	'question_id'          => 0,
	'question_title'       => '',
	'question_description' => '',
	'question_type'        => 'fill_in_the_blank',
	'answer_required'      => true,
	'question_mark'        => 10,
	'answer_explanation'   => '',
	'question_settings'    => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'fill_in_the_blank',
		'randomize_question' => '0',
		'show_question_mark' => '1',
		'is_image_matching'  => '0',
	),
);

$question       = wp_parse_args( $question, $default_question );
$field_name     = '';
$field_names    = array();
$register_rules = '';
if ( $answer_is_required ) {
	$register_rules = ", { required: '" . esc_js( $required_message ) . "' }";
}

?>
<div class="tutor-quiz-question-options">
	<?php foreach ( $question['question_answers'] as $answer ) : ?>
		<div class="tutor-quiz-question-option">
			<?php
			$answer_title = $answer['answer_title'];
			$dash_count   = substr_count( $answer_title, '{dash}' );

			if ( $dash_count > 0 ) {
				$input_index = 0;

				$answer_title = preg_replace_callback(
					'/{dash}/',
					function () use ( &$input_index, $tutor_is_started_quiz, $question, $register_rules, &$field_name, &$field_names ) {

						$attempt_id  = (int) $tutor_is_started_quiz->attempt_id;
						$question_id = (int) $question['question_id'];

						$input_name = sprintf(
							'attempt[%d][quiz_question][%d][][%d]',
							$attempt_id,
							$question_id,
							$input_index
						);

						$register_attr = "register('{$input_name}'{$register_rules})";
						$input_html    = sprintf(
							'<input
								type="text"
								class="tutor-quiz-question-input"
								placeholder="%s"
								name="%s"
								x-bind="%s"
							/>',
							esc_attr__( 'Type your answer here', 'tutor' ),
							$input_name,
							esc_attr( $register_attr )
						);

						$input_index++;
						if ( '' === $field_name ) {
							$field_name = $input_name;
						}
						$field_names[] = $input_name;

						return $input_html;
					},
					$answer_title
				);
			}

			echo wp_kses(
				$answer_title,
				array(
					'input' => array(
						'type'        => true,
						'class'       => true,
						'name'        => true,
						'placeholder' => true,
						'x-bind'      => true,
					),
				)
			);
			?>
		</div>
	<?php endforeach; ?>
</div>

<?php if ( $field_name ) : ?>
	<?php $unique_field_names = array_values( array_unique( $field_names ) ); ?>
	<div
		class="tutor-quiz-questions-error"
		x-data="{ fieldNames: <?php echo esc_attr( wp_json_encode( $unique_field_names ) ); ?> }"
		x-cloak
		x-show="fieldNames.some((name) => errors?.[name]?.message)"
		x-text="(() => {
			const match = fieldNames.find((name) => errors?.[name]?.message);
			return match ? errors?.[match]?.message : '';
		})()"
	></div>
<?php endif; ?>
