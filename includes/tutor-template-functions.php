<?php

if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * @param null $template
 *
 * @return bool|string
 *
 * Load template with override file system
 *
 * @since v.1.0.0
 */

if ( ! function_exists('tutor_get_template')) {
	function tutor_get_template( $template = null ) {
		if ( ! $template ) {
			return false;
		}
		$template = str_replace( '.', DIRECTORY_SEPARATOR, $template );

		$template_location = trailingslashit( get_template_directory() ) . "tutor/{$template}.php";
		$file_in_theme = $template_location;
		if ( ! file_exists( $template_location ) ) {
			$template_location = trailingslashit( tutor()->path ) . "templates/{$template}.php";

			if ( ! file_exists($template_location)){
				echo '<div class="tutor-notice-warning"> '.__(sprintf('The file you are trying to load is not exists in your theme or tutor plugins location, if you are a developer and extending tutor plugin, please create a php file at location %s ', "<code>{$file_in_theme}</code>"), 'tutor').' </div>';
			}
		}

		return $template_location;
	}
}

/**
 * @param null $template
 *
 * Load template for TUTOR
 *
 * @since v.1.0.0
 */

if ( ! function_exists('tutor_load_template')) {
	function tutor_load_template( $template = null ) {
		include tutor_get_template( $template );
	}
}

if ( ! function_exists('tutor_course_loop_start')){
	function tutor_course_loop_start($echo = true ){
		ob_start();
		tutor_load_template('loop.loop-start');
		$output = apply_filters('tutor_course_loop_start', ob_get_clean());

		if ( $echo ) {
			echo $output;
		}
		return $output;
	}
}

if ( ! function_exists('tutor_course_loop_end')) {
	function tutor_course_loop_end( $echo = true ) {
		ob_start();
		tutor_load_template( 'loop.loop-end' );

		$output = apply_filters( 'tutor_course_loop_end', ob_get_clean() );
		if ( $echo ) {
			echo $output;
		}

		return $output;
	}
}

function tutor_course_loop_before_content(){
	ob_start();
	tutor_load_template( 'loop.loop-before-content' );

	$output = apply_filters( 'tutor_course_loop_before_content', ob_get_clean() );
	echo $output;
}

function tutor_course_loop_after_content(){
	ob_start();
	tutor_load_template( 'loop.loop-after-content' );

	$output = apply_filters( 'tutor_course_loop_after_content', ob_get_clean() );
	echo $output;
}

if ( ! function_exists('tutor_course_loop_title')) {
	function tutor_course_loop_title() {
		ob_start();
		tutor_load_template( 'loop.title' );
		$output = apply_filters( 'tutor_course_loop_title', ob_get_clean() );

		echo $output;
	}
}


if ( ! function_exists('tutor_course_loop_header')) {
	function tutor_course_loop_header() {
		ob_start();
		tutor_load_template( 'loop.header' );
		$output = apply_filters( 'tutor_course_loop_header', ob_get_clean() );

		echo $output;
	}
}

if ( ! function_exists('tutor_course_loop_footer')) {
	function tutor_course_loop_footer() {
		ob_start();
		tutor_load_template( 'loop.footer' );
		$output = apply_filters( 'tutor_course_loop_footer', ob_get_clean() );

		echo $output;
	}
}

//tutor_course_loop_footer


if ( ! function_exists('tutor_course_loop_start_content_wrap')) {
	function tutor_course_loop_start_content_wrap() {
		ob_start();
		tutor_load_template( 'loop.start_content_wrap' );
		$output = apply_filters( 'tutor_course_loop_start_content_wrap', ob_get_clean() );

		echo $output;
	}
}

if ( ! function_exists('tutor_course_loop_end_content_wrap')) {
	function tutor_course_loop_end_content_wrap() {
		ob_start();
		tutor_load_template( 'loop.end_content_wrap' );
		$output = apply_filters( 'tutor_course_loop_end_content_wrap', ob_get_clean() );

		echo $output;
	}
}

if ( ! function_exists('tutor_course_loop_thumbnail')) {
	function tutor_course_loop_thumbnail() {
		ob_start();
		tutor_load_template( 'loop.thumbnail' );
		$output = apply_filters( 'tutor_course_loop_thumbnail', ob_get_clean() );

		echo $output;
	}
}

