<?php
/**
 * Template for displaying enrolled course view nav menu
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php do_action('tutor_course/single/enrolled/nav/before'); ?>
<nav class="tutor-nav" tutor-priority-nav>
	<?php
		foreach ( $course_nav_item as $nav_key => $nav_item ) {
			/**
			 * Apply filters to show default active tab
			 */
			$default_active_key = apply_filters( 'tutor_default_topics_active_tab', 'info' );
			?>
				<li class="tutor-nav-item">
					<a class="tutor-nav-link<?php echo $nav_key == $default_active_key ? ' is-active' : ''; ?>" href="#" data-tutor-nav-target="tutor-course-details-tab-<?php echo $nav_key; ?>"><?php echo $nav_item['title']; ?></a>
				</li>
			<?php
		}
	?>
	<li class="tutor-nav-item tutor-nav-more tutor-d-none">
		<a class="tutor-nav-link tutor-nav-more-item" href="#"><span class="tutor-mr-4">More</span> <span class="tutor-nav-more-icon tutor-icon-times"></span></a>
		<ul class="tutor-nav-more-list tutor-dropdown"></ul>
	</li>
</nav>
<?php do_action('tutor_course/single/enrolled/nav/after'); ?>
