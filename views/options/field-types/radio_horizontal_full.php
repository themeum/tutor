<?php $field_id = 'field_' . $field['key']; ?>
<div class="tutor-option-field-row tutor-d-block" id="<?php echo $field_id; ?>">
    <?php include tutor()->path . "views/options/template/field_heading.php"; ?>

    <div class="tutor-option-field-input">
        <div class="type-check tutor-d-flex">
            <?php
            if (!empty($field['options'])) :
                foreach ($field['options'] as $optionKey => $option) :
                    $option_value = $this->get($field['key'], tutils()->array_get('default', $field));
            ?>
                    <div class="tutor-form-check">
                        <input id="radio_<?php echo $optionKey ?>" type="radio" name="tutor_option[<?php echo $field['key']; ?>]" value="<?php echo $optionKey ?>" <?php checked($option_value,  $optionKey) ?> class="tutor-form-check-input" />
                        <label for="radio_<?php echo $optionKey ?>"><?php echo $option ?></label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>