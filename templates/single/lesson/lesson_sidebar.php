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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;
$post_id = get_the_ID();
if ( ! empty( $_POST['lesson_id'] ) ) {
	$post_id = sanitize_text_field( $_POST['lesson_id'] );
}
$currentPost = $post;
$_is_preview = get_post_meta( $post_id, '_is_preview', true );
$course_id   = 0;
if ( $post->post_type === 'tutor_quiz' ) {
	$course    = tutor_utils()->get_course_by_quiz( get_the_ID() );
	$course_id = $course->ID;
} elseif ( $post->post_type === 'tutor_assignments' ) {
	$course_id = tutor_utils()->get_course_id_by( 'assignment', $post->ID );
} elseif ( $post->post_type === 'tutor_zoom_meeting' ) {
	$course_id = get_post_meta( $post->ID, '_tutor_zm_for_course', true );
} else {
	$course_id = tutor_utils()->get_course_id_by( 'lesson', $post->ID );
}
$user_id                      = get_current_user_id();
$enable_qa_for_this_course    = get_post_meta( $course_id, '_tutor_enable_qa', true ) == 'yes';
$enable_q_and_a_on_course     = tutor_utils()->get_option( 'enable_q_and_a_on_course' ) && $enable_qa_for_this_course;
$is_enrolled                  = tutor_utils()->is_enrolled( $course_id );
$is_instructor_of_this_course = tutor_utils()->is_instructor_of_this_course( $user_id, $course_id );
$is_user_admin                = current_user_can( 'administrator' );
?>

<?php do_action( 'tutor_lesson/single/before/lesson_sidebar' ); ?>
	<div class="tutor-sidebar-tabs-wrap">
		<div class="tutor-lessons-tab-area tutor-<?php echo esc_html( isset( $context ) ? $context : 'desktop' ); ?>-sidebar-area">
			<div data-sidebar-tab="tutor-lesson-sidebar-tab-content" class="tutor-sidebar-tab-item tutor-lessons-tab <?php echo $enable_q_and_a_on_course ? 'active' : ''; ?> flex-center">
				<span class="tutor-icon-education-filled"></span>
				<span class="text-medium-caption tutor-color-black-70">
					<?php esc_html_e( 'Lesson List', 'tutor' ); ?>
				</span>
			</div>
			<?php if ( $enable_q_and_a_on_course && ( $is_enrolled || $is_instructor_of_this_course || $is_user_admin ) ): ?>
				<div data-sidebar-tab="sideabr-qna-tab-content" class="tutor-sidebar-tab-item tutor-quiz-tab flex-center">
					<span class="tutor-icon-question-filled"></span>
					<span class="text-medium-caption tutor-color-black-70">
						<?php esc_html_e( 'Question & Answer', 'tutor' ); ?>
					</span>
				</div>
			<?php endif; ?>
		</div>

		<div class="tutor-sidebar-tabs-content">
			<div id="tutor-lesson-sidebar-tab-content" class="tutor-lesson-sidebar-tab-item active">
				<?php
				$topics = tutor_utils()->get_topics( $course_id );
				if ( $topics->have_posts() ) {
					while ( $topics->have_posts() ) {
						$topics->the_post();
						$topic_id       = get_the_ID();
						$topic_summery  = get_the_content();
						$total_contents = tutor_utils()->count_completed_contents_by_topic( $topic_id );
						?>

						<div class="tutor-topics-in-single-lesson tutor-topics-<?php echo $topic_id; ?>">
							<div class="tutor-topics-title tutor-d-flex tutor-justify-content-between">
								<div class="tutor-topics-title-left">
									<div class="tutor-topics-title-inner">
										<div class="text-medium-h6 tutor-color-text-brand"><?php the_title(); ?></div>
										<?php if ( true ): ?>
											<div class="tutor-topics-title-info">
												<div class="tooltip-wrap tutor-d-flex">
													<i class="tutor-icon-circle-outline-info-filled tutor-icon-24 color-black-40"></i>
													<span class="tooltip-txt tooltip-bottom">
														<?php echo $topic_summery; ?>
													</span>
												</div>
											</div>
										<?php endif; ?>
									</div>
								</div>
								<div class="tutor-topics-title-right">
									<?php if ( isset( $total_contents['contents'] ) && $total_contents['contents'] > 0 ) : ?>
										<div class="tutor-topic-subtitle tutor-fs-7 tutor-fw-normal tutor-color-black-60">
											<?php echo esc_html( isset( $total_contents['completed'] ) ? $total_contents['completed'] : 0 ); ?>/<?php echo esc_html( isset( $total_contents['contents'] ) ? $total_contents['contents'] : 0 ); ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
							<?php
								do_action( 'tutor/lesson_list/before/topic', $topic_id );
								$lessons = tutor_utils()->get_course_contents_by_topic( get_the_ID(), -1 );
								$is_enrolled = tutor_utils()->is_enrolled( $course_id, get_current_user_id() );

								while ( $lessons->have_posts() ) {
									$lessons->the_post();
									$show_permalink = !$_is_preview || $is_enrolled || get_post_meta( $post->ID, '_is_preview', true );
									if ( $post->post_type === 'tutor_quiz' ) {
										$quiz = $post;
										?>
											<div class="tutor-lessons-under-topic" data-quiz-id="<?php echo $quiz->ID; ?>">
												<div class="tutor-single-lesson-items <?php echo ( $currentPost->ID == get_the_ID() ) ? 'active tutor-color-design-brand' : ''; ?>">
													<a href="<?php echo $show_permalink ? get_permalink( $quiz->ID ) : '#'; ?>" class="tutor-single-quiz-a tutor-d-flex tutor-justify-content-between" data-quiz-id="<?php echo $quiz->ID; ?>">
														<div class="tutor-single-lesson-items-left tutor-d-flex">
															<span class="tutor-icon-quiz-filled"></span>
															<span class="lesson_title tutor-fs-7 tutor-fw-normal tutor-color-black-70">
																<?php echo $quiz->post_title; ?>
															</span>
														</div>
														<div class="tutor-single-lesson-items-right tutor-d-flex tutor-lesson-right-icons">
															<span class="text-regular-caption tutor-color-black-70">
																<?php
																	$time_limit = tutor_utils()->get_quiz_option( $quiz->ID, 'time_limit.time_value' );
																	if ( $time_limit ) {
																		$time_type = tutor_utils()->get_quiz_option( $quiz->ID, 'time_limit.time_type' );

																		$time_type=='minutes' ? $time_limit=$time_limit*60 : 0;
																		$time_type=='hours' ? $time_limit=$time_limit*3660 : 0;
																		$time_type=='days' ? $time_limit=$time_limit*86400 : 0;
																		$time_type=='weeks' ? $time_limit=$time_limit*86400*7 : 0;

																		// To Fix: If time larger than 24 hours, the hour portion starts from 0 again. Fix later.
																		echo gmdate('H:i:s', $time_limit);
																	}
																	
																	$has_attempt = tutor_utils()->has_attempted_quiz( get_current_user_id(), $quiz->ID )
																?>

																<?php if($show_permalink): ?>
																	<input type='checkbox' class='tutor-form-check-input tutor-form-check-circle' disabled="disabled" readonly="readonly" <?php echo esc_attr( $has_attempt ? 'checked="checked"' : '' ); ?>/>
																<?php else: ?>
																	<i class="tutor-icon-lock-stroke-filled"></i>
																<?php endif; ?>
															</span>
														</div>
													</a>
												</div>
											</div>
										<?php
											
									} elseif ( $post->post_type === 'tutor_assignments' ) {
										/**
										 * Assignments
											 *
										 * @since this block v.1.3.3
										 */
										?>
											<div class="tutor-lessons-under-topic">
												<div class="tutor-single-lesson-items <?php echo ( $currentPost->ID == get_the_ID() ) ? 'active tutor-color-design-brand' : ''; ?>">
													<a href="<?php echo $show_permalink ? get_permalink( $post->ID ) : '#'; ?>" class="tutor-single-assignment-a tutor-d-flex tutor-justify-content-between" data-assignment-id="<?php echo $post->ID; ?>">
														<div class="tutor-single-lesson-items-left tutor-d-flex">
															<span class="tutor-icon-assignment-filled"></span>
															<span class="lesson_title tutor-fs-7 tutor-fw-normal tutor-color-black-70">
																<?php echo $post->post_title; ?>
															</span>
														</div>
														<div class="tutor-single-lesson-items-right tutor-d-flex tutor-lesson-right-icons">
															<?php if($show_permalink): ?>
																<?php do_action( 'tutor/assignment/right_icon_area', $post ); ?>
															<?php else: ?>
																<i class="tutor-icon-lock-stroke-filled"></i>
															<?php endif; ?>
														</div>
													</a>
												</div>
											</div>
										<?php
									} elseif ( $post->post_type === 'tutor_zoom_meeting' ) {
										/**
										 * Zoom Meeting
											 *
										 * @since this block v.1.7.1
										 */
										?>
											<div class="tutor-lessons-under-topic">
												<div class="tutor-single-lesson-items <?php echo ( $currentPost->ID == get_the_ID() ) ? 'active tutor-color-design-brand' : ''; ?>">
													<a href="<?php echo $show_permalink ? esc_url( get_permalink( $post->ID ) ) : '#'; ?>" class="sidebar-single-zoom-meeting-a tutor-d-flex tutor-justify-content-between">
														<div class="tutor-single-lesson-items-left tutor-d-flex">
															<span class="tutor-icon-zoom"></span>
															<span class="lesson_title tutor-fs-7 tutor-fw-normal tutor-color-black-70">
																<?php echo esc_html( $post->post_title ); ?>
															</span>
														</div>
														<div class="tutor-single-lesson-items-right tutor-d-flex tutor-lesson-right-icons">
															<?php if($show_permalink): ?>
																<?php do_action( 'tutor/zoom/right_icon_area', $post->ID ); ?>
															<?php else: ?>
																<i class="tutor-icon-lock-stroke-filled"></i>
															<?php endif; ?>
														</div>
													</a>
												</div>
											</div>
										<?php
										
									} else {

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
											<div class="tutor-lessons-under-topic">
												<div class="tutor-single-lesson-items <?php echo ( $currentPost->ID == get_the_ID() ) ? 'active tutor-color-design-brand' : ''; ?>">
													<a href="<?php echo $show_permalink ? get_the_permalink() : '#'; ?>" class="tutor-single-lesson-a tutor-d-flex tutor-justify-content-between" data-lesson-id="<?php the_ID(); ?>">
														<div class="tutor-single-lesson-items-left tutor-d-flex">
															<?php
																$tutor_lesson_type_icon = $play_time ? 'youtube-brand' : 'document-file';
																echo "<span class='tutor-icon-$tutor_lesson_type_icon'></span>";
															?>
															<span class="lesson_title tutor-fs-7 tutor-fw-normal tutor-color-black-70">
																<?php the_title(); ?>
															</span>
														</div>
														<div class="tutor-single-lesson-items-right tutor-d-flex">
															<?php
																do_action( 'tutor/lesson_list/right_icon_area', $post );
																if ( $play_time ) {
																	echo "<span class='text-regular-caption tutor-color-black-70'>" . tutor_utils()->get_optimized_duration( $play_time ) . '</span>';
																}
																$lesson_complete_icon = $is_completed_lesson ? 'checked' : '';

																if($show_permalink) {
																	echo "<input $lesson_complete_icon type='checkbox' class='tutor-form-check-input tutor-form-check-circle' disabled readonly />";
																} else {
																	echo '<i class="tutor-icon-lock-stroke-filled"></i>';
																}
															?>
														</div>
													</a>
												</div>
											</div>
										<?php
									}
								}
								$lessons->reset_postdata();
								do_action( 'tutor/lesson_list/after/topic', $topic_id );
							?>
						</div>
						<?php
					}
					$topics->reset_postdata();
					wp_reset_postdata();
				}
				?>
			</div>

			<div id="sideabr-qna-tab-content" class="tutor-lesson-sidebar-tab-item">
				<?php
					tutor_lesson_sidebar_question_and_answer();
				?>
			</div>
		</div>
	</div>
<?php do_action( 'tutor_lesson/single/after/lesson_sidebar' ); ?>
