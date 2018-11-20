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
                        <h2><?php the_title(); ?></h2>
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
                                <div class="<?php echo ($currentPost->ID === get_the_ID()) ? 'active' : ''; ?>">
                                    <a href="<?php the_permalink(); ?>">
										<?php if ($play_time){ ?>
                                            <i class="tutor-icon-youtube"></i>
										<?php }else{
										    ?>
                                            <i class="tutor-icon-document"></i>
                                            <?php
                                        } ?>

                                        <span class="lesson_title"><?php the_title(); ?></span>

	                                    <?php if ($is_completed_lesson){ ?>
                                            <i class="tutor-icon-mark"></i>
	                                    <?php } ?>
										<?php if ($play_time){ ?>
                                            <span class="play_duration"><?php echo $play_time; ?></span>
										<?php } ?>

                                    </a>
                                </div>
								<?php
							}
							$lessons->reset_postdata();
						}


						$quizzes = tutor_utils()->get_attached_quiz($topic_id);
						if ($quizzes){
							?>
                            <div class="tutor-quizzes-list">
								<?php
								foreach ($quizzes as $quiz){
									?>
                                    <p class="quiz-single-item quiz-single-item-<?php echo $quiz->ID; ?>">
                                        <span class="quiz-icon">
                                            <i class="tutor-icon-clock"></i>
                                        </span>

                                        <span class="quiz-title">
                                            <a href="<?php echo get_permalink($quiz->ID); ?>"> <?php echo $quiz->post_title; ?></a>

                                        </span>

                                        <?php $time_limit = tutor_utils()->get_quiz_option($quiz->ID, 'time_limit.time_value');
                                        if ($time_limit){
                                            $time_type = tutor_utils()->get_quiz_option($quiz->ID, 'time_limit.time_type');
                                            echo "<span class='quiz-time-limit'>{$time_limit} {$time_type}</span>";
                                        }
                                        ?>
                                    </p>
									<?php
								}
								?>
                            </div>
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