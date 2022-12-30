<?php
/**
 * Vertical radio for settings.
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
<div class="tutor-option-field-row tutor-d-block" id="<?php echo esc_attr( $field_id ); ?>"
>
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input tutor-d-block">
		<div class="type-check tutor-d-block has-desc">
			<?php
			if ( ! empty( $field['options'] ) ) :
				foreach ( $field['options'] as $option_key => $option ) :
					$option_value = $this->get( $field['key'], tutils()->array_get( 'default', $field ) );
					?>
					<div class="tutor-mb-16">
						<div class="tutor-form-check">
							<input id="radio_<?php echo esc_attr( $option_key ); ?>" type="radio" name="tutor_option[<?php echo esc_attr( $field['key'] ); ?>]" value="<?php echo esc_attr( $option_key ); ?>" <?php esc_attr( checked( $option_value, $option_key ) ); ?> class="tutor-form-check-input" />
							<label for="radio_<?php echo esc_attr( $option_key ); ?>">
								<?php echo esc_attr( ucwords( str_replace( '_', ' ', $option_key ) ) ); ?>
								<p class="desc"><?php echo esc_attr( $option ); ?></p>
							</label>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
