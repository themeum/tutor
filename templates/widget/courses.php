<?php
/**
 * The template for displaying Tutor Course Widget
 *
 * @package Tutor\Templates
 * @subpackage Widget
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.3.1
 */

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
		<div class="<?php echo esc_attr( tutor_widget_course_loop_classes() ); ?>">
			<div class="tutor-card tutor-course-card tutor-mb-12">
				<?php tutor_load_template( 'loop.course' ); ?>
			</div>
		</div>
		<?php
	endwhile;
endif;
