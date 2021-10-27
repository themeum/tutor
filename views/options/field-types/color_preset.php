<?php
$value = $this->get( $field['key'] );
if ( isset( $field['default'] ) && empty( $value ) ) {
	$value = $field['default'];
}
vd( $value );
$field_id = 'field_' . $field['key'];
?>

<div class="tutor-option-field-row d-block" id="<?php echo $field_id; ?>"
>
	<?php require tutor()->path . 'views/options/template/field_heading.php'; ?>

	<div class="tutor-option-field-input d-block">
		<div class="type-check d-block has-desc">
			<?php
			if ( ! empty( $field['options'] ) ) :
				foreach ( $field['options'] as $optionKey => $option ) :
					$option_value = $this->get( $field['key'], tutils()->array_get( 'default', $field ) );
					?>
					<div class="tutor-form-check">
						<input id="radio_<?php echo $optionKey; ?>" type="radio" name="tutor_option[<?php echo $field['key']; ?>]" value="<?php echo $optionKey; ?>" <?php checked( $option_value, $optionKey ); ?> class="tutor-form-check-input" />
						<label for="radio_<?php echo $optionKey; ?>">
							<?php echo ucwords( str_replace( '_', ' ', $optionKey ) ); ?>
							<p class="desc"><?php echo $option; ?></p>
						</label>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>



<div class="tutor-option-field-row d-block">
	<div class="tutor-option-field-label">
		<h5 class="label"><?php _e( $fields_group['label'], 'tutor-pro' ); ?></h5>
		<p class="desc"><?php _e( $fields_group['desc'], 'tutor-pro' ); ?></p>
	</div>


	<div class="tutor-option-field-input color-preset-grid">
		<?php
		foreach ( $fields_group['fields'] as $fields ) :
			// pr( $fields );
			$option_value = $this->get( $field['key'], tutils()->array_get( 'default', $field ) );
			?>
		<label for="<?php esc_attr_e( $fields['key'] ); ?>" class="color-preset-input preset-1">
			<input type="radio" name="tutor_option[<?php esc_attr_e( $fields_group['key'] ); ?>]" id="<?php esc_attr_e( $fields['key'] ); ?>" value="<?php esc_attr_e( $fields['key'] ); ?>" checked="">
			<div class="preset-item">
				<div class="header">
					<?php
					foreach ( $fields['colors'] as $color ) :
						?>
					<span data-preset="<?php echo $color['preset_name']; ?>" style="background-color: <?php echo $color['value']; ?>;">1</span>
					<?php endforeach; ?>
				</div>
				<div class="footer">
					<span class="text-regular-body"><?php esc_attr_e( $fields['label'] ); ?></span><span class="check-icon"></span>
				</div>
			</div>
		</label>
		<?php endforeach; ?>
	</div>
</div>
