<?php
/**
 * Notification checkbox for tutor settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$field_key = isset( $field['key'] ) ? esc_attr( $field['key'] ) : null;
$field_id  = esc_attr( 'field_' . $field_key );
if ( ! empty( $field['options'] ) ) { ?>
	<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
		<?php include tutor()->path . 'views/options/template/common/field_heading.php'; ?>
		<div class="type-check tutor-d-flex">
			<?php foreach ( $field['options'] as $option_key => $option ) : ?>
				<div class="tutor-form-check">
					<input type="checkbox" id="check_<?php echo esc_attr( $option_key ); ?>" name="tutor_option<?php echo esc_attr( $option_key ); ?>" value="1" <?php checked( $option['value'], 1 ); ?> class="tutor-form-check-input" />
					<label for="check_<?php echo esc_attr( $option_key ); ?>"> <?php echo esc_html( $option['label'] ); ?> </label>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}
