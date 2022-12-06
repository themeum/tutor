<?php
/**
 * A single course loop
 *
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

do_action( 'tutor_course/loop/before_content' );

do_action( 'tutor_course/loop/badge' );


do_action( 'tutor_course/loop/before_header' );
do_action( 'tutor_course/loop/header' );
do_action( 'tutor_course/loop/after_header' );


do_action( 'tutor_course/loop/start_content_wrap' );

do_action( 'tutor_course/loop/before_rating' );
do_action( 'tutor_course/loop/rating' );
do_action( 'tutor_course/loop/after_rating' );

do_action( 'tutor_course/loop/before_title' );
do_action( 'tutor_course/loop/title' );
do_action( 'tutor_course/loop/after_title' );

do_action( 'tutor_course/loop/before_meta' );
do_action( 'tutor_course/loop/meta' );
do_action( 'tutor_course/loop/after_meta' );

do_action( 'tutor_course/loop/before_excerpt' );
do_action( 'tutor_course/loop/excerpt' );
do_action( 'tutor_course/loop/after_excerpt' );

do_action( 'tutor_course/loop/end_content_wrap' );
do_action( 'tutor_course/loop/tutor_pagination' );

/**
 * Hooks for enrolled course progress
 * That will affected on dashboard enrolled course page
 *
 * @since 2.0.0
 */
do_action( 'tutor_course/loop/before_enrolled_progress' );
do_action( 'tutor_course/loop/enrolled_course_progress' );
do_action( 'tutor_course/loop/after_enrolled_progress' );

do_action( 'tutor_course/loop/before_footer' );
do_action( 'tutor_course/loop/footer' );
do_action( 'tutor_course/loop/after_footer' );

do_action( 'tutor_course/loop/after_content' );


