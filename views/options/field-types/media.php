<?php
/**
 * Media input for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$field_key = isset( $field['key'] ) ? esc_attr( $field['key'] ) : null;

$field_id = esc_attr( 'field_' . $field_key );
$value    = (int) $this->get( $field_key );

?>

<div class="option-media-wrap" id="<?php echo esc_attr( $field_id ); ?>">
	<div class="option-media-preview">
		<?php
		if ( $value ) {
			?>
			<img src="<?php echo esc_url( wp_get_attachment_url( $value ) ); ?>" />
			<?php
		}
		?>
	</div>

	<input type="hidden" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $value ); ?>">
	<div class="option-media-type-btn-wrap">
		<button class="tutor-btn tutor-option-media-upload-btn">
			<i class="dashicons dashicons-upload"></i>
			<?php
			$btn_text = tutor_utils()->array_get( 'btn_text', $field );
			if ( ! $btn_text ) {
				$btn_text = esc_attr( $field['label'] );
			}
			echo esc_html( $btn_text );
			?>
		</button>
		<button class="tutor-btn tutor-btn-outline-primary tutor-media-option-trash-btn" style="display: <?php echo esc_attr( $value ? '' : 'none' ); ?>;"><i class="tutor-icon-trash-can"></i>
			<?php esc_html_e( 'Delete', 'tutor' ); ?>
		</button>
	</div>
</div>
