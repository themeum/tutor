<?php

/**
 * DOZENT hook
 */

add_action('dozent_course/archive/before_loop', 'dozent_course_archive_filter_bar');

add_action('dozent_course/archive/before_loop_course', 'dozent_course_loop_before_content');
add_action('dozent_course/archive/after_loop_course', 'dozent_course_loop_after_content');

add_action('dozent_course/loop/header', 'dozent_course_loop_header');

add_action('dozent_course/loop/start_content_wrap', 'dozent_course_loop_start_content_wrap');
add_action('dozent_course/loop/title', 'dozent_course_loop_title');
add_action('dozent_course/loop/meta', 'dozent_course_loop_meta');

add_action('dozent_course/loop/rating', 'dozent_course_loop_rating');
add_action('dozent_course/loop/end_content_wrap', 'dozent_course_loop_end_content_wrap');

add_action('dozent_course/loop/footer', 'dozent_course_loop_footer');

add_action( 'dozent_course/single/before/inner-wrap', 'wc_print_notices', 10 );
add_action( 'dozent_course/single/enrolled/before/inner-wrap', 'wc_print_notices', 10 );

