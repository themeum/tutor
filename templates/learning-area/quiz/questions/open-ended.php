<?php
/**
 * Open-ended and Short Answer
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;

$question_type = $question['question_settings']['question_type'] ?? 'open_ended';
$quiz_settings = $quiz_settings ?? array();
$limit_key     = 'short_answer' === $question_type ? 'short_answer_characters_limit' : 'open_ended_answer_characters_limit';

$characters_limit    = (int) ( $quiz_settings[ $limit_key ] ?? 0 );
$field_name          = $question_field_name_base ?? '';
$register_rule_parts = array();
if ( $answer_is_required ) {
	$register_rule_parts[] = "required: '" . esc_js( $required_message ) . "'";
}
if ( $characters_limit > 0 ) {
	$max_length_message    = __( 'The answer exceeds the allowed character limit', 'tutor' );
	$register_rule_parts[] = "maxLength: { value: {$characters_limit}, message: '" . esc_js( $max_length_message ) . "' }";
}
$register_rules = '';
if ( ! empty( $register_rule_parts ) ) {
	$register_rules = ', { ' . implode( ', ', $register_rule_parts ) . ' }';
}
$register_attr = "register('{$field_name}'{$register_rules})";

?>

<div
	class="tutor-quiz-question-options"
	<?php if ( $characters_limit > 0 ) : ?>
		x-data="{ max: <?php echo esc_attr( $characters_limit ); ?>, remaining: <?php echo esc_attr( $characters_limit ); ?> }"
		x-effect="remaining = Math.max(0, max - (values?.['<?php echo esc_attr( $field_name ); ?>'] || '').length)"
	<?php endif; ?>
>
	<?php
		$input_field = InputField::make()
			->type( InputType::TEXTAREA )
			->name( $field_name )
			->clearable()
			->attr( 'x-bind', $register_attr )
			->placeholder( __( 'Type your answer here', 'tutor' ) );

		$input_field->render();
	?>
	<?php if ( $characters_limit > 0 ) : ?>
		<p class="tutor-small tutor-text-subdued">
			<?php esc_html_e( 'Character Remaining: ', 'tutor' ); ?>
			<span x-text="remaining"></span>
		</p>
	<?php endif; ?>
</div>
