<?php
/**
 * Template for displaying single course
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

get_header();
?>

	<div class="lms-full-width-course-top lms-course-top-info">
		<div <?php lms_post_class(); ?>>

			<h1 class="lms-course-header-h1"><?php the_title(); ?></h1>

			<div class="lms-course-summery">
				<?php lms_the_excerpt(); ?>
			</div>

			<div class="lms-course-lead-meta">
			<span class="lms-author">
				<?php _e(sprintf("Created by : %s", get_lms_course_author()) , 'lms'); ?>,
			</span>

				<span class="lms-course-lead-updated">
				<?php _e(sprintf("Last updated : %s", get_the_modified_date()) , 'lms'); ?>
			</span>
			</div>
		</div><!-- .wrap -->
	</div>


	<div <?php lms_post_class(); ?>>

		<?php lms_course_benefits_html(); ?>

		<?php lms_course_topics(); ?>



	</div><!-- .wrap -->

<?php
get_footer();
