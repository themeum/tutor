<?php
    extract($data); // $course_id, $context
    // echo var_dump($data);
?>
<div class="tutor-qa-new tutor-quesanswer" data-course_id="<?php echo $course_id; ?>" data-question_id="0" data-context="<?php echo $context; ?>">
    <div class="tutor-quesanswer-askquestion">
        <textarea placeholder="You Have any question?" class="tutor-form-control"></textarea>
        <!-- <div class="tutor-bs-d-flex tutor-bs-justify-content-end"> -->
        <?php if('course-single-qna-sidebar'==$data['context']):?>
            <div class="sidebar-ask-new-qna-submit tutor-mt-10">
                <button class="sidebar-ask-new-qna-cancel-btn tutor-modal-close-btn tutor-btn tutor-btn-md">
                    <?php _e('Cancel', 'tutor'); ?>
                </button>
                <button class="sidebar-ask-new-qna-submit-btn tutor-btn tutor-btn-primary tutor-btn-md">
                    <?php _e('Submit My Question', 'tutor'); ?>
                </button>
            </div>
            <div class="tutor-bs-d-flex tutor-bs-justify-content-center">
                <a class="sidebar-ask-new-qna-btn tutor-btn tutor-btn-primary tutor-btn-md">
                <?php _e('Ask a New Question', 'tutor'); ?>
                </a>
            </div>
        <?php else: ?>
            <div class="tutor-bs-d-flex tutor-bs-justify-content-end tutor-mt-15">
                <button class="sidebar-ask-new-qna-submit-btn tutor-btn tutor-btn-primary tutor-btn-md">
                    <?php _e('Ask Question', 'tutor'); ?>
                </button>
            </div>
        <?php endif ?>
        <!-- </div> -->
    </div>
</div>
<div class="tutor-qna-single-question"></div>