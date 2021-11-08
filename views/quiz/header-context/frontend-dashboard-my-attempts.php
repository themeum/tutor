
<div>
    <a class="prev-btn" href="<?php echo $back_url; ?>">
        <span>&leftarrow;</span>
        <?php _e('Back to Attempt List', 'tutor'); ?>
    </a>
</div>
<div>
    <p><?php _e('Course', 'tutor'); ?>: <?php echo $course_title; ?></p>
    <h3><?php echo $quiz_title; ?></h3>
    <div class="tutor-bs-d-flex">
        <div><?php _e('Quiz Time', 'tutor'); ?>: <?php echo $quiz_time; ?></div>
        <div><?php _e('Attempt Time', 'tutor'); ?>: <?php echo $attempt_time; ?></div>
    </div>
</div>