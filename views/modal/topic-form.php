<div id="<?php echo $data['wrapper_id']; ?>" class="tutor-modal tutor-modal-scrollable <?php echo $data['wrapper_class']; ?>">
    <div class="tutor-modal-overlay"></div>
    <div class="tutor-modal-window">
        <div class="tutor-modal-content">
            <div class="tutor-modal-header">
                <div class="tutor-modal-title">
                    <?php echo $data['modal_title']; ?>
                </div>
                <button class="tutor-iconic-btn tutor-modal-close" data-tutor-modal-close>
                    <span class="tutor-icon-times" area-hidden="true"></span>
                </button>
            </div>

            <div class="tutor-modal-body">
                <div class="tutor-mb-32">
                    <label class="tutor-fs-7 tutor-fw-medium tutor-color-black-70 tutor-mb-4 d-block"><?php _e('Topic Name', 'tutor'); ?></label>
                    <div class="tutor-input-group tutor-mb-16">
                        <input type="text" name="topic_title" class="tutor-form-control tutor-mb-12" value="<?php echo !empty($data['title']) ? $data['title'] : ''; ?>"/>
                        <p class="tutor-input-feedback tutor-has-icon">
                            <i class="tutor-icon-circle-info-o tutor-input-feedback-icon"></i>
                            <?php _e('Topic titles are displayed publicly wherever required. Each topic may contain one or more lessons, quiz and assignments.', 'tutor'); ?>
                        </p>
                    </div>
                </div>
                
                <div>
                    <label class="tutor-fs-7 tutor-fw-medium tutor-color-black-70 tutor-mb-4 d-block"><?php _e('Topic Summary', 'tutor'); ?></label>
                    <div class="tutor-input-group tutor-mb-16">
                        <textarea name="topic_summery" class="tutor-form-control tutor-mb-12"><?php echo !empty($data['summary']) ? $data['summary'] : ''; ?></textarea>
                        <p class="tutor-input-feedback tutor-has-icon">
                            <i class="tutor-icon-circle-info-o tutor-input-feedback-icon"></i>
                            <?php _e('Add a summary of short text to prepare students for the activities for the topic. The text is shown on the course page beside the tooltip beside the topic name.', 'tutor'); ?>
                        </p>
                        <input type="hidden" name="topic_course_id" value="<?php echo $data['course_id']; ?>">
                        <input type="hidden" name="topic_id" value="<?php echo $data['topic_id']; ?>">
                    </div>
                </div>
            </div>

            <div class="tutor-modal-footer">
                <button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
                    <?php _e('Cancel', 'tutor'); ?>
                </button>

                <button type="button" class="tutor-btn tutor-btn-primary <?php echo !empty($data['button_class']) ? $data['button_class'] : ''; ?>" id="<?php echo !empty($data['button_id']) ? $data['button_id'] : ''; ?>">
                    <?php echo $data['button_text']; ?>
                </button>
            </div>
        </div>
    </div>
</div>