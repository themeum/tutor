<?php
extract($data); // $course_id, $context
?>
<div class="tutor-qa-new tutor-quesanswer" data-course_id="<?php echo $course_id; ?>" data-question_id="0" data-context="<?php echo $context; ?>">
    <div class="tutor-quesanswer-askquestion">
        <textarea placeholder="<?php _e('Do you have any question?', 'tutor'); ?>" class="tutor-form-control"></textarea>
        <?php if ('course-single-qna-sidebar' == $data['context']) : ?>
            <div class="sidebar-ask-new-qna-submit tutor-row tutor-mt-16">
                <div class="tutor-col">
                    <button class="sidebar-ask-new-qna-cancel-btn tutor-btn tutor-btn-outline-primary tutor-btn-block">
                        <?php _e('Cancel', 'tutor'); ?>
                    </button>
                </div>

                <div class="tutor-col">
                    <button class="sidebar-ask-new-qna-submit-btn tutor-btn tutor-btn-primary tutor-btn-block">
                        <?php _e('Submit', 'tutor'); ?>
                    </button>
                </div>
            </div>

            <div class="sidebar-ask-new-qna-btn-wrap">
                <a class="sidebar-ask-new-qna-btn tutor-btn tutor-btn-primary tutor-btn-block">
                    <?php _e('Ask a New Question', 'tutor'); ?>
                </a>
            </div>
        <?php else : ?>
            <div class="tutor-d-flex tutor-justify-end tutor-mt-24">
                <button class="sidebar-ask-new-qna-submit-btn tutor-btn tutor-btn-primary">
                    <?php _e('Ask Question', 'tutor'); ?>
                </button>
            </div>
        <?php endif ?>
    </div>
</div>
<div class="tutor-qna-single-question"></div>