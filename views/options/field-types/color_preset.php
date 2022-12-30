<?php
/**
 * Color Preset area for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$value = $this->get( $field['key'] );
if ( isset( $field['default'] ) && empty( $value ) ) {
	$value = $field['default'];
}
$field_key = isset( $field['key'] ) ? esc_attr( $field['key'] ) : null;

$field_id = esc_attr( 'field_' . $field_key );
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
					<div class="tutor-form-check">
						<input id="radio_<?php echo esc_attr( $option_key ); ?>" type="radio" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $option_key ); ?>" <?php echo esc_attr( checked( $option_value, $option_key ) ); ?> class="tutor-form-check-input" />
						<label for="radio_<?php echo esc_attr( $option_key ); ?>">
							<?php echo esc_attr( ucwords( str_replace( '_', ' ', $option_key ) ) ); ?>
							<p class="desc"><?php echo esc_attr( $option ); ?></p>
						</label>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>



<div class="tutor-option-field-row tutor-d-block">
	<div class="tutor-option-field-label">
		<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8" tutor-option-name><?php esc_attr( $fields_group['label'] ); ?></div>
		<div class="tutor-fs-7 tutor-color-muted"><?php esc_attr( $fields_group['desc'] ); ?></div>
	</div>

	<div class="tutor-option-field-input color-preset-grid">
		<?php
		foreach ( $fields_group['fields'] as $fields ) :
			$option_value = $this->get( $field['key'], tutils()->array_get( 'default', $field ) );
			?>
		<label for="<?php echo esc_attr( $fields['key'] ); ?>" class="color-preset-input preset-1">
			<input type="radio" name="tutor_option[<?php echo esc_attr( $fields_group['key'] ); ?>]" id="<?php echo esc_attr( $fields['key'] ); ?>" value="<?php echo esc_attr( $fields['key'] ); ?>" checked="">
			<div class="preset-item">
				<div class="header">
					<?php
					foreach ( $fields['colors'] as $color ) :
						?>
					<span data-preset="<?php echo esc_attr( $color['preset_name'] ); ?>" style="background-color: <?php echo esc_attr( $color['value'] ); ?>;"></span>
					<?php endforeach; ?>
				</div>
				<div class="footer">
					<span class="tutor-fs-6"><?php echo esc_attr( $fields['label'] ); ?></span><span class="check-icon"></span>
				</div>
			</div>
		</label>
		<?php endforeach; ?>
	</div>
</div>
