<div class="tutor-option-field-row tutor-bs-d-block">
<?php include tutor()->path . "views/options/template/field_heading.php";?>

    <div class="tutor-option-field-input image-previewer">
        <div class="tutor-bs-d-flex logo-upload">
            <div class="logo-preview">
                <span class="preview-loading"></span>
                <img src="https://tutor.test/wp-content/plugins/tutor/assets/images/images-v2/icons/tutor-logo-course-builder.svg" alt="course builder logo">
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
                    <span class="tutor-btn-icon ttr-image-filled"></span>
                    <span>Upload Image</span>
                </label>
            </div>
        </div>
    </div>
</div>