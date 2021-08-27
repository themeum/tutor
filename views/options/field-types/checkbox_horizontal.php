<?php
if (!empty($field['options'])) {
    $field_id = 'field_' . $field['key'];

?>
    <div class="tutor-option-field-row d-block" id="<?php echo $field_id; ?>">
        <?php include tutor()->path . "views/options/template/field_heading.php"; ?>

        <div class="tutor-option-field-input">
            <div class="type-check d-flex">
                <?php foreach ($field['options'] as $optionKey => $option) : ?>
                    <div class="tutor-form-check">
                        <input type="checkbox" id="check_<?php echo $optionKey ?>" name="tutor_option[<?php echo $field['key'] ?>][<?php echo $optionKey ?>]" value="1" <?php checked($this->get($field['key'] . '.' . $optionKey), '1') ?> class="tutor-form-check-input" />

                        <label for="check_<?php echo $optionKey ?>"> <?php echo $option; ?> </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php } ?>