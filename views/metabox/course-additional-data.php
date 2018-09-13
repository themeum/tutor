<?php
$course_id = get_the_ID();
$benefits = get_post_meta($course_id, '_lms_course_benefits', true);
$requirements = get_post_meta($course_id, '_lms_course_requirements', true);
$target_audience = get_post_meta($course_id, '_lms_course_target_audience', true);
?>

<div class="lms-option-field-row">
	<div class="lms-option-field-label">
		<label for="">
            <?php _e('Benefits of this course', 'lms'); ?> <br />
            <p class="text-muted">(<?php _e('What I will learn', 'lms'); ?>)</p>
        </label>
	</div>
	<div class="lms-option-field">
		<textarea name="course_benefits" rows="10"><?php echo $benefits; ?></textarea>

		<p class="desc">
			<?php _e('The students will know what they will learn after completing this course, One line per answer', 'lms'); ?>
		</p>
	</div>
</div>



<div class="lms-option-field-row">
    <div class="lms-option-field-label">
        <label for="">
			<?php _e('Requirements / instruction', 'lms'); ?> <br />
        </label>
    </div>
    <div class="lms-option-field">
        <textarea name="course_requirements" rows="10"><?php echo $requirements; ?></textarea>

        <p class="desc">
			<?php _e('One per line, additional requirements or special instruction to students.', 'lms'); ?>
        </p>
    </div>
</div>


<div class="lms-option-field-row">
    <div class="lms-option-field-label">
        <label for="">
			<?php _e('Target audience', 'lms'); ?> <br />
        </label>
    </div>
    <div class="lms-option-field">
        <textarea name="course_target_audience" rows="10"><?php echo $target_audience; ?></textarea>

        <p class="desc">
			<?php _e('One per line, target some specific persons who really need to take this course.', 'lms'); ?>
        </p>
    </div>
</div>