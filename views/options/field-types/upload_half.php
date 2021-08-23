<div class="tutor-option-field-row col-1x145">
    <div class="tutor-option-field-label">
        <h5 class="label">Signature</h5>
        <p class="desc">Upload a signature that will be printed on the certificate.</p>
    </div>
    <div class="tutor-option-field-input image-previewer is-selected">
        <div class="signature-upload-wrap">
            <div class="signature-upload">
                <div class="signature-preview">
                    <span class="preview-loading"></span>
                    <img src="<?php echo tutor()->url ?>assets/images/images-v2/<?php echo $field['default'] ?>" alt="signature preview">
                    <!-- <img src="" alt="" /> -->
                    <span class="delete-btn"></span>
                </div>
                <div class="signature-info">
                    <p style="font-size: 15px">
                        File Support:
                        <span style="color: #222427; font-weight: 500;">jpg, .jpeg, .png</span>
                    </p>
                    <p style="font-size: 13px; margin-top:7px;">Image size ratio: 4:1</p>
                </div>
            </div>
            <label for="signature-uploader" class="tutor-btn tutor-is-sm">
                <input type="file" name="signature-uploader" id="signature-uploader" accept=".jpg, .jpeg, .png, .svg">
                <!-- <span class="tutor-btn-icon las la-image"></span> -->
                <span class="tutor-btn-icon tutor-v2-icon-test icon-image-filled"></span>
                <span>Upload Image</span>
            </label>
        </div>
    </div>
</div>