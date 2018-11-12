<?php
$course_id = get_the_ID();

$duration = maybe_unserialize(get_post_meta($course_id, '_course_duration', true));
$durationHours = tutor_utils()->avalue_dot('hours', $duration);
$durationMinutes = tutor_utils()->avalue_dot('minutes', $duration);
$durationSeconds = tutor_utils()->avalue_dot('seconds', $duration);


$levels = tutor_utils()->course_levels();

$course_level = get_post_meta($course_id, '_tutor_course_level', true);
$benefits = get_post_meta($course_id, '_tutor_course_benefits', true);
$requirements = get_post_meta($course_id, '_tutor_course_requirements', true);
$target_audience = get_post_meta($course_id, '_tutor_course_target_audience', true);
$material_includes = get_post_meta($course_id, '_tutor_course_material_includes', true);
?>


<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for=""><?php _e('Video Run Time', 'tutor'); ?></label>
    </div>
    <div class="tutor-option-field">

        <div class="tutor-option-gorup-fields-wrap">
            <div class="tutor-lesson-video-runtime">

                <div class="tutor-option-group-field">
                    <input type="text" value="<?php echo $durationHours ? $durationHours : '00'; ?>" name="course_duration[hours]">
                    <p><?php _e('HH', 'tutor'); ?></p>
                </div>
                <div class="tutor-option-group-field">
                    <input type="text" value="<?php echo $durationMinutes ? $durationMinutes : '00'; ?>" name="course_duration[minutes]">
                    <p><?php _e('MM', 'tutor'); ?></p>
                </div>

                <div class="tutor-option-group-field">
                    <input type="text" value="<?php echo $durationSeconds ? $durationSeconds : '00'; ?>" name="course_duration[seconds]">
                    <p><?php _e('SS', 'tutor'); ?></p>
                </div>

            </div>
        </div>

    </div>
</div>


<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for="">
			<?php _e('Level', 'tutor'); ?> <br />
        </label>
    </div>
    <div class="tutor-option-field">
        <select name="course_level" class="tutor_select2">
            <?php
            foreach ($levels as $level_key => $level){
                echo '<option value="'.$level_key.'" '.selected($level_key, $course_level).' >'.$level.'</option>';
            }
            ?>
        </select>

        <p class="desc">
			<?php _e('Set the course level', 'tutor'); ?>
        </p>
    </div>
</div>


<div class="tutor-option-field-row">
	<div class="tutor-option-field-label">
		<label for="">
            <?php _e('Benefits of this course', 'tutor'); ?> <br />
            <p class="text-muted">(<?php _e('What I will learn', 'tutor'); ?>)</p>
        </label>
	</div>
	<div class="tutor-option-field">
		<textarea name="course_benefits" rows="10"><?php echo $benefits; ?></textarea>

		<p class="desc">
			<?php _e('The students will know what they will learn after completing this course, One line per answer', 'tutor'); ?>
		</p>
	</div>
</div>

<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for="">
			<?php _e('Requirements / instruction', 'tutor'); ?> <br />
        </label>
    </div>
    <div class="tutor-option-field">
        <textarea name="course_requirements" rows="10"><?php echo $requirements; ?></textarea>

        <p class="desc">
			<?php _e('One per line, additional requirements or special instruction to students.', 'tutor'); ?>
        </p>
    </div>
</div>

<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for="">
			<?php _e('Target audience', 'tutor'); ?> <br />
        </label>
    </div>
    <div class="tutor-option-field">
        <textarea name="course_target_audience" rows="10"><?php echo $target_audience; ?></textarea>

        <p class="desc">
			<?php _e('One per line, target some specific persons who really need to take this course.', 'tutor'); ?>
        </p>
    </div>
</div>


<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for="">
			<?php _e('Material Includes', 'tutor'); ?> <br />
        </label>
    </div>
    <div class="tutor-option-field">
        <textarea name="course_material_includes" rows="10"><?php echo $material_includes; ?></textarea>

        <p class="desc">
			<?php _e('A list of assets which you providing to students.', 'tutor'); ?>
        </p>
    </div>
</div>