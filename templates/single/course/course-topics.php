<?php
/**
 * Template for displaying single course
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$topics      = tutor_utils()->get_topics();
$course_id   = get_the_ID();
$is_enrolled = tutor_utils()->is_enrolled( $course_id );
$index       = 0;

do_action( 'tutor_course/single/before/topics' );
?>
<h3 class="tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mb-24">
	<?php
		$title = __( 'Course Curriculum', 'tutor' );
		echo esc_html( apply_filters( 'tutor_course_topics_title', $title ) );
	?>
</h3>

<?php if ( $topics->have_posts() ) : ?>
	<div class="tutor-accordion tutor-mt-24">
	<?php while ( $topics->have_posts() ) : ?>
		<?php
			$topics->the_post();
			$topic_summery = get_the_content();
			$index++;
		?>
			<div class="tutor-accordion-item">
				<h4 class="tutor-accordion-item-header">
					<?php the_title(); ?>
					<?php if ( ! empty( $topic_summery ) ): ?>
						<div class="tooltip-wrap tooltip-icon">
							<span class="tooltip-txt tooltip-right"><?php echo esc_attr( $topic_summery ); ?></span>
						</div>
					<?php endif; ?>
				</h4>
				<?php
					$topic_contents = tutor_utils()->get_course_contents_by_topic( get_the_ID(), -1 );
					if ( $topic_contents->have_posts() ) {
					?>
						<div class="tutor-accordion-item-body">
							<div class="tutor-accordion-item-body-content">
								<ul class="tutor-courses-lession-list">
									<?php while ( $topic_contents->have_posts() ) : ?>
										<?php
											$topic_contents->the_post();
											global $post;

											// Get Lesson video information if any
											$video     = tutor_utils()->get_video_info();
											$play_time = $video ? $video->playtime : false;
											$is_preview = get_post_meta( $post->ID, '_is_preview', true );

											// Determine topic content icon based on lesson, video, quiz etc.
											$topic_content_icon                                     = $play_time ? 'tutor-icon-youtube-brand' : 'tutor-icon-document-alt-filled';
											$post->post_type === 'tutor_quiz' ? $topic_content_icon = 'tutor-icon-question-mark-circle-filled' : 0;
											$post->post_type === 'tutor_assignments' ? $topic_content_icon  = 'tutor-icon-document-alt-filled' : 0;
											$post->post_type === 'tutor_zoom_meeting' ? $topic_content_icon = 'tutor-icon-zoom' : 0;
											$is_locked = !($is_enrolled || $is_preview);
										?>
										<li>
											<div class="tutor-courses-lession-list-single-item">
												<span class="<?php echo $topic_content_icon; ?> tutor-icon-24 tutor-color-black-30 tutor-mr-16"></span>
												<h5 class="tutor-fs-6 tutor-color-black">
													<?php
														$lesson_title = '';

														// Add zoom meeting countdown info
														$countdown = '';
														if ( $post->post_type === 'tutor_zoom_meeting' ) {
															$zoom_meeting = tutor_zoom_meeting_data( $post->ID );
															$countdown    = '<div class="tutor-zoom-lesson-countdown tutor-lesson-duration" data-timer="' . $zoom_meeting->countdown_date . '" data-timezone="' . $zoom_meeting->timezone . '"></div>';
														}

														// Show clickable content if enrolled
														// Or if it is public and not paid, then show content forcefully
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

															echo $lesson_title;
														} else {
															$lesson_title .= get_the_title();
															echo apply_filters( 'tutor_course/contents/lesson/title', $lesson_title, get_the_ID() );
														}
													?>
												</h5>
											</div>
											<div>
												<span class="tutor-fs-7 tutor-color-muted">
													<?php echo $play_time ? tutor_utils()->get_optimized_duration( $play_time ) : ''; ?>
												</span>
												<span class="<?php echo $is_locked ? ' tutor-icon-lock-stroke-filled' : 'tutor-icon-eye-filled'; ?> tutor-icon-24 tutor-color-black-20 tutor-ml-20" area-hidden="true"></span>
											</div>
										</li>
									<?php endwhile; ?>
								</ul>
							</div>
						</div>
						<?php
						$topic_contents->reset_postdata();
					}
				?>
			</div>
			<?php endwhile; ?>
		</div>
<?php endif; ?>

<?php do_action( 'tutor_course/single/after/topics' ); ?>
