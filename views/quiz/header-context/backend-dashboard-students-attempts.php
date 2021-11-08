<header class="tutor-wp-dashboard-header d-flex justify-content-between align-items-center tutor-px-30 tutor-py-20 tutor-mb-22 tutor-pt-15 tutor-pb-15" style="margin-left:-20px">
    <div class="header-title text-medium-h5 color-text-primary">
        <p>
            <a href="<?php echo $back_url; ?>">
                <strong>Back</strong>
            </a>
        </p>
        <div>
            <?php echo __('Course', 'tutor').': '.$course_title; ?>
        </div>
		<span class="text-primary-h5">
            <?php echo __('Quiz', 'tutor') . ': ' . $quiz_title; ?>
        </span>
    </div>
</header>