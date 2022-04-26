<?php if(!empty($back_url)): ?>
    <a class="tutor-btn tutor-btn-ghost" href="<?php echo $back_url; ?>">
        <span class="tutor-icon-previous tutor-mr-8" area-hidden="true"></span>
        <?php _e('Back', 'tutor'); ?>
    </a>
<?php endif; ?>

<div class="tutor-fs-7 tutor-color-secondary tutor-mt-24">
    <?php _e('Course', 'tutor'); ?>: <?php echo $course_title; ?>
</div>

<div class="header-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-8 tutor-mb-12">
    <?php echo $quiz_title; ?>
</div>

<div class="tutor-mb-32 tutor-fs-7 tutor-color-secondary">
    <div class="tutor-d-flex">
        <div class="tutor-mr-16 tutor-color-secondary">
            <?php _e('Quiz Time', 'tutor'); ?>: <span class="tutor-fw-medium"><?php echo $quiz_time; ?></span>
        </div>
        <div class="tutor-color-secondary">
            <?php _e('Attempt Time', 'tutor'); ?>: <span class="tutor-fw-medium"><?php echo $attempt_time; ?></span>
        </div>
    </div>
</div>