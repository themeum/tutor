<?php
    if(empty($back_url)) {
        return;
    }
?>

<header class="tutor-wp-dashboard-header justify-content-between align-items-center tutor-px-30 tutor-py-20 tutor-mb-22 tutor-pt-15 tutor-pb-15" style="margin-left:-20px; height:auto;">
    <div class="tutor-color-text-primary back">
        <a href="<?php echo $back_url; ?>">
            <span class="tutor-icon previous-line"></span> <span class="text"><?php _e('Back', 'tutor'); ?></span>
        </a>
    </div>
    
    <div class="text-regular-small tutor-color-text-subsued tutor-mt-30">
        <?php echo __('Course', 'tutor').': '.$course_title; ?>
    </div>
    <div class="header-title tutor-text-medium-h5 tutor-color-text-primary tutor-mt-10">
            <?php echo __('Quiz', 'tutor') . ': ' . $quiz_title; ?>
    </div>
</header>