<?php
/**
 * Template for displaying single course
 *
 * @package Tutor\Templates
 * @subpackage Single\Course
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $is_enrolled;

$topics      = tutor_utils()->get_topics();
$course_id   = get_the_ID();
$index       = 0;

/**
 * $is_enrolled getting null for Addons plugin like Elementor addons
 *
 * @since 2.1.8
 */
if ( is_null( $is_enrolled ) ) {
	$is_enrolled = tutor_utils()->is_enrolled( $course_id );
}

do_action( 'tutor_course/single/before/topics' );
?>
<div class="tutor-mt-40">
	<?php if ( $topics->have_posts() ) : ?>

		<h3 class="tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mb-24 tutor-course-content-title">
			<?php
				echo esc_html( apply_filters( 'tutor_course_topics_title', __( 'Course Content', 'tutor' ) ) );
			?>
		</h3>

		<div class="tutor-accordion tutor-mt-24">
		<?php while ( $topics->have_posts() ) : ?>
			<?php
				$topics->the_post();
				$topic_summery = get_the_content();
				$index++;
			?>
			<div class="tutor-accordion-item">
				<h4 class="tutor-accordion-item-header<?php echo 1 == $index ? ' is-active' : ''; ?>">
					<?php the_title(); ?>
					<?php if ( ! empty( $topic_summery ) ) : ?>
						<div class="tooltip-wrap tooltip-icon">
							<span class="tooltip-txt tooltip-right"><?php echo esc_attr( $topic_summery ); ?></span>
						</div>
					<?php endif; ?>
				</h4>
	
				<?php $topic_contents = tutor_utils()->get_course_contents_by_topic( get_the_ID(), -1 ); ?>
				<?php if ( $topic_contents->have_posts() ) : ?>
					<div class="tutor-accordion-item-body" style="<?php echo 1 != $index ? 'display: none;' : ''; ?>">
						<div class="tutor-accordion-item-body-content">
							<ul class="tutor-course-content-list">
								<?php while ( $topic_contents->have_posts() ) : ?>
									<?php
										$topic_contents->the_post();
										global $post;

										// Get Lesson video information if any.
										$video      = tutor_utils()->get_video_info();
										$play_time  = $video ? $video->playtime : false;
										$is_preview = get_post_meta( $post->ID, '_is_preview', true );

										// Determine topic content icon based on lesson, video, quiz etc.
										$topic_content_icon                                     = $play_time ? 'tutor-icon-brand-youtube-bold' : 'tutor-icon-document-text';
										'tutor_quiz' === $post->post_type ? $topic_content_icon = 'tutor-icon-circle-question-mark' : 0;
										'tutor_assignments' === $post->post_type ? $topic_content_icon  = 'tutor-icon-document-text' : 0;
										'tutor_zoom_meeting' === $post->post_type ? $topic_content_icon = 'tutor-icon-brand-zoom' : 0;
										'tutor-google-meet' === $post->post_type ? $topic_content_icon  = 'tutor-icon-brand-google-meet' : 0;

										$is_public_course = \TUTOR\Course_List::is_public( $course_id );
										$is_locked        = ! ( $is_enrolled || $is_preview || $is_public_course );
									?>
									<li class="tutor-course-content-list-item">
										<div class="tutor-d-flex tutor-align-center">
											<span class="tutor-course-content-list-item-icon <?php echo esc_attr( $topic_content_icon ); ?> tutor-mr-12"></span>
											<h5 class="tutor-course-content-list-item-title">
												<?php
													$lesson_title    = '';
													$title_tag_allow = array(
														'a' => array(
															'href' => true,
															'class' => true,
														),
														'span' => array( 'class' => true ),
													);

													// Add zoom meeting countdown info.
													$countdown = '';
													if ( 'tutor_zoom_meeting' === $post->post_type ) {
														$zoom_meeting = tutor_zoom_meeting_data( $post->ID );
														$countdown    = '<div class="tutor-zoom-lesson-countdown tutor-lesson-duration" data-timer="' . $zoom_meeting->countdown_date . '" data-timezone="' . $zoom_meeting->timezone . '"></div>';
													}

													/**
													 * Show clickable content if enrolled.
													 * Or if it is public and not paid, then show content forcefully.
													 */
													if ( $is_enrolled || ( get_post_meta( $course_id, '_tutor_is_public_course', true ) == 'yes' && ! tutor_utils()->is_course_purchasable( $course_id ) ) ) {
														$lesson_title .= "<a href='" . get_the_permalink() . "'> " . get_the_title() . ' </a>';

														if ( $countdown ) {
															if ( $zoom_meeting->is_expired ) {
																$lesson_title .= '<span class="tutor-zoom-label">' . __( 'Expired', 'tutor' ) . '</span>';
															} elseif ( $zoom_meeting->is_started ) {
																$lesson_title .= '<span class="tutor-zoom-label tutor-zoom-live-label">' . __( 'Live', 'tutor' ) . '</span>';
															}
															$lesson_title .= $countdown;
														}

														echo wp_kses(
															$lesson_title,
															$title_tag_allow
														);
													} else {
														$lesson_title .= get_the_title();
														echo wp_kses( apply_filters( 'tutor_course/contents/lesson/title', $lesson_title, get_the_ID() ), $title_tag_allow );
													}
													?>
											</h5>
										</div>
										
										<div>
											<span class="tutor-course-content-list-item-duration tutor-fs-7 tutor-color-muted">
												<?php echo esc_html( $play_time ? tutor_utils()->get_optimized_duration( $play_time ) : '' ); ?>
											</span>
											<span class="tutor-course-content-list-item-status <?php echo $is_locked ? 'tutor-icon-lock-line' : 'tutor-icon-eye-line'; ?> tutor-color-muted tutor-ml-20" area-hidden="true"></span>
										</div>
									</li>
								<?php endwhile; ?>
							</ul>
						</div>
					</div>
					<?php $topic_contents->reset_postdata(); ?>
				<?php endif; ?>
			</div>
			<?php endwhile; ?>
		</div>
	<?php endif; ?>
</div>

<?php do_action( 'tutor_course/single/after/topics' ); ?>
