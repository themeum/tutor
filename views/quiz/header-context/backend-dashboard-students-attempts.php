<?php
    if(empty($back_url)) {
        return;
    }
?>

<header class="tutor-wp-dashboard-header tutor-justify-between tutor-align-center tutor-px-32 tutor-py-20 tutor-mb-24 tutor-pt-16 tutor-pb-16">
    <div class="tutor-mb-24">
        <a class="tutor-btn tutor-btn-ghost" href="<?php echo $back_url; ?>">
            <span class="tutor-icon-previous" area-hidden="true"></span>
            <?php _e('Back', 'tutor'); ?>
        </a>
    </div>
    
    <div class="tutor-fs-7 tutor-color-secondary">
        <?php echo __('Course', 'tutor').': '.$course_title; ?>
    </div>
    <div class="header-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-12">
        <?php echo __('Quiz', 'tutor') . ': ' . $quiz_title; ?>
    </div>
</header>