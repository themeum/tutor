<?php

if ( ! defined( 'ABSPATH' ) )
	exit;

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
			'lms-wrap lms-courses-wrap',
			'wrap',
		) );

		$class = implode( ' ', $classes );

		if ( $echo ) {
			echo $class;
		}

		return $class;
	}
}
if ( ! function_exists('lms_post_class')) {
	function lms_post_class() {
		$classes = apply_filters( 'lms_post_class', array(
			'lms-wrap',
			'wrap',
		) );

		post_class( $classes );
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

/**
 * Get the post thumbnail
 */
if ( ! function_exists('get_lms_course_thumbnail')) {
	function get_lms_course_thumbnail() {
		$post_id           = get_the_ID();
		$post_thumbnail_id = (int) get_post_thumbnail_id( $post_id );

		if ( $post_thumbnail_id ) {
			$size = 'post-thumbnail';
			$size = apply_filters( 'post_thumbnail_size', $size, $post_id );
			$html = wp_get_attachment_image( $post_thumbnail_id, $size, false );
		} else {
			$placeHolderUrl = lms()->url . 'assets/images/placeholder.jpg';
			$html = '<img src="' . $placeHolderUrl . '" />';
		}

		echo $html;
	}
}

function lms_course_loop_author(){
	ob_start();
	lms_load_template( 'loop.course-author' );
	$output = apply_filters( 'lms_course_archive_filter_bar', ob_get_clean() );

	echo $output;
}

/**
 * @param int $post_id
 *
 * echo the excerpt of LMS post type
 */
if ( ! function_exists('lms_the_excerpt')) {
	function lms_the_excerpt( $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		echo lms_get_the_excerpt( $post_id );
	}
}
/**
 * @param int $post_id
 *
 * @return mixed
 *
 * Return excerpt of LMS post type
 */
if ( ! function_exists('lms_get_the_excerpt')) {
	function lms_get_the_excerpt( $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		return apply_filters( 'lms_get_the_excerpt', get_the_excerpt( $post_id ) );
	}
}

/**
 * @return mixed
 *
 * return course author
 */

if ( ! function_exists('get_lms_course_author')) {
	function get_lms_course_author() {
		global $post;

		return apply_filters( 'get_lms_course_author', get_the_author_meta( 'display_name', $post->post_author ) );
	}
}

if ( ! function_exists('lms_course_benefits')) {
	function lms_course_benefits( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}

		$benefits = get_post_meta( $course_id, '_lms_course_benefits', true );

		return apply_filters( 'lms_course_benefits', $benefits );
	}
}

if ( ! function_exists('lms_course_benefits_html')) {
	function lms_course_benefits_html($echo = true) {
		ob_start();
		lms_load_template( 'single.course-benefits' );
		$output = apply_filters( 'lms_course_benefits_html', ob_get_clean() );

		if ($echo){
			echo $output;
		}
		return $output;
	}
}

if ( ! function_exists('lms_course_topics')) {
	function lms_course_topics( $echo = true ) {
		ob_start();
		lms_load_template( 'single.course-topics' );
		$output = apply_filters( 'lms_course_topics', ob_get_clean() );

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}
}