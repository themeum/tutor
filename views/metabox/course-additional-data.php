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

<div class="tutor-mb-30">
    <label class="text-medium-body tutor-font-size-16 color-text-primary">
        <?php _e( 'What Will I Learn?', 'tutor' ); ?>
    </label>
    <textarea class="tutor-form-control tutor-textarea-auto-height tutor-mt-10" name="course_benefits" rows="2" placeholder="<?php esc_attr_e( 'Write here the course benefits (One per line)', 'tutor' ); ?>"><?php echo $benefits; ?></textarea>
</div>

<div class="tutor-mb-30">
    <label class="text-medium-body tutor-font-size-16 color-text-primary">
        <?php _e( 'Targeted Audience', 'tutor' ); ?> <br />
    </label>
    <textarea class="tutor-form-control tutor-textarea-auto-height tutor-mt-10" name="course_target_audience" rows="2" placeholder="<?php esc_attr_e( 'Specify the target audience that will benefit the most from the course. (One line per target audience.)', 'tutor' ); ?>"><?php echo $target_audience; ?></textarea>
</div>

<div class="tutor-bs-row tutor-mb-30">
    <div class="tutor-bs-col-12 tutor-mb-10">
        <label class="text-medium-body tutor-font-size-16 color-text-primary"><?php _e( 'Total Course Duration', 'tutor' ); ?></label>
    </div>
    <div class="tutor-bs-col-sm-4">
        <input class="tutor-form-control" type="number" min="0" value="<?php echo $durationHours ? $durationHours : '00'; ?>" name="course_duration[hours]">
        <span class="tutor-text-thin-caption color-text-hints"><?php _e( 'Hour', 'tutor' ); ?></span>
    </div>
    <div class="tutor-bs-col-sm-4">
        <input class="tutor-form-control" type="number" class="tutor-number-validation" min="0" data-min="0" data-max="59" value="<?php echo $durationMinutes ? $durationMinutes : '00'; ?>" name="course_duration[minutes]">
        <span class="tutor-text-thin-caption color-text-hints"><?php _e( 'Minute', 'tutor' ); ?></span>
    </div>

    <div class="tutor-bs-col-sm-4">
        <input class="tutor-form-control" type="number" class="tutor-number-validation" min="0" data-min="0" data-max="59" value="<?php echo $durationSeconds ? $durationSeconds : '00'; ?>" name="course_duration[seconds]">
        <span class="tutor-text-thin-caption color-text-hints"><?php _e( 'Second', 'tutor' ); ?></span>
    </div>
</div>

<div class="tutor-mb-30">
    <label class="text-medium-body tutor-font-size-16 color-text-primary">
        <?php _e( 'Materials Included', 'tutor' ); ?> <br />
    </label>
    <textarea class="tutor-form-control tutor-textarea-auto-height tutor-mt-10" name="course_material_includes" rows="2" placeholder="<?php esc_attr_e( 'A list of assets you will be providing for the students in this course (One per line)', 'tutor' ); ?>"><?php echo $material_includes; ?></textarea>
</div>

<div class="tutor-mb-30">
    <label class="text-medium-body tutor-font-size-16 color-text-primary">
        <?php _e( 'Requirements/Instructions', 'tutor' ); ?> <br />
    </label>
    <textarea class="tutor-form-control tutor-textarea-auto-height tutor-mt-10" name="course_requirements" rows="2" placeholder="<?php esc_attr_e( 'Additional requirements or special instructions for the students (One per line)', 'tutor' ); ?>"><?php echo $requirements; ?></textarea>
</div>

<div class="tutor-mb-30">
    <label class="tutor-form-label tutor-font-size-16 color-text-primary"><?php _e( 'Course Tag', 'tutor' ); ?></label>
    <div class="tutor-input-group tutor-mb-15">
        <?php echo tutor_course_tags_dropdown($course_id, array('classes' => 'tutor_select2')); ?>
    </div>
</div>

<input type="hidden" name="_tutor_course_additional_data_edit" value="true" />

<?php do_action('tutor_course_metabox_after_additional_data'); ?>
