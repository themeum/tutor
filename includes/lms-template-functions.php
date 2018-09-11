<?php

/**
 * @param null $template
 *
 * @return bool|string
 *
 * Load template with override file system
 */

if ( ! function_exists('lms_get_template')) {
	function lms_get_template( $template = null ) {
		if ( ! $template ) {
			return false;
		}
		$template = str_replace( '.', DIRECTORY_SEPARATOR, $template );

		$template_location = trailingslashit( get_template_directory() ) . "lms/{$template}.php";
		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( lms()->path ) . "templates/{$template}.php";

		}

		return $template_location;
	}
}

if ( ! function_exists('lms_load_template')) {
	function lms_load_template( $template = null ) {
		include lms_get_template( $template );
	}
}

if ( ! function_exists('lms_course_loop_start')){
	function lms_course_loop_start($echo = true ){
		ob_start();
		lms_load_template('loop.loop-start');
		$output = apply_filters('lms_course_loop_start', ob_get_clean());

		if ( $echo ) {
			echo $output;
		}
		return $output;
	}
}

if ( ! function_exists('lms_course_loop_end')) {
	function lms_course_loop_end( $echo = true ) {
		ob_start();
		lms_load_template( 'loop.loop-end' );

		$output = apply_filters( 'lms_course_loop_end', ob_get_clean() );
		if ( $echo ) {
			echo $output;
		}

		return $output;
	}
}

function lms_course_loop_before_content(){
	ob_start();
	lms_load_template( 'loop.loop-before-content' );

	$output = apply_filters( 'lms_course_loop_before_content', ob_get_clean() );
	echo $output;
}

function lms_course_loop_after_content(){
	ob_start();
	lms_load_template( 'loop.loop-after-content' );

	$output = apply_filters( 'lms_course_loop_after_content', ob_get_clean() );
	echo $output;
}

if ( ! function_exists('lms_course_loop_title')) {
	function lms_course_loop_title() {
		ob_start();
		lms_load_template( 'loop.title' );
		$output = apply_filters( 'lms_course_loop_title', ob_get_clean() );

		echo $output;
	}
}

if ( ! function_exists('lms_course_loop_thumbnail')) {
	function lms_course_loop_thumbnail() {
		ob_start();
		lms_load_template( 'loop.thumbnail' );
		$output = apply_filters( 'lms_course_loop_title', ob_get_clean() );

		echo $output;
	}
}
if( ! function_exists('lms_course_loop_wrap_classes')) {
	function lms_course_loop_wrap_classes( $echo = true ) {
		$courseID   = get_the_ID();
		$courseCols = lms_utils()->get_option( 'courses_col_per_row', 4 );
		$classes    = apply_filters( 'lms_course_loop_wrap_classes', array(
			'lms-course',
			'lms-course-loop',
			'lms-course-loop-' . $courseID,
			'lms-course-col-' . $courseCols,
		) );

		$class = implode( ' ', $classes );
		if ( $echo ) {
			echo $class;
		}

		return $class;
	}
}

if ( ! function_exists('lms_container_classes')) {
	function lms_container_classes( $echo = true ) {
		$classes = apply_filters( 'lms_container_classes', array(
			'lms-courses-wrap',
			'wrap',
		) );

		$class = implode( ' ', $classes );

		if ( $echo ) {
			echo $class;
		}

		return $class;
	}
}

if ( ! function_exists('lms_course_archive_filter_bar')) {
	function lms_course_archive_filter_bar() {
		ob_start();
		lms_load_template( 'global.course-archive-filter-bar' );
		$output = apply_filters( 'lms_course_archive_filter_bar', ob_get_clean() );

		echo $output;
	}
}

