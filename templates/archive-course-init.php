<?php
/**
 * Template for course archive init
 *
 * @package Tutor\Templates
 * @subpackage CourseArchive
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use TUTOR\Input;

! isset( $course_filter ) ? $course_filter         = false : 0;
! isset( $supported_filters ) ? $supported_filters = tutor_utils()->get_option( 'supported_course_filters', array() ) : 0;
! isset( $loop_content_only ) ? $loop_content_only = false : 0;
! isset( $column_per_row ) ? $column_per_row       = tutor_utils()->get_option( 'courses_col_per_row', 3 ) : 0;
! isset( $course_per_page ) ? $course_per_page     = tutor_utils()->get_option( 'courses_per_page', 12 ) : 0;
! isset( $show_pagination ) ? $show_pagination     = true : 0;
! isset( $current_page ) ? $current_page           = 1 : 0;

// Hide pagination is there is no page after first one.
$pages_count = 0;
if ( isset( $the_query ) ) {
	$pages_count = $the_query->max_num_pages;
} else {
	global $wp_query;
	$pages_count = $wp_query->max_num_pages;
}
	$pages_count < 2 ? $show_pagination = false : 0;

	// Set in global variable to avoid too many stack to pass to other templates.
	$GLOBALS['tutor_course_archive_arg'] = compact(
		'course_filter',
		'supported_filters',
		'loop_content_only',
		'column_per_row',
		'course_per_page',
		'show_pagination'
	);

	// Render the loop.
	ob_start();
	do_action( 'tutor_course/archive/before_loop' );

	if ( ( isset( $the_query ) && $the_query->have_posts() ) || have_posts() ) {
		/* Start the Loop */

		tutor_course_loop_start();

		while ( isset( $the_query ) ? $the_query->have_posts() : have_posts() ) {
			isset( $the_query ) ? $the_query->the_post() : the_post();

			/**
			 * Usage Idea, you may keep a loop within a wrap, such as bootstrap col
			 *
			 * @hook tutor_course/archive/before_loop_course
			 * @type action
			 */
			do_action( 'tutor_course/archive/before_loop_course' );

			tutor_load_template( 'loop.course' );

			/**
			 * Usage Idea, If you start any div before course loop, you can end it here, such as </div>
			 *
			 * @hook tutor_course/archive/after_loop_course
			 * @type action
			 */
			do_action( 'tutor_course/archive/after_loop_course' );
		}

		tutor_course_loop_end();
	} else {

		/**
		 * No course found
		 */
		tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() );
	}

	do_action( 'tutor_course/archive/after_loop' );

	if ( $show_pagination ) {

		// Load the pagination now.
		global $wp_query;

		$current_url = wp_doing_ajax() ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ?? '' ) ) : tutor()->current_url;
		//phpcs:disable WordPress.Security.NonceVerification.Missing
		$push_link = add_query_arg( array_merge( $_POST, $GLOBALS['tutor_course_archive_arg'] ), $current_url );

		$data            = wp_doing_ajax() ? Input::sanitize_array( $_POST ) : Input::sanitize_array( $_GET );
		$pagination_data = array(
			'total_page' => isset( $the_query ) ? $the_query->max_num_pages : $wp_query->max_num_pages,
			'per_page'   => $course_per_page,
			'paged'      => $current_page,
			'data_set'   => array( 'push_state_link' => $push_link ),
			'ajax'       => array_merge(
				$data,
				array(
					'loading_container' => '.tutor-course-filter-loop-container',
					'action'            => 'tutor_course_filter_ajax',
				)
			),
		);

		tutor_load_template_from_custom_path(
			tutor()->path . 'templates/dashboard/elements/pagination.php',
			$pagination_data
		);
	}

	$course_loop = ob_get_clean();

	if ( isset( $loop_content_only ) && true == $loop_content_only ) {
		echo $course_loop; //phpcs:ignore --$course_loop contain sanitized data
		return;
	}

	$course_archive_arg = isset( $GLOBALS['tutor_course_archive_arg'] ) ? $GLOBALS['tutor_course_archive_arg']['column_per_row'] : null;
	$columns            = null === $course_archive_arg ? tutor_utils()->get_option( 'courses_col_per_row', 3 ) : $course_archive_arg;
	$has_course_filters = $course_filter && count( $supported_filters );

	$supported_filters_keys = array_keys( $supported_filters );
	?>

<div class="tutor-wrap tutor-wrap-parent tutor-courses-wrap tutor-container course-archive-page" data-tutor_courses_meta="<?php echo esc_attr( json_encode( $GLOBALS['tutor_course_archive_arg'] ) ); ?>">
	<?php if ( $has_course_filters && in_array( 'search', $supported_filters_keys ) ) : ?>
		<div class="tutor-d-block tutor-d-lg-none tutor-mb-32">
			<div class="tutor-d-flex tutor-align-center tutor-justify-between">
				<span class="tutor-fs-3 tutor-fw-medium tutor-color-black"><?php esc_html_e( 'Courses', 'tutor' ); ?></span>
				<a href="#" class="tutor-iconic-btn tutor-iconic-btn-secondary tutor-iconic-btn-md" tutor-toggle-course-filter><span class="tutor-icon-slider-vertical"></span></a>
			</div>
		</div>
	<?php endif; ?>

	<div class="tutor-row tutor-gx-xl-5">
		<?php if ( $has_course_filters ) : ?>
			<div class="tutor-col-3 tutor-course-filter-container">
				<div class="tutor-course-filter" tutor-course-filter>
					<?php tutor_load_template( 'course-filter.filters' ); ?>
				</div>
			</div>

			<!-- <?php if ( $columns < 3 ) : ?>
				<div class="tutor-col-1 tutor-d-none tutor-d-xl-block" area-hidden="true"></div>
			<?php endif; ?> -->

			<div class="tutor-col-xl-<?php echo $columns < 3 ? 8 : 9; ?> ">
				<div>
					<?php tutor_load_template( 'course-filter.course-archive-filter-bar' ); ?>
				</div>
				<div class="tutor-pagination-wrapper-replaceable" tutor-course-list-container>
					<?php echo $course_loop; //phpcs:ignore --$course_loop contain sanitized data ?> 
				</div>
			</div>
		<?php else : ?>
			<div class="tutor-col-12">
				<div class="">
					<?php tutor_load_template( 'course-filter.course-archive-filter-bar' ); ?>
				</div>
				<div class="tutor-pagination-wrapper-replaceable" tutor-course-list-container>
					<?php echo $course_loop; //phpcs:ignore --$course_loop contain sanitized data ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php
if ( ! is_user_logged_in() ) {
	tutor_load_template_from_custom_path( tutor()->path . '/views/modal/login.php' );
}
?>
