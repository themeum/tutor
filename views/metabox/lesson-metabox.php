<div class="lms-option-field-row">
    <div class="lms-option-field-label">
        <label for=""><?php _e('Select Course', 'lms'); ?></label>
    </div>
    <div class="lms-option-field">
        <?php
        $courses = lms_utils()->get_courses();
        ?>

        <select name="selected_course" class="select2">
            <option value=""><?php _e('Select a course'); ?></option>

	        <?php
            $course_id = get_post_meta(get_the_ID(), '_lms_attached_course_id', true);
	        foreach ($courses as $course){
		        echo "<option value='{$course->ID}' ".selected($course->ID, $course_id)." >{$course->post_title}</option>";
	        }
	        ?>
        </select>



        <p class="desc">
            <?php _e('Select the course to access this lesson on that course', 'lms'); ?>
        </p>
    </div>
</div>