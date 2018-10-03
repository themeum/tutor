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

<div class="tutor-single-course-segment  tutor-course-topics-wrap">

	<div class="tutor-course-topics-header">
		<div class="tutor-course-topics-header-left">
			<h3><?php _e('Topics for this course', 'tutor'); ?></h3>
		</div>

		<div class="tutor-course-topics-header-right">
			<span><?php echo tutor_utils()->get_lesson()->post_count; ?> <?php _e('Lessons', 'tutor'); ?></span>
		</div>
	</div>


	<div class="tutor-course-topics-contents">
		<?php
		$topics = tutor_utils()->get_topics();

		if ($topics->have_posts()){
			while ($topics->have_posts()){ $topics->the_post();
				?>

				<div class="tutor-course-topic">
					<div class="tutor-course-title">
						<h2><?php the_title(); ?></h2>
					</div>


					<div class="tutor-course-lessons">

						<?php
						$lessons = tutor_utils()->get_lessons_by_topic(get_the_ID());
						if ($lessons->have_posts()){
							while ($lessons->have_posts()){ $lessons->the_post();
								?>

								<div class="tutor-course-lesson">
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