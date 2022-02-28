<?php 
    $attachments = $data['attachments'];
    $size_below = isset($data['size_below']) && $data['size_below']==true;
?>

<div class="tutor-attachment-cards <?php echo (isset($data['is_responsive']) && $data['is_responsive']) ? 'tutor-attachment-cards-responsive' : ''; ?> tutor-attachment-size-<?php echo $size_below ? 'below' : 'aside'; ?> tutor-course-builder-attachments <?php echo (isset($data['no_control']) && $data['no_control']) ? 'tutor-no-control' : ''; ?>">
    <?php 
    if ( is_array($attachments) && count($attachments)) {
        foreach ( $attachments as $attachment ) {
            if(!is_object($attachment) || !property_exists($attachment, 'id')){ continue; }
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
                    <span class="tutor-delete-attachment tutor-action-icon tutor-icon-line-cross-line tutor-icon-18"></span>
                </div>
            </div>
            <?php 
        }
    }
    ?>
</div>

<?php if ( isset( $data['add_button'] ) && true === $data['add_button'] ): ?>
    <button type="button" class="tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-md tutorUploadAttachmentBtn" data-name="<?php echo isset( $data['name'] ) ? esc_attr( $data['name'] ) : ''; ?>">
        <span class="btn-icon tutor-icon-attach-filled tutor-icon-24"></span>
        <span><?php esc_html_e( 'Upload Attachments', 'tutor' ); ?></span>
    </button>
<?php endif; ?>
