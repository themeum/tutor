<?php
/**
 * Course Lists
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @since v.1.0.0
 */
?>

<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for="">
			<?php _e('Select course', 'tutor-prerequisites'); ?>
        </label>
    </div>
    <div class="tutor-option-field">
		<?php
		$courses = tutor_utils()->get_courses(array(get_the_ID()));
		$savedPrerequisitesIDS = (array) maybe_unserialize(get_post_meta(get_the_ID(), '_tutor_course_prerequisites_ids', true));
		?>

        <select name="_tutor_course_prerequisites_ids[]" class="tutor_select2" style="min-width: 300px;" multiple="multiple">
            <option value=""><?php _e('Select a Product'); ?></option>
			<?php
			foreach ($courses as $course){
			    $selected = in_array($course->ID, $savedPrerequisitesIDS) ? ' selected="selected" ' : '';
				echo "<option value='{$course->ID}' {$selected} >{$course->post_title}</option>";
			}
			?>
        </select>

        <p class="desc">
			<?php _e('Selected course should be complete before enroll this course.', 'tutor-prerequisites'); ?>
        </p>
    </div>
</div>
