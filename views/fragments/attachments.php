<div class="tutor-attachment-cards tutor-course-builder-attachments <?php echo (isset($data['no_control']) && $data['no_control']) ? 'tutor-no-control' : ''; ?>">
    <?php 
    $attachments = $data['attachments'];
    $size_below = isset($data['size_below']) && $data['size_below']==true;
    if ( is_array($attachments) && count($attachments)) {
        foreach ( $attachments as $attachment ) {
            ?>
            <div data-attachment_id="<?php echo $attachment->id; ?>">
                <div>
                    <a class="filename" href="<?php echo $attachment->url; ?>" target="_blank">
                        <?php echo $attachment->title; ?>
                    </a>
                    <?php if($size_below): ?>
                        <span class="filesize"><?php _e('Size', 'tutor'); ?>: <?php echo $attachment->size; ?></span>
                    <?php endif; ?>
                    <input type="hidden" name="<?php echo isset($data['name']) ? $data['name'] : ''; ?>" value="<?php echo $attachment->id; ?>">
                </div>
                <div>
                    <?php if(!$size_below): ?>
                        <span class="filesize"><?php _e('Size', 'tutor'); ?>: <?php echo $attachment->size; ?></span>
                    <?php endif; ?>
                    <span class="tutor-delete-attachment tutor-action-icon tutor-icon-line-cross"></span>
                </div>
            </div>
        <?php }
    }
    ?>
</div>

<?php 
    if(isset($data['add_button']) && $data['add_button']==true) {
        ?>
            <button type="button" class="tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-md tutorUploadAttachmentBtn" data-name="<?php echo isset($data['name']) ? $data['name'] : ''; ?>">
                <?php _e('Add Attachment', 'tutor'); ?>
            </button>
        <?php
    }
?>