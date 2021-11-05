<div class="attempt-answers-header quiz-view-attempts-wrapper">
    <a href="<?php echo $back_url; ?>"> 
        <i class="tutor-icon-angle-left"></i>
        <?php _e('Back to quiz', 'tutor'); ?>
    </a>
</div>

<div>
    <p><?php _e('Quiz', 'tutor'); ?></p>
    <h3><?php echo $quiz_title; ?></h3>
    <div class="tutor-bs-d-flex">
        <div><?php _e('Questions', 'tutor'); ?>: <?php echo $question_count; ?></div>
        <div><?php _e('Quiz Time', 'tutor'); ?>: <?php echo $quiz_time; ?></div>
        <div><?php _e('Total Marks', 'tutor'); ?>: <?php echo $total_marks; ?></div>
        <div><?php _e('Passing Marks', 'tutor'); ?>: <?php echo $pass_marks; ?></div>
    </div>
</div>