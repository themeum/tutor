<?php if(!empty($back_url)): ?>
    <div class="tutor-color-black back">
        <a class="tutor-back-btn" href="<?php echo $back_url; ?>">
            <span class="tutor-icon-previous-line tutor-color-design-dark"></span>
            <span class="text tutro-text-regular-caption tutor-color-black"><?php _e('Back', 'tutor'); ?></span>
        </a>
    </div>
<?php endif; ?>

<div class="text-regular-small tutor-color-black-60 tutor-mt-24">
    <?php _e('Course', 'tutor'); ?>: <?php echo $course_title; ?>
</div>

<div class="header-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-8 tutor-mb-12">
    <?php echo $quiz_title; ?>
</div>

<div class="tutor-mb-32 tutor-fs-7 tutor-fw-normal tutor-color-black-60">
    <div class="tutor-d-flex">
        <div class="tutor-mr-16 tutor-color-black-70">
            <?php _e('Quiz Time', 'tutor'); ?>: <span class="tutor-fweight-600"><?php echo $quiz_time; ?></span>
        </div>
        <div class="tutor-color-black-70">
            <?php _e('Attempt Time', 'tutor'); ?>: <span class="tutor-fweight-600"><?php echo $attempt_time; ?></span>
        </div>
    </div>
</div>