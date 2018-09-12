<div class="lms-course-filter-wrap">

    <div class="lms-course-archive-results-wrap">

		<?php
		$courseCount = lms_utils()->get_course_count();
		$lessonCount = lms_utils()->get_lesson_count();

		_e(sprintf("%s Courses %s Lessons", "<strong>{$courseCount}</strong>", "<strong>{$lessonCount}</strong>"), "lms");
		?>

    </div>

    <div class="lms-course-archive-filters-wrap">
        <form class="lms-course-filter-form" method="get">
            <select name="lms_course_filter">
                <option value="newest_first" <?php if (isset($_GET["lms_course_filter"]) ? selected("newest_first",$_GET["lms_course_filter"]) : "" ); ?> ><?php _e("Release Date (newest first)", "lms");
					?></option>
                <option value="oldest_first" <?php if (isset($_GET["lms_course_filter"]) ? selected("oldest_first",$_GET["lms_course_filter"]) : "" ); ?>><?php _e("Release Date (oldest first)", "lms"); ?></option>
                <option value="course_title_az" <?php if (isset($_GET["lms_course_filter"]) ? selected("course_title_az",$_GET["lms_course_filter"]) : "" ); ?>><?php _e("Course Title (a-z)", "lms"); ?></option>
                <option value="course_title_za" <?php if (isset($_GET["lms_course_filter"]) ? selected("course_title_za",$_GET["lms_course_filter"]) : "" ); ?>><?php _e("Course Title (z-a)", "lms"); ?></option>
            </select>
        </form>
    </div>

    <div style="clear: both;"></div>

</div>