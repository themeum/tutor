<?php
if (!isset($field['fields']) || !is_array($field['fields']) || !count($field['fields'])) {
    return;
}
?>
<div class="tutor-option-field-row">
    <?php include tutor()->path . "views/options/template/field_heading.php"; ?>

    <div class="tutor-option-field-input">
        <div class="tutor-d-flex tutor-flex-column double-input">
            <?php
            foreach ($field['fields'] as $groupFieldKey => $group_field) {
                $input_name = "tutor_option[{$field['key']}][{$groupFieldKey}]";
                $default_value = isset($group_field['default']) ? $group_field['default'] : false;
                $input_value = $this->get($field['key'] . '.' . $groupFieldKey, $default_value);
                $label = $group_field['title'] ?? $groupFieldKey;
                include tutor()->path . "views/options/field-types/groups/{$group_field['type']}.php";
            }
            ?>
        </div>
    </div>
</div>