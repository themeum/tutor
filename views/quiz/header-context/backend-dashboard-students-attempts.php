<?php
    if(empty($back_url)) {
        return;
    }
?>

<header class="tutor-wp-dashboard-header tutor-justify-content-between tutor-align-items-center tutor-px-32 tutor-py-20 tutor-mb-24 tutor-pt-15 tutor-pb-15" style="margin-left:-20px; height:auto;">
    <div class="tutor-color-text-primary back tutor-mb-24">
        <a class="tutor-back-btn" href="<?php echo $back_url; ?>">
            <span class="tutor-icon-previous-line tutor-color-design-dark"></span>
            <span class="text text tutro-text-regular-caption tutor-color-text-primary"><?php _e('Back', 'tutor'); ?></span>
        </a>
    </div>
    
    <div class="text-regular-small tutor-color-text-subsued">
        <?php echo __('Course', 'tutor').': '.$course_title; ?>
    </div>
    <div class="header-title tutor-text-medium-h5 tutor-color-text-primary tutor-mt-12">
            <?php echo __('Quiz', 'tutor') . ': ' . $quiz_title; ?>
    </div>
</header>