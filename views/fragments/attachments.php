<?php 
    $attachments = $data['attachments'];
    $size_below = isset($data['size_below']) && $data['size_below']==true;
?>

<div class="tutor-attachment-cards tutor-attachment-size-<?php echo $size_below ? 'below' : 'aside'; ?> tutor-course-builder-attachments <?php echo (isset($data['no_control']) && $data['no_control']) ? 'tutor-no-control' : ''; ?>">
    <?php 
    if ( is_array($attachments) && count($attachments)) {
        foreach ( $attachments as $attachment ) {
            if(!is_object($attachment) || !property_exists($attachment, 'id')){ continue; }
            ?>
            <div class="tutor-card tutor-d-flex tutor-align-items-center tutor-px-20 tutor-py-16 tutor-mb-16" data-attachment_id="<?php echo $attachment->id; ?>">
                <div class="tutor-w-100 tutor-pr-24">
                    <div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mb-4"><?php echo $attachment->title; ?></div>
                    <div class="tutor-fs-7 tutor-color-muted"><?php _e('Size', 'tutor'); ?>: <?php echo $attachment->size; ?></div>
                    <input type="hidden" name="<?php echo isset($data['name']) ? $data['name'] : ''; ?>" value="<?php echo $attachment->id; ?>">
                </div>

                <div class="tutor-ml-auto">
                    <span class="tutor-delete-attachment tutor-iconic-btn tutor-iconic-btn-secondary tutor-iconic-btn-lg" role="button">
                        <span class="tutor-icon-times" area-hidden="true"></span>
                    </span>
                </div>
            </div>
            <?php 
        }
    }
    ?>
</div>

<?php if ( isset( $data['add_button'] ) && true === $data['add_button'] ): ?>
    <button type="button" class="tutor-btn tutor-btn-outline-primary tutorUploadAttachmentBtn" data-name="<?php echo isset( $data['name'] ) ? esc_attr( $data['name'] ) : ''; ?>">
        <span class="tutor-icon-paperclip tutor-mr-8"></span>
        <span><?php esc_html_e( 'Upload Attachments', 'tutor' ); ?></span>
    </button>
<?php endif; ?>