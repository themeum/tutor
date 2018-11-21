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
?>

<?php do_action('tutor_lesson/single/before/lesson_lists'); ?>

    <div class="tutor-topics-lesson-list">
		<?php

		$course_id = get_post_meta($post->ID, '_tutor_course_id_for_lesson', true);
		$topics = tutor_utils()->get_topics($course_id);

		if ($topics->have_posts()){

			while ($topics->have_posts()){ $topics->the_post();
                $topic_id = get_the_ID();
                $topic_summery = get_the_content();
				?>

                <div class="tutor-topics-in-single-lesson tutor-topics-<?php echo $topic_id; ?>">
                    <div class="tutor-topics-title">
                        <h3>
                            <?php
                                the_title();
                                if($topic_summery) {
                                    echo "<i class='tutor-icon-down'></i>";
                                }
                            ?>
                        </h3>
                    </div>

                    <?php
                    if ($topic_summery){
                        ?>
                        <div class="tutor-topics-summery">
                            <?php echo $topic_summery; ?>
                        </div>
                        <?php
                    }

                    ?>

                    <div class="tutor-lessons-under-topic">
						<?php
						do_action('tutor/lesson_list/before/topic', $topic_id);

						$lessons = tutor_utils()->get_lessons_by_topic(get_the_ID());
						if ($lessons->have_posts()){
							while ($lessons->have_posts()){
								$lessons->the_post();

								$video = tutor_utils()->get_video_info();

								$play_time = false;
								if ($video){
									$play_time = $video->playtime;
								}

								$is_completed_lesson = tutor_utils()->is_completed_lesson();
                                ?>

                                <div class="tutor-single-lesson-items <?php echo ($currentPost->ID === get_the_ID()) ? 'active' : ''; ?>">
                                    <a href="<?php the_permalink(); ?>">

                                        <?php
                                        $tutor_lesson_type_icon = $play_time ? 'youtube' : 'document';
                                        echo "<i class='tutor-icon-$tutor_lesson_type_icon'></i>";
                                        ?>
                                        <span class="lesson_title"><?php the_title(); ?></span>
                                        <span class="tutor-lesson-right-icons">
                                        <?php
                                            if ($play_time){
                                                echo "<i class='tutor-play-duration'>$play_time</i>";
                                            }
                                            $lesson_complete_icon = $is_completed_lesson ? 'tutor-icon-mark tutor-done' : '';
                                            echo "<i class='tutor-lesson-complete $lesson_complete_icon'></i>";
                                            ?>
                                        </span>
                                    </a>
                                </div>

                                <?php
							}
							$lessons->reset_postdata();
						}

						#quizzes
						$quizzes = tutor_utils()->get_attached_quiz($topic_id);
						if ($quizzes){
							?>
								<?php
								foreach ($quizzes as $quiz){
									?>
                                    <div class="tutor-single-lesson-items quiz-single-item-<?php echo $quiz->ID; ?>">
                                        <a href="<?php echo get_permalink($quiz->ID); ?>">
                                            <i class="tutor-icon-doubt"></i>
                                            <span class="lesson_title"><?php echo $quiz->post_title; ?></span>
                                            <span class="tutor-lesson-right-icons">

                                            <?php
                                                $time_limit = tutor_utils()->get_quiz_option($quiz->ID, 'time_limit.time_value');
                                                if ($time_limit){
                                                    $time_type = tutor_utils()->get_quiz_option($quiz->ID, 'time_limit.time_type');
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

                        <?php do_action('tutor/lesson_list/after/topic', $topic_id); ?>
                    </div>
                </div>

				<?php
			}
			$topics->reset_postdata();
			wp_reset_postdata();
		}
		?>
    </div>

<?php do_action('tutor_lesson/single/after/lesson_lists'); ?>