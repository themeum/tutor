<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>


<div class="tutor-course-filter-wrap">
    <div class="tutor-course-archive-results-wrap">
		<?php
		$courseCount = tutor_utils()->get_archive_page_course_count();
		echo sprintf(__("%s Courses", "tutor"), "<strong>{$courseCount}</strong>");
		?>
    </div>

    <div class="tutor-course-archive-filters-wrap">
        <form class="tutor-course-filter-form" method="get">
            <select name="tutor_course_filter">
                <option value="newest_first" <?php if (isset($_GET["tutor_course_filter"]) ? selected("newest_first",$_GET["tutor_course_filter"]) : "" ); ?> ><?php _e("Release Date (newest first)", "tutor");
					?></option>
                <option value="oldest_first" <?php if (isset($_GET["tutor_course_filter"]) ? selected("oldest_first",$_GET["tutor_course_filter"]) : "" ); ?>><?php _e("Release Date (oldest first)", "tutor"); ?></option>
                <option value="course_title_az" <?php if (isset($_GET["tutor_course_filter"]) ? selected("course_title_az",$_GET["tutor_course_filter"]) : "" ); ?>><?php _e("Course Title (a-z)", "tutor"); ?></option>
                <option value="course_title_za" <?php if (isset($_GET["tutor_course_filter"]) ? selected("course_title_za",$_GET["tutor_course_filter"]) : "" ); ?>><?php _e("Course Title (z-a)", "tutor"); ?></option>
            </select>
        </form>
    </div>
</div>