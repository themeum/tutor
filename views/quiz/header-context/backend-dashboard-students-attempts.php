<?php
    if(empty($back_url)) {
        return;
    }
?>

<header class="tutor-wp-dashboard-header tutor-justify-content-between tutor-align-items-center tutor-px-32 tutor-py-20 tutor-mb-24 tutor-pt-16 tutor-pb-16" style="margin-left:-20px; height:auto;">
    <div class="tutor-color-black back tutor-mb-24">
        <a class="tutor-back-btn" href="<?php echo $back_url; ?>">
            <span class="tutor-icon-previous-line tutor-color-design-dark"></span>
            <span class="text text tutro-text-regular-caption tutor-color-black"><?php _e('Back', 'tutor'); ?></span>
        </a>
    </div>
    
    <div class="text-regular-small tutor-color-black-60">
        <?php echo __('Course', 'tutor').': '.$course_title; ?>
    </div>
    <div class="header-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-12">
            <?php echo __('Quiz', 'tutor') . ': ' . $quiz_title; ?>
    </div>
</header>