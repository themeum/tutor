<?php
if (!empty($field['group_options'])) {
?>
    <div class="tutor-option-field-input">
        <div class="type-toggle-grid">
            <?php foreach ($field['group_options'] as $key => $option) :
                $default = $option['default'] ?? '';
                $option_value = $this->get($option['key'], $default);
            ?>
                <div class="toggle-item">
                    <label class="tutor-form-toggle">
                        <input type="checkbox" class="tutor-form-toggle-input" name="tutor_option[<?php echo $option['key']; ?>]" value="1" <?php checked($option_value, '1') ?>>
                        <span class="tutor-form-toggle-control"></span>
                        <span class="label-after"> <?php echo $option['label'] ?> </span>
                    </label>
                    <div class="tooltip-wrap tooltip-icon">
                        <span class="tooltip-txt tooltip-right"><?php echo $option['desc'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php } ?>