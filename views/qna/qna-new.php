<?php
    extract($data); // $course_id, $context
?>
<div class="tutor-qa-new tutor-quesanswer" data-course_id="<?php echo $course_id; ?>" data-question_id="0" data-context="<?php echo $context; ?>">
    <div class="tutor-quesanswer-askquestion">
        <textarea placeholder="You Have any question?" class="tutor-form-control"></textarea>
        <div class="tutor-bs-d-flex tutor-bs-justify-content-end">
            <button class="tutor-btn tutor-btn-primary tutor-btn-md">
                <?php _e('Ask a New Question', 'tutor'); ?>
                <!-- <?php _e('Submit My Question', 'tutor'); ?> -->
            </button>
            <a class="sidebar-ask-new-qna-btn tutor-btn tutor-btn-primary tutor-btn-md">
                <?php _e('Submit My Question', 'tutor'); ?>
            </a>
        </div>
    </div>
</div>
<div class="tutor-qna-single-question"></div>