if( ! function_exists('tutor_course_loop_wrap_classes')) {
	function tutor_course_loop_wrap_classes( $echo = true ) {
		$courseID   = get_the_ID();
		$classes    = apply_filters( 'tutor_course_loop_wrap_classes', array(
			'tutor-course',
			'tutor-course-loop',
			'tutor-course-loop-' . $courseID,
		) );

		$class = implode( ' ', $classes );
		if ( $echo ) {
			echo $class;
		}

		return $class;
	}
}

if( ! function_exists('tutor_course_loop_col_classes')) {
	function tutor_course_loop_col_classes( $echo = true ) {
		$courseCols = tutor_utils()->get_option( 'courses_col_per_row', 4 );
		$classes    = apply_filters( 'tutor_course_loop_col_classes', array(
			'tutor-course-col-' . $courseCols,
		) );

		$class = implode( ' ', $classes );
		if ( $echo ) {
			echo $class;
		}

		return $class;
	}
}


if ( ! function_exists('tutor_container_classes')) {
	function tutor_container_classes( $echo = true ) {

		$classes = apply_filters( 'tutor_container_classes', array(
			'tutor-wrap tutor-courses-wrap',
			'wrap',
		) );

		$class = implode( ' ', $classes );

		if ( $echo ) {
			echo $class;
		}

		return $class;
	}
}
if ( ! function_exists('tutor_post_class')) {
	function tutor_post_class() {
		$classes = apply_filters( 'tutor_post_class', array(
			'tutor-wrap',
			'wrap',
		) );

		post_class( $classes );
	}
}

if ( ! function_exists('tutor_course_archive_filter_bar')) {
	function tutor_course_archive_filter_bar() {
		ob_start();
		tutor_load_template( 'global.course-archive-filter-bar' );
		$output = apply_filters( 'tutor_course_archive_filter_bar', ob_get_clean() );

		echo $output;
	}
}

/**
 * Get the post thumbnail
 */
if ( ! function_exists('get_tutor_course_thumbnail')) {
	function get_tutor_course_thumbnail() {
		$post_id           = get_the_ID();
		$post_thumbnail_id = (int) get_post_thumbnail_id( $post_id );

		if ( $post_thumbnail_id ) {
			$size = 'post-thumbnail';
			$size = apply_filters( 'post_thumbnail_size', $size, $post_id );
			$html = wp_get_attachment_image( $post_thumbnail_id, $size, false );
		} else {
			$placeHolderUrl = tutor()->url . 'assets/images/placeholder.jpg';
			$html = '<img src="' . $placeHolderUrl . '" />';
		}

		echo $html;
	}
}

if ( ! function_exists('tutor_course_loop_meta')) {
	function tutor_course_loop_meta() {
		ob_start();
		tutor_load_template( 'loop.meta' );
		$output = apply_filters( 'tutor_course_loop_meta', ob_get_clean() );

		echo $output;
	}
}

/**
 * Get course author name in loop
 *
 * @since: v.1.0.0
 */

if ( ! function_exists('tutor_course_loop_author')) {
	function tutor_course_loop_author() {
		ob_start();
		tutor_load_template( 'loop.course-author' );
		$output = apply_filters( 'tutor_course_loop_author', ob_get_clean() );

		echo $output;
	}
}

if ( ! function_exists('tutor_course_loop_price')) {
	function tutor_course_loop_price() {
		ob_start();
		tutor_load_template( 'loop.course-price' );
		$output = apply_filters( 'tutor_course_loop_price', ob_get_clean() );

		echo $output;
	}
}

if ( ! function_exists('tutor_course_loop_rating')) {
	function tutor_course_loop_rating() {
		ob_start();
		tutor_load_template( 'loop.rating' );
		$output = apply_filters( 'tutor_course_loop_rating', ob_get_clean() );

		echo $output;
	}
}

if ( ! function_exists('tutor_course_loop_add_to_cart')) {
	function tutor_course_loop_add_to_cart() {
		ob_start();
		tutor_load_template( 'loop.add-to-cart' );
		$output = apply_filters( 'tutor_course_loop_add_to_cart_link', ob_get_clean() );

		echo $output;
	}
}

/**
 * @param int $post_id
 *
 * echo the excerpt of TUTOR post type
 *
 * @since: v.1.0.0
 */
if ( ! function_exists('tutor_the_excerpt')) {
	function tutor_the_excerpt( $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		echo tutor_get_the_excerpt( $post_id );
	}
}
/**
 * @param int $post_id
 *
 * @return mixed
 *
 * Return excerpt of TUTOR post type
 *
 * @since: v.1.0.0
 */
if ( ! function_exists('tutor_get_the_excerpt')) {
	function tutor_get_the_excerpt( $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		return apply_filters( 'tutor_get_the_excerpt', get_the_excerpt( $post_id ) );
	}
}

