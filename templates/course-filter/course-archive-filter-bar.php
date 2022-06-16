<?php
    /**
     * @package TutorLMS/Templates
     * @version 1.4.3
     */

    if(!tutor_utils()->get_option('course_archive_filter_sorting', true, true, true)) {
        return;
    }

	$sort_by = '';
	if(isset( $_GET['course_order'] )) {
        $sort_by = tutor_sanitize_data( $_GET['course_order'] );
    }
?>

<div style="text-align: right;" tutor-course-filter>
    <form style="display: inline-block;">
        <select class="tutor-form-select" name="course_order">
            <option value="newest_first" <?php selected("newest_first", $sort_by); ?> >
                <?php esc_html_e("Release Date (newest first)", "tutor"); ?>
            </option>
            <option value="oldest_first" <?php selected("oldest_first", $sort_by); ?>>
                <?php esc_html_e("Release Date (oldest first)", "tutor"); ?>
            </option>
            <option value="course_title_az" <?php selected("course_title_az", $sort_by); ?>>
                <?php esc_html_e("Course Title (a-z)", "tutor"); ?>
            </option>
            <option value="course_title_za" <?php selected("course_title_za", $sort_by); ?>>
                <?php esc_html_e("Course Title (z-a)", "tutor"); ?>
            </option>
        </select>
    </form>
</div>
<br/>