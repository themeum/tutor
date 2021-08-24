<div class="tutor-option-field-row d-block">
<?php include tutor()->path . "views/options/template/field_heading.php";?>

    <div class="tutor-option-field-input">
        <div class="radio-thumbnail has-title public-profile fields-wrapper">
            <?php foreach ($field['group_options'] as $key => $option) : ?>
                <label for="profile-<?php echo $key ?>">
                    <input type="radio" name="profile-<?php echo $field['key'] ?>" id="profile-<?php echo $key ?>">
                    <span class="icon-wrapper">
                        <img src="<?php echo tutor()->url ?>assets/images/images-v2/<?php echo $option['image']; ?>" alt="">
                    </span>
                    <span class="title"><?php echo $option['title']; ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
</div>