
<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e('Enroll Student', 'tutor'); ?></h1>
    <hr class="wp-header-end">

    <form action="" id="new-enroll_student-form" method="post">
        <input type="hidden" name="tutor_action" value="enrol_student">

        <?php echo apply_filters('student_enrolled_to_course_msg', ''); ?>

        <?php  ?>
        
		<?php do_action('tutor_add_new_enroll_student_form_fields_before'); ?>

        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label for="">
					<?php _e('Student', 'tutor'); ?>

                    <span class="tutor-required-fields">*</span>
                </label>
            </div>
            <div class="tutor-option-field">
                <select name="student_id" id="select2_search_user_ajax"></select>
            </div>
        </div>


        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label>
					<?php _e('Course', 'tutor'); ?>
                    <span class="tutor-required-fields">*</span>
                </label>
            </div>

            <div class="tutor-option-field">
                <?php
                $courses = tutor_utils()->get_courses(array(get_the_ID()));
                ?>
                <select name="course_id" class="tutor_select2">
                    <option value=""><?php _e('Select a Product'); ?></option>
	                <?php
	                foreach ($courses as $course){
		                $selected = in_array($course->ID, $savedPrerequisitesIDS) ? ' selected="selected" ' : '';
		                echo "<option value='{$course->ID}' {$selected} >{$course->post_title}</option>";
	                }
	                ?>
                </select>
            </div>
        </div>



		<?php do_action('tutor_add_new_enroll_student_form_fields_after'); ?>

        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label"></div>

            <div class="tutor-option-field">
                <div class="tutor-form-group tutor-reg-form-btn-wrap">
                    <button type="submit" name="tutor_enroll_student_btn" value="enroll" class="tutor-button tutor-button-primary">
                        <i class="tutor-icon-plus-square-button"></i>
						<?php _e('Enroll Student', 'tutor'); ?></button>
                </div>
            </div>
        </div>

    </form>



</div>