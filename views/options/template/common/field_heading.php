<?php
/**
 * Template: Field heading
 */

?>
<div class="tutor-option-field-label">
	<?php isset( $field['label'] ) ? printf( '<h5 class="label">%s</h5>', esc_attr( $field['label'] ) ) : null; ?>
	<?php isset( $field['desc'] ) ? printf( '<p class="desc">%s</p>', wp_kses_post( $field['desc'] ) ) : null; ?>
</div>
