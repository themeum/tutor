<div class="tutor-option-field-row col-1x2 col-per-row">
    <div class="tutor-option-field-label">
        <label><?php echo $field['label']; ?></label>
        <p class="desc"><?php echo $field['desc'] ?></p>
    </div>

    <div class="tutor-option-field-input">
        <div class="d-flex radio-thumbnail items-per-row">
            <?php foreach ($field['options'] as $key => $option) : ?>
                <label for="items-per-row-<?php echo $key ?>" class="items-per-row-label">
                    <input type="radio" name="items-per-row" id="items-per-row-<?php echo $key ?>">
                    <span class="icon-wrapper icon-col">
                        <?php echo str_repeat("<span></span>", $key); ?>
                    </span>
                    <span class="title"><?php echo $option ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
</div>