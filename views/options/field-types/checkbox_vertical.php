<?php
/**
 * Full horizontal checkbox for tutor settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

if ( ! empty( $field['options'] ) ) {
	$field_key  = isset( $field['key'] ) ? esc_attr( $field['key'] ) : null;
	$field_id   = esc_attr( 'field_' . $field_key );
	$saved_data = $this->get( $field_key, array() );
	$saved_data = ! is_array( $saved_data ) ? array( $saved_data ) : $saved_data;
	?>
	<div class="tutor-option-field-row tutor-d-block" id="<?php echo esc_attr( $field_id ); ?>">
		<?php include tutor()->path . 'views/options/template/common/field_heading.php'; ?>

		<div class="tutor-option-field-input">
			<div class="type-check tutor-d-block">
				<?php foreach ( $field['options'] as $option_key => $option ) : ?>
					<?php $_checked = in_array( $option_key, $saved_data ) ? 'checked="checked"' : ''; ?>
					<div class="tutor-mb-16">
						<div class="tutor-form-check">
							<input type="checkbox" id="check_<?php echo esc_attr( $option_key ); ?>_<?php echo esc_attr( $field_key ); ?>" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $option_key ); ?>" <?php echo wp_kses_post( $_checked ); ?> class="tutor-form-check-input" />
							<label for="check_<?php echo esc_attr( $option_key ); ?>_<?php echo esc_attr( $field_key ); ?>"> <?php echo esc_attr( $option ); ?> </label>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}
