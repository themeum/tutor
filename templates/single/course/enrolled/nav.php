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
<div class="tab-header tutor-d-flex">
	<?php
		$counter = 0;
		$more_items = array();

		foreach ($course_nav_item as $nav_key => $nav_item){
			if($counter>=4) {
				$more_items[$nav_key] = $nav_item;
				continue;
			}
			$counter++;
			/**
			 * Apply filters to show default active tab
			 */
			$default_active_key = apply_filters( 'tutor_default_topics_active_tab', 'info' );
			?>
				<div class="tab-header-item <?php echo $nav_key == $default_active_key ? 'is-active' : ''; ?>" data-tutor-tab-target="tutor-course-details-tab-<?php echo $nav_key; ?>">
					<span><?php echo $nav_item['title']; ?></span>
				</div>
			<?php
		}

		if(count($more_items)) {
			?>
			<div class="tab-header-item-seemore tutor-ml-auto">
				<div class="tab-header-item-seemore-toggle" data-seemore-target="course-details-tab-seemore-1">
					<?php _e('More', 'tutor'); ?> <span class="icon-seemore tutor-icon-line-cross-line tutor-icon-20 tutor-color-text-brand"></span>
				</div>
				<div id="course-details-tab-seemore-1" class="tab-header-item-seemore-popup">
					<ul class="tutor-m-0 tutor-p-0">
						<?php
							$asset_base = tutor()->url . 'assets/images/';
							foreach($more_items as $key=>$item) {
								?>
								<li class="tab-header-item" data-tutor-tab-target="tutor-course-details-tab-<?php echo $key; ?>">
									<span><?php echo $item['title']; ?></span>
								</li>
								<?php
							}
						?>
					</ul>
				</div>
			</div>
			<?php
		}
	?>
</div>
<?php do_action('tutor_course/single/enrolled/nav/after'); ?>
