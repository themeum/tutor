<div class="dozent-option-field-row">
    <div class="dozent-option-field-label">
        <label for=""><?php _e('Select Course', 'dozent'); ?></label>
    </div>
    <div class="dozent-option-field">
        <?php
        $courses = dozent_utils()->get_courses_for_teachers();
        ?>

        <select name="selected_course" class="dozent_select2">
            <option value=""><?php _e('Select a course'); ?></option>

	        <?php
            $course_id = get_post_meta(get_the_ID(), '_dozent_course_id_for_lesson', true);
	        foreach ($courses as $course){
		        echo "<option value='{$course->ID}' ".selected($course->ID, $course_id)." >{$course->post_title}</option>";
	        }
	        ?>
        </select>

        <p class="desc">
            <?php _e('Choose the course for this lesson', 'dozent'); ?>
        </p>
    </div>
</div>