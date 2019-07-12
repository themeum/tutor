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


<?php do_action('tutor_course_metabox_before_additional_data'); ?>

<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for=""><?php _e('Total Course Duration', 'tutor'); ?></label>
    </div>
    <div class="tutor-option-field">

        <div class="tutor-option-gorup-fields-wrap">
            <div class="tutor-lesson-video-runtime">

                <div class="tutor-option-group-field">
                    <input type="text" value="<?php echo $durationHours ? $durationHours : '00'; ?>" name="course_duration[hours]">
                    <p class="desc"><?php _e('HH', 'tutor'); ?></p>
                </div>
                <div class="tutor-option-group-field">
                    <input type="text" value="<?php echo $durationMinutes ? $durationMinutes : '00'; ?>" name="course_duration[minutes]">
                    <p class="desc"><?php _e('MM', 'tutor'); ?></p>
                </div>

                <div class="tutor-option-group-field">
                    <input type="text" value="<?php echo $durationSeconds ? $durationSeconds : '00'; ?>" name="course_duration[seconds]">
                    <p class="desc"><?php _e('SS', 'tutor'); ?></p>
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
    <div class="tutor-option-field tutor-course-level-meta">
	    <?php
	    foreach ($levels as $level_key => $level){
	        ?>
            <label> <input type="radio" name="course_level" value="<?php echo $level_key; ?>" <?php $course_level ? checked($level_key,
                    $course_level) : $level_key === 'intermediate' ? checked(1, 1): ''; ?> > <?php
                echo
                $level; ?> </label>
		    <?php
	    }
	    ?>
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
		<textarea name="course_benefits" rows="2"><?php echo $benefits; ?></textarea>

		<p class="desc">
			<?php _e('The students will know what they will learn after completing this course, One line per answer', 'tutor'); ?>
		</p>
	</div>
</div>

<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for="">
			<?php _e('Requirements/Instructions', 'tutor'); ?> <br />
        </label>
    </div>
    <div class="tutor-option-field">
        <textarea name="course_requirements" rows="2"><?php echo $requirements; ?></textarea>

        <p class="desc">
			<?php _e('One per line, additional requirements or special instructions for the students.', 'tutor'); ?>
        </p>
    </div>
</div>

<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for="">
			<?php _e('Targeted Audience', 'tutor'); ?> <br />
        </label>
    </div>
    <div class="tutor-option-field">
        <textarea name="course_target_audience" rows="2"><?php echo $target_audience; ?></textarea>

        <p class="desc">
			<?php _e('Specify the targeted audience who will benefit most from the course, One line per target audience', 'tutor'); ?>
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
        <textarea name="course_material_includes" rows="2"><?php echo $material_includes; ?></textarea>

        <p class="desc">
			<?php _e('A list of assets you will be providing for the students in this course', 'tutor'); ?>
        </p>
    </div>
</div>

<?php do_action('tutor_course_metabox_after_additional_data'); ?>