/**
 * @return mixed
 *
 * return course author
 *
 * @since: v.1.0.0
 */

if ( ! function_exists('get_tutor_course_author')) {
	function get_tutor_course_author() {
		global $post;
		return apply_filters( 'get_tutor_course_author', get_the_author_meta( 'display_name', $post->post_author ) );
	}
}
/**
 * @param int $course_id
 *
 * @return mixed
 * Course benefits return array
 *
 * @since: v.1.0.0
 */

if ( ! function_exists('tutor_course_benefits')) {
	function tutor_course_benefits( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}
		$benefits = get_post_meta( $course_id, '_tutor_course_benefits', true );

		$benefits_array = array();
		if ($benefits){
			$benefits_array = explode("\n", $benefits);
		}

		$array = array_filter(array_map('trim', $benefits_array));

		return apply_filters( 'tutor_course/single/benefits', $array, $course_id );
	}
}

/**
 * @param bool $echo
 *
 * @return mixed
 *
 * Course single page benefits
 *
 * @since: v.1.0.0
 */

if ( ! function_exists('tutor_course_benefits_html')) {
	function tutor_course_benefits_html($echo = true) {
		ob_start();
		tutor_load_template( 'single.course.course-benefits' );
		$output = apply_filters( 'tutor_course/single/benefits_html', ob_get_clean() );

		if ($echo){
			echo $output;
		}
		return $output;
	}
}

/**
 * @param bool $echo
 *
 * @return mixed|void
 *
 * Return Topics HTML
 *
 * @since: v.1.0.0
 */
if ( ! function_exists('tutor_course_topics')) {
	function tutor_course_topics( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.course.course-topics' );
		$output = apply_filters( 'tutor_course/single/topics', ob_get_clean() );
		wp_reset_postdata();

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}
}

/**
 * @param int $course_id
 *
 * @return mixed|void
 *
 * return course requirements in array
 *
 * @since: v.1.0.0
 */
if ( ! function_exists('tutor_course_requirements')) {
	function tutor_course_requirements( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}
		$requirements = get_post_meta( $course_id, '_tutor_course_requirements', true );

		$requirements_array = array();
		if ($requirements){
			$requirements_array = explode("\n", $requirements);
		}

		$array = array_filter(array_map('trim', $requirements_array));
		return apply_filters( 'tutor_course/single/requirements', $array, $course_id );
	}
}

/**
 * @param bool $echo
 *
 * @return mixed|void
 *
 * Return course requirements in course single page
 *
 * @since: v.1.0.0
 */
if ( ! function_exists('tutor_course_requirements_html')) {
	function tutor_course_requirements_html($echo = true) {
		ob_start();
		tutor_load_template( 'single.course.course-requirements' );
		$output = apply_filters( 'tutor_course/single/requirements_html', ob_get_clean() );

		if ($echo){
			echo $output;
		}
		return $output;
	}
}


/**
 * @param int $course_id
 *
 * @return mixed|void
 *
 * Return target audience in course single page
 *
 * @since: v.1.0.0
 */
if ( ! function_exists('tutor_course_target_audience')) {
	function tutor_course_target_audience( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}
		$target_audience = get_post_meta( $course_id, '_tutor_course_target_audience', true );

		$target_audience_array = array();
		if ($target_audience){
			$target_audience_array = explode("\n", $target_audience);
		}

		$array = array_filter(array_map('trim', $target_audience_array));
		return apply_filters( 'tutor_course/single/target_audience', $array, $course_id );
	}
}

/**
 * @param bool $echo
 *
 * @return mixed|void
 *
 * Return target audience in course single page
 *
 * @since: v.1.0.0
 */
if ( ! function_exists('tutor_course_target_audience_html')) {
	function tutor_course_target_audience_html($echo = true) {
		ob_start();
		tutor_load_template( 'single.course.course-target-audience' );
		$output = apply_filters( 'tutor_course/single/audience_html', ob_get_clean() );

		if ($echo){
			echo $output;
		}
		return $output;
	}
}

if ( ! function_exists('tutor_course_target_reviews_html')) {
	function tutor_course_target_reviews_html($echo = true) {
		ob_start();
		tutor_load_template( 'single.course.reviews' );
		$output = apply_filters( 'tutor_course/single/reviews_html', ob_get_clean() );

		if ($echo){
			echo $output;
		}
		return $output;
	}
}

/**
 * @param bool $echo
 *
 * @return mixed
 *
 * Course single page main content / description
 *
 * @since: v.1.0.0
 */
