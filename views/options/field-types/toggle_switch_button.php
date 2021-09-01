<?php
$field_id = 'field_' . $field['key'];
$email_edit_url = add_query_arg(
    array('page' => 'tutor_settings', 'tab_page' => 'email', 'edit' => $field['key']),
    admin_url('admin.php')
);
?>
<div class="tutor-option-field-row" id="<?php echo $field_id; ?>">
    <div class="tutor-option-field-label has-tooltip">
        <label>Course Enrolled</label>
        <div class="tooltip-wrap tooltip-icon">
            <span class="tooltip-txt tooltip-right">I'm your tiny tooltips text</span>
        </div>
    </div>
    <div class="tutor-option-field-input d-flex has-btn-after">
        <label class="tutor-form-toggle">
            <input type="checkbox" class="tutor-form-toggle-input" checked="">
            <span class="tutor-form-toggle-control"></span>
        </label>
        <a class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs" href="<?php echo $email_edit_url ?>">Edit</a>
    </div>
</div>