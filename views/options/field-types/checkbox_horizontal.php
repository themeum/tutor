<?php
if (!empty($field['options'])) {
    $field_id = 'field_' . $field['key'];
    $saved_data = $this->get($field['key'], array());
    !is_array($saved_data) ? $saved_data = array() : 0;
    ?>
    <div class="tutor-option-field-row tutor-bs-d-block" id="<?php echo $field_id; ?>">
        <?php include tutor()->path . "views/options/template/field_heading.php"; ?>

        <div class="tutor-option-field-input">
            <div class="type-check tutor-bs-d-flex">
                <?php foreach ($field['options'] as $optionKey => $option) : ?>
                    <?php 
                        $input_id = 'tutor_check_' . $optionKey;
                        $_checked = in_array($optionKey, $saved_data) ? 'checked="checked"' : ''; 
                    ?>
                    <div class="tutor-form-check">
                        <input type="checkbox" id="<?php echo $input_id; ?>" name="tutor_option[<?php echo $field['key'] ?>][]" value="<?php echo $optionKey; ?>" <?php echo $_checked; ?> class="tutor-form-check-input" />
                        <label for="<?php echo $input_id; ?>"> <?php echo $option; ?> </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php 
}