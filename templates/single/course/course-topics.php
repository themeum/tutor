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

?>

<div class="tutor-single-course-segment  tutor-course-topics-wrap">

	<div class="tutor-course-topics-header">
		<div class="tutor-course-topics-header-left">
			<h3 class="tutor-segment-title"><?php _e('Topics for this course', 'tutor'); ?></h3>
		</div>

		<div class="tutor-course-topics-header-right">
			<span><?php echo tutor_utils()->get_lesson()->post_count; ?> <?php _e('Lessons', 'tutor'); ?></span>
		</div>
	</div>


	<div class="tutor-course-topics-contents">
		<?php
		$topics = tutor_utils()->get_topics();

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
                                $lesson_icon = $play_time ? '<svg width="16" height="13" xmlns="http://www.w3.org/2000/svg"><path d="M15.841 2.803v-.004c-.007-.05-.168-1.239-.68-1.797C14.56.324 13.893.256 13.535.22l-.102-.012C11.284.04 8.036.022 7.993.022c-.032 0-3.28.018-5.441.188l-.09.01C2.104.256 1.438.325.84.999.325 1.56.163 2.749.156 2.803A27.644 27.644 0 0 0 0 5.529v1.264c0 1.348.155 2.713.157 2.73.006.05.168 1.239.68 1.796.554.625 1.246.715 1.66.768.073.01.138.018.216.032 1.235.128 5.118.168 5.286.17.033 0 3.284-.008 5.447-.176l.093-.01c.358-.038 1.023-.109 1.619-.78.514-.56.676-1.748.683-1.803.001-.014.156-1.379.156-2.727V5.53c0-1.348-.155-2.713-.156-2.727zm-4.51 3.851L6.262 9.49a.267.267 0 0 1-.396-.233v-5.8a.267.267 0 0 1 .401-.23l5.067 2.966a.266.266 0 0 1-.005.462z" fill="#B1B8C9" fill-rule="nonzero"/></svg>' : '<svg width="13" height="16" xmlns="http://www.w3.org/2000/svg"><g fill="#B1B8C9" fill-rule="evenodd"><path d="M12.052 2.222h-.444v11.161c0 .741-.59 1.345-1.314 1.345H2.222v.414c0 .473.376.858.839.858h8.989a.849.849 0 0 0 .839-.858V3.08a.848.848 0 0 0-.837-.858z"/><path d="M5.777 14.222h4.496a.849.849 0 0 0 .838-.858V1.303a.849.849 0 0 0-.838-.859H4.282v.284c.003.03.005.06.005.091V3.17c0 .667-.53 1.209-1.181 1.209H.809l-.073-.003H.444v8.988c0 .474.376.858.839.858h4.494zm.17-2.917H3.016a.401.401 0 0 1-.397-.406c0-.223.178-.405.397-.405h2.932c.219 0 .396.182.396.405 0 .224-.179.406-.397.406zm2.932-2.07H3.016a.401.401 0 0 1-.397-.406c0-.224.178-.405.397-.405h5.863c.219 0 .396.181.396.405a.401.401 0 0 1-.396.406zm-5.863-3.01h5.863c.219 0 .396.182.396.406a.401.401 0 0 1-.396.405H3.016a.401.401 0 0 1-.397-.405c0-.224.178-.406.397-.406z" fill-rule="nonzero"/><path d="M.376 3.556h2.44a.746.746 0 0 0 .74-.74V.376A.374.374 0 0 0 2.917.11L.11 2.917a.374.374 0 0 0 .266.639z"/></g></svg>';

								?>

								<div class="tutor-course-lesson">
									<h4><?php
                                        echo $lesson_icon;
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