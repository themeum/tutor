<div class="dozent-course-filter-wrap">
    <div class="dozent-course-archive-results-wrap">
		<?php
		$courseCount = dozent_utils()->get_archive_page_course_count();
		_e(sprintf("%s Courses", "<strong>{$courseCount}</strong>"), "dozent");
		?>
    </div>

    <div class="dozent-course-archive-filters-wrap">
        <form class="dozent-course-filter-form" method="get">
            <select name="dozent_course_filter">
                <option value="newest_first" <?php if (isset($_GET["dozent_course_filter"]) ? selected("newest_first",$_GET["dozent_course_filter"]) : "" ); ?> ><?php _e("Release Date (newest first)", "dozent");
					?></option>
                <option value="oldest_first" <?php if (isset($_GET["dozent_course_filter"]) ? selected("oldest_first",$_GET["dozent_course_filter"]) : "" ); ?>><?php _e("Release Date (oldest first)", "dozent"); ?></option>
                <option value="course_title_az" <?php if (isset($_GET["dozent_course_filter"]) ? selected("course_title_az",$_GET["dozent_course_filter"]) : "" ); ?>><?php _e("Course Title (a-z)", "dozent"); ?></option>
                <option value="course_title_za" <?php if (isset($_GET["dozent_course_filter"]) ? selected("course_title_za",$_GET["dozent_course_filter"]) : "" ); ?>><?php _e("Course Title (z-a)", "dozent"); ?></option>
            </select>
        </form>
    </div>

    <div style="clear: both;"></div>
</div>