<?php

/**
 * Select filed for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$field_key = sanitize_key( $field['key'] );
$field_id  = sanitize_key( 'field_' . $field_key );
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>
	<div class="tutor-option-field-input">
		<select name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" class="tutor-form-select">
			<?php
			if ( ! isset( $field['options'] ) || $field['options'] !== false ) {
				echo '<option value="-1">' . esc_html__( 'Select Option', 'tutor' ) . '</option>';
			}
			if ( ! empty( $field['options'] ) ) {
				foreach ( $field['options'] as $option_key => $option ) {
					?>
					<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $this->get( $field['key'], ( isset( $field['default'] ) ? $field['default'] : null ) ), $option_key ); ?>><?php echo esc_attr( $option ); ?></option>
					<?php
				}
			}
			?>
		</select>
	</div>
</div>
