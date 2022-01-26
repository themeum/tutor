<?php if(!empty($back_url)): ?>
    <div class="tutor-color-text-primary back">
        <a class="tutor-back-btn" href="<?php echo $back_url; ?>">
            <span class="ttr-previous-line tutor-color-design-dark"></span>
            <span class="text tutro-text-regular-caption tutor-color-text-primary"><?php _e('Back', 'tutor'); ?></span>
        </a>
    </div>
<?php endif; ?>

<div class="text-regular-small tutor-color-text-subsued tutor-mt-26">
    <?php _e('Course', 'tutor'); ?>: <?php echo $course_title; ?>
</div>

<div class="header-title tutor-text-medium-h5 tutor-color-text-primary tutor-mt-8 tutor-mb-10">
    <?php echo $quiz_title; ?>
</div>

<div class="tutor-mb-30 tutor-text-regular-small tutor-color-text-subsued">
    <div class="tutor-bs-d-flex">
        <div class="tutor-mr-15 tutor-color-text-title">
            <?php _e('Quiz Time', 'tutor'); ?>: <span class="tutor-fweight-600"><?php echo $quiz_time; ?></span>
        </div>
        <div class="tutor-color-text-title">
            <?php _e('Attempt Time', 'tutor'); ?>: <span class="tutor-fweight-600"><?php echo $attempt_time; ?></span>
        </div>
    </div>
</div>