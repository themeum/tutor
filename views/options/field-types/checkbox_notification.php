<?php
if (!empty($field['options'])) {
    // $field_id = 'field_' . $field['key'];
    // $saved_data = $this->get($field['key'], array());
    ?>
    <div class="tutor-option-field-row">
        <?php include tutor()->path . "views/options/template/field_heading.php"; ?>

            <div class="type-check d-flex">
                <?php foreach ($field['options'] as $optionKey => $option) : ?>
                   
                    <div class="tutor-form-check">
                        <input type="checkbox" id="check_<?php echo $optionKey ?>" name="tutor_option<?php echo $optionKey; ?>" value="1" class="tutor-form-check-input" />
                        <label for="check_<?php echo $optionKey ?>"> <?php echo $option; ?> </label>
                    </div>
                    
                <?php endforeach; ?>
            </div>
    </div>
    <?php 
}
