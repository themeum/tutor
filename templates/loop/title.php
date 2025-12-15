<?php
/**
 * Course loop title
 *
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

?>

<div class="tutor-progress-card-header">
	<?php if ( ! empty( $category ) ) : ?>
		<div class="tutor-progress-card-category">
			<?php echo esc_html( $category ); ?>
		</div>
	<?php endif; ?>
	<h3 class="tutor-progress-card-title">
		<?php the_title(); ?>
	</h3>
</div>
