<?php
$value = $this->get( $field['key'] );
if ( $value != '0' && ! $value && isset( $field['default'] ) ) {
	$value = $field['default'];
}
$field_id = 'field_' . $field['key'];

?>
<div class="tutor-option-field-row" id="<?php echo $field_id; ?>">
	<?php require tutor()->path . 'views/options/template/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<input type="email" name="tutor_option[<?php echo $field['key']; ?>]" class="tutor-form-control" placeholder='Please write your "<?php echo $field['label']; ?>"' value="<?php echo isset( $value ) ? $value : ''; ?>" />
	</div>
</div>
