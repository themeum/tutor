<?php
$course_id = get_the_ID();

// Extract: $duration, $durationHours, $durationMinutes, $durationSeconds
extract(tutor_utils()->get_course_duration($course_id, true));

$benefits = get_post_meta($course_id, '_tutor_course_benefits', true);
$requirements = get_post_meta($course_id, '_tutor_course_requirements', true);
$target_audience = get_post_meta($course_id, '_tutor_course_target_audience', true);
$material_includes = get_post_meta($course_id, '_tutor_course_material_includes', true);
?>


<?php do_action('tutor_course_metabox_before_additional_data'); ?>

<div class="tutor-bs-row tutor-mb-30">
    <div class="tutor-bs-col-12">
        <label class="text-medium-body"><?php _e('Total Course Durationd', 'tutor'); ?></label>
    </div>
    <div class="tutor-bs-col-sm-4">
        <input class="tutor-form-control" type="number" value="<?php echo $durationHours ? $durationHours : '00'; ?>" name="course_duration[hours]">
        <span class="text-thin-caption"><?php _e('HH', 'tutor'); ?></span>
    </div>
    <div class="tutor-bs-col-sm-4">
        <input class="tutor-form-control" type="number" class="tutor-number-validation" data-min="0" data-max="59" value="<?php echo $durationMinutes ? $durationMinutes : '00'; ?>" name="course_duration[minutes]">
        <span class="text-thin-caption"><?php _e('MM', 'tutor'); ?></span>
    </div>

    <div class="tutor-bs-col-sm-4">
        <input class="tutor-form-control" type="number" class="tutor-number-validation" data-min="0" data-max="59" value="<?php echo $durationSeconds ? $durationSeconds : '00'; ?>" name="course_duration[seconds]">
        <span class="text-thin-caption"><?php _e('SS', 'tutor'); ?></span>
    </div>
</div>

<div class="tutor-mb-30">
    <label class="text-medium-body">
        <?php _e('Benefits of the course', 'tutor'); ?>
    </label>
    <textarea class="tutor-form-control" name="course_benefits" rows="2"><?php echo $benefits; ?></textarea>
    <span class="text-thin-caption">
        <?php _e('List the knowledge and skills that students will learn after completing this course. (One per line)', 'tutor'); ?>
    </span>
</div>

<div class="tutor-mb-30">
    <label class="text-medium-body">
        <?php _e('Requirements/Instructions', 'tutor'); ?> <br />
    </label>
    <textarea class="tutor-form-control" name="course_requirements" rows="2"><?php echo $requirements; ?></textarea>
    <span class="text-thin-caption">
        <?php _e('Additional requirements or special instructions for the students (One per line)', 'tutor'); ?>
    </span>
</div>

<div class="tutor-mb-30">
    <label class="text-medium-body">
        <?php _e('Targeted Audience', 'tutor'); ?> <br />
    </label>
    <textarea class="tutor-form-control" name="course_target_audience" rows="2"><?php echo $target_audience; ?></textarea>
    <span class="text-thin-caption">
        <?php _e('Specify the target audience that will benefit the most from the course. (One line per target audience.)', 'tutor'); ?>
    </span>
</div>

<div class="tutor-mb-30">
    <label class="text-medium-body">
        <?php _e('Materials Included', 'tutor'); ?> <br />
    </label>
    <textarea class="tutor-form-control" name="course_material_includes" rows="2"><?php echo $material_includes; ?></textarea>
    <span class="text-thin-caption">
        <?php _e('A list of assets you will be providing for the students in this course (One per line)', 'tutor'); ?>
    </span>
</div>

<div class="tutor-mb-30">
    <label class="tutor-form-label"><?php _e('Choose a tag', 'tutor'); ?></label>
    <div class="tutor-input-group tutor-mb-15">
        <?php echo tutor_course_tags_dropdown($course_id, array('classes' => 'tutor_select2')); ?>
    </div>
</div>

<input type="hidden" name="_tutor_course_additional_data_edit" value="true" />

<?php do_action('tutor_course_metabox_after_additional_data'); ?>
