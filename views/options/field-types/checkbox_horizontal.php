<div class="tutor-option-field-row d-block">
    <div class="tutor-option-field-label">
        <label><?php echo $field['label']; ?></label>
        <p class="desc"><?php echo $field['desc'] ?></p>
    </div>
    <div class="tutor-option-field-input">
        <div class="type-check d-flex">
            <?php foreach ($field['options'] as $optionKey => $option) : ?>
                <div class="tutor-form-check">
                    <input type="checkbox" id="<?php $optionKey ?>" class="tutor-form-check-input" name="<?php $optionKey ?>" />
                    <label for="<?php $optionKey ?>"> <?php echo $option; ?> </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>