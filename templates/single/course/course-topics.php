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

$topics = dozent_utils()->get_topics();
$course_id = get_the_ID();

?>


<?php do_action('dozent_course/single/before/topics'); ?>

<?php if($topics->have_posts()) { ?>
    <div class="dozent-single-course-segment  dozent-course-topics-wrap">
        <div class="dozent-course-topics-header">
            <div class="dozent-course-topics-header-left">
                <h4 class="dozent-segment-title"><?php _e('Topics for this course', 'dozent'); ?></h4>
            </div>
            <div class="dozent-course-topics-header-right">
                <?php
                    $dozent_lesson_count = dozent_utils()->get_lesson()->post_count;
                    $dozent_course_duration = get_dozent_course_duration_context($course_id);

                    if($dozent_lesson_count) {
                        echo "<span> $dozent_lesson_count";
                        _e(' Lessons', 'dozent');
                        echo "</span>";
                    }
                    if($dozent_course_duration){
                        echo "<span>$dozent_course_duration</span>";
                    }
                ?>
            </div>
        </div>
        <div class="dozent-course-topics-contents">
            <?php

            $index = 0;

            if ($topics->have_posts()){
                while ($topics->have_posts()){ $topics->the_post();
                    $index++;
                    ?>

                    <div class="dozent-course-topic <?php if($index == 1) echo "dozent-active"; ?>">
                        <div class="dozent-course-title">
                            <h4> <i class="dozent-icon-plus"></i> <?php the_title(); ?></h4>
                        </div>


                        <div class="dozent-course-lessons">

                            <?php
                            $lessons = dozent_utils()->get_lessons_by_topic(get_the_ID());
                            if ($lessons->have_posts()){
                                while ($lessons->have_posts()){ $lessons->the_post();

                                    $video = dozent_utils()->get_video_info();

                                    $play_time = false;
                                    if ($video){
                                        $play_time = $video->playtime;
                                    }

                                    $lesson_icon = $play_time ? 'dozent-icon-youtube' : 'dozent-icon-document';

                                    ?>

                                    <div class="dozent-course-lesson">
                                        <h5>
                                        <?php
                                            echo "<i class='$lesson_icon'></i>";
                                            the_title();
                                        ?>
                                        </h5>
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


<?php do_action('dozent_course/single/after/topics'); ?>