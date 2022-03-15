<?php
    extract($data); // $course_id, $context
    // echo var_dump($data);
?>
<div class="tutor-qa-new tutor-quesanswer" data-course_id="<?php echo $course_id; ?>" data-question_id="0"
    data-context="<?php echo $context; ?>">
    <div class="tutor-quesanswer-askquestion">
        <textarea placeholder="You Have any question?" class="tutor-form-control"></textarea>
        <?php if('course-single-qna-sidebar'==$data['context']):?>
        <div class="sidebar-ask-new-qna-submit tutor-mt-12">
            <button class="sidebar-ask-new-qna-cancel-btn tutor-modal-close-btn tutor-btn tutor-btn-md">
                <?php _e('Cancel', 'tutor'); ?>
            </button>
            <button class="sidebar-ask-new-qna-submit-btn tutor-btn tutor-btn-primary tutor-btn-md">
                <?php _e('Submit My Question', 'tutor'); ?>
            </button>
        </div>
        <div class="tutor-d-flex tutor-justify-content-center">
            <a class="sidebar-ask-new-qna-btn tutor-btn tutor-btn-primary tutor-btn-md">
                <?php _e('Ask a New Question', 'tutor'); ?>
            </a>
        </div>
        <?php else: ?>
        <div class="tutor-d-flex tutor-justify-content-end tutor-mt-24">
            <button class="sidebar-ask-new-qna-submit-btn tutor-btn tutor-btn-primary tutor-btn-md">
                <?php _e('Ask Question', 'tutor'); ?>
            </button>
        </div>
        <?php endif ?>
    </div>
</div>
<div class="tutor-qna-single-question"></div>