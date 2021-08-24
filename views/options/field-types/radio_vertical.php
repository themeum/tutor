<div class="tutor-option-field-row d-block">
    <?php include tutor()->path . "views/options/template/field_heading.php"; ?>

    <div class="tutor-option-field-input d-block">
        <div class="type-check d-block has-desc">
            <?php
            if (!empty($field['options'])) :
                foreach ($field['options'] as $optionKey => $option) :
                    $option_value = $this->get($field['key'], tutils()->array_get('default', $field));
            ?>
                    <div class="tutor-form-check">
                        <input id="radio_<?php echo $optionKey ?>" type="radio" name="tutor_option[<?php echo $field['key']; ?>]" value="<?php echo $optionKey ?>" <?php checked($option_value,  $optionKey) ?> class="tutor-form-check-input" />
                        <label for="radio_<?php echo $optionKey ?>">
                            <?php echo ucwords(str_replace('_', ' ', $optionKey)); ?>
                            <p class="desc"><?php echo $option ?></p>
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>