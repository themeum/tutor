<header class="tutor-wp-dashboard-header justify-content-between align-items-center tutor-px-30 tutor-py-20 tutor-mb-22 tutor-pt-15 tutor-pb-15" style="margin-left:-20px">
    <div class="color-text-primary back">
            <a href="<?php echo $back_url; ?>">
            <span class="ttr-previous-line"></span> <span class="text"><?php _e('Back', 'tutor'); ?></span>
            </a>
    </div>
    <div class="text-regular-small color-text-subsued tutor-mt-30">
        <?php echo __('Course', 'tutor').': '.$course_title; ?>
    </div>
    <div class="header-title text-medium-h5 color-text-primary tutor-mt-10">
            <?php echo __('Quiz', 'tutor') . ': ' . $quiz_title; ?>
    </div>
</header>