if ( ! function_exists('tutor_course_content')) {
	function tutor_course_content( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.course.course-content' );
		$output = apply_filters( 'tutor_course/single/content', ob_get_clean() );

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}
}

/**
 * Course single page lead info
 *
 * @since: v.1.0.0
 */
if ( ! function_exists('tutor_course_lead_info')) {
	function tutor_course_lead_info( $echo = true ) {
		ob_start();

		$course_id = get_the_ID();
		$course_post_type = tutor()->course_post_type;
		$queryCourse = new WP_Query(array('p' => $course_id, 'post_type' => $course_post_type));

		if ($queryCourse->have_posts()){
			while ($queryCourse->have_posts()){
				$queryCourse->the_post();
				tutor_load_template( 'single.course.lead-info' );
			}
			wp_reset_postdata();
		}

		$output = apply_filters( 'tutor_course/single/lead_info', ob_get_clean() );

		if ( $echo ) {
			echo $output;
		}
		return $output;
	}
}

/**
 * @param bool $echo
 *
 * @return mixed|void
 */

if ( ! function_exists('tutor_course_enrolled_lead_info')) {
	function tutor_course_enrolled_lead_info( $echo = true ) {
		ob_start();

		$course_id        = get_the_ID();
		$course_post_type = tutor()->course_post_type;
		$queryCourse      = new WP_Query( array( 'p' => $course_id, 'post_type' => $course_post_type ) );

		if ( $queryCourse->have_posts() ) {
			while ( $queryCourse->have_posts() ) {
				$queryCourse->the_post();
				tutor_load_template( 'single.course.enrolled.lead-info' );
			}
			wp_reset_postdata();
		}

		$output = apply_filters( 'tutor_course/single/enrolled/lead_info', ob_get_clean() );

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}
}

if ( ! function_exists('tutor_lesson_lead_info')) {
	function tutor_lesson_lead_info( $lesson_id = 0, $echo = true ) {
		if ( ! $lesson_id ) {
			$lesson_id = get_the_ID();
		}

		ob_start();

		$course_id = tutor_utils()->get_course_id_by_lesson( $lesson_id );


		$course_post_type = tutor()->course_post_type;
		$queryCourse      = new WP_Query( array( 'p' => $course_id, 'post_type' => $course_post_type ) );

		if ( $queryCourse->have_posts() ) {
			while ( $queryCourse->have_posts() ) {
				$queryCourse->the_post();
				tutor_load_template( 'single.course.enrolled.lead-info' );
			}
			wp_reset_postdata();
		}

		$output = apply_filters( 'tutor_course/single/enrolled/lead_info', ob_get_clean() );

		if ( $echo ) {
			echo $output;
		}

		return $output;

	}
}
/**
 * @param bool $echo
 *
 * @return mixed
 *
 * Return enroll box in single course
 *
 * @since: v.1.0.0
 */

if ( ! function_exists('tutor_course_enroll_box')) {
	function tutor_course_enroll_box( $echo = true ) {
		$isLoggedIn = is_user_logged_in();
		$enrolled = tutor_utils()->is_enrolled();

		ob_start();
		if ($isLoggedIn) {
			if ( $enrolled ) {
				tutor_load_template( 'single.course.course-enrolled' );
				$output = apply_filters( 'tutor_course/single/enrolled', ob_get_clean() );
			} else {
				tutor_load_template( 'single.course.course-enroll' );
				$output = apply_filters( 'tutor_course/single/enroll', ob_get_clean() );
			}
		}else{
			tutor_load_template( 'single.course.login' );
			$output = apply_filters( 'tutor_course/global/login', ob_get_clean() );
		}
		if ( $echo ) {
			echo $output;
		}

		return $output;
	}
}

if ( ! function_exists('tutor_course_enrolled_nav')) {
	function tutor_course_enrolled_nav($echo = true) {
		$course_post_type = tutor()->course_post_type;
		$lesson_post_type = tutor()->lesson_post_type;

		ob_start();
		global $post;

        if ( ! empty($post->post_type) && $post->post_type === $course_post_type){
	        tutor_load_template( 'single.course.enrolled.nav' );
        }elseif(! empty($post->post_type) && $post->post_type === $lesson_post_type){
	        $lesson_id = get_the_ID();
	        $course_id = tutor_utils()->get_course_id_by_lesson($lesson_id);

	        $course_post_type = tutor()->course_post_type;
	        $queryCourse = new WP_Query(array('p' => $course_id, 'post_type' => $course_post_type));

	        if ($queryCourse->have_posts()){
		        while ($queryCourse->have_posts()){
			        $queryCourse->the_post();
			        tutor_load_template( 'single.course.enrolled.nav' );
		        }
		        wp_reset_postdata();
	        }
        }
		$output = apply_filters( 'tutor_course/single/enrolled/nav', ob_get_clean() );

		if ( $echo ) {
			echo $output;
		}
		return $output;
	}
}

