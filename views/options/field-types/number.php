<?php
/**
 * Number input for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field settings.
 *
 * @var mixed $this  Options_V2 or Tools_V2 instance.
 * @var array $field
 */

$field_key = $field['key'] ?? '';
$value     = (string) $this->get( $field_key );
if ( isset( $field['default'] ) && '' === trim( $value ) ) {
	$value = $field['default'];
}

$field_id    = 'field_' . $field_key;
$min         = $field['min'] ?? 0;
$max         = $field['max'] ?? 0;
$number_type = $field['number_type'] ?? 'decimal';
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<input class="tutor-form-control" 
			type="number"
			data-number-type="<?php echo esc_attr( $number_type ); ?>"

			name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" 
			value="<?php echo esc_attr( $value ); ?>" 
			placeholder="<?php echo esc_attr( $min ); ?>"

			min="<?php echo esc_attr( $min ); ?>"
			<?php if ( $max ) : ?>
				max="<?php echo esc_attr( $max ); ?>"
			<?php endif; ?>
			>
	</div>
</div>
