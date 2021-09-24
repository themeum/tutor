<div id="<?php echo $data['wrapper_id']; ?>" class="tutor-modal modal-sticky-header-footer <?php echo $data['wrapper_class']; ?>">
    <span class="tutor-modal-overlay"></span>
    <div class="tutor-modal-root">
        <div class="tutor-modal-inner">
            <div class="tutor-modal-header">
                <h3 class="tutor-modal-title">
                    <?php echo $data['modal_title']; ?>
                </h3>
                <button data-tutor-modal-close class="tutor-modal-close">
                    <span class="las la-times"></span>
                </button>
            </div>
            <div class="tutor-modal-body-alt">
                <div class="tutor-mb-30">
                    <label class="tutor-form-label"><?php _e('Topic Name', 'tutor'); ?></label>
                    <div class="tutor-input-group tutor-mb-15">
                        <input type="text" name="topic_title" class="tutor-form-control tutor-mb-10" value="<?php echo !empty($data['title']) ? $data['title'] : ''; ?>"/>
                        <p class="tutor-input-feedback tutor-has-icon">
                            <i class="tutor-v2-icon-test icon-info-circle-outline-filled tutor-input-feedback-icon"></i>
                            <?php _e('Topic titles are displayed publicly wherever required. Each topic may contain one or more lessons, quiz and assignments.', 'tutor'); ?>
                        </p>
                    </div>
                </div>
                <div>
                    <label class="tutor-form-label"><?php _e('Topic Summary', 'tutor'); ?></label>
                    <div class="tutor-input-group tutor-mb-15">
                        <textarea name="topic_summery" class="tutor-form-control tutor-mb-10"><?php echo !empty($data['summary']) ? $data['summary'] : ''; ?></textarea>
                        <p class="tutor-input-feedback tutor-has-icon">
                            <i class="tutor-v2-icon-test icon-info-circle-outline-filled tutor-input-feedback-icon"></i>
                            <?php _e('The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.', 'tutor'); ?>
                        </p>
                        <input type="hidden" name="tutor_topic_course_ID" value="<?php echo $data['course_id']; ?>">
                        <input type="hidden" name="topic_id" value="<?php echo $data['topic_id']; ?>">
                    </div>
                </div>
            </div>
            <div class="tutor-modal-footer">
                <div class="row">
                    <div class="col">
                        <button type="button" class="tutor-btn <?php echo !empty($data['button_class']) ? $data['button_class'] : ''; ?>" id="<?php echo !empty($data['button_id']) ? $data['button_id'] : ''; ?>">
                            <?php echo $data['button_text']; ?>
                        </button>
                    </div>
                    <div class="col-auto">
                        <button data-tutor-modal-close class="tutor-btn tutor-is-default">
                            <?php _e('Cancel', 'tutor'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>