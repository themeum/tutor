<?php 
    extract($data); // $course_id, $context
?>
<div class="tutor-qa-new tutor-quesanswer" data-course_id="<?php echo $course_id; ?>" data-context="<?php echo $context; ?>">
    <div class="tutor-quesanswer-askquestion tutor-mt-25">
        <textarea placeholder="You Have any question?" class="tutor-form-control"></textarea>
        <div class="tutor-bs-d-flex tutor-bs-justify-content-end tutor-mt-30">
            <button class="tutor-btn tutor-btn-primary tutor-btn-md">
                <?php _e('Ask Question', 'tutor'); ?>
            </button>
        </div>
    </div>
</div>