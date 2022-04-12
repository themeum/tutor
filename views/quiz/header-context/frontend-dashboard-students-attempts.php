<?php if(!empty($back_url)): ?>
    <div class="tutor-mb-24">
        <a class="tutor-btn tutor-btn-ghost" href="<?php echo $back_url; ?>">
            <span class="tutor-icon-previous tutor-mr-8" area-hidden="true"></span>
            <?php _e('Back', 'tutor'); ?>
        </a>
    </div>
<?php endif; ?>

<div class="tutor-fs-7 tutor-color-secondary">
    <?php _e('Course', 'tutor'); ?>: <?php echo $course_title; ?>
</div>

<div class="header-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-12 tutor-mb-20">
    <?php echo $quiz_title; ?>
</div>

<div class="tutor-mb-32 tutor-fs-7 tutor-color-secondary">
    <div class="tutor-d-flex">
        <div class="tutor-mr-16">
            <?php _e('Student', 'tutor'); ?>: <span class="tutor-color-black"><strong><?php echo $student_name; ?></strong></span>
        </div>
        <div class="tutor-mr-16">
            <?php _e('Quiz Time', 'tutor'); ?>: <span class="tutor-color-black"><strong><?php echo $quiz_time; ?></strong></span>
        </div>
        <div>
            <?php _e('Attempt Time', 'tutor'); ?>: <span class="tutor-color-black"><strong><?php echo $attempt_time; ?></strong></span>
        </div>
    </div>
</div>