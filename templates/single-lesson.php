<?php
/**
 * Template for displaying single lesson
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

get_header();

global $post;
$currentPost = $post;
?>


<?php do_action('lms_lesson/single/before/wrap'); ?>


<?php do_action('lms_lesson/single/before/lead_info'); ?>
<?php lms_lesson_lead_info(); ?>
<?php do_action('lms_lesson/single/after/lead_info'); ?>


    <div <?php lms_post_class(); ?>>

        <div class="lms-lesson-single-wrap">

            <div class="lms-topics-wrap">
	            <?php

                $course_id = get_post_meta($post->ID, '_lms_course_id_for_lesson', true);
	            $topics = lms_utils()->get_topics($course_id);

	            if ($topics->have_posts()){

		            while ($topics->have_posts()){ $topics->the_post();
			            ?>

                        <div class="lms-topics-in-single-lesson">
                            <div class="lms-topics-title">
                                <h2><?php the_title(); ?></h2>
                            </div>

                            <div class="lms-lessons-under-topic">
		                        <?php
		                        $lessons = lms_utils()->get_lessons_by_topic(get_the_ID());
		                        if ($lessons->have_posts()){
			                        while ($lessons->have_posts()){ $lessons->the_post();
				                        ?>
                                        <p class="<?php echo ($currentPost->ID === get_the_ID()) ? 'active' : ''; ?>">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </p>
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
		            wp_reset_postdata();
	            }
	            ?>


            </div>


            <div class="lms-lesson-content-wrap">

	            <?php lms_lesson_video(); ?>

	            <?php the_content(); ?>
            </div>

        </div>



    </div><!-- .wrap -->

<?php do_action('lms_lesson/single/after/wrap'); ?>


<?php
get_footer();
