<?php
/**
 * Lesson comment template.
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
<div id="tutor-course-spotlight-comments" class="tutor-tab-item<?php echo esc_attr( $is_active ? ' is-active' : '' ); ?>">
	<div class="tutor-container">
		<div class="tutor-course-spotlight-comments">
			<?php require __DIR__ . '/../comment.php'; ?>
		</div>
	</div>
</div>
