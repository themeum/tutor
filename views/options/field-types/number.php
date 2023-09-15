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

$value = $this->get( $field['key'] );
if ( isset( $field['default'] ) && trim( $value ) === '' ) {
	$value = $field['default'];
}

$field_key   = isset( $field['key'] ) ? esc_attr( $field['key'] ) : null;
$field_id    = esc_attr( 'field_' . $field_key );
$min         = isset( $field['min'] ) ? $field['min'] : 0;
$max         = isset( $field['max'] ) ? $field['max'] : 0;
$number_type = isset( $field['number_type'] ) ? $field['number_type'] : 'decimal';

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
