<?php

/**
 * Display Topics and Lesson lists for learn
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

global $post;
$currentPost = $post;

$course_id = get_post_meta($post->ID, '_dozent_course_id_for_lesson', true);
?>

<?php do_action('dozent_lesson/single/before/lesson_lists'); ?>

    <div class="dozent-topics-lesson-list">


        <div class="dozent-topics-in-single-lesson">
            <div class="dozent-single-lesson-items ">
                <a href="<?php echo get_the_permalink($course_id); ?>">
                    <i class="dozent-icon-mortarboard"></i>
                    <span class="lesson_title"><?php _e('Course Home', 'dozent'); ?></span>
                </a>
            </div>

            <div class="dozent-single-lesson-items ">
                <a href="http://10.0.1.28/lms/dev/course/basic-php-development-course/lesson/course-overview/">
                    <i class="dozent-icon-grid"></i>
                    <span class="lesson_title">Dashboard</span>
                </a>
            </div>
        </div>


		<?php
        $topics = dozent_utils()->get_topics($course_id);

		if ($topics->have_posts()){

			while ($topics->have_posts()){ $topics->the_post();
                $topic_id = get_the_ID();
                $topic_summery = get_the_content();
				?>

                <div class="dozent-topics-in-single-lesson dozent-topics-<?php echo $topic_id; ?>">
                    <div class="dozent-topics-title">
                        <h3>
                            <?php
                                the_title();
                                if($topic_summery) {
                                    echo "<i class='dozent-icon-angle-down'></i>";
                                }
                            ?>
                        </h3>
                    </div>

                    <?php
                    if ($topic_summery){
                        ?>
                        <div class="dozent-topics-summery">
                            <?php echo $topic_summery; ?>
                        </div>
                        <?php
                    }

                    ?>

                    <div class="dozent-lessons-under-topic">
						<?php
						do_action('dozent/lesson_list/before/topic', $topic_id);

						$lessons = dozent_utils()->get_lessons_by_topic(get_the_ID());
						if ($lessons->have_posts()){
							while ($lessons->have_posts()){
								$lessons->the_post();

								$video = dozent_utils()->get_video_info();

								$play_time = false;
								if ($video){
									$play_time = $video->playtime;
								}

								$is_completed_lesson = dozent_utils()->is_completed_lesson();
                                ?>

                                <div class="dozent-single-lesson-items <?php echo ($currentPost->ID === get_the_ID()) ? 'active' : ''; ?>">
                                    <a href="<?php the_permalink(); ?>">

                                        <?php
                                        $dozent_lesson_type_icon = $play_time ? 'youtube' : 'document';
                                        echo "<i class='dozent-icon-$dozent_lesson_type_icon'></i>";
                                        ?>
                                        <span class="lesson_title"><?php the_title(); ?></span>
                                        <span class="dozent-lesson-right-icons">
                                        <?php
                                            if ($play_time){
                                                echo "<i class='dozent-play-duration'>$play_time</i>";
                                            }
                                            $lesson_complete_icon = $is_completed_lesson ? 'dozent-icon-mark dozent-done' : '';
                                            echo "<i class='dozent-lesson-complete $lesson_complete_icon'></i>";
                                            ?>
                                        </span>
                                    </a>
                                </div>

                                <?php
							}
							$lessons->reset_postdata();
						}

						#quizzes
						$quizzes = dozent_utils()->get_attached_quiz($topic_id);
						if ($quizzes){
							?>
								<?php
								foreach ($quizzes as $quiz){
									?>
                                    <div class="dozent-single-lesson-items quiz-single-item-<?php echo $quiz->ID; ?>">
                                        <a href="<?php echo get_permalink($quiz->ID); ?>">
                                            <i class="dozent-icon-doubt"></i>
                                            <span class="lesson_title"><?php echo $quiz->post_title; ?></span>
                                            <span class="dozent-lesson-right-icons">

                                            <?php
                                                $time_limit = dozent_utils()->get_quiz_option($quiz->ID, 'time_limit.time_value');
                                                if ($time_limit){
                                                    $time_type = dozent_utils()->get_quiz_option($quiz->ID, 'time_limit.time_type');
                                                    echo "<span class='quiz-time-limit'>{$time_limit} {$time_type}</span>";
                                                }
                                            ?>
                                            </span>
                                        </a>
                                    </div>
									<?php
								}
								?>
							<?php
						}
						?>

                        <?php do_action('dozent/lesson_list/after/topic', $topic_id); ?>
                    </div>
                </div>

				<?php
			}
			$topics->reset_postdata();
			wp_reset_postdata();
		}
		?>
    </div>

<?php do_action('dozent_lesson/single/after/lesson_lists'); ?>