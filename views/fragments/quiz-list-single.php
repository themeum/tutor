<div id="tutor-quiz-<?php echo $data['quiz_id']; ?>" class="course-content-item tutor-quiz tutor-quiz-<?php echo $data['quiz_id']; ?>">
    <div class="tutor-course-content-top">
        <span class="color-text-hints ttr-humnurger-filled tutor-font-size-24 tutor-pr-10"></span>
        <a href="javascript:;" class="open-tutor-quiz-modal" data-quiz-id="<?php echo $data['quiz_id']; ?>" data-topic-id="<?php echo $data['topic_id']; ?>"> 
            <?php echo stripslashes($data['quiz_title']); ?> 
        </a>
        <div>
            <?php do_action('tutor_course_builder_before_quiz_btn_action', $data['quiz_id']); ?>
            <a href="javascript:;" class="open-tutor-quiz-modal" data-quiz-id="<?php echo $data['quiz_id']; ?>" data-topic-id="<?php echo $data['topic_id']; ?>"> 
                <span class="color-text-hints ttr-edit-filled tutor-font-size-24"></span>
            </a>
            <a href="javascript:;" class="tutor-delete-quiz-btn" data-quiz-id="<?php echo $data['quiz_id']; ?>">
                <span class="color-text-hints ttr-delete-stroke-filled tutor-font-size-24"></span>
            </a>
        </div>
    </div>
</div>