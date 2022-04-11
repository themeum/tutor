<?php
    $passing_grade = tutor_utils()->get_quiz_option(get_the_ID(), 'passing_grade', 0);
?>
<?php if(!empty($back_url)): ?>
    <div class="tutor-mb-24">
        <a class="tutor-btn tutor-btn-ghost" href="<?php echo $back_url; ?>">
            <span class="tutor-icon-previous tutor-mr-8" area-hidden="true"></span>
            <?php _e('Back', 'tutor'); ?>
        </a>
    </div>
<?php endif; ?>

<div class="tutor-fs-7 tutor-color-secondary">
    <?php _e('Quiz', 'tutor'); ?>
</div>

<div class="header-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-12 tutor-mb-32">
    <?php echo $quiz_title; ?>
</div>
<div class="tutor-d-flex tutor-justify-between tutor-py-20 tutor-my-20 tutor-border-top tutor-border-bottom">
    <div class="tutor-d-flex">
        <?php _e('Questions', 'tutor'); ?>: 
        <span class="tutor-color-black">
            <?php echo $question_count; ?>
        </span>
    </div>
    <div class="tutor-d-flex-flex">
        <?php _e('Quiz Time', 'tutor'); ?>: 
        <span class="tutor-color-black">
            <?php echo $quiz_time; ?>
        </span>
    </div>
    <div class="tutor-d-flex">
        <?php _e('Total Marks', 'tutor'); ?>: 
        <span class="tutor-color-black">
            <?php echo $earned_marks . '/' . $total_marks; ?>
        </span>
    </div>
    <div class="tutor-d-flex">
        <?php _e('Passing Marks', 'tutor'); ?>: 
        <span class="tutor-color-black">
            <?php
                $pass_marks = ($total_marks * $passing_grade) / 100;
                echo number_format_i18n($pass_marks, 2);
			?>
        </span>
    </div>
</div>