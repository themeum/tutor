<?php if(!empty($back_url)): ?>
    <div class="tutor-color-black back tutor-mb-24">
        <a class="tutor-back-btn" href="<?php echo $back_url; ?>">
            <span class="tutor-icon-previous-line tutor-color-design-dark"></span>
            <span class="text text tutro-text-regular-caption tutor-color-black"><?php _e('Back', 'tutor'); ?></span>
        </a>
    </div>
<?php endif; ?>

<div class="text-regular-small tutor-color-black-60">
    <?php _e('Course', 'tutor'); ?>: <?php echo $course_title; ?>
</div>

<div class="header-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-12 tutor-mb-20">
    <?php echo $quiz_title; ?>
</div>

<div class="tutor-mb-32 tutor-fs-7 tutor-fw-normal tutor-color-black-60">
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