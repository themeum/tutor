<?php
    extract($data); // $course_id, $context
?>
<div class="tutor-qa-new tutor-quesanswer" data-course_id="<?php echo $course_id; ?>" data-question_id="0" data-context="<?php echo $context; ?>">
    <div class="tutor-quesanswer-askquestion">
        <textarea placeholder="You Have any question?" class="tutor-form-control"></textarea>
        <!-- <div class="tutor-bs-d-flex tutor-bs-justify-content-end"> -->
            <div class="sidebar-ask-new-qna-submit">
                <button class="sidebar-ask-new-qna-submit-btn tutor-btn tutor-btn-primary tutor-btn-md tutor-mr-10">
                    <?php _e('Submit My Question', 'tutor'); ?>
                </button>
                <button class="sidebar-ask-new-qna-cancel-btn tutor-btn tutor-btn-primary tutor-btn-md">
                    <?php _e('Cancel', 'tutor'); ?>
                </button>
            </div>
            <a class="sidebar-ask-new-qna-btn tutor-btn tutor-btn-primary tutor-btn-md">
                <?php _e('Ask a New Question', 'tutor'); ?>
            </a>
        <!-- </div> -->
    </div>
</div>
<div class="tutor-qna-single-question"></div>