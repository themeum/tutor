<?php
/**
 * Front end course builder meta-box wrapper
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.6
 */

?>
<div class="tutor-course-builder-section">
	<div class="tutor-course-builder-section-title">
		<span class="tutor-fs-5 tutor-fw-bold tutor-color-secondary">
			<i class="tutor-icon-angle-up" area-hidden="true"></i>
			<span><?php echo esc_html( $title ); ?></span>
		</span>
	</div>
	<div class="tutor-course-builder-section-content">
        <?php echo $content; //phpcs:ignore --data already escaped inside template file ?>
	</div>
</div>
