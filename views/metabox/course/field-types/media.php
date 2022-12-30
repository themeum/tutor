<?php
/**
 * Media meta box
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$value = (int) $this->get( $field['field_key'] );
?>


<div class="option-media-wrap">
	<div class="option-media-preview">
		<?php
		if ( $value ) {
			?>
			<img src="<?php echo esc_url( wp_get_attachment_url( $value ) ); ?>" />
			<?php
		}
		?>
	</div>

	<input type="hidden" name="_tutor_course_settings[<?php echo esc_attr( $field['field_key'] ); ?>]" value="<?php echo esc_attr( $value ); ?>">
	<button class="button button-cancel tutor-option-media-upload-btn">
		<i class="dashicons dashicons-upload"></i>
		<?php echo esc_attr( $field['label'] ); ?>
	</button>
</div>

