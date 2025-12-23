<?php
/**
 * My Courses Page
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\EmptyState;
use Tutor\Components\Nav;
use Tutor\Components\Pagination;
use Tutor\Components\SearchFilter;
use Tutor\Components\Sorting;
use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Models\CourseModel;

// Get the user ID and active tab.
$current_user_id                     = get_current_user_id();
! isset( $active_tab ) ? $active_tab = 'my-courses' : 0;

// Map required course status according to page.
$status_map = array(
	'my-courses'                  => CourseModel::STATUS_PUBLISH,
	'my-courses/draft-courses'    => CourseModel::STATUS_DRAFT,
	'my-courses/pending-courses'  => CourseModel::STATUS_PENDING,
	'my-courses/schedule-courses' => CourseModel::STATUS_FUTURE,
);

// Set currently required course status fo rcurrent tab.
$status    = isset( $status_map[ $active_tab ] ) ? $status_map[ $active_tab ] : CourseModel::STATUS_PUBLISH;
$post_type = apply_filters( 'tutor_dashboard_course_list_post_type', array( tutor()->course_post_type ) );

$order  = Input::get( 'order', 'DESC' );
$search = Input::get( 'search', '' );

// Get counts for course tabs.
$count_map = array(
	'publish' => CourseModel::get_courses_by_instructor( $current_user_id, CourseModel::STATUS_PUBLISH, 0, 0, true, $post_type, $search ),
	'pending' => CourseModel::get_courses_by_instructor( $current_user_id, CourseModel::STATUS_PENDING, 0, 0, true, $post_type, $search ),
	'draft'   => CourseModel::get_courses_by_instructor( $current_user_id, CourseModel::STATUS_DRAFT, 0, 0, true, $post_type, $search ),
	'future'  => CourseModel::get_courses_by_instructor( $current_user_id, CourseModel::STATUS_FUTURE, 0, 0, true, $post_type, $search ),
);

$per_page           = tutor_utils()->get_option( 'courses_per_page', 10 );
$current_page       = Input::get( 'current_page', 1, Input::TYPE_INT );
$offset             = $per_page * ( $current_page - 1 );
$results            = CourseModel::get_courses_by_instructor( $current_user_id, $status, $offset, $per_page, false, $post_type, $search, $order );
$show_course_delete = true;
$post_type_query    = Input::get( 'type', '' );
$post_type_args     = $post_type_query ? array( 'type' => $post_type_query ) : array();

$current_url = add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( $active_tab ) );

$nav_items = array(
	array(
		'type'    => 'dropdown',
		'active'  => true,
		'options' => array(
			array(
				'label'  => __( 'Published', 'tutor' ),
				'count'  => $count_map['publish'] ?? 0,
				'url'    => add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'my-courses' ) ),
				'active' => 'my-courses' === $active_tab,
			),
			array(
				'label'  => __( 'Pending', 'tutor' ),
				'count'  => $count_map['pending'] ?? 0,
				'url'    => add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'my-courses/pending-courses' ) ),
				'active' => 'my-courses/pending-courses' === $active_tab,
			),
			array(
				'label'  => __( 'Draft', 'tutor' ),
				'count'  => $count_map['draft'] ?? 0,
				'url'    => add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'my-courses/draft-courses' ) ),
				'active' => 'my-courses/draft-courses' === $active_tab,
			),
			array(
				'label'  => __( 'Schedule', 'tutor' ),
				'count'  => $count_map['future'] ?? 0,
				'url'    => add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'my-courses/schedule-courses' ) ),
				'active' => 'my-courses/schedule-courses' === $active_tab,
			),
		),
	),
);

if ( ! current_user_can( 'administrator' ) && ! tutor_utils()->get_option( 'instructor_can_delete_course' ) ) {
	$show_course_delete = false;
}
?>

<div class="tutor-dashboard-my-courses" x-data="tutorMyCourses()">
	<div class="tutor-surface-l1 tutor-border tutor-rounded-2xl">
		<div class="tutor-flex tutor-flex-wrap tutor-gap-4 tutor-items-center tutor-justify-between tutor-p-6 tutor-sm-p-5 tutor-border-b">
			<?php Nav::make()->variant( Variant::PRIMARY )->size( Size::SMALL )->items( $nav_items )->render(); ?>
			<div class="tutor-flex tutor-items-center tutor-gap-5">
				<?php do_action( 'tutor_course_create_button' ); ?>
				<button 
					class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-gap-2"
					:class="createMutation.isPending ? 'tutor-btn-loading' : ''"
					@click="handleCreateCourse()"
					:disabled="createMutation.isPending"
				>
					<?php tutor_utils()->render_svg_icon( Icon::ADD ); ?>
					<?php esc_html_e( 'New Course', 'tutor' ); ?>
				</button>
			</div>
		</div>
		<div class="tutor-flex tutor-flex-wrap tutor-gap-4 tutor-items-center tutor-justify-between tutor-py-5 tutor-px-6 tutor-sm-p-5 tutor-border-b">
			<?php
			$hidden_inputs = array();
			if ( ! empty( $post_type_query ) ) {
				$hidden_inputs['type'] = $post_type_query;
			}

			SearchFilter::make()
				->form_id( 'tutor-my-courses-search-form' )
				->placeholder( __( 'Search courses...', 'tutor' ) )
				->size( Size::SMALL )
				->action( $current_url )
				->hidden_inputs( $hidden_inputs )
				->render();
			?>
			<div class="tutor-flex tutor-items-center tutor-gap-3">
				<?php do_action( 'tutor_dashboard_my_courses_filter' ); ?>
				<?php Sorting::make()->order( $order )->render(); ?>
			</div>
		</div>

		<?php if ( empty( $results ) ) : ?>
			<?php EmptyState::make()->title( 'No Courses Found' )->render(); ?>
		<?php else : ?>
		<div class="tutor-p-6 tutor-sm-p-5 tutor-grid tutor-grid-cols-3 tutor-lg-grid-cols-2 tutor-md-grid-cols-3 tutor-sm-grid-cols-2 tutor-xs-grid-cols-1 tutor-gap-4">
			<?php
			global $post;
			$tutor_nonce_value = wp_create_nonce( tutor()->nonce_action );
			foreach ( $results as $post ) :
				setup_postdata( $post );
				$tutor_course_img   = get_tutor_course_thumbnail_src();
				$course_duration    = tutor_utils()->get_course_duration( $post->ID, false );
				$course_students    = tutor_utils()->count_enrolled_users_by_course();
				$is_main_instructor = CourseModel::is_main_instructor( $post->ID );
				$course_edit_link   = apply_filters( 'tutor_dashboard_course_list_edit_link', tutor_utils()->course_edit_link( $post->ID, tutor()->has_pro ? 'frontend' : 'backend' ), $post );
				?>
				<div class="tutor-my-courses-card">
					<div class="tutor-my-courses-card-body">
						<div class="tutor-my-courses-card-thumb">
							<?php do_action( 'tutor_my_courses_before_thumbnail', $post->ID ); ?>
							<img src="<?php echo empty( $tutor_course_img ) ? esc_url( $placeholder_img ) : esc_url( $tutor_course_img ); ?>" alt="<?php the_title(); ?>" loading="lazy">
							<div class="tutor-my-courses-card-actions">
								<a href="<?php echo esc_url( $course_edit_link ); ?>" class="tutor-btn tutor-btn-secondary tutor-btn-x-small tutor-btn-icon">
									<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
								</a>
								<a href="<?php echo esc_url( get_the_permalink() ); ?>" class="tutor-btn tutor-btn-secondary tutor-btn-x-small tutor-btn-icon">
									<?php tutor_utils()->render_svg_icon( Icon::EYE_LINE ); ?>
								</a>
							</div>
						</div>
						<div class="tutor-my-courses-card-content">
							<?php do_action( 'tutor_my_courses_before_meta', get_the_ID() ); ?>

							<div class="tutor-tiny tutor-text-secondary tutor-flex tutor-items-center tutor-gap-2 tutor-mb-2">
								<?php tutor_utils()->render_svg_icon( Icon::RELOAD_2, 14, 14, array( 'class' => 'tutor-icon-brand' ) ); ?>
								<?php echo esc_html( get_the_date() ); ?> - <?php echo esc_html( get_the_time() ); ?>
							</div>

							<a href="<?php echo esc_url( $course_edit_link ); ?>" class="tutor-p2 tutor-font-medium tutor-block">
								<?php the_title(); ?>
							</a>

							<?php if ( ! empty( $course_duration ) || ! empty( $course_students ) ) : ?>
							<div class="tutor-tiny tutor-text-subdued tutor-flex tutor-items-center tutor-gap-6 tutor-mt-4">
								<?php if ( ! empty( $course_students ) ) : ?>
								<div class="tutor-flex tutor-items-center tutor-gap-2">
									<?php tutor_utils()->render_svg_icon( Icon::PASSED, 14, 14 ); ?>
									<?php echo esc_html( $course_students ); ?>
								</div>
								<?php endif; ?>

								<?php if ( ! empty( $course_duration ) ) : ?>
								<div class="tutor-flex tutor-items-center tutor-gap-2">
									<?php tutor_utils()->render_svg_icon( Icon::TIME, 14, 14 ); ?>
									<?php echo esc_html( $course_duration ); ?>
								</div>
								<?php endif; ?>
							</div>
							<?php endif; ?>
						</div>
					</div>
					<div class="tutor-my-courses-card-footer">
						<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-overflow-hidden">
							<?php if ( apply_filters( 'tutor_membership_only_mode', false ) ) : ?>
							<span class="tutor-font-medium tutor-text-subdued">
								<?php esc_html_e( 'Plan:', 'tutor' ); ?>
							</span>
							<?php endif ?>
							<?php
							if ( null === tutor_utils()->get_course_price() ) {
								esc_html_e( 'Free', 'tutor' );
							} else {
								echo wp_kses_post( tutor_utils()->get_course_price() );
							}
							?>
						</div>
						<div
							class="tutor-my-courses-card-footer-actions"
							x-data="tutorPopover({
								placement: 'top-end',
								offset: 4,
								onShow: () => { $el.classList.add('tutor-popover-open') },
								onHide: () => { $el.classList.remove('tutor-popover-open') }
							})"
						>
							<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
								<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ); ?>
							</button>

							<div 
								x-ref="content"
								x-show="open"
								x-cloak
								@click.outside="handleClickOutside()"
								class="tutor-popover"
							>
								<div class="tutor-popover-menu" style="min-width: 182px;">
									<!-- Submit Action -->
									<?php if ( tutor()->has_pro && in_array( $post->post_status, array( CourseModel::STATUS_DRAFT ), true ) ) : ?>
										<?php
										$params = http_build_query(
											array(
												'tutor_action' => 'update_course_status',
												'status' => CourseModel::STATUS_PENDING,
												'course_id' => $post->ID,
												tutor()->nonce => $tutor_nonce_value,
											)
										);
										?>
									<a href="?<?php echo esc_attr( $params ); ?>" class="tutor-popover-menu-item">
										<?php tutor_utils()->render_svg_icon( Icon::PUBLISH, 20, 20 ); ?>
										<span>
											<?php
											$can_publish_course = current_user_can( 'administrator' ) || (bool) tutor_utils()->get_option( 'instructor_can_publish_course' );
											if ( $can_publish_course ) {
												esc_html_e( 'Publish', 'tutor' );
											} else {
												esc_html_e( 'Submit', 'tutor' );
											}
											?>
										</span>
									</a>
									<?php endif; ?>
									<!-- # Submit Action -->

									<!-- Cancel Submission -->
									<?php if ( tutor()->has_pro && in_array( $post->post_status, array( CourseModel::STATUS_PENDING ), true ) ) : ?>
										<?php
										$params = http_build_query(
											array(
												'tutor_action' => 'update_course_status',
												'status' => CourseModel::STATUS_DRAFT,
												'course_id' => $post->ID,
												tutor()->nonce => $tutor_nonce_value,
											)
										);
										?>
									<a href="?<?php echo esc_attr( $params ); ?>" class="tutor-popover-menu-item">
										<?php tutor_utils()->render_svg_icon( Icon::CROSS_2, 20, 20 ); ?>
										<?php esc_html_e( 'Cancel Submission', 'tutor' ); ?>
									</a>
									<?php endif; ?>
									<!-- # Cancel Submission -->

									<!-- Edit Link -->
									<a href="<?php echo esc_url( $course_edit_link ); ?>" class="tutor-popover-menu-item tutor-hidden tutor-sm-flex">
										<?php tutor_utils()->render_svg_icon( Icon::EDIT_2, 20, 20 ); ?>
										<?php esc_html_e( 'Edit', 'tutor' ); ?>
									</a>
									<!-- Edit Link -->

									<!-- View Link -->
									<a href="<?php echo esc_url( get_the_permalink() ); ?>" class="tutor-popover-menu-item tutor-hidden tutor-sm-flex">
										<?php tutor_utils()->render_svg_icon( Icon::EYE_LINE, 20, 20 ); ?>
										<?php esc_html_e( 'Overview', 'tutor' ); ?>
									</a>
									<!-- View Link -->

									<!-- Duplicate Action -->
									<?php if ( tutor()->has_pro && in_array( $post->post_status, array( CourseModel::STATUS_PUBLISH, CourseModel::STATUS_PENDING, CourseModel::STATUS_DRAFT, CourseModel::STATUS_FUTURE ), true ) ) : ?>
										<?php
										$params = http_build_query(
											array(
												'tutor_action' => 'duplicate_course',
												'course_id' => $post->ID,
											)
										);
										?>
									<a href="?<?php echo esc_attr( $params ); ?>" class="tutor-popover-menu-item">
										<?php tutor_utils()->render_svg_icon( Icon::COPY_2, 20, 20 ); ?>
										<?php esc_html_e( 'Duplicate', 'tutor' ); ?>
									</a>
									<?php endif; ?>
									<!-- # Duplicate Action -->

									<!-- Move to Draft Action -->
									<?php if ( tutor()->has_pro && in_array( $post->post_status, array( CourseModel::STATUS_PUBLISH, CourseModel::STATUS_FUTURE ), true ) ) : ?>
										<?php
										$params = http_build_query(
											array(
												'tutor_action' => 'update_course_status',
												'status' => CourseModel::STATUS_DRAFT,
												'course_id' => $post->ID,
												tutor()->nonce => $tutor_nonce_value,
											)
										);
										?>
									<a href="?<?php echo esc_attr( $params ); ?>" class="tutor-popover-menu-item">
										<?php tutor_utils()->render_svg_icon( Icon::MOVE, 20, 20 ); ?>
										<?php esc_html_e( 'Move to Draft', 'tutor' ); ?>
									</a>
									<?php endif; ?>
									<!-- # Move to Draft Action -->

									<!-- Delete Action -->
									<?php if ( $show_course_delete && $is_main_instructor && in_array( $post->post_status, array( CourseModel::STATUS_PUBLISH, CourseModel::STATUS_DRAFT, CourseModel::STATUS_FUTURE ), true ) ) : ?>
										<button 
											class="tutor-popover-menu-item tutor-border-t"
											@click="hide(); TutorCore.modal.showModal('tutor-course-delete-modal', { courseId: <?php echo esc_html( $post->ID ); ?> });"
										>
											<?php tutor_utils()->render_svg_icon( Icon::DELETE_2, 20, 20 ); ?>
											<?php esc_html_e( 'Delete', 'tutor' ); ?>
										</button>
									<?php endif; ?>
									<!-- # Delete Action -->
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
			endforeach;
			wp_reset_postdata();
			?>
		</div>
		<?php endif; ?>

		<?php if ( $count_map[ $status ] > $per_page ) : ?>
		<div class="tutor-p-6">
			<?php
			Pagination::make()
				->current( $current_page )
				->total( $count_map[ $status ] )
				->limit( $per_page )
				->render();
			?>
		</div>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $results ) && $show_course_delete ) : ?>
	<div x-data="tutorModal({ id: 'tutor-course-delete-modal' })" x-cloak>
		<template x-teleport="body">
			<div x-bind="getModalBindings()">
				<div x-bind="getBackdropBindings()"></div>
				<div x-bind="getModalContentBindings()" style="max-width: 426px;">
					<button x-data="tutorIcon({ name: 'cross', width: 16, height: 16})", x-bind="getCloseButtonBindings()"></button>

					<div class="tutor-p-7 tutor-pt-10 tutor-flex tutor-flex-column tutor-items-center">
						<?php tutor_utils()->render_svg_icon( Icon::BIN, 100, 100 ); ?>
						<h5 class="tutor-h5 tutor-font-medium tutor-mt-8">
							<?php esc_html_e( 'Delete This Course?', 'tutor' ); ?>
						</h5>
						<p class="tutor-p3 tutor-text-secondary tutor-mt-2 tutor-text-center">
							<?php esc_html_e( 'Are you sure you want to delete this course permanently from the site? Please confirm your choice.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-modal-footer">
						<button class="tutor-btn tutor-btn-ghost tutor-btn-small" @click="TutorCore.modal.closeModal('tutor-course-delete-modal')">
							<?php esc_html_e( 'Cancel', 'tutor' ); ?>
						</button>
						<button 
							class="tutor-btn tutor-btn-destructive tutor-btn-small"
							:class="deleteMutation?.isPending ? 'tutor-btn-loading' : ''"
							@click="handleDeleteCourse(payload?.courseId)"
							:disabled="deleteMutation?.isPending"
						>
							<?php esc_html_e( 'Yes, Delete This', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>
		</template>
	</div>
	<?php endif; ?>
</div>
