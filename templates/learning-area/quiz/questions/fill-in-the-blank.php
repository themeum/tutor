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

$base_field_name = ( $question_field_name_base ?? '' ) . '[]';
$field_name      = '';
$field_names     = array();
$register_rules  = '';
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
					function () use ( &$input_index, $base_field_name, $register_rules, &$field_name, &$field_names ) {

						$input_name = sprintf( '%s[%d]', $base_field_name, $input_index );

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
