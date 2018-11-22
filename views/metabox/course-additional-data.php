<?php
$course_id = get_the_ID();

$duration = maybe_unserialize(get_post_meta($course_id, '_course_duration', true));
$durationHours = dozent_utils()->avalue_dot('hours', $duration);
$durationMinutes = dozent_utils()->avalue_dot('minutes', $duration);
$durationSeconds = dozent_utils()->avalue_dot('seconds', $duration);

$levels = dozent_utils()->course_levels();

$course_level = get_post_meta($course_id, '_dozent_course_level', true);
$benefits = get_post_meta($course_id, '_dozent_course_benefits', true);
$requirements = get_post_meta($course_id, '_dozent_course_requirements', true);
$target_audience = get_post_meta($course_id, '_dozent_course_target_audience', true);
$material_includes = get_post_meta($course_id, '_dozent_course_material_includes', true);
?>

<div class="dozent-option-field-row">
    <div class="dozent-option-field-label">
        <label for=""><?php _e('Total Course Duration', 'dozent'); ?></label>
    </div>
    <div class="dozent-option-field">

        <div class="dozent-option-gorup-fields-wrap">
            <div class="dozent-lesson-video-runtime">

                <div class="dozent-option-group-field">
                    <input type="text" value="<?php echo $durationHours ? $durationHours : '00'; ?>" name="course_duration[hours]">
                    <p><?php _e('HH', 'dozent'); ?></p>
                </div>
                <div class="dozent-option-group-field">
                    <input type="text" value="<?php echo $durationMinutes ? $durationMinutes : '00'; ?>" name="course_duration[minutes]">
                    <p><?php _e('MM', 'dozent'); ?></p>
                </div>

                <div class="dozent-option-group-field">
                    <input type="text" value="<?php echo $durationSeconds ? $durationSeconds : '00'; ?>" name="course_duration[seconds]">
                    <p><?php _e('SS', 'dozent'); ?></p>
                </div>

            </div>
        </div>

    </div>
</div>


<div class="dozent-option-field-row">
    <div class="dozent-option-field-label">
        <label for="">
			<?php _e('Level', 'dozent'); ?> <br />
        </label>
    </div>
    <div class="dozent-option-field">
        <select name="course_level" class="dozent_select2">
            <?php
            foreach ($levels as $level_key => $level){
                echo '<option value="'.$level_key.'" '.selected($level_key, $course_level).' >'.$level.'</option>';
            }
            ?>
        </select>

        <p class="desc">
			<?php _e('Set the course level', 'dozent'); ?>
        </p>
    </div>
</div>


<div class="dozent-option-field-row">
	<div class="dozent-option-field-label">
		<label for="">
            <?php _e('Benefits of this course', 'dozent'); ?> <br />
            <p class="text-muted">(<?php _e('What I will learn', 'dozent'); ?>)</p>
        </label>
	</div>
	<div class="dozent-option-field">
		<textarea name="course_benefits" rows="10"><?php echo $benefits; ?></textarea>

		<p class="desc">
			<?php _e('The students will know what they will learn after completing this course, One line per answer', 'dozent'); ?>
		</p>
	</div>
</div>

<div class="dozent-option-field-row">
    <div class="dozent-option-field-label">
        <label for="">
			<?php _e('Requirements/Instructions', 'dozent'); ?> <br />
        </label>
    </div>
    <div class="dozent-option-field">
        <textarea name="course_requirements" rows="10"><?php echo $requirements; ?></textarea>

        <p class="desc">
			<?php _e('One per line, additional requirements or special instructions for the students.', 'dozent'); ?>
        </p>
    </div>
</div>

<div class="dozent-option-field-row">
    <div class="dozent-option-field-label">
        <label for="">
			<?php _e('Targeted Audience', 'dozent'); ?> <br />
        </label>
    </div>
    <div class="dozent-option-field">
        <textarea name="course_target_audience" rows="10"><?php echo $target_audience; ?></textarea>

        <p class="desc">
			<?php _e('Specify the targeted audience who will benefit most from the course, One line per target audience', 'dozent'); ?>
        </p>
    </div>
</div>


<div class="dozent-option-field-row">
    <div class="dozent-option-field-label">
        <label for="">
			<?php _e('Material Includes', 'dozent'); ?> <br />
        </label>
    </div>
    <div class="dozent-option-field">
        <textarea name="course_material_includes" rows="10"><?php echo $material_includes; ?></textarea>

        <p class="desc">
			<?php _e('A list of assets you will be providing for the students in this course', 'dozent'); ?>
        </p>
    </div>
</div>