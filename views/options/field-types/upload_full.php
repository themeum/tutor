<div class="tutor-option-field-row d-block">
    <div class="tutor-option-field-label">
        <label><?php echo $field['label'] ?></label>
    </div>
    <div class="tutor-option-field-input image-previewer is-selected">
        <div class="d-flex logo-upload">
            <div class="logo-preview">
                <span class="preview-loading"></span>
                <img src="<?php echo tutor()->url ?>assets/images/images-v2/icons/tutor-logo-course-builder.svg" alt="course builder logo">
                <!-- <img src="" alt="" /> -->
                <span class="delete-btn"></span>
            </div>
            <div class="logo-upload-wrap">
                <p>
                    Size: <strong>200x40 pixels;</strong> File Support:
                    <strong>jpg, .jpeg or .png.</strong>
                </p>
                <label for="builder-logo-upload" class="tutor-btn tutor-is-sm">
                    <input type="file" name="builder-logo-upload" id="builder-logo-upload" accept=".jpg, .jpeg, .png, .svg">
                    <span class="tutor-btn-icon tutor-v2-icon-test icon-image-filled"></span>
                    <span>Upload Image</span>
                </label>
            </div>
        </div>
    </div>
</div>