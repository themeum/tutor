<?php
/**
 * Course contents
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

?>
<div class="wp_editor_config_example" style="display: none;">
	<?php wp_editor( '', 'tutor_lesson_editor_config' ); ?>
</div>

<div class="wp_editor_config_example" style="display: none;">
	<?php wp_editor( '', 'tutor_assignment_editor_config' ); ?>
</div>

<div class="course-contents">

	<?php

	$query_topics = new WP_Query(
		array(
			'post_type'      => 'topics',
			'post_parent'    => $course_id,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'posts_per_page' => -1,
		)
	);

	$query_topics = $query_topics->posts;

	// Actually all kind of contents.
	// This keyword '_tutor_course_id_for_lesson' used just to support backward compatibility.
	global $wpdb;
	$unassigned_contents = $wpdb->get_results(
		"SELECT content.* FROM {$wpdb->posts} content 
	        INNER JOIN {$wpdb->postmeta} meta ON content.ID=meta.post_id
        WHERE content.post_parent=0 
            AND meta.meta_key='_tutor_course_id_for_lesson' 
            AND meta.meta_value=" . $course_id
	);

	if ( is_array( $unassigned_contents ) && count( $unassigned_contents ) ) {

		$query_topics[] = (object) array(
			'ID'         => 0,
			'post_title' => __( 'Un-assigned Topic Contents', 'tutor' ),
			'contents'   => $unassigned_contents,
		);
	}

	foreach ( $query_topics as $topic ) {
		$is_topic = $topic->ID > 0;
		?>
		<div id="tutor-topics-<?php echo esc_attr( $topic->ID ); ?>" class="tutor-topics-wrap" data-topic-id="<?php echo esc_attr( $topic->ID ); ?>">
			<div class="tutor-topics-top">
				<div class="tutor-topic-title">
					<span class="<?php echo $is_topic ? 'tutor-icon-hamburger-menu course-move-handle' : 'tutor-icon-warning'; ?> tutor-px-12"></span>
					<span class="topic-inner-title tutor-fs-6 tutor-fw-bold tutor-color-black tutor-d-flex tutor-align-center">
						<?php echo esc_html( stripslashes( $topic->post_title ) ); ?>
					</span>
					<?php if ( $is_topic ) : ?>
						<span class="tutor-iconic-btn" data-tutor-modal-target="tutor-topics-edit-id-<?php echo esc_attr( $topic->ID ); ?>">
							<i class="tutor-icon-edit" area-hidden="true"></i>
						</span>
						<span class="topic-delete-btn tutor-iconic-btn" action-delete-course-topic>
							<i class="tutor-icon-trash-can-line" area-hidden="true"></i>
						</span>
					<?php endif; ?>
					<span class="expand-collapse-wrap">
						<i class="tutor-icon-angle-down" area-hidden="true"></i>
					</span>
				</div>
				<?php
				if ( $is_topic ) {
					tutor_load_template_from_custom_path(
						tutor()->path . '/views/modal/topic-form.php',
						array(
							'modal_title'   => __( 'Update Topic', 'tutor' ),
							'wrapper_id'    => 'tutor-topics-edit-id-' . $topic->ID,
							'topic_id'      => $topic->ID,
							'course_id'     => $course_id,
							'title'         => $topic->post_title,
							'summary'       => $topic->post_content,
							'wrapper_class' => 'tutor-topics-edit-form',
							'button_text'   => __( 'Update Topic', 'tutor' ),
							'button_class'  => 'tutor-save-topic-btn',
						),
						false
					);
				}
				?>
			</div>
			<div class="tutor-topics-body" style="display: <?php echo ( isset( $current_topic_id ) && $current_topic_id == $topic->ID ) ? 'block' : 'none'; ?>;">
				<div class="tutor-lessons">
				<?php
					$post_type       = apply_filters( 'tutor_course_contents_post_types', array( tutor()->lesson_post_type, 'tutor_quiz' ) );
					$course_contents = ! $is_topic ? $topic->contents : get_posts(
						array(
							'post_type'      => $post_type,
							'post_parent'    => $topic->ID,
							'posts_per_page' => -1,
							'orderby'        => 'menu_order',
							'order'          => 'ASC',
						)
					);

					$counter = array(
						'lesson'     => 0,
						'quiz'       => 0,
						'assignment' => 0,
					);

					foreach ( $course_contents as $content ) {

						if ( 'tutor_quiz' === $content->post_type ) {
							$quiz = $content;
							$counter['quiz']++;
							tutor_load_template_from_custom_path(
								tutor()->path . '/views/fragments/quiz-list-single.php',
								array(
									'quiz_id'    => $quiz->ID,
									'topic_id'   => $topic->ID,
									'quiz_title' => __( 'Quiz', 'tutor' ) . ' ' . $counter['quiz'] . ': ' . $quiz->post_title,
								),
								false
							);

						} elseif ( 'tutor_assignments' === $content->post_type ) {
							$counter['assignment']++;
							?>
							<div data-course_content_id="<?php echo esc_attr( $content->ID ); ?>" id="tutor-assignmentø-<?php echo esc_attr( $content->ID ); ?>" class="course-content-item tutor-assignment tutor-assignment-<?php echo esc_attr( $content->ID ); ?>">
								<div class="tutor-course-content-top tutor-d-flex tutor-align-center">
									<span class="tutor-icon-hamburger-menu tutor-cursor-move tutor-px-12"></span>
									<a href="javascript:;" class="<?php echo $is_topic ? 'open-tutor-assignment-modal' : ''; ?>" data-assignment-id="<?php echo esc_attr( $content->ID ); ?>" data-topic-id="<?php echo esc_attr( $topic->ID ); ?>">
										<?php echo esc_html__( 'Assignment', 'tutor' ) . ' ' . esc_html( $counter['assignment'] ) . ': ' . esc_html( $content->post_title ); ?>
									</a>
									<div class="tutor-course-content-top-right-action">
										<?php if ( $is_topic ) : ?>
											<a href="javascript:;" class="open-tutor-assignment-modal tutor-iconic-btn" data-assignment-id="<?php echo esc_attr( $content->ID ); ?>" data-topic-id="<?php echo esc_attr( $topic->ID ); ?>">
												<span class="tutor-icon-edit" area-hidden="true"></span>
											</a>
										<?php endif; ?>
										<a href="javascript:;" class="tutor-delete-lesson-btn tutor-iconic-btn" data-lesson-id="<?php echo esc_attr( $content->ID ); ?>">
											<span class="tutor-icon-trash-can-line" area-hidden="true"></span>
										</a>
									</div>
								</div>
							</div>
							<?php
						} elseif ( 'lesson' == $content->post_type ) {
							$counter['lesson']++;
							?>
							<div data-course_content_id="<?php echo esc_attr( $content->ID ); ?>" id="tutor-lesson-<?php echo esc_attr( $content->ID ); ?>" class="course-content-item tutor-lesson tutor-lesson-<?php echo esc_attr( $content->ID ); ?>">
								<div class="tutor-course-content-top tutor-d-flex tutor-align-center">
									<span class="tutor-icon-hamburger-menu tutor-cursor-move tutor-px-12"></span>
									<a href="javascript:;" class="<?php echo $is_topic ? 'open-tutor-lesson-modal' : ''; ?>" data-lesson-id="<?php echo esc_attr( $content->ID ); ?>" data-topic-id="<?php echo esc_attr( $topic->ID ); ?>">
										<?php echo esc_html__( 'Lesson', 'tutor' ) . ' ' . esc_html( $counter['lesson'] ) . ': ' . esc_html( stripslashes( $content->post_title ) ); ?>
									</a>
									<div class="tutor-course-content-top-right-action">
										<?php if ( $is_topic ) : ?>
											<a href="javascript:;" class="open-tutor-lesson-modal tutor-iconic-btn" data-lesson-id="<?php echo esc_attr( $content->ID ); ?>" data-topic-id="<?php echo esc_attr( $topic->ID ); ?>">
												<span class="tutor-icon-edit" area-hidden="true"></span>
											</a>
										<?php endif; ?>
										<a href="javascript:;" class="tutor-delete-lesson-btn tutor-iconic-btn" data-lesson-id="<?php echo esc_attr( $content->ID ); ?>">
											<span class="tutor-icon-trash-can-line" area-hidden="true"></span>
										</a>
									</div>
								</div>
							</div>
							<?php
						} else {
							! isset( $counter[ $content->post_type ] ) ? $counter[ $content->post_type ] = 0 : 0;
							$counter[ $content->post_type ]++;
							do_action( 'tutor/course/builder/content/' . $content->post_type, $content, $topic, $course_id, $counter[ $content->post_type ] );
						}
					}
					?>
				</div>

				<?php if ( $is_topic ) : ?>
					<div class="tutor_add_content_wrap tutor_add_content_wrap_btn_sm" data-topic_id="<?php echo esc_attr( $topic->ID ); ?>">
						<?php do_action( 'tutor_course_builder_before_btn_group', $topic->ID ); ?>

						<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm open-tutor-lesson-modal create-lesson-in-topic-btn" data-topic-id="<?php echo esc_attr( $topic->ID ); ?>" data-lesson-id="0" >
							<i class="tutor-icon-plus-square tutor-mr-8"></i>
							<?php esc_html_e( 'Lesson', 'tutor' ); ?>
						</button>

						<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-add-quiz-btn" data-topic-id="<?php echo esc_attr( $topic->ID ); ?>">
							<i class="tutor-icon-plus-square tutor-mr-8"></i>
							<?php esc_html_e( 'Quiz', 'tutor' ); ?>
						</button>

						<?php do_action( 'tutor_course_builder_after_btn_group', $topic->ID, $course_id ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
	?>
	<input type="hidden" id="tutor_topics_lessons_sorting" name="tutor_topics_lessons_sorting" value="" />
</div>
