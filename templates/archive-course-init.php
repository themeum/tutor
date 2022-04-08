<?php
	// Initialize argument variables
	!isset($course_filter) 		? $course_filter	 = false : 0;
	!isset($supported_filters) 	? $supported_filters = tutor_utils()->get_option( 'supported_course_filters', array() ) : 0;
	!isset($loop_content_only) 	? $loop_content_only = false : 0;
	!isset($column_per_row)		? $column_per_row 	 = tutor_utils()->get_option( 'courses_col_per_row', 3 ) : 0;
	!isset($course_per_page)	? $course_per_page	 = tutor_utils()->get_option( 'courses_per_page', 12 ) : 0;
	!isset($show_pagination)	? $show_pagination	 = true : 0;
	!isset($current_page)		? $current_page	 	 = 1 : 0;

	// Set in global variable to avoid too many stack to pass to other templates
	$GLOBALS['tutor_course_archive_arg'] = compact(
		'course_filter',
		'supported_filters',
		'loop_content_only',
		'column_per_row',
		'course_per_page',
		'show_pagination'
	);

	// Render the loop
	ob_start();
	do_action( 'tutor_course/archive/before_loop' );

	if ( (isset($the_query) && $the_query->have_posts()) || have_posts() ) {
		/* Start the Loop */

		tutor_course_loop_start();

		while ( isset($the_query) ? $the_query->have_posts() : have_posts() ){
			isset($the_query) ? $the_query->the_post() : the_post();

			/**
			 * @hook tutor_course/archive/before_loop_course
			 * @type action
			 * Usage Idea, you may keep a loop within a wrap, such as bootstrap col
			 */
			do_action( 'tutor_course/archive/before_loop_course' );

			tutor_load_template( 'loop.course' );

			/**
			 * @hook tutor_course/archive/after_loop_course
			 * @type action
			 * Usage Idea, If you start any div before course loop, you can end it here, such as </div>
			 */
			do_action( 'tutor_course/archive/after_loop_course' );
		}

		tutor_course_loop_end();
	} else {

		/**
		 * No course found
		 */
		// tutor_load_template('course-none');
		tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() );
	}

	do_action( 'tutor_course/archive/after_loop' );

	if($show_pagination){
		// Load the pagination now
		global $wp_query;
		$pagination_data = array(
			'total_page'  => isset($the_query) ? $the_query->max_num_pages : $wp_query->max_num_pages,
			'per_page'    => $course_per_page,
			'paged'       => $current_page,
			'ajax'		  => array_merge($GLOBALS['tutor_course_archive_arg'], array(
				'loading_container' => '.tutor-course-filter-loop-container',
				'action' => 'tutor_course_filter_ajax',
			))
		);

		tutor_load_template_from_custom_path(
			tutor()->path . 'templates/dashboard/elements/pagination.php',
			$pagination_data
		);
	}
	
	$course_loop = ob_get_clean();

	if(isset($loop_content_only) && $loop_content_only==true){
		echo $course_loop;
		return;
	}
?>

<div class="tutor-mb-32">
	<div class="tutor-wrap tutor-courses-wrap tutor-container course-archive-page " data-tutor_courses_meta="<?php echo esc_attr( json_encode($GLOBALS['tutor_course_archive_arg']) ); ?>">
		<?php if ($course_filter && count($supported_filters)): ?>
			<div class="tutor-course-listing-filter tutor-filter-course-grid-3 course-archive-page">
				<div class="tutor-course-filter tutor-course-filter-container">
					<div class="tutor-course-filter-widget">
						<?php tutor_load_template('course-filter.filters'); ?>
					</div>
				</div>
				<div class="<?php tutor_container_classes(); ?> tutor-course-filter-loop-container tutor-pagination-wrapper-replacable replace-inner-contents" data-column_per_row="<?php esc_attr_e( $column_per_row ); ?>">
					<?php 
						echo $course_loop; 
					?>
				</div>
			</div>
		<?php else: ?>
			<div class="<?php tutor_container_classes(); ?>	tutor-course-filter-loop-container tutor-pagination-wrapper-replacable replace-inner-contents">
				<?php 
					echo $course_loop; 
				?>
			</div>
		<?php endif; ?>
	</div>
</div>