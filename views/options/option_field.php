<?php
/**
 * Filed input row.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Options
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Template variables.
 *
 * @var \Tutor\Options_V2 $this
 * @var array            $field
 */

?>
<div class="tutor-option-field-row">
	<div class="tutor-option-field-label">
		<label><?php echo isset( $field['label'] ) ? esc_html( $field['label'] ) : ''; ?></label>
		<p class="desc"><?php echo isset( $field['desc'] ) ? wp_kses_post( $field['desc'] ) : ''; ?></p>
	</div>
	<div class="tutor-option-field-input">
		<?php echo $this->field_type( $field ); //phpcs:ignore -- contain safe data ?>
	</div>
</div>
