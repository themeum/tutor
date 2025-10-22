<?php
/**
 * Lesson files template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="tutor-course-spotlight-files" class="tutor-tab-item<?php echo esc_attr( $is_active ? ' is-active' : '' ); ?>">
	<div class="tutor-container">
		<div class="tutor-row tutor-justify-center">
			<div class="tutor-col-xl-8">
				<div class="tutor-fs-5 tutor-fw-medium tutor-color-black"><?php esc_html_e( 'Exercise Files', 'tutor' ); ?></div>
				<?php get_tutor_posts_attachments(); ?>
			</div>
		</div>
	</div>
</div>
