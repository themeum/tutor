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

$topics = tutor_utils()->get_topics();

?>


<?php do_action('tutor_course/single/before/topics'); ?>

<?php if($topics->have_posts()) { ?>
    <div class="tutor-single-course-segment  tutor-course-topics-wrap">
        <div class="tutor-course-topics-header">
            <div class="tutor-course-topics-header-left">
                <h4 class="tutor-segment-title"><?php _e('Topics for this course', 'tutor'); ?></h4>
            </div>

            <div class="tutor-course-topics-header-right">
                <span><?php echo tutor_utils()->get_lesson()->post_count; ?> <?php _e('Lessons', 'tutor'); ?></span>
            </div>
        </div>
        <div class="tutor-course-topics-contents">
            <?php
            if ($topics->have_posts()){
                while ($topics->have_posts()){ $topics->the_post();
                    ?>

                    <div class="tutor-course-topic">
                        <div class="tutor-course-title">
                            <h4><?php the_title(); ?></h4>
                        </div>


                        <div class="tutor-course-lessons">

                            <?php
                            $lessons = tutor_utils()->get_lessons_by_topic(get_the_ID());
                            if ($lessons->have_posts()){
                                while ($lessons->have_posts()){ $lessons->the_post();

                                    $video = tutor_utils()->get_video_info();

                                    $play_time = false;
                                    if ($video){
                                        $play_time = $video->playtime;
                                    }
                                    # @TODO: Need An vidoe & Text Icon font
                                    $lesson_icon = $play_time ? 'icon-star-empty' : 'icon-star';

                                    ?>

                                    <div class="tutor-course-lesson">
                                        <h4><?php
                                            echo "<i class='$lesson_icon'></i>";
                                            the_title();
                                            ?></h4>
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
<?php } ?>


<?php do_action('tutor_course/single/after/topics'); ?>