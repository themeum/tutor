<?php
/**
 * Display Topics and Lesson lists for learn
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

global $post;

$currentPost = $post;

$course_id = 0;
if ($post->post_type === 'tutor_quiz'){
	$course = tutor_utils()->get_course_by_quiz(get_the_ID());
	$course_id = $course->ID;
} elseif ($post->post_type === 'tutor_assignments'){
	$course_id = tutor_utils()->get_course_id_by('assignment', $post->ID);
} elseif ($post->post_type === 'tutor_zoom_meeting'){
	$course_id = get_post_meta($post->ID, '_tutor_zm_for_course', true);
} else {
	$course_id = tutor_utils()->get_course_id_by('lesson', $post->ID);
}
$disable_qa_for_this_course = get_post_meta($course_id, '_tutor_enable_qa', true)!='yes';
$enable_q_and_a_on_course = tutor_utils()->get_option('enable_q_and_a_on_course') && $disable_qa_for_this_course != 'yes';


?>

<?php do_action('tutor_lesson/single/before/lesson_sidebar'); ?>

    <div class="tutor-sidebar-tabs-wrap">
        <div class="tutor-lessons-tab">
			<div class="tutor-lessons-lessons-tab <?php echo $enable_q_and_a_on_course ? "active" : ""; ?> flex-center">
               <span class="ttr-education-filled"></span>
               <span class="text-medium-caption color-text-title"><?php esc_html_e('Lesson List', 'tutor'); ?></span>
            </div>
			<?php if($enable_q_and_a_on_course) { ?>
			<div class="tutor-lessons-quiz-tab flex-center">
               <span class="ttr-question-filled"></span>
               <span class="text-medium-caption color-text-title">
               <?php esc_html_e('Browse Q&A', 'tutor'); ?>
               </span>
            </div>
			<?php } ?>
        </div>

        <div class="tutor-sidebar-tabs-content">

			<div id="tutor-lesson-sidebar-tab-content" class="tutor-lesson-sidebar-tab-item">
				<?php
				$topics = tutor_utils()->get_topics($course_id);
				if ($topics->have_posts()){
					while ($topics->have_posts()){ $topics->the_post();
						$topic_id = get_the_ID();
						$topic_summery = get_the_content();
						?>

                        <div class="tutor-topics-in-single-lesson tutor-topics-<?php echo $topic_id; ?>">
                            <div class="tutor-topics-title d-flex justify-content-between">
								<div class="tutor-topics-title-left">
									<?php
										if ($topic_summery){
									?>
									<p class='tutor-topic-subtitle text-regular-caption color-text-subsued'><?php echo $topic_summery; ?></p>
									<?php } ?>
									<h3 class="text-medium-h6 color-text-brand">
										<?php
											the_title();
										?>
									</h3>
								</div>
								<div class="tutor-topics-title-right align-self-end">
									<p class="tutor-topic-subtitle text-regular-caption color-text-subsued">3/5</p>
								</div>
							</div>

                            <div class="tutor-lessons-under-topic" style="display: none">
								<?php
								do_action('tutor/lesson_list/before/topic', $topic_id);

								$lessons = tutor_utils()->get_course_contents_by_topic(get_the_ID(), -1);
								if ($lessons->have_posts()){
									while ($lessons->have_posts()){
										$lessons->the_post();

										if ($post->post_type === 'tutor_quiz') {
											$quiz = $post;
											?>
                                            <div class="tutor-single-lesson-items quiz-single-item quiz-single-item-<?php echo $quiz->ID; ?> <?php echo ( $currentPost->ID === get_the_ID() ) ? 'active' : ''; ?>" data-quiz-id="<?php echo $quiz->ID; ?>">
                                                <a href="<?php echo get_permalink($quiz->ID); ?>" class="tutor-single-quiz-a d-flex justify-content-between" data-quiz-id="<?php echo $quiz->ID; ?>">
													<div class="tutor-single-lesson-items-left d-flex">
														<span class="ttr-quiz-filled color-design-brand"></span>
														<span class="lesson_title text-regular-caption color-text-title">
														<?php echo $quiz->post_title; ?>
														</span>
													</div>
													<div class="tutor-single-lesson-items-right d-flex tutor-lesson-right-icons">
														<span class="text-regular-caption color-text-title">
														<?php
															do_action('tutor/lesson_list/right_icon_area', $post);

															$time_limit = tutor_utils()->get_quiz_option($quiz->ID, 'time_limit.time_value');
															if ($time_limit){
																$time_type = tutor_utils()->get_quiz_option($quiz->ID, 'time_limit.time_type');
																echo "{$time_limit} {$time_type}";
															}
														?>
														</span>
													</div>
                                                </a>
                                            </div>
											<?php
										}elseif($post->post_type === 'tutor_assignments'){
											/**
											 * Assignments
											 * @since this block v.1.3.3
											 */

											?>
                                            <div class="tutor-single-lesson-items assignments-single-item assignment-single-item-<?php echo $post->ID; ?> <?php echo ( $currentPost->ID === get_the_ID() ) ? 'active' : ''; ?>"
                                                 data-assignment-id="<?php echo $post->ID; ?>">
                                                <a href="<?php echo get_permalink($post->ID); ?>" class="tutor-single-assignment-a d-flex justify-content-between" data-assignment-id="<?php echo $post->ID; ?>">
													<div class="tutor-single-lesson-items-left d-flex">
														<span class="ttr-assignment-filled color-design-brand"></span>
														<span class="lesson_title text-regular-caption color-text-title">
														<?php echo $post->post_title; ?>
														</span>
													</div>
													<div class="tutor-single-lesson-items-right d-flex tutor-lesson-right-icons">
														<?php do_action('tutor/lesson_list/right_icon_area', $post); ?>
													</div>
                                                </a>
                                            </div>
											<?php

										}elseif($post->post_type === 'tutor_zoom_meeting'){
											/**
											 * Zoom Meeting
											 * @since this block v.1.7.1
											 */

											?>
                                            <div class="tutor-single-lesson-items zoom-meeting-single-item zoom-meeting-single-item-<?php echo $post->ID; ?> <?php echo ( $currentPost->ID === get_the_ID() ) ? 'active' : ''; ?>"
                                                 data-assignment-id="<?php echo $post->ID; ?>">
                                                <a href="<?php echo get_permalink($post->ID); ?>" class="sidebar-single-zoom-meeting-a d-flex justify-content-between">
													<div class="tutor-single-lesson-items-left d-flex">
														<span class="ttr-zoom-brand color-design-brand"></span>
														<span class="lesson_title text-regular-caption color-text-title">
															<?php echo $post->post_title; ?>
														</span>
													</div>
													<div class="tutor-single-lesson-items-right d-flex tutor-lesson-right-icons">
														<?php do_action('tutor/lesson_list/right_icon_area', $post); ?>
													</div>
                                                </a>
                                            </div>
											<?php

										}else{

											/**
											 * Lesson
											 */

											$video = tutor_utils()->get_video_info();

											$play_time = false;
											if ( $video ) {
												$play_time = $video->playtime;
											}
											$is_completed_lesson = tutor_utils()->is_completed_lesson();
											?>

                                            <div class="tutor-single-lesson-items <?php echo ( $currentPost->ID === get_the_ID() ) ? 'active' : ''; ?>">
                                                <a href="<?php the_permalink(); ?>" class="tutor-single-lesson-a d-flex justify-content-between" data-lesson-id="<?php the_ID(); ?>">
													<div class="tutor-single-lesson-items-left d-flex">
														<?php
															$tutor_lesson_type_icon = $play_time ? 'youtube-brand' : 'document';
															echo "<span class='ttr-$tutor_lesson_type_icon'></span>";
														?>
														<span class="lesson_title text-regular-caption color-text-title">
															<?php the_title(); ?>
														</span>
													</div>
													<div class="tutor-single-lesson-items-right d-flex tutor-lesson-right-icons">
														<?php
															do_action('tutor/lesson_list/right_icon_area', $post);
															if ( $play_time ) {
																echo "<span class='text-regular-caption color-text-title'>".tutor_utils()->get_optimized_duration($play_time)."</span>";
															}
															$lesson_complete_icon = $is_completed_lesson ? 'tutor-icon-mark tutor-done' : '';
															echo "<i class='tutor-lesson-complete $lesson_complete_icon'></i>";
                                                        ?>
														
													</div>
                                                </a>
                                            </div>

											<?php
										}
									}
									$lessons->reset_postdata();
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

            <div id="tutor-lesson-sidebar-qa-tab-content" class="tutor-lesson-sidebar-tab-item" style="display: none;">
				<?php
				tutor_lesson_sidebar_question_and_answer();
				?>
            </div>

        </div>

    </div>

<?php do_action('tutor_lesson/single/after/lesson_sidebar'); ?>