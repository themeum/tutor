<?php
/**
 * Toggle switch button for email settings page.
 *
 * @package Tutor LMS
 * @since 2.0.0
 */

$field_id = 'field_' . $field['key'];
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<div class="certificate-thumb">
		<img src="<?php echo tutor()->url; ?>assets/images/images-v2/<?php echo $field['thumbs_url']; ?>" alt="">
	</div>
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input tutor-d-flex has-btn-after">
		<label class="tutor-form-toggle">
			<input type="checkbox" class="tutor-form-toggle-input" checked="">
			<span class="tutor-form-toggle-control"></span>
		</label>
		<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs">Edit</button>
		<!-- <span class="delete-btn" style="width: 18px; height: 20px"></span> -->
		<span class="delete-btn tutor-v2-icon-test icon-delete-stroke-filled"></span>
	</div>
</div>
