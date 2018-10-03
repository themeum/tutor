<?php
/**
 * Template for displaying lead info
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */


if ( ! defined( 'ABSPATH' ) )
	exit;

?>

<div class="tutor-full-width-course-top tutor-course-top-info">
    <div <?php tutor_post_class(); ?>>

        <h1 class="tutor-course-header-h1"><?php the_title(); ?></h1>

        <div class="tutor-course-summery">
			<?php tutor_the_excerpt(); ?>
        </div>

        <div class="tutor-course-lead-meta">
			<span class="tutor-author">
				<?php _e(sprintf("Created by : %s", get_tutor_course_author()) , 'tutor'); ?>,
			</span>

            <span class="tutor-course-lead-updated">
				<?php _e(sprintf("Last updated : %s", get_the_modified_date()) , 'tutor'); ?>
			</span>
        </div>
    </div><!-- .wrap -->
</div>