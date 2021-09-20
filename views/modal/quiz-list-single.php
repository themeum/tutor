<div id="tutor-quiz-<?php echo $data['quiz_id']; ?>" class="course-content-item tutor-quiz tutor-quiz-<?php echo $data['quiz_id']; ?>">
    <div class="tutor-lesson-top">
        <i class="tutor-icon-move"></i>
        <a href="javascript:;" class="open-tutor-quiz-modal" data-quiz-id="<?php echo $data['quiz_id']; ?>" data-topic-id="<?php echo $data['topic_id']; ?>"> 
            <i class=" tutor-icon-doubt"></i>[<?php _e('QUIZ', 'tutor'); ?>] <?php echo stripslashes($data['quiz_title']); ?> 
        </a>
        <?php do_action('tutor_course_builder_before_quiz_btn_action', $data['quiz_id']); ?>
        <a href="javascript:;" class="tutor-delete-quiz-btn" data-quiz-id="<?php echo $data['quiz_id']; ?>">
            <i class="tutor-icon-garbage"></i>
        </a>
    </div>
</div>