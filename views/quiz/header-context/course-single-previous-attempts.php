<?php
    $passing_grade = tutor_utils()->get_quiz_option(get_the_ID(), 'passing_grade', 0);
?>
<?php if(!empty($back_url)): ?>
    <div class="tutor-color-black back tutor-mb-24">
        <a class="tutor-back-btn" href="<?php echo $back_url; ?>">
            <span class="tutor-icon-previous-line tutor-color-design-dark"></span>
            <span class="text text tutro-text-regular-caption tutor-color-black"><?php _e('Back', 'tutor'); ?></span>
        </a>
    </div>
<?php endif; ?>

<div class="text-regular-small tutor-color-black-60">
    <?php _e('Quiz', 'tutor'); ?>
</div>

<div class="header-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-12 tutor-mb-32">
    <?php echo $quiz_title; ?>
</div>
<div class="tutor-d-flex tutor-justify-content-between tutor-py-20 tutor-my-20 tutor-border-top tutor-border-bottom">
    <div class="flex">
        <?php _e('Questions', 'tutor'); ?>: 
        <span class="tutor-color-black">
            <?php echo $question_count; ?>
        </span>
    </div>
    <div class="flex">
        <?php _e('Quiz Time', 'tutor'); ?>: 
        <span class="tutor-color-black">
            <?php echo $quiz_time; ?>
        </span>
    </div>
    <div class="flex">
        <?php _e('Total Marks', 'tutor'); ?>: 
        <span class="tutor-color-black">
            <?php echo $earned_marks . '/' . $total_marks; ?>
        </span>
    </div>
    <div class="flex">
        <?php _e('Passing Marks', 'tutor'); ?>: 
        <span class="tutor-color-black">
            <?php
                $pass_marks = ($total_marks * $passing_grade) / 100;
                echo number_format_i18n($pass_marks, 2);
			?>
        </span>
    </div>
</div>