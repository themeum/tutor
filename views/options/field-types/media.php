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

defined( 'ABSPATH' ) || exit;

/**
 * Field settings.
 *
 * @var mixed $this  Options_V2 or Tools_V2 instance.
 * @var array $field
 */

$field_key = $field['key'] ?? '';
$field_id  = 'field_' . $field_key;
$value     = (int) $this->get( $field_key );
$btn_text  = tutor_utils()->array_get( 'btn_text', $field );
if ( ! $btn_text ) {
	$btn_text = $field['label'] ?? '';
}
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
			<?php echo esc_html( $btn_text ); ?>
		</button>
		<button class="tutor-btn tutor-btn-outline-primary tutor-media-option-trash-btn" style="display: <?php echo esc_attr( $value ? '' : 'none' ); ?>;"><i class="tutor-icon-trash-can"></i>
			<?php esc_html_e( 'Delete', 'tutor' ); ?>
		</button>
	</div>
</div>
