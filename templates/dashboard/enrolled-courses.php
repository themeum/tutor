<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */


/**
 * Pagination info
 */
$per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$paged    = (isset($_GET['current_page']) && is_numeric($_GET['current_page']) && $_GET['current_page'] >= 1) ? $_GET['current_page'] : 1;
$offset     = ($per_page * $paged) - $per_page;

$page_tabs = array(
	'enrolled-courses'                   => __('Enrolled Courses', 'tutor'),
	'enrolled-courses/active-courses'    => __('Active Courses', 'tutor'),
	'enrolled-courses/completed-courses' => __('Completed Courses', 'tutor'),
);

// Default tab set
(!isset($active_tab, $page_tabs[$active_tab])) ? $active_tab = 'enrolled-courses' : 0;

// Get Paginated course list
$courses_list_array = array(
	'enrolled-courses'                   => tutor_utils()->get_enrolled_courses_by_user(get_current_user_id(), array('private', 'publish'), $offset, $per_page),
	'enrolled-courses/active-courses'    => tutor_utils()->get_active_courses_by_user(null, $offset, $per_page),
	'enrolled-courses/completed-courses' => tutor_utils()->get_courses_by_user(null, $offset, $per_page),
);

// Get Full course list
$full_courses_list_array = array(
	'enrolled-courses'                   => tutor_utils()->get_enrolled_courses_by_user(get_current_user_id(), array('private', 'publish')),
	'enrolled-courses/active-courses'    => tutor_utils()->get_active_courses_by_user(),
	'enrolled-courses/completed-courses' => tutor_utils()->get_courses_by_user(),
);


// Prepare course list based on page tab
$courses_list =  $courses_list_array[$active_tab];
$paginated_courses_list =  $full_courses_list_array[$active_tab];

?>

<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16 tutor-capitalize-text"><?php esc_html_e($page_tabs[$active_tab]); ?></div>
<div class="tutor-dashboard-content-inner enrolled-courses">
	<div class="tutor-dashboard-inline-links">
		<ul>
			<?php
			foreach ($page_tabs as $slug => $tab) {
			?>
				<li class="<?php echo $slug == $active_tab ? 'active' : ''; ?>">
					<a href="<?php echo esc_url(tutor_utils()->get_tutor_dashboard_page_permalink($slug)); ?>">
						<?php
						echo esc_html($tab);

						$course_count = ($full_courses_list_array[$slug] && $full_courses_list_array[$slug]->have_posts()) ? count($full_courses_list_array[$slug]->posts) : 0;
						if ($course_count) {
							echo esc_html('&nbsp;(' . $course_count . ')');
						}
						?>
					</a>
				</li>
			<?php
			}
			?>
		</ul>
	</div>

	<?php if ($courses_list && $courses_list->have_posts()) : ?>
		<div class="tutor-course-listing-grid tutor-course-listing-grid-3">
			<?php
			while ($courses_list->have_posts()) {

				$courses_list->the_post();
				$avg_rating       = tutor_utils()->get_course_rating()->rating_avg;
				$tutor_course_img = get_tutor_course_thumbnail_src();

				/**
				 * wp 5.7.1 showing plain permalink for private post
				 * since tutor do not work with plain permalink
				 * url is set to post_type/slug (courses/course-slug)
				 *
				 * @since 1.8.10
				 */

				$post       = $courses_list->post;
				$custom_url = home_url($post->post_type . '/' . $post->post_name);

				/**
				 * @hook tutor_course/archive/before_loop_course
				 * @type action
				 * Usage Idea, you may keep a loop within a wrap, such as bootstrap col
				 */
				do_action('tutor_course/archive/before_loop_course');

				tutor_load_template('loop.course');

				/**
				 * @hook tutor_course/archive/after_loop_course
				 * @type action
				 * Usage Idea, If you start any div before course loop, you can end it here, such as </div>
				 */
				do_action('tutor_course/archive/after_loop_course');
			}

			wp_reset_postdata();
			?>
		</div>
		<div class="tutor-mt-20">
			<?php
			if ($paginated_courses_list->found_posts > $per_page) {
				$pagination_data = array(
					'total_items' => $paginated_courses_list->found_posts,
					'per_page'    => $per_page,
					'paged'       => $paged,
				);
				tutor_load_template_from_custom_path(
					tutor()->path . 'templates/dashboard/elements/pagination.php',
					$pagination_data
				);
			}
			?>

		</div>
	<?php else : ?>
		<?php tutor_utils()->tutor_empty_state(tutor_utils()->not_found_text()); ?>
	<?php endif; ?>
</div>