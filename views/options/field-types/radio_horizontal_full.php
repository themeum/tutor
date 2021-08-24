<?php
$default = isset($field['default']) ? $field['default'] : '';
$option_value = $this->get($field['key'], $default);
echo '<pre>';
print_r($option_value);
echo '</pre>';
?>
<div class="tutor-option-field-row d-block">
    <?php include tutor()->path . "views/options/template/field_heading.php"; ?>
    <div class="tutor-option-field-input">
        <div class="type-check d-flex">
            <?php foreach ($field['options'] as $key => $option) :
                $field_id = 'radio_id_' . $key;
                $field_value = $field['key'][$key] ?? [];
                echo '<pre>';
                print_r($field_value);
                echo '</pre>';
            ?>
                <div class="tutor-form-check">
                    <input type="hidden" name="tutor_option[<?php echo $field['key'] ?>][<?php echo $key ?>]" value="off">
                    <input type="checkbox" id="<?php echo $field_id ?>" class="tutor-form-check-input" name="tutor_option[<?php echo $field['key'] ?>][<?php echo $key ?>]" <?php checked($this->get($field['key'] . '.' . $key), '1') ?> value="on">
                    <label for="<?php echo $field_id ?>">
                        <?php echo $option ?? null ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>