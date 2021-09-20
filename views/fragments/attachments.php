<div class="tutor-attachment-cards tutor-course-builder-attachments is-lesson">
    <?php 
    $attachments = $data['attachments'];
    if ( is_array($attachments) && count($attachments)) {
        foreach ( $attachments as $attachment ) {
            ?>
            <div data-attachment_id="<?php echo $attachment->id; ?>">
                <div>
                    <a href="<?php echo $attachment->url; ?>" target="_blank">
                        <?php echo $attachment->title; ?>
                    </a>
                    <input type="hidden" name="<?php echo $data['name']; ?>" value="<?php echo $attachment->id; ?>">
                </div>
                <div>
                    <span class="filesize"><?php _e('Size', 'tutor'); ?>: <?php echo $attachment->size; ?></span>
                    <span class="tutor-delete-attachment tutor-icon-line-cross"></span>
                </div>
            </div>
        <?php }
    }
    ?>
</div>