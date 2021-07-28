<div class="tutor-option-field-row d-block">
    <div class="tutor-option-field-label">
        <label><?php echo $field['label']; ?></label>
        <p class="desc"><?php echo $field['desc'] ?></p>
    </div>
    <div class="tutor-option-field-input d-block">
        <div class="type-check d-block has-desc">
            <?php
            foreach ($field['options'] as $key => $option) :
            ?>
                <div class="tutor-form-check">
                    <input type="radio" id="radio_<?php echo $key ?>" class="tutor-form-check-input" name="radio_b" checked />
                    <label for="radio_<?php echo $key ?>">
                        <?php echo ucwords(str_replace('_', ' ', $key)); ?>
                        <p class="desc"><?php echo $option ?></p>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>