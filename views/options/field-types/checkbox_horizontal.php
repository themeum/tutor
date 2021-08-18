<?php
if (!empty($field['options'])) {
    $default = $field['default'] ?? '';
    $option_value = $this->get($field['key'], $default);
?>
    <div class="tutor-option-field-row d-block">
        <div class="tutor-option-field-label">
            <label><?php echo $field['label']; ?></label>
            <p class="desc"><?php echo $field['desc'] ?></p>
        </div>
        <div class="tutor-option-field-input">
            <div class="type-check d-flex">
                <?php foreach ($field['options'] as $optionKey => $option) : ?>
                    <div class="tutor-form-check">
                        <input type="checkbox" id="check_<?php echo $optionKey ?>" value="1" class="tutor-form-check-input" name="tutor_option[<?php echo $field['key'] ?>][<?php echo $optionKey ?>]" <?php checked($this->get($field['key'] . '.' . $optionKey), '1') ?>>
                        <label for="check_<?php echo $optionKey ?>"> <?php echo $option; ?> </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php } ?>