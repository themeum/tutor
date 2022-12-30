<?php
/**
 * Enrolled Courses Page
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

use TUTOR\Input;

// Pagination.
$per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$paged    = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset   = ( $per_page * $paged ) - $per_page;

$page_tabs = array(
	'enrolled-courses'                   => __( 'Enrolled Courses', 'tutor' ),
	'enrolled-courses/active-courses'    => __( 'Active Courses', 'tutor' ),
	'enrolled-courses/completed-courses' => __( 'Completed Courses', 'tutor' ),
);

// Default tab set.
( ! isset( $active_tab, $page_tabs[ $active_tab ] ) ) ? $active_tab = 'enrolled-courses' : 0;

// Get Paginated course list.
$courses_list_array = array(
	'enrolled-courses'                   => tutor_utils()->get_enrolled_courses_by_user( get_current_user_id(), array( 'private', 'publish' ), $offset, $per_page ),
	'enrolled-courses/active-courses'    => tutor_utils()->get_active_courses_by_user( null, $offset, $per_page ),
	'enrolled-courses/completed-courses' => tutor_utils()->get_courses_by_user( null, $offset, $per_page ),
);

// Get Full course list.
$full_courses_list_array = array(
	'enrolled-courses'                   => tutor_utils()->get_enrolled_courses_by_user( get_current_user_id(), array( 'private', 'publish' ) ),
	'enrolled-courses/active-courses'    => tutor_utils()->get_active_courses_by_user(),
	'enrolled-courses/completed-courses' => tutor_utils()->get_courses_by_user(),
);


// Prepare course list based on page tab.
$courses_list           = $courses_list_array[ $active_tab ];
$paginated_courses_list = $full_courses_list_array[ $active_tab ];

?>

<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16 tutor-capitalize-text"><?php echo esc_html( $page_tabs[ $active_tab ] ); ?></div>
<div class="tutor-dashboard-content-inner enrolled-courses">
	<div class="tutor-mb-32">
		<ul class="tutor-nav" tutor-priority-nav>
			<?php foreach ( $page_tabs as $slug => $tab ) : ?>
				<li class="tutor-nav-item">
					<a class="tutor-nav-link<?php echo $slug == $active_tab ? ' is-active' : ''; ?>" href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( $slug ) ); ?>">
						<?php
						echo esc_html( $tab );

						$course_count = ( $full_courses_list_array[ $slug ] && $full_courses_list_array[ $slug ]->have_posts() ) ? count( $full_courses_list_array[ $slug ]->posts ) : 0;
						if ( $course_count ) :
							echo esc_html( '&nbsp;(' . $course_count . ')' );
						endif;
						?>
					</a>
				</li>
			<?php endforeach; ?>

			<li class="tutor-nav-item tutor-nav-more tutor-d-none">
				<a class="tutor-nav-link tutor-nav-more-item" href="#"><span class="tutor-mr-4"><?php esc_html_e( 'More', 'tutor-pro' ); ?></span> <span class="tutor-nav-more-icon tutor-icon-times"></span></a>
				<ul class="tutor-nav-more-list tutor-dropdown"></ul>
			</li>
		</ul>
	</div>

	<?php if ( $courses_list && $courses_list->have_posts() ) : ?>
		<div class="tutor-grid tutor-grid-3">
			<?php
			while ( $courses_list->have_posts() ) :
				$courses_list->the_post();
				?>
			<div class="tutor-card tutor-course-card">
				<?php tutor_load_template( 'loop.thumbnail' ); ?>

				<div class="tutor-card-body">
					<?php tutor_load_template( 'loop.rating' ); ?>
					
					<div class="tutor-course-name tutor-fs-6 tutor-fw-bold tutor-mb-32">
						<a href="<?php echo esc_url( get_the_permalink() ); ?>">
							<?php the_title(); ?>
						</a>
					</div>
					
					<div class="tutor-mt-auto">
						<?php tutor_load_template( 'loop.enrolled-course-progress' ); ?>
					</div>

					<div class="tutor-mt-24">
						<?php tutor_course_loop_price(); ?>
					</div>
				</div>
			</div>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		
		<div class="tutor-mt-20">
			<?php
			if ( $paginated_courses_list->found_posts > $per_page ) :
				$pagination_data = array(
					'total_items' => $paginated_courses_list->found_posts,
					'per_page'    => $per_page,
					'paged'       => $paged,
				);
				tutor_load_template_from_custom_path(
					tutor()->path . 'templates/dashboard/elements/pagination.php',
					$pagination_data
				);
			endif;
			?>
		</div>
	<?php else : ?>
		<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
	<?php endif; ?>
</div>
