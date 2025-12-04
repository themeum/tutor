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

use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Models\CourseModel;


$current_url = admin_url( 'admin.php?page=playground&subpage=dashboard' );
// Pagination.
$per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$paged    = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset   = ( $per_page * $paged ) - $per_page;

$post_type_query = Input::get( 'type', '' );

$page_tabs = apply_filters(
	'tutor_enrolled_courses_page_tabs',
	array(
		'enrolled-courses'                   => __( 'Enrolled Courses', 'tutor' ),
		'enrolled-courses/active-courses'    => __( 'Active Courses', 'tutor' ),
		'enrolled-courses/completed-courses' => __( 'Completed Courses', 'tutor' ),
	),
	$post_type_query
);

// Default tab set.
( ! isset( $active_tab, $page_tabs[ $active_tab ] ) ) ? $active_tab = 'enrolled-courses' : 0;

// Get Paginated course list.
$courses_list_array = array(
	'enrolled-courses'                   => CourseModel::get_enrolled_courses_by_user( get_current_user_id(), array( 'private', 'publish' ), $offset, $per_page ),
	'enrolled-courses/active-courses'    => CourseModel::get_active_courses_by_user( null, $offset, $per_page ),
	'enrolled-courses/completed-courses' => CourseModel::get_completed_courses_by_user( null, $offset, $per_page ),
);

// Get Full course list.
$full_courses_list_array = array(
	'enrolled-courses'                   => CourseModel::get_enrolled_courses_by_user( get_current_user_id(), array( 'private', 'publish' ) ),
	'enrolled-courses/active-courses'    => CourseModel::get_active_courses_by_user(),
	'enrolled-courses/completed-courses' => CourseModel::get_completed_courses_by_user(),
);


// Prepare course list based on page tab.
$courses_list           = $courses_list_array[ $active_tab ];
$paginated_courses_list = $full_courses_list_array[ $active_tab ];

$post_type_args = $post_type_query ? array( 'type' => $post_type_query ) : array();

$current_url = admin_url( 'admin.php?page=playground&subpage=dashboard' );

/**
 * Get the label of the active dropdown option.
 *
 * @since 4.0.0
 *
 * @param array $options Array of dropdown options.
 * @return string The label of the active option, or the first option's label if none are active.
 */
function get_active_dropdown_label( $options ) {
	foreach ( $options as $option ) {
		if ( ! empty( $option['active'] ) ) {
			return $option['label'] ?? '';
		}
	}
	return $options[0]['label'] ?? '';
}

$page_nav_items = array(
	array(
		'type'    => 'dropdown',
		'icon'    => Icon::ENROLLED,
		'active'  => true,
		'options' => array(
			array(
				'label'  => __( 'Enrolled', 'tutor' ),
				'icon'   => Icon::ENROLLED,
				'url'    => esc_url( add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses' ) ) ),
				'active' => $active_tab === 'enrolled-courses' ? true : false,
			),
			array(
				'label'  => __( 'Active', 'tutor' ),
				'icon'   => Icon::PLAY_LINE,
				// 'url'    => esc_url( add_query_arg( 'dashboard-page', 'courses', $current_url ) ),
				'url'    => esc_url( add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses/active-courses' ) ) ),
				'active' => $active_tab === 'enrolled-courses/active-courses' ? true : false,
			),
			array(
				'label'  => __( 'Complete', 'tutor' ),
				'icon'   => Icon::COMPLETED_CIRCLE,
				'url'    => esc_url( add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses/completed-courses' ) ) ),
				'active' => $active_tab === 'enrolled-courses/completed-courses' ? true : false,
			),
		),
	),
	array(
		'type'   => 'link',
		'label'  => __( 'Wishlist', 'tutor' ),
		'icon'   => Icon::WISHLIST,
		// 'url'    => esc_url( add_query_arg( 'dashboard-page', 'wishlist', $current_url ) ),
		'url'    => esc_url( add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses' ) ) ),
		'active' => false,
	),
	array(
		'type'   => 'link',
		'label'  => __( 'Quiz Attempts', 'tutor' ),
		'icon'   => Icon::QUIZ_2,
		// 'url'    => esc_url( add_query_arg( 'dashboard-page', 'quiz-attempts', $current_url ) ),
		'url'    => esc_url( add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'enrolled-courses' ) ) ),
		'active' => false,
	),
);

?>

<div class="tutor-dashboard-courses-wrapper">
	<div class="tutor-dashboard-page-nav tutor-p-6">
		<ul class="tutor-dashboard-page-nav-list">
			<?php foreach ( $page_nav_items as $item ) : ?>
				<li class="tutor-dashboard-page-nav-item">
					<?php if ( 'dropdown' === ( $item['type'] ?? 'link' ) ) : ?>
						<?php
						$options      = $item['options'] ?? array();
						$active_label = get_active_dropdown_label( $options );
						?>
						<div x-data="tutorPopover({ placement: 'bottom-start', offset: 4 })">
							<button 
								x-ref="trigger" 
								@click="toggle()"
								class="tutor-dashboard-page-nav-link <?php echo ! empty( $item['active'] ) ? 'active' : ''; ?>"
							>
								<?php if ( ! empty( $item['icon'] ) ) : ?>
									<?php tutor_utils()->render_svg_icon( $item['icon'], 20, 20 ); ?>
								<?php endif; ?>
								<?php echo esc_html( $active_label ); ?>
								<?php
								tutor_utils()->render_svg_icon(
									Icon::CHEVRON_DOWN,
									20,
									20,
									array( 'class' => 'tutor-icon-subdued' )
								);
								?>
							</button>

							<div 
								x-ref="content"
								x-show="open"
								x-cloak
								@click.outside="handleClickOutside()"
								class="tutor-popover tutor-dashboard-page-nav-dropdown"
							>
								<ul>
									<?php foreach ( $options as $option ) : ?>
										<li>
											<a 
												href="<?php echo esc_url( $option['url'] ?? '#' ); ?>" 
												class="tutor-dashboard-page-nav-dropdown-link <?php echo ! empty( $option['active'] ) ? 'active' : ''; ?>"
											>
												<?php if ( ! empty( $option['icon'] ) ) : ?>
													<?php tutor_utils()->render_svg_icon( $option['icon'], 20, 20 ); ?>
												<?php endif; ?>
												<?php echo esc_html( $option['label'] ?? '' ); ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
					<?php else : ?>
						<a 
							href="<?php echo esc_url( $item['url'] ?? '#' ); ?>" 
							class="tutor-dashboard-page-nav-link <?php echo ! empty( $item['active'] ) ? 'active' : ''; ?>"
						>
							<?php if ( ! empty( $item['icon'] ) ) : ?>
								<?php tutor_utils()->render_svg_icon( $item['icon'], 20, 20 ); ?>
							<?php endif; ?>
							<?php echo esc_html( $item['label'] ?? '' ); ?>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="tutor-enrolled-courses tutor-flex tutor-flex-column tutor-gap-2 tutor-p-6">
		<?php
		if ( $courses_list && $courses_list->have_posts() ) :
			while ( $courses_list->have_posts() ) :
				?>
				<?php $courses_list->the_post(); ?>
				<?php tutor_load_template( 'dashboard.enrolled-courses.courses-card' ); ?>
			<?php endwhile; ?>
		<?php endif; ?>
	</div>
</div>

<!-- Old markup base  -->
<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16 tutor-text-capitalize"><?php echo esc_html( $page_tabs[ $active_tab ] ); ?></div>
<div class="tutor-dashboard-content-inner enrolled-courses">
	<div class="tutor-mb-32">
		<ul class="tutor-nav" tutor-priority-nav>
			<?php foreach ( $page_tabs as $slug => $tab ) : ?>
				<li class="tutor-nav-item">
					<a class="tutor-nav-link<?php echo $slug == $active_tab ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( $slug ) ) ); ?>">
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

			<?php do_action( 'tutor_dashboard_enrolled_courses_filter' ); ?>

			<li class="tutor-nav-item tutor-nav-more tutor-d-none">
				<a class="tutor-nav-link tutor-nav-more-item" href="#"><span class="tutor-mr-4"><?php esc_html_e( 'More', 'tutor' ); ?></span> <span class="tutor-nav-more-icon tutor-icon-times"></span></a>
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