if ( ! function_exists('tutor_course_video')){
	function tutor_course_video($echo = true){
		ob_start();
		tutor_load_template( 'single.video.video' );
		$output = apply_filters( 'tutor_course/single/video', ob_get_clean() );

		if ( $echo ) {
			echo $output;
		}
		return $output;
	}
}

if ( ! function_exists('tutor_lesson_video')){
    function tutor_lesson_video($echo = true){
	    ob_start();
	    tutor_load_template( 'single.video.video' );
	    $output = apply_filters( 'tutor_lesson/single/video', ob_get_clean() );

	    if ( $echo ) {
		    echo $output;
	    }
	    return $output;
    }
}

/**
 *
 * Get all lessons attachments
 *
 * @param bool $echo
 *
 * @return mixed
 *
 * @since v.1.0.0
 */
if ( ! function_exists('get_tutor_posts_attachments')){
	function get_tutor_posts_attachments($echo = true){
		ob_start();
		tutor_load_template( 'global.attachments' );
		$output = apply_filters( 'tutor_lesson/single/attachments', ob_get_clean() );

		if ( $echo ) {
			echo $output;
		}
		return $output;
	}
}

/**
 * @param bool $echo
 *
 * @return mixed
 *
 * Get the lessons with topics
 *
 * @since v.1.0.0
 */
if ( ! function_exists('tutor_lessons_as_list')) {
	function tutor_lessons_as_list( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.lesson.lesson_lists' );
		$output = apply_filters( 'tutor_lesson/single/lesson_lists', ob_get_clean() );

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}
}

if ( ! function_exists('tutor_lesson_mark_complete_html')) {
	function tutor_lesson_mark_complete_html( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.lesson.complete_form' );
		$output = apply_filters( 'tutor_lesson/single/complete_form', ob_get_clean() );

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}
}

if ( ! function_exists('tutor_course_mark_complete_html')) {
	function tutor_course_mark_complete_html( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.course.complete_form' );
		$output = apply_filters( 'tutor_course/single/complete_form', ob_get_clean() );

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}
}


/**
 * @param bool $echo
 *
 * @return mixed
 *
 * @show progress bar about course complete
 *
 * @since v.1.0.0
 */

if ( ! function_exists('tutor_course_completing_progress_bar')) {
	function tutor_course_completing_progress_bar( $echo = true ) {
		ob_start();
		tutor_load_template( 'single.course.enrolled.completing-progress' );
		$output = apply_filters( 'tutor_course/single/completing-progress-bar', ob_get_clean() );

		if ( $echo ) {
			echo $output;
		}

		return $output;
	}
}

function tutor_course_question_and_answer($echo = true){
	ob_start();
	tutor_load_template( 'single.course.enrolled.question_and_answer' );
	$output = apply_filters( 'tutor_course/single/question_and_answer', ob_get_clean() );

	if ( $echo ) {
		echo $output;
	}

	return $output;
}


function tutor_course_announcements($echo = true){
	ob_start();
	tutor_load_template( 'single.course.enrolled.announcements' );
	$output = apply_filters( 'tutor_course/single/announcements', ob_get_clean() );

	if ( $echo ) {
		echo $output;
	}

	return $output;
}

function tutor_single_quiz_top($echo = true){
	ob_start();
	tutor_load_template( 'single.quiz.top' );
	$output = apply_filters( 'tutor_single_quiz/top', ob_get_clean() );

	if ( $echo ) {
		echo $output;
	}
	return $output;
}

function tutor_single_quiz_body($echo = true){
	ob_start();
	tutor_load_template( 'single.quiz.body' );
	$output = apply_filters( 'tutor_single_quiz/body', ob_get_clean() );

	if ( $echo ) {
		echo $output;
	}
	return $output;
}

function tutor_single_quiz_no_course_belongs($echo = true){
	ob_start();
	tutor_load_template( 'single.quiz.no_course_belongs' );
	$output = apply_filters( 'tutor_single_quiz/no_course_belongs', ob_get_clean() );

	if ( $echo ) {
		echo $output;
	}
	return $output;
}