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

// Get counts for course tabs.
$count_map = array(
	'publish' => CourseModel::get_courses_by_instructor( $current_user_id, CourseModel::STATUS_PUBLISH, 0, 0, true, $post_type ),
	'pending' => CourseModel::get_courses_by_instructor( $current_user_id, CourseModel::STATUS_PENDING, 0, 0, true, $post_type ),
	'draft'   => CourseModel::get_courses_by_instructor( $current_user_id, CourseModel::STATUS_DRAFT, 0, 0, true, $post_type ),
	'future'  => CourseModel::get_courses_by_instructor( $current_user_id, CourseModel::STATUS_FUTURE, 0, 0, true, $post_type ),
);

$course_archive_arg = isset( $GLOBALS['tutor_course_archive_arg'] ) ? $GLOBALS['tutor_course_archive_arg']['column_per_row'] : null;
$courseCols         = null === $course_archive_arg ? tutor_utils()->get_option( 'courses_col_per_row', 4 ) : $course_archive_arg;
$per_page           = tutor_utils()->get_option( 'courses_per_page', 10 );
$paged              = Input::get( 'current_page', 1, Input::TYPE_INT );
$offset             = $per_page * ( $paged - 1 );
$results            = CourseModel::get_courses_by_instructor( $current_user_id, $status, $offset, $per_page, false, $post_type );
$show_course_delete = true;
$post_type_query    = Input::get( 'type', '' );
$post_type_args     = $post_type_query ? array( 'type' => $post_type_query ) : array();

$tabs = array(
	'publish' => array(
		'title' => __( 'Publish', 'tutor' ),
		'link'  => 'my-courses',
	),
	'pending' => array(
		'title' => __( 'Pending', 'tutor' ),
		'link'  => 'my-courses/pending-courses',
	),
	'draft'   => array(
		'title' => __( 'Draft', 'tutor' ),
		'link'  => 'my-courses/draft-courses',
	),
	'future'  => array(
		'title' => __( 'Schedule', 'tutor' ),
		'link'  => 'my-courses/schedule-courses',
	),
);

$nav_items = array(
	array(
		'type'    => 'dropdown',
		'active'  => true,
		'options' => array(
			array(
				'label'  => __( 'Published', 'tutor' ) . ' (' . ( $count_map['publish'] ?? 0 ) . ')',
				'url'    => add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'my-courses' ) ),
				'active' => 'my-courses' === $active_tab,
			),
			array(
				'label'  => __( 'Pending', 'tutor' ) . ' (' . ( $count_map['pending'] ?? 0 ) . ')',
				'url'    => add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'my-courses/pending-courses' ) ),
				'active' => 'my-courses/pending-courses' === $active_tab,
			),
			array(
				'label'  => __( 'Draft', 'tutor' ) . ' (' . ( $count_map['draft'] ?? 0 ) . ')',
				'url'    => add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( 'my-courses/draft-courses' ) ),
				'active' => 'my-courses/draft-courses' === $active_tab,
			),
			array(
				'label'  => __( 'Schedule', 'tutor' ) . ' (' . ( $count_map['future'] ?? 0 ) . ')',
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

<div class="tutor-dashboard-my-courses">
	<div class="tutor-surface-l1 tutor-border tutor-rounded-2xl">
		<div class="tutor-flex tutor-items-center tutor-justify-between tutor-p-6 tutor-border-b">
			<?php
			tutor_load_template(
				'core-components.nav',
				array(
					'items'   => $nav_items,
					'size'    => 'sm',
					'variant' => 'primary',
				)
			);
			?>
			<div class="tutor-flex tutor-items-center tutor-gap-5">
				<?php do_action( 'tutor_course_create_button' ); ?>
				<button class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-gap-2 tutor-create-new-course tutor-dashboard-create-course">
					<!-- @TODO: Need to add API integration -->
					<?php tutor_utils()->render_svg_icon( Icon::ADD ); ?>
					<?php esc_html_e( 'New Course', 'tutor' ); ?>
				</button>
			</div>
		</div>
		<div class="tutor-flex tutor-items-center tutor-justify-between tutor-py-5 tutor-px-6 tutor-border-b">
			<div>
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<div class="tutor-input-content tutor-input-content-left">
							<?php
							tutor_utils()->render_svg_icon(
								Icon::SEARCH_2,
								20,
								20,
								array( 'class' => 'tutor-icon-idle' )
							)
							?>
						</div>
						<input 
							type="text"
							placeholder="Search courses..."
							class="tutor-input tutor-input-sm tutor-input-content-left"
							style="width: 280px;"
						>
					</div>
				</div>
			</div>
			<div class="tutor-flex tutor-items-center tutor-gap-3">
				<?php do_action( 'tutor_dashboard_my_courses_filter' ); ?>
				<div
					x-data="tutorPopover({
						placement: 'bottom-end',
						offset: 4,
					})"
				>
					<button type="button" x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-outline tutor-btn-x-small tutor-btn-icon">
						<?php tutor_utils()->render_svg_icon( Icon::STEPPER ); ?>
					</button>
					<div 
						x-ref="content"
						x-show="open"
						x-cloak
						@click.outside="handleClickOutside()"
						class="tutor-popover"
					>
						<div class="tutor-popover-menu" style="min-width: 108px;">
							<a href="#" class="tutor-popover-menu-item">
								<?php esc_html_e( 'Newest First', 'tutor' ); ?>
							</a>
							<a href="#" class="tutor-popover-menu-item">
								<?php esc_html_e( 'Oldest First', 'tutor' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="tutor-p-6 tutor-grid tutor-grid-cols-3 tutor-gap-4">
			<?php
			global $post;
			$tutor_nonce_value = wp_create_nonce( tutor()->nonce_action );
			foreach ( $results as $post ) :
				setup_postdata( $post );

				$tutor_course_img   = get_tutor_course_thumbnail_src();
				$id_string_delete   = 'tutor_my_courses_delete_' . $post->ID;
				$row_id             = 'tutor-dashboard-my-course-' . $post->ID;
				$course_duration    = get_tutor_course_duration_context( $post->ID, true );
				$course_students    = tutor_utils()->count_enrolled_users_by_course();
				$is_main_instructor = CourseModel::is_main_instructor( $post->ID );
				$course_edit_link   = apply_filters( 'tutor_dashboard_course_list_edit_link', tutor_utils()->course_edit_link( $post->ID, tutor()->has_pro ? 'frontend' : 'backend' ), $post );
				?>
				<div class="tutor-my-courses-card">
					<div class="tutor-my-courses-card-body">
						<div class="tutor-my-courses-card-thumb">
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
									3h 30m
									<?php // echo esc_html( $course_duration ); ?>
								</div>
								<?php endif; ?>
							</div>
							<?php endif; ?>
						</div>
					</div>
					<div class="tutor-my-courses-card-footer">
						<div class="tutor-flex tutor-items-center tutor-gap-2">
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
											class="tutor-popover-menu-item"
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

		<div class="tutor-p-6">
			<nav class="tutor-pagination" role="navigation" aria-label="Pagination Navigation">
				<span class="tutor-pagination-info" aria-live="polite">
					Page <span class="tutor-pagination-current">3</span> of <span class="tutor-pagination-total">12</span>
				</span>

				<ul class="tutor-pagination-list">
					<li>
						<a class="tutor-pagination-item tutor-pagination-item-prev" aria-label="Previous page" aria-disabled="true">
							<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_LEFT_2 ); ?>
						</a>
					</li>

					<li><a class="tutor-pagination-item">1</a></li>
					<li>
						<a class="tutor-pagination-item tutor-pagination-item-active" aria-current="page">2</a>
					</li>
					<li><a class="tutor-pagination-item">3</a></li>
					<li><span class="tutor-pagination-ellipsis" aria-hidden="true">…</span></li>
					<li><a class="tutor-pagination-item">6</a></li>
					<li><a class="tutor-pagination-item">7</a></li>

					<li>
						<a class="tutor-pagination-item tutor-pagination-item-next" aria-label="Next page">
							<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_RIGHT_2 ); ?>
						</a>
					</li>
				</ul>
			</nav>
		</div>
	</div>

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
						<button class="tutor-btn tutor-btn-destructive tutor-btn-small" x-bind:data-course-id="payload?.courseId">
							<?php esc_html_e( 'Yes, Delete This', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>
		</template>
	</div>




	<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16">
		<?php esc_html_e( 'My Courses', 'tutor' ); ?>
	</div>
	
	<div class="tutor-dashboard-content-inner">
		<div class="tutor-mb-32 tutor-w-100">
			<ul class="tutor-nav">
				<?php foreach ( $tabs as $key => $tab ) : ?>
				<li class="tutor-nav-item">
					<a class="tutor-nav-link<?php echo esc_attr( $tab['link'] === $active_tab ? ' is-active' : '' ); ?>" href="<?php echo esc_url( add_query_arg( $post_type_args, tutor_utils()->get_tutor_dashboard_page_permalink( $tab['link'] ) ) ); ?>">
						<?php echo esc_html( $tab['title'] ); ?> <?php echo esc_html( '(' . $count_map[ $key ] . ')' ); ?>
					</a>
				</li>
				<?php endforeach; ?>
				<?php do_action( 'tutor_dashboard_my_courses_filter' ); ?>
			</ul>
			
		</div>
	
		<!-- Course list -->
		<?php
		$placeholder_img = tutor()->url . 'assets/images/placeholder.svg';

		if ( ! is_array( $results ) || ( ! count( $results ) && 1 == $paged ) ) {
			tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() );
		} else {
			?>
			<div class="tutor-grid tutor-grid-3">
				<?php
				global $post;
				$tutor_nonce_value = wp_create_nonce( tutor()->nonce_action );
				foreach ( $results as $post ) :
					setup_postdata( $post );

					$avg_rating         = tutor_utils()->get_course_rating()->rating_avg;
					$tutor_course_img   = get_tutor_course_thumbnail_src();
					$id_string_delete   = 'tutor_my_courses_delete_' . $post->ID;
					$row_id             = 'tutor-dashboard-my-course-' . $post->ID;
					$course_duration    = get_tutor_course_duration_context( $post->ID, true );
					$course_students    = tutor_utils()->count_enrolled_users_by_course();
					$is_main_instructor = CourseModel::is_main_instructor( $post->ID );
					$course_edit_link   = apply_filters( 'tutor_dashboard_course_list_edit_link', tutor_utils()->course_edit_link( $post->ID, tutor()->has_pro ? 'frontend' : 'backend' ), $post );
					?>

					<div id="<?php echo esc_attr( $row_id ); ?>" class="tutor-card tutor-course-card tutor-mycourse-<?php the_ID(); ?>">
						<a href="<?php echo esc_url( get_the_permalink() ); ?>" class="tutor-d-block">
							<div class="tutor-ratio tutor-ratio-16x9">
								<img class="tutor-card-image-top" src="<?php echo empty( $tutor_course_img ) ? esc_url( $placeholder_img ) : esc_url( $tutor_course_img ); ?>" alt="<?php the_title(); ?>" loading="lazy">
							</div>
						</a>

						<?php if ( false === $is_main_instructor ) : ?>
						<div class="tutor-course-co-author-badge"><?php esc_html_e( 'Co-author', 'tutor' ); ?></div>
						<?php endif; ?>
						<div class="tutor-card-body">
							<?php do_action( 'tutor_my_courses_before_meta', get_the_ID() ); ?>
							<div class="tutor-meta tutor-mb-8">
								<span>
									<?php echo esc_html( get_the_date() ); ?> <?php echo esc_html( get_the_time() ); ?>
								</span>
							</div>
	
							<div class="tutor-course-name tutor-fs-6 tutor-fw-bold tutor-mb-16">
								<a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php the_title(); ?></a>
							</div>
	
							<?php if ( ! empty( $course_duration ) || ! empty( $course_students ) ) : ?>
							<div class="tutor-meta tutor-mt-16">
								<?php if ( ! empty( $course_duration ) ) : ?>
									<div>
										<span class="tutor-icon-clock-line tutor-meta-icon" area-hidden="true"></span>
										<span class="tutor-meta-value">
										<?php
										echo wp_kses(
											stripslashes( $course_duration ),
											array(
												'span' => array( 'class' => true ),
											)
										);
										?>
																		</span>
									</div>
								<?php endif; ?>
	
								<?php if ( ! empty( $course_students ) ) : ?>
									<div>
										<span class="tutor-icon-user-line tutor-meta-icon" area-hidden="true"></span>
										<span class="tutor-meta-value">
										<?php
										echo wp_kses(
											stripslashes( $course_students ),
											array(
												'span' => array( 'class' => true ),
											)
										);
										?>
																		</span>
									</div>
								<?php endif; ?>
							</div>
							<?php endif; ?>
						</div>

						<div class="tutor-card-footer">
							<div class="tutor-d-flex tutor-align-center tutor-justify-between">
								<div class="tutor-d-flex tutor-align-center">
									<span class="tutor-fs-7 tutor-fw-medium tutor-color-muted tutor-mr-4">
										<?php
										$membership_only_mode = apply_filters( 'tutor_membership_only_mode', false );
										echo esc_html( $membership_only_mode ? __( 'Plan:', 'tutor' ) : '' );
										?>
									</span>
									<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
										<?php
										$price = tutor_utils()->get_course_price();
										if ( null === $price ) {
											esc_html_e( 'Free', 'tutor' );
										} else {
											echo wp_kses_post( tutor_utils()->get_course_price() );
										}
										?>
									</span>
								</div>
								<div class="tutor-iconic-btn-group tutor-mr-n8">
									<a href="<?php echo esc_url( $course_edit_link ); ?>" class="tutor-iconic-btn tutor-my-course-edit">
										<i class="tutor-icon-edit" area-hidden="true"></i>
									</a>
									<div class="tutor-dropdown-parent">
										<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
											<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
										</button>
										<div id="table-dashboard-course-list-<?php echo esc_attr( $post->ID ); ?>" class="tutor-dropdown tutor-dropdown-dark tutor-text-left">

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
											<a class="tutor-dropdown-item" href="?<?php echo esc_attr( $params ); ?>">
												<i class="tutor-icon-share tutor-mr-8" area-hidden="true"></i>
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

											<!-- Duplicate Action -->
											<?php if ( tutor()->has_pro && in_array( $post->post_status, array( CourseModel::STATUS_PUBLISH, CourseModel::STATUS_PENDING, CourseModel::STATUS_DRAFT, CourseModel::STATUS_FUTURE ) ) ) : ?>
												<?php
												$params = http_build_query(
													array(
														'tutor_action' => 'duplicate_course',
														'course_id' => $post->ID,
													)
												);
												?>
											<a class="tutor-dropdown-item" href="?<?php echo esc_attr( $params ); ?>">
												<i class="tutor-icon-copy-text tutor-mr-8" area-hidden="true"></i>
												<span><?php esc_html_e( 'Duplicate', 'tutor' ); ?></span>
											</a>
											<?php endif; ?>
											<!-- # Duplicate Action -->
											
											<!-- Move to Draft Action -->
											<?php if ( tutor()->has_pro && in_array( $post->post_status, array( CourseModel::STATUS_PUBLISH, CourseModel::STATUS_FUTURE ) ) ) : ?>
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
											<a class="tutor-dropdown-item" href="?<?php echo esc_attr( $params ); ?>">
												<i class="tutor-icon-archive tutor-mr-8" area-hidden="true"></i>
												<span><?php esc_html_e( 'Move to Draft', 'tutor' ); ?></span>
											</a>
											<?php endif; ?>
											<!-- # Move to Draft Action -->
											
											<!-- Cancel Submission -->
											<?php if ( tutor()->has_pro && in_array( $post->post_status, array( CourseModel::STATUS_PENDING ) ) ) : ?>
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
											<a href="?<?php echo esc_attr( $params ); ?>" class="tutor-dropdown-item">
												<i class="tutor-icon-times tutor-mr-8" area-hidden="true"></i>
												<span><?php esc_html_e( 'Cancel Submission', 'tutor' ); ?></span>
											</a>
											<?php endif; ?>
											<!-- # Cancel Submission -->
											
											<!-- Delete Action -->
											<?php if ( $is_main_instructor && in_array( $post->post_status, array( CourseModel::STATUS_PUBLISH, CourseModel::STATUS_DRAFT, CourseModel::STATUS_FUTURE ) ) ) : ?>
												<?php if ( $show_course_delete ) : ?>
												<a href="#" data-tutor-modal-target="<?php echo esc_attr( $id_string_delete ); ?>" class="tutor-dropdown-item tutor-admin-course-delete">
													<i class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></i>
													<span><?php esc_html_e( 'Delete', 'tutor' ); ?></span>
												</a>
												<?php endif; ?>
											<?php endif; ?>
											<!-- # Delete Action -->

										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Delete prompt modal -->
						<!-- <div id="<?php echo esc_attr( $id_string_delete ); ?>" class="tutor-modal">
							<div class="tutor-modal-overlay"></div>
							<div class="tutor-modal-window">
								<div class="tutor-modal-content tutor-modal-content-white">
									<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
										<span class="tutor-icon-times" area-hidden="true"></span>
									</button>

									<div class="tutor-modal-body tutor-text-center">
										<div class="tutor-mt-48">
											<img class="tutor-d-inline-block" src="<?php echo esc_attr( tutor()->url ); ?>assets/images/icon-trash.svg" />
										</div>

										<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12"><?php esc_html_e( 'Delete This Course?', 'tutor' ); ?></div>
										<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Are you sure you want to delete this course permanently from the site? Please confirm your choice.', 'tutor' ); ?></div>

										<div class="tutor-d-flex tutor-justify-center tutor-my-48">
											<button data-tutor-modal-close class="tutor-btn tutor-btn-outline-primary">
												<?php esc_html_e( 'Cancel', 'tutor' ); ?>
											</button>
											<button class="tutor-btn tutor-btn-primary tutor-list-ajax-action tutor-ml-20" data-request_data='{"course_id":<?php echo esc_attr( $post->ID ); ?>,"action":"tutor_delete_dashboard_course","redirect_to":"<?php echo esc_url( tutor_utils()->get_current_url() ); ?>"}' data-delete_element_id="<?php echo esc_attr( $row_id ); ?>">
												<?php esc_html_e( 'Yes, Delete This', 'tutor' ); ?>
											</button>
										</div>
									</div>
								</div>
							</div>
						</div> -->
					</div>
					<?php
				endforeach;
				wp_reset_postdata();
				?>
			</div>
			<div class="tutor-mt-20">
				<?php
				if ( $count_map[ $status ] > $per_page ) {
					$pagination_data = array(
						'total_items' => $count_map[ $status ],
						'per_page'    => $per_page,
						'paged'       => $paged,
					);

					tutor_load_template_from_custom_path(
						tutor()->path . 'templates/dashboard/elements/pagination.php',
						$pagination_data
					);
				}
				?>

			</div>
			<?php
		}
		?>
	</div>
</div>
