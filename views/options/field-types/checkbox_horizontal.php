<?php
/**
 * Horizontal checkbox for tutor settings.
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

if ( ! empty( $field['options'] ) ) {
	$field_key = sanitize_key( $field['key'] );
	$field_id  = 'field_' . $field_key;
	$default   = array();
	if ( isset( $field['default'] ) ) {
		$default = array_combine( $field['default'], $field['default'] );
	}

	$saved_data = $this->get( $field_key, $default );
	if ( ! is_array( $saved_data ) ) {
		$saved_data = array();
	}
	?>
	<div class="tutor-option-field-row tutor-option-checkbox-horizontal" id="<?php echo esc_attr( $field_id ); ?>">
		<?php include tutor()->path . 'views/options/template/common/field_heading.php'; ?>
		<div class="tutor-option-field-input tutor-d-flex">
			<div class="type-check tutor-d-flex">
				<?php
				foreach ( $field['options'] as $option_key => $option ) :
						$input_id = 'tutor_check_' . $option_key;
					?>
					<div class="tutor-form-check">
						<input type="checkbox" id="<?php echo esc_attr( $input_id ); ?>" name="tutor_option[<?php echo esc_attr( $field_key ); ?>][<?php echo esc_attr( $option_key ); ?>]" value="<?php echo esc_attr( $option_key ); ?>" <?php checked( isset( $saved_data[ $option_key ] ) ); ?> class="tutor-form-check-input" />
						<label for="<?php echo esc_attr( $input_id ); ?>"> <?php echo esc_html( $option ); ?> </label>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}
