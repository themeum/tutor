<?php

/**
 * LMS hook
 */

add_action('lms_course/archive/before_loop', 'lms_course_archive_filter_bar');

add_action('lms_course/archive/before_loop_course', 'lms_course_loop_before_content');
add_action('lms_course/archive/after_loop_course', 'lms_course_loop_after_content');

add_action('lms_course/loop/thumbnail', 'lms_course_loop_thumbnail');
add_action('lms_course/loop/title', 'lms_course_loop_title');

add_action('lms_course/loop/before_rating', 'lms_course_loop_author');

