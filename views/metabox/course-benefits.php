<?php
$course_id = get_the_ID();
$benefits = get_post_meta($course_id, '_lms_course_benefits', true)
?>

<div class="lms-option-field-row">
	<div class="lms-option-field-label">
		<label for=""><?php _e('Benefits of this course', 'lms'); ?></label>
	</div>
	<div class="lms-option-field">
		<textarea name="course_benefits" rows="10"><?php echo $benefits; ?></textarea>

		<p class="desc">
			<?php _e('The students will know what they will learn after completing this course, One line per answer', 'lms'); ?>
		</p>
	</div>
</div>