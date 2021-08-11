<div class="tutor-option-field-row d-block">
    <div class="tutor-option-field-label">
        <label><?php echo $field['label']; ?></label>
        <p class="desc"><?php echo $field['desc'] ?></p>
    </div>
    <div class="tutor-option-field-input">
        <div class="radio-thumbnail has-title instructor-list">
            <?php foreach ($field['group_options'] as $key => $options) : ?>
                <div class="<?php echo $key; ?>">
                    <div class="layout-label"><?php echo ucwords($key) ?></div>
                    <div class="fields-wrapper">
                        <?php foreach ($options as $key => $option) : ?>
                            <label for="<?php echo $key ?>">
                                <input type="radio" name="instructor-list-layout" id="<?php echo $key ?>" checked="">
                                <span class="icon-wrapper">
                                    <img src="<?php echo tutor()->url ?>assets/images/images-v2/<?php echo $option['image']; ?>" alt="">
                                </span>
                                <span class="title"><?php echo $option['title']; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>