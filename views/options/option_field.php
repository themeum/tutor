<?php
/**
 * Filed input row.
 *
 * @package Tutor LMS
 * @since 2.0
 */

?>
<div class="tutor-option-field-row">
	<div class="tutor-option-field-label">
		<label><?php echo isset( $field['label'] ) ? esc_attr( $field['label'] ) : ''; ?></label>
		<p class="desc"><?php echo isset( $field['desc'] ) ? esc_attr( $field['desc'] ) : ''; ?></p>
	</div>
	<div class="tutor-option-field-input">
		<?php echo $this->field_type( $field ); ?>
	</div>
</div>
