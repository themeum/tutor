<?php $field_id = 'field_' . $field['key']; ?>
<div class="tutor-option-field-row tutor-d-block" id="<?php echo $field_id; ?>">
    <?php include tutor()->path . "views/options/template/field_heading.php"; ?>

    <div class="tutor-option-field-input">
        <div class="radio-thumbnail has-title public-profile fields-wrapper">
            <?php if (!empty($field['group_options'])) : ?>
                <?php foreach ($field['group_options'] as $optionKey => $option) :
                    $option_value = $this->get($field['key'], tutils()->array_get('default', $field));
                ?>
                    <label for="profile-<?php echo $optionKey ?>">
                        <input type="radio" name="tutor_option[<?php echo $field['key']; ?>]" id="profile-<?php echo $optionKey ?>" <?php checked($option_value,  $optionKey) ?> value="<?php echo $optionKey ?>">
                        <span class="icon-wrapper">
                            <img src="<?php echo tutor()->url ?>assets/images/images-v2/<?php echo $option['image']; ?>" alt="">
                        </span>
                        <span class="title"><?php echo $option['title']; ?></span>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>