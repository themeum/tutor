<?php
/**
 * Display Topics and Lesson lists for learn
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use TUTOR\Input;
use Tutor\Models\QuizModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$post_id = get_the_ID();
if ( ! empty( Input::post( 'lesson_id' ) ) ) {
	$post_id = Input::post( 'lesson_id' );
}

$currentPost = $post;
$_is_preview = get_post_meta( $post_id, '_is_preview', true );
$course_id   = tutor_utils()->get_course_id_by_subcontent( $post->ID );

$user_id                      = get_current_user_id();
$enable_qa_for_this_course    = get_post_meta( $course_id, '_tutor_enable_qa', true ) == 'yes';
$enable_q_and_a_on_course     = tutor_utils()->get_option( 'enable_q_and_a_on_course' ) && $enable_qa_for_this_course;
$is_enrolled                  = tutor_utils()->is_enrolled( $course_id );
$is_instructor_of_this_course = tutor_utils()->has_user_course_content_access( $user_id, $course_id );
$is_user_admin                = current_user_can( 'administrator' );
$is_public_course             = \TUTOR\Course_List::is_public( $course_id );
?>

<?php do_action( 'tutor_lesson/single/before/lesson_sidebar' ); ?>
<div class="tutor-course-single-sidebar-title tutor-d-flex tutor-justify-between">
	<span class="tutor-fs-6 tutor-fw-medium tutor-color-secondary"><?php esc_html_e( 'Course Content', 'tutor' ); ?></span>
	<span class="tutor-d-block tutor-d-xl-none">
		<a href="#" class="tutor-iconic-btn" tutor-hide-course-single-sidebar>
			<span class="tutor-icon-times" area-hidden="true"></span>
		</a>
	</span>
</div>

<?php
$topics = tutor_utils()->get_topics( $course_id );
if ( $topics->have_posts() ) {

	// Loop through topics.
	while ( $topics->have_posts() ) {
		$topics->the_post();
		$topic_id        = get_the_ID();
		$topic_summery   = get_the_content();
		$total_contents  = tutor_utils()->count_completed_contents_by_topic( $topic_id );
		$lessons         = tutor_utils()->get_course_contents_by_topic( get_the_ID(), -1 );
		$is_topic_active = ! empty(
			array_filter(
				$lessons->posts,
				function ( $content ) use ( $currentPost ) {
					return $content->ID == $currentPost->ID;
				}
			)
		);
		?>
		<div class="tutor-course-topic tutor-course-topic-<?php echo esc_attr( $topic_id ); ?>">
			<div class="tutor-accordion-item-header<?php echo $is_topic_active ? ' is-active' : ''; ?>" tutor-course-single-topic-toggler>
				<div class="tutor-row tutor-gx-1">
					<div class="tutor-col">
						<div class="tutor-course-topic-title">
							<?php the_title(); ?>
							<?php if ( true ) : ?>
								<?php if ( trim( $topic_summery ) ) : ?>
									<div class="tutor-course-topic-title-info tutor-ml-8">
										<div class="tooltip-wrap">
											<i class="tutor-course-topic-title-info-icon tutor-icon-circle-info-o"></i>
											<span class="tooltip-txt tooltip-bottom">
												<?php echo esc_textarea( $topic_summery ); ?>
											</span>
										</div>
									</div>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>

					<div class="tutor-col-auto tutor-align-self-center">
						<?php if ( isset( $total_contents['contents'] ) && $total_contents['contents'] > 0 ) : ?>
							<div class="tutor-course-topic-summary tutor-pl-8">
								<?php echo esc_html( isset( $total_contents['completed'] ) ? $total_contents['completed'] : 0 ); ?>/<?php echo esc_html( isset( $total_contents['contents'] ) ? $total_contents['contents'] : 0 ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="tutor-accordion-item-body <?php echo $is_topic_active ? '' : 'tutor-display-none'; ?>">
				<?php
				do_action( 'tutor/lesson_list/before/topic', $topic_id );

				// Loop through lesson, quiz, assignment, zoom lesson.
				while ( $lessons->have_posts() ) {
					$lessons->the_post();

					$show_permalink = ! $_is_preview || $is_enrolled || get_post_meta( $post->ID, '_is_preview', true ) || $is_public_course || $is_instructor_of_this_course;
					$show_permalink = apply_filters( 'tutor_course/single/content/show_permalink', $show_permalink, get_the_ID() );

					$lock_icon      = ! $show_permalink;
					$show_permalink = null === $show_permalink ? true : $show_permalink;

					if ( 'tutor_quiz' === $post->post_type ) {
						$quiz = $post;
						?>
						<div class="tutor-course-topic-item tutor-course-topic-item-quiz<?php echo ( get_the_ID() == $currentPost->ID ) ? ' is-active' : ''; ?>" data-quiz-id="<?php echo esc_attr( $quiz->ID ); ?>">
							<a href="<?php echo $show_permalink ? esc_url( get_permalink( $quiz->ID ) ) : '#'; ?>" data-quiz-id="<?php echo esc_attr( $quiz->ID ); ?>">
								<div class="tutor-d-flex tutor-mr-32">
									<span class="tutor-course-topic-item-icon tutor-icon-quiz-o tutor-mr-8 tutor-mt-2" area-hidden="true"></span>
									<span class="tutor-course-topic-item-title tutor-fs-7 tutor-fw-medium">
										<?php echo esc_html( $quiz->post_title ); ?>
									</span>
								</div>
								<div class="tutor-d-flex tutor-ml-auto tutor-flex-shrink-0">
									<?php
									$time_limit   = (int) tutor_utils()->get_quiz_option( $quiz->ID, 'time_limit.time_value' );
									$last_attempt = ( new QuizModel() )->get_first_or_last_attempt( $quiz->ID );

									$attempt_ended = is_object( $last_attempt ) && ( 'attempt_ended' === ( $last_attempt->attempt_status ) || $last_attempt->is_manually_reviewed ) ? true : false;

									if ( $time_limit ) {
										$time_type                             = tutor_utils()->get_quiz_option( $quiz->ID, 'time_limit.time_type' );
										 'minutes' == $time_type ? $time_limit = $time_limit * 60 : 0;
										 'hours' == $time_type ? $time_limit   = $time_limit * 3660 : 0;
										 'days' == $time_type ? $time_limit    = $time_limit * 86400 : 0;
										 'weeks' == $time_type ? $time_limit   = $time_limit * 86400 * 7 : 0;

										// To Fix: If time larger than 24 hours, the hour portion starts from 0 again. Fix later.
										$markup = '<span class="tutor-course-topic-item-duration tutor-fs-7 tutor-fw-medium tutor-color-muted tutor-mr-8">' . tutor_utils()->course_content_time_format( gmdate( 'H:i:s', $time_limit ) ) . '</span>';
										echo wp_kses(
											$markup,
											array(
												'span' => array( 'class' => true ),
											)
										);
									}
									?>

									<?php if ( ! $lock_icon ) : ?>
										<input type="checkbox" class="tutor-form-check-input tutor-form-check-circle" disabled="disabled" readonly="readonly" <?php echo esc_attr( $attempt_ended ? 'checked="checked"' : '' ); ?> />
									<?php else : ?>
										<i class="tutor-icon-lock-line tutor-fs-7 tutor-color-muted tutor-mr-4" area-hidden="true"></i>
									<?php endif; ?>
								</div>
							</a>
						</div>
					<?php } elseif ( 'tutor_assignments' === $post->post_type ) { ?>
						<div class="tutor-course-topic-item tutor-course-topic-item-assignment<?php echo ( get_the_ID() == $currentPost->ID ) ? ' is-active' : ''; ?>">
							<a href="<?php echo $show_permalink ? esc_url( get_permalink( $post->ID ) ) : '#'; ?>" data-assignment-id="<?php echo esc_attr( $post->ID ); ?>">
								<div class="tutor-d-flex tutor-mr-32">
									<span class="tutor-course-topic-item-icon tutor-icon-assignment tutor-mr-8" area-hidden="true"></span>
									<span class="tutor-course-topic-item-title tutor-fs-7 tutor-fw-medium">
										<?php echo esc_html( $post->post_title ); ?>
									</span>
								</div>
								<div class="tutor-d-flex tutor-ml-auto tutor-flex-shrink-0">
									<?php if ( $show_permalink ) : ?>
										<?php do_action( 'tutor/assignment/right_icon_area', $post, $lock_icon ); ?>
									<?php else : ?>
										<i class="tutor-icon-lock-line tutor-fs-7 tutor-color-muted tutor-mr-4" area-hidden="true"></i>
									<?php endif; ?>
								</div>
							</a>
						</div>
					<?php } elseif ( 'tutor_zoom_meeting' === $post->post_type ) { ?>
						<div class="tutor-course-topic-item tutor-course-topic-item-zoom<?php echo esc_attr( ( get_the_ID() == $currentPost->ID ) ? ' is-active' : '' ); ?>">
							<a href="<?php echo $show_permalink ? esc_url( get_permalink( $post->ID ) ) : '#'; ?>">
								<div class="tutor-d-flex tutor-mr-32">
									<span class="tutor-course-topic-item-icon tutor-icon-brand-zoom-o tutor-mr-8 tutor-mt-2" area-hidden="true"></span>
									<span class="tutor-course-topic-item-title tutor-fs-7 tutor-fw-medium">
										<?php echo esc_html( $post->post_title ); ?>
									</span>
								</div>
								<div class="tutor-d-flex tutor-ml-auto tutor-flex-shrink-0">
									<?php if ( $show_permalink ) : ?>
										<?php do_action( 'tutor/zoom/right_icon_area', $post->ID, $lock_icon ); ?>
									<?php else : ?>
										<i class="tutor-icon-lock-line tutor-fs-7 tutor-color-muted tutor-mr-4" area-hidden="true"></i>
									<?php endif; ?>
								</div>
							</a>
						</div>
					<?php } elseif ( 'tutor-google-meet' === $post->post_type ) { ?>
						<div class="tutor-course-topic-item tutor-course-topic-item-zoom<?php echo esc_attr( get_the_ID() == $currentPost->ID ? ' is-active' : '' ); ?>">
							<a href="<?php echo $show_permalink ? esc_url( get_permalink( $post->ID ) ) : '#'; ?>">
								<div class="tutor-d-flex tutor-mr-32">
									<span class="tutor-course-topic-item-icon tutor-icon-brand-google-meet tutor-mr-8 tutor-mt-2" area-hidden="true"></span>
									<span class="tutor-course-topic-item-title tutor-fs-7 tutor-fw-medium">
										<?php echo esc_html( $post->post_title ); ?>
									</span>
								</div>
								<div class="tutor-d-flex tutor-ml-auto tutor-flex-shrink-0">
									<?php if ( $show_permalink ) : ?>
										<?php do_action( 'tutor/google_meet/right_icon_area', $post->ID, false ); ?>
									<?php else : ?>
										<i class="tutor-icon-lock-line tutor-fs-7 tutor-color-muted tutor-mr-4" area-hidden="true"></i>
									<?php endif; ?>
								</div>
							</a>
						</div>
					<?php } else { ?>
						
						<?php
						$video     = tutor_utils()->get_video_info();
						$play_time = false;
						if ( $video ) {
							$play_time = $video->playtime;
						}
						$is_completed_lesson = tutor_utils()->is_completed_lesson();
						?>
						<div class="tutor-course-topic-item tutor-course-topic-item-lesson<?php echo esc_attr( get_the_ID() == $currentPost->ID ? ' is-active' : '' ); ?>">
							<a href="<?php echo $show_permalink ? esc_url( get_the_permalink() ) : '#'; ?>" data-lesson-id="<?php the_ID(); ?>">
								<div class="tutor-d-flex tutor-mr-32">
									<?php
									$tutor_lesson_type_icon = $play_time ? 'brand-youtube-bold' : 'document-text';
									$markup                 = '<span class="tutor-course-topic-item-icon tutor-icon-' . $tutor_lesson_type_icon . ' tutor-mr-8 tutor-mt-2" area-hidden="true"></span>';
									echo wp_kses(
										$markup,
										array(
											'span' => array(
												'class' => true,
												'area-hidden' => true,
											),
										)
									);
									?>
									<span class="tutor-course-topic-item-title tutor-fs-7 tutor-fw-medium">
										<?php the_title(); ?>
									</span>
								</div>

								<div class="tutor-d-flex tutor-ml-auto tutor-flex-shrink-0">
									<?php
									if ( $play_time ) {
										$markup = "<span class='tutor-course-topic-item-duration tutor-fs-7 tutor-fw-medium tutor-color-muted tutor-mr-8'>" . tutor_utils()->get_optimized_duration( $play_time ) . '</span>';
										echo wp_kses(
											$markup,
											array(
												'span' => array( 'class' => true ),
											)
										);
									}

									$lesson_complete_icon = $is_completed_lesson ? 'checked' : '';

									if ( ! $lock_icon ) {
										$markup = "<input $lesson_complete_icon type='checkbox' class='tutor-form-check-input tutor-form-check-circle' disabled readonly />";
										echo wp_kses(
											$markup,
											array(
												'input' => array(
													'checked' => true,
													'class' => true,
													'type' => true,
													'disabled' => true,
													'readonly' => true,
												),
											)
										);
									} else {
										$markup = '<i class="tutor-icon-lock-line tutor-fs-7 tutor-color-muted tutor-mr-4" area-hidden="true"></i>';
										echo wp_kses(
											$markup,
											array(
												'i' => array(
													'class' => true,
													'area-hidden' => true,
												),
											)
										);
									}
									?>
								</div>
							</a>
						</div>
						<?php
					}
				}
				$lessons->reset_postdata();
				do_action( 'tutor/lesson_list/after/topic', $topic_id );
				?>
			</div>
		</div>
		<?php
	}
	$topics->reset_postdata();
	wp_reset_postdata();
}
?>
<?php do_action( 'tutor_lesson/single/after/lesson_sidebar' ); ?>
