<?php
$value = $this->get($field['field_key']);
if ( ! $value && isset($field['default'])){
	$value = $field['default'];
}
?>


<div class="option-media-wrap">
    <div class="option-media-preview">
        <img src="<?php echo wp_get_attachment_url($value); ?>" />
    </div>

    <input type="hidden" name="_tutor_course_settings[<?php echo $field['field_key']; ?>]" value="<?php echo $value; ?>">
    <button class="button button-cancel tutor-option-media-upload-btn">
        <i class="dashicons dashicons-upload"></i>
        <?php echo $field['label']; ?>
    </button>
</div>

