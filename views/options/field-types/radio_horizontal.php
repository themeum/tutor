<?php $field_id = 'field_' . $field['key'];
 ?>
<div class="tutor-option-field-row tutor-bs-col-1x2 tutor-bs-col-per-row" id="<?php echo $field_id; ?>"
>
    <?php include tutor()->path . "views/options/template/field_heading.php"; ?>

    <div class="tutor-option-field-input">
        <div class="tutor-bs-d-flex radio-thumbnail items-per-row">
            <?php
            $i = 1;
            if (!empty($field['options'])) :
                foreach ($field['options'] as $optionKey => $option) :
                    $option_value = $this->get($field['key'], tutils()->array_get('default', $field));
            ?>
                    <label for="items-per-row-<?php echo $optionKey ?>" class="items-per-row-label">
                        <input type="radio" name="tutor_option[<?php echo $field['key']; ?>]" id="items-per-row-<?php echo $optionKey ?>" <?php checked($option_value,  $optionKey) ?> value="<?php echo $optionKey ?>" />
                        <span class="icon-wrapper icon-col">
                            <?php echo str_repeat("<span></span>", $i++); ?>
                        </span>
                        <span class="title"><?php echo $option ?></span>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>