<div class="tutor-option-field-input">
    <div class="type-toggle-grid">
        <?php foreach ($field['group_options'] as $key => $option) : ?>
            <div class="toggle-item">
                <label class="tutor-form-toggle">
                    <input type="checkbox" class="tutor-form-toggle-input" name="<?php echo $option['key'] ?>">
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