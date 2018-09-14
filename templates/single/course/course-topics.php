<?php
/**
 * Template for displaying single course
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

?>

<div class="lms-single-course-segment  lms-course-topics-wrap">

	<div class="lms-course-topics-header">
		<div class="lms-course-topics-header-left">
			<h3><?php _e('Topics for this course', 'lms'); ?></h3>
		</div>

		<div class="lms-course-topics-header-right">
			<span><?php echo lms_utils()->get_lesson()->post_count; ?> <?php _e('Lessons', 'lms'); ?></span>
		</div>
	</div>


	<div class="lms-course-topics-contents">
		<?php
		$topics = lms_utils()->get_topics();

		if ($topics->have_posts()){
			while ($topics->have_posts()){ $topics->the_post();
				?>

				<div class="lms-course-topic">
					<div class="lms-course-title">
						<h2><?php the_title(); ?></h2>
					</div>


					<div class="lms-course-lessons">

						<?php
						$lessons = lms_utils()->get_lessons_by_topic(get_the_ID());
						if ($lessons->have_posts()){
							while ($lessons->have_posts()){ $lessons->the_post();
								?>

								<div class="lms-course-lesson">
									<h4><?php the_title(); ?></h4>
								</div>

								<?php
							}
							$lessons->reset_postdata();
						}
						?>
					</div>
				</div>
				<?php
			}
			$topics->reset_postdata();
		}
		?>
	</div>
</div>