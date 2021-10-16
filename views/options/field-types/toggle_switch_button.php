<?php
$default      = isset( $field['default'] ) ? $field['default'] : '';
$option_value = $this->get( $field['key'], $default );
$field_id     = 'field_' . $field['key'];
$tooltip_desc = ! empty( $field['desc'] ) ? $field['desc'] : null;
?>
<div class="tutor-option-field-row" id="<?php echo $field_id; ?>">
	<div class="tutor-option-field-label <?php echo $tooltip_desc ? 'has-tooltip' : ''; ?>">
		<label><?php echo $field['label']; ?></label>
		<?php
		if ( $tooltip_desc ) {
			?>
				<div class="tooltip-wrap tooltip-icon">
					<span class="tooltip-txt tooltip-right"><?php echo $field['desc']; ?></span>
				</div>
				<?php
		}
		?>
	</div>
	<div class="tutor-option-field-input tutor-bs-d-flex has-btn-after">
		<label class="tutor-form-toggle">
			<input type="hidden" name="tutor_option[<?php echo $field['key']; ?>]" value="off">
			<input type="checkbox" name="tutor_option[<?php echo $field['key']; ?>]" value="on" <?php $option_value ? checked( $option_value[1], 'on' ) : ''; ?> class="tutor-form-toggle-input">
			<span class="tutor-form-toggle-control"></span>
		</label>
		<?php
		if ( isset( $field['buttons'] ) ) {
			foreach ( $field['buttons'] as $key => $button ) {
				if ( $button['type'] == 'anchor' ) {
					?>
					<a class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs" href="<?php echo $button['url']; ?>"><?php echo $button['text']; ?></a>
					<?php
				}
			}
		}
		?>
	</div>
</div>
