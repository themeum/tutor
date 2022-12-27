<?php
/**
 * Toggle switch button for email settings page.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$field_id = 'field_' . $field['key'];
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<div class="certificate-thumb">
		<?php
			$img_url = tutor()->url . 'assets/images/images-v2/' . $field['thumbs_url'];
		?>
		<img src="<?php echo esc_url( $img_url ); ?>>" alt="">
	</div>
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input tutor-d-flex has-btn-after">
		<label class="tutor-form-toggle">
			<input type="checkbox" class="tutor-form-toggle-input" checked="">
			<span class="tutor-form-toggle-control"></span>
		</label>
		<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-btn-sm"><?php esc_html_e( 'Edit', 'tutor' ); ?></button>
		<span class="icon-trash-can-line" area-hidden="true"></span>
	</div>
</div>
