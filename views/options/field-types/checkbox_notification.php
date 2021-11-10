<?php
/**
 * Notification  for tutor settings.
 *
 * @package Tutor LMS
 * @since 2.0
 */
if ( ! empty( $field['options'] ) ) { ?>
	<div class="tutor-option-field-row">
		<?php include tutor()->path . 'views/options/template/field_heading.php'; ?>
		<div class="type-check d-flex">
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
