<?php
/**
 * Course topics
 *
 * @package Tutor\Views
 * @subpackage Tutor\MetaBox
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

?>
<div id="tutor-course-content-builder-root">
	<?php $course_id = get_the_ID(); ?>
	<div id="tutor-course-content-wrap">
		<?php require tutor()->path . 'views/metabox/course-contents.php'; ?>
	</div>

	<div class="new-topic-btn-wrap">
		<button data-tutor-modal-target="tutor-modal-add-topic" class="create_new_topic_btn tutor-btn tutor-btn-primary tutor-btn-md tutor-mt-16"> 
			<i class="tutor-icon-plus-square tutor-mr-12"></i> <?php esc_html_e( 'Add new topic', 'tutor' ); ?>
		</button>
	</div>

	<?php
		// Topic modal for new topic creation.
		tutor_load_template_from_custom_path(
			tutor()->path . '/views/modal/topic-form.php',
			array(
				'modal_title'   => __( 'Add Topic', 'tutor' ),
				'wrapper_id'    => 'tutor-modal-add-topic',
				'topic_id'      => null,
				'course_id'     => $course_id,
				'wrapper_class' => '',
				'button_text'   => __( 'Add Topic', 'tutor' ),
				'button_class'  => 'tutor-save-topic-btn',
			),
			false
		);
		?>

	<div class="tutor-modal tutor-modal-scrollable tutor-quiz-builder-modal-wrap<?php echo is_admin() ? ' tutor-admin-design-init' : ''; ?>" data-target="quiz-builder-tab-quiz-info" style="z-index:1001;">
		<div class="tutor-modal-overlay"></div>
		<div class="tutor-modal-window">
			<div class="tutor-modal-content">
				<div class="tutor-px-32 tutor-py-24 tutor-bg-white">
					<div class="tutor-d-flex tutor-align-center tutor-justify-between">
						<div class="tutor-modal-title">
							<?php esc_html_e( 'Quiz', 'tutor' ); ?>
						</div>
						<button class="tutor-modal-close tutor-iconic-btn" data-tutor-modal-close>
							<span class="tutor-icon-times" area-hidden="true"></span>
						</button>
					</div>

					<div class="tutor-mt-32">
						<div class="tutor-modal-steps">
							<ul>
								<li class="tutor-is-completed" data-tab="quiz-builder-tab-quiz-info">
									<span><?php esc_html_e( 'Quiz Info', 'tutor' ); ?></span>
									<span class="tutor-modal-step-btn">1</span>
								</li>
								<li data-tab="quiz-builder-tab-questions">
									<span><?php esc_html_e( 'Question', 'tutor' ); ?></span>
									<span class="tutor-modal-step-btn">2</span>
								</li>
								<li data-tab="quiz-builder-tab-settings">
									<span><?php esc_html_e( 'Settings', 'tutor' ); ?></span>
									<span class="tutor-modal-step-btn">3</span>
								</li>
							</ul>
						</div>
					</div>
				</div>

				<div class="tutor-modal-body tutor-modal-container"></div>

				<div class="tutor-modal-footer">
					<div>
						<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
							<?php esc_html_e( 'Cancel', 'tutor' ); ?>
						</button>
					</div>

					<div>
						<button type="button" data-action="back" class="tutor-btn tutor-btn-outline-primary tutor-mr-12" action-tutor-prev-quiz>
							<?php esc_html_e( 'Back', 'tutor' ); ?>
						</button>
						<button type="button" data-action="next" class="tutor-btn tutor-btn-primary" action-tutor-next-quiz>
							<?php esc_html_e( 'Save & Next', 'tutor' ); ?>
						</button>
						<button class="tutor-btn tutor-btn-primary quiz-modal-question-save-btn" action-tutor-save-quiz>
							<?php esc_html_e( 'Add to Questions', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-modal tutor-modal-scrollable tutor-lesson-modal-wrap<?php echo is_admin() ? ' tutor-admin-design-init' : ''; ?>">
		<div class="tutor-modal-overlay"></div>
		<div class="tutor-modal-window">
			<div class="tutor-modal-content">
				<div class="tutor-modal-header">
					<div class="tutor-modal-title">
						<?php esc_html_e( 'Lesson', 'tutor' ); ?>
					</div>
					<button data-tutor-modal-close class="tutor-iconic-btn tutor-modal-close">
						<span class="tutor-icon-times" area-hidden="true"></span>
					</button>
				</div>

				<div class="tutor-modal-body tutor-modal-container"></div>

				<div class="tutor-modal-footer">
					<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>

					<button type="button" class="tutor-btn tutor-btn-primary update_lesson_modal_btn">
						<?php esc_html_e( 'Update Lesson', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-modal tutor-modal-scrollable tutor-assignment-modal-wrap<?php echo is_admin() ? ' tutor-admin-design-init' : ''; ?>">
		<div class="tutor-modal-overlay"></div>
		<div class="tutor-modal-window">
			<div class="tutor-modal-content">
				<div class="tutor-modal-header">
					<div class="tutor-modal-title">
						<?php esc_html_e( 'Assignment', 'tutor' ); ?>
					</div>
					<button class="tutor-iconic-btn tutor-modal-close" data-tutor-modal-close>
						<span class="tutor-icon-times" area-hidden="true"></span>
					</button>
				</div>

				<div class="tutor-modal-body tutor-modal-container"></div>

				<div class="tutor-modal-footer">
					<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>
					<button type="button" class="tutor-btn tutor-btn-primary update_assignment_modal_btn">
						<?php esc_html_e( 'Update Assignment', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<?php do_action( 'course-topic/after/modal_wrappers' ); ?>
</div>
