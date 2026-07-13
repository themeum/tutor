<?php
/**
 * Course Card Header Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Enrolled_Courses
 * @author Themeum
 *
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

$category = isset( $category ) ? $category : '';
?>

<div class="tutor-progress-card-header">
	<?php if ( ! empty( $category ) ) : ?>
		<div class="tutor-progress-card-category">
			<?php echo esc_html( $category ); ?>
		</div>
	<?php endif; ?>
	<h3 class="tutor-progress-card-title tutor-line-clamp-2">
		<?php the_title(); ?>
	</h3>
</div>
