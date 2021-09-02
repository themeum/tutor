<?php
$default = isset($field['default']) ? $field['default'] : '';
$option_value = $this->get($field['key'], $default);
$field_id = 'field_' . $field['key'];
?>
<div class="tutor-option-field-row d-block" id="<?php echo $field_id; ?>">
    <?php include tutor()->path . "views/options/template/field_heading.php"; ?>
    <div class="tutor-option-field-input">
        <div class="type-check d-flex">
            <?php foreach ($field['options'] as $key => $option) :
                $field_id = 'radio_id_' . $key;
                $field_value = $option_value[$key] ?? [];
            ?>
                <div class="tutor-form-check">
                    <input type="hidden" name="tutor_option[<?php echo $field['key'] ?>][<?php echo $key ?>]" value="off">
                    <input type="checkbox" id="<?php echo $field_id ?>" class="tutor-form-check-input" name="tutor_option[<?php echo $field['key'] ?>][<?php echo $key ?>]" <?php checked($field_value[1], 'on') ?> value="on">
                    <label for="<?php echo $field_id ?>">
                        <?php echo $option ?? null ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>