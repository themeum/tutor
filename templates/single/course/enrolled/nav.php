<?php
/**
 * Template for displaying enrolled course view nav menu
 *
 * @package Tutor\Templates
 * @subpackage Single\Course\Enrolled
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'tutor_course/single/enrolled/nav/before' );

?>
<nav class="tutor-nav" tutor-priority-nav>
	<?php
	foreach ( $course_nav_item as $nav_key => $nav_item ) {
		/**
		 * Apply filters to show default active tab
		 */
		$default_active_key = apply_filters( 'tutor_default_topics_active_tab', 'info' );
		?>
				<li class="tutor-nav-item">
					<a class="tutor-nav-link<?php echo $nav_key == $default_active_key ? ' is-active' : ''; ?>" href="#" data-tutor-nav-target="tutor-course-details-tab-<?php echo esc_attr( $nav_key ); ?>"><?php echo esc_html( $nav_item['title'] ); ?></a>
				</li>
			<?php
	}
	?>
	<li class="tutor-nav-item tutor-nav-more tutor-d-none">
		<a class="tutor-nav-link tutor-nav-more-item" href="#"><span class="tutor-mr-4"><?php esc_html_e( 'More', 'tutor' ); ?></span> <span class="tutor-nav-more-icon tutor-icon-times"></span></a>
		<ul class="tutor-nav-more-list tutor-dropdown"></ul>
	</li>
</nav>
<?php do_action( 'tutor_course/single/enrolled/nav/after' ); ?>
