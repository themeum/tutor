<div class="attempt-answers-header quiz-view-attempts-wrapper">
    <h3><?php _e('View Attemptsdd', 'tutor-pro'); ?></h3>
    <a href="<?php echo remove_query_arg('view_quiz_attempt_id') ?>" class="tutor-btn back-to-quiz-btn"> 
        <i class="tutor-icon-angle-left"></i>
        <?php _e('Back to quiz',	'tutor-pro'); ?>
    </a>
</div>

<div>
    <p><?php _e('Quiz', 'tutor'); ?></p>
    <h3><?php echo $quiz_title; ?></h3>
    <div class="tutor-bs-d-flex">
        <div><?php _e('Student', 'tutor'); ?>: <?php echo $student_name; ?></div>
        <div><?php _e('Quiz Time', 'tutor'); ?>: <?php echo $quiz_time; ?></div>
        <div><?php _e('Attempt Time', 'tutor'); ?>: <?php echo $attempt_time; ?></div>
    </div>
</div>