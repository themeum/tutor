<!--update announcements modal-->
<div class="tutor-modal-wrap tutor-announcements-modal-wrap tutor-accouncement-update-modal">
    <div class="tutor-modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <h1><?php esc_html_e('Update Announcement', 'tutor');?></h1>
            </div>
            <div class="tutor-announcements-modal-close-wrap">
                        <a href="#" class="tutor-announcement-close-btn">
                            <i class="tutor-icon-line-cross"></i>
                        </a>
            </div>
        </div>

        <div class="modal-container">
            
        <form action="" class="tutor-announcements-update-form">
                <?php tutor_nonce_field();?>
                <input type="hidden" name="announcement_id" id="announcement_id">
                <div class="tutor-option-field-row">
                    <label for="tutor_announcement_course">
                        <?php esc_html_e('Select Course', 'tutor');?>
                    </label>
                    
                    <div class="tutor-announcement-form-control">
                        <select name="tutor_announcement_course" id="tutor-announcement-course-id">
                            <?php if($courses):?>
                            <?php foreach($courses as $course):?>

                                <option value="<?= esc_attr($course->ID)?>">
                                    <?= $course->post_title;?>
                                </option>
                            <?php endforeach;?>
                            <?php else:?>
                            <option value="">No course found</option>
                            <?php endif;?>                            
                        </select>
                        
                    </div>
                </div>

                <div class="tutor-option-field-row">
                    <label for="tutor_announcement_course">
                        <?php esc_html_e('Announcement Title', 'tutor');?>
                    </label>
                    
                    <div class="tutor-announcement-form-control">
                        <input type="text" name="tutor_annoument_title" id="tutor-announcement-title" value="" placeholder="<?php _e('Announcement title', 'tutor'); ?>"> 
                    </div>
                </div>

                <div class="tutor-option-field-row">
                    <label for="tutor_announcement_course">
                        <?php esc_html_e('Summary', 'tutor');?>
                    </label>
                    
                    <div class="tutor-announcement-form-control">
                        <textarea rows="8" type="text" id="tutor-announcement-summary" name="tutor_annoument_summary" value="" placeholder="<?php _e('Summary...', 'tutor'); ?>"></textarea>
                    </div>
                </div>
                <div class="tutor-option-field-row">
                        <input type="checkbox" name="tutor_notify_students">
                        <span><?php esc_html_e('Notify to all students of this course.', 'tutor');?></span>
                </div>

                <div class="tutor-option-field-row">
                    <div class="tutor-announcements-update-alert"></div>
                </div>

                <div class="modal-footer">
                    <div class="tutor-quiz-builder-modal-control-btn-group">
                        <div class="quiz-builder-btn-group-left">
                            <button class="tutor-btn"><?php esc_html_e('Publish','tutor')?></button>
                        </div>
                        <div class="quiz-builder-btn-group-right">
                            <button type="button" class="quiz-modal-tab-navigation-btn  quiz-modal-btn-cancel tutor-announcement-close-btn"><?php esc_html_e('Cancel','tutor')?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!--update announcements modal end-->