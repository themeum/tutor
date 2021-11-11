<?php 
    extract($data); // $course_id, $context
?>
<div class="tutor-qa-reply tutor-qna-new-form" data-course_id="<?php echo $course_id; ?>" data-context="<?php echo $context; ?>">
    <textarea class="tutor-form-control"></textarea>
    <div class="tutor-text-right">
        <button class="tutor-btn tutor-is-xs">
            <?php _e('Submit My Question', 'tutor'); ?>
        </button>
    </div>
</div>