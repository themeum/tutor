<div class="tutor-course-builder-header">
    <a href="javascript:;" class="tutor-expand-all-topic"><?php _e('Expand all'); ?></a> |
    <a href="javascript:;" class="tutor-collapse-all-topic"><?php _e('Collapse all'); ?></a>
</div>

<?php $course_id = get_the_ID(); ?>
<div id="tutor-course-content-wrap">
	<?php
	include  tutor()->path.'views/metabox/course-contents.php';
	?>
</div>

<div class="new-topic-btn-wrap">
    <a href="javascript:;" class="create_new_topic_btn tutor_btn_lg"> <i class="tutor-icon-add-line"></i> <?php _e('Add new topic', 'tutor'); ?></a>
</div>


<div class="tutor-metabox-add-topics" style="display: none">
    <h3><?php _e('Add Topic', 'tutor'); ?></h3>

    <div class="tutor-option-field-row">
        <div class="tutor-option-field-label">
            <label for=""><?php _e('Topic Name', 'tutor'); ?></label>
        </div>
        <div class="tutor-option-field">
            <input type="text" name="topic_title" value="">
            <p class="desc">
				<?php _e('Topic titles will be publicly show where required, you can call it as a section also in course', 'tutor'); ?>
            </p>
        </div>
    </div>

    <div class="tutor-option-field-row">
        <div class="tutor-option-field-label">
            <label for=""><?php _e('Topic Summery', 'tutor'); ?></label>
        </div>
        <div class="tutor-option-field">
            <textarea name="topic_summery"></textarea>
            <p class="desc">
				<?php _e('The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.', 'tutor'); ?>
            </p>
			<?php
			//submit_button(__('Add Topic', 'tutor'), 'primary', 'submit', true, array('id' => 'tutor-add-topic-btn')); ?>
            <input type="hidden" name="tutor_topic_course_ID" value="<?php echo $course_id; ?>">
            <button type="button" class="button button-primary" id="tutor-add-topic-btn"><?php _e('Add Topic', 'tutor'); ?></button>
        </div>
    </div>
</div>

<!--<div class="tutor-modal-wrap tutor-quiz-modal-wrap">
    <div class="tutor-modal-content">
        <div class="modal-header">
            <div class="search-bar">
                <input type="text" class="tutor-modal-search-input" placeholder="<?php /*_e('Search quiz...'); */?>">
            </div>
            <div class="modal-close-wrap">
                <a href="javascript:;" class="modal-close-btn">&times;</a>
            </div>
        </div>
        <div class="modal-container"></div>
        <div class="modal-footer">
            <button type="button" class="button button-primary add_quiz_to_post_btn"><?php /*_e('Add Quiz', 'tutor'); */?></button>
        </div>
    </div>
</div>-->


<div class="tutor-modal-wrap tutor-quiz-builder-modal-wrap">
    <div class="tutor-modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <h1><?php _e('Quiz'); ?></h1>
            </div>
            <div class="modal-close-wrap">
                <a href="javascript:;" class="modal-close-btn"><i class="tutor-icon-line-cross"></i> </a>
            </div>
        </div>
        <div class="modal-container"></div>
    </div>
</div>

<div class="tutor-modal-wrap tutor-lesson-modal-wrap">
    <div class="tutor-modal-content">
        <div class="modal-header">
            <div class="lesson-modal-close-wrap">
                <a href="javascript:;" class="modal-close-btn">&times;</a>
            </div>
        </div>
        <div class="modal-container"></div>
    </div>
</div>


<div class="tutor-modal-wrap tutor-assignment-builder-modal-wrap">
    <div class="tutor-modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <h1><?php _e('Assignments', 'tutor'); ?></h1>
            </div>
            <div class="modal-close-wrap">
                <a href="javascript:;" class="modal-close-btn"><i class="tutor-icon-line-cross"></i> </a>
            </div>
        </div>
        <div class="modal-container"></div>
    </div>
</div>