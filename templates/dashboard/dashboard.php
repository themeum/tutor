<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( tutor_utils()->get_option( 'enable_profile_completion' ) ) {
	$profile_completion = tutor_utils()->user_profile_completion();
	$is_instructor      = tutor_utils()->is_instructor(null, true);
	$total_count        = count( $profile_completion );
	$incomplete_count   = count(
		array_filter(
			$profile_completion,
			function( $data ) {
				return ! $data['is_set'];
			}
		)
	);
	$complete_count     = $total_count - $incomplete_count;

	if ( $is_instructor ) {
		if ( isset($total_count) && isset($incomplete_count) && $incomplete_count <= $total_count ) {
			?>
			<div class="profile-completion tutor-mb-40">
				<div class="tutor-row tutor-align-items-center">
					<div class="tutor-col-lg-7 profile-completion-content <?php echo tutor_utils()->is_instructor() ? 'tutor-profile-completion-content-admin' : ''; ?>">
						<div class="list-item-title tutor-fs-6 tutor-fw-medium tutor-color-black">
							<?php esc_html_e( 'Complete Your Profile', 'tutor' ); ?>
						</div>
						<div class="tutor-mt-12 tutor-align-items-center" style="display: grid; grid-template-columns: repeat(<?php echo $total_count + 1; ?>, 1fr); ">
							<?php
							for ( $i = 1; $i <= $total_count; $i++ ) {
								$class = $i > $complete_count ?
											'tutor-btn tutor-btn-sm tutor-btn-disable tutor-no-hover tutor-btn-full' :
											'tutor-btn tutor-btn-sm tutor-btn-full'
								?>
									<li class="<?php echo "tutor-profile-complete-dash-{$total_count}"; ?>  tutor-mr-8">
										<span class="<?php echo $class; ?>"></span>
									</li>
									<?php
							}
							?>
							<li>
								<span class="tutor-round-icon">
									<i class="tutor-icon-award-filled"></i>
								</span>
							</li>
						</div>
						<div class="list-item-title tutor-fs-6 tutor-mt-20">
							<?php
								$profile_complete_text = "Please complete profile";
								if($complete_count > ( $total_count / 2 ) && $complete_count < $total_count) {
									$profile_complete_text = 'You are almost done';
								} else if($complete_count === $total_count) {
									$profile_complete_text = 'Thanks for completing your profile';
								}
								$profile_complete_status = _e($profile_complete_text, 'tutor');

							?>
							<span class="tutor-color-muted"><?php $profile_complete_status ?></span>:&nbsp;
							<span class="tutor-color-black">
								<?php echo $complete_count . '/' . $total_count; ?>
							</span>
						</div>
					</div>
					<div class="tutor-col-lg-5 profile-completion-items warning">
						<ul class="tutor-m-0 tutor-p-0">
							<?php
							foreach ( $profile_completion as $key => $data ) {
								$is_set = $data['is_set']; // Whether the step is done or not
								?>
									<li class="tutor-d-flex tutor-align-items-center">
									<?php if ( $is_set ) : ?>
											<span class="icon tutor-icon-tick-circle-outline-filled not-empty tutor-mr-8"></span>
										<?php else : ?>
											<span class="tutor-icon-cross-circle-outline-filled empty tutor-mr-4"></span>
										<?php endif; ?>

										<span class="<?php echo $is_set ? 'tutor-color-black-70' : 'tutor-color-muted'; ?>">
										<?php echo $data['label_html']; ?>
										</span>
									</li>
									<?php
							}
							?>
						</ul>
					</div>
				</div>
			</div>
			<?php
		}
	} else {
		if ( ! $profile_completion['_tutor_profile_photo']['is_set'] ) {
			$alert_message = sprintf(
				'<div class="tutor-alert tutor-primary tutor-mb-20">
			<div class="tutor-alert-text">
				<span class="tutor-alert-icon tutor-icon-34 tutor-icon-circle-outline-info-filled tutor-mr-12"></span>
				<span>
					%s
				</span>
			</div>
			<div class="tutor-alert-btns">
				<div class="alert-btn-group">
					<a href="%s" class="tutor-btn tutor-btn-sm">Click Here</a>
				</div>
			</div>
		</div>',
				$profile_completion['_tutor_profile_photo']['label_html'],
				tutor_utils()->tutor_dashboard_url( 'settings' )
			);

			echo $alert_message;
			// echo '<div class="tutor-alert tutor-warning">
			// <div class="tutor-alert-text">
			// <span class="tutor-alert-icon tutor-icon-34 tutor-icon-circle-outline-info-filled tutor-mr-12"></span>
			// <span>
			// {$profile_completion["_tutor_profile_photo"]["label_html"]}
			// </span>
			// </div>
			// <div class="tutor-alert-btns">
			// <span class="tutor-alert-close tutor-icon-28 tutor-color-black-40 tutor-icon-cross-filled"></span>
			// </div>
			// </div>';

		}
	}
}
?>

<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-capitalize-text tutor-mb-24 tutor-dashboard-title"><?php _e( 'Dashboard', 'tutor' ); ?></div>
<!-- <h3 class="tutor-dashboard-title"><?php // _e('Dashboard', 'tutor'); ?></h3> -->

<div class="tutor-dashboard-content-inner">

	<?php
	$enrolled_course   = tutor_utils()->get_enrolled_courses_by_user();
	$completed_courses = tutor_utils()->get_completed_courses_ids_by_user();
	$total_students    = tutor_utils()->get_total_students_by_instructor( get_current_user_id() );
	$my_courses        = tutor_utils()->get_courses_by_instructor( get_current_user_id(), 'publish' );
	$earning_sum       = tutor_utils()->get_earning_sum();

	$enrolled_course_count                          = $enrolled_course ? $enrolled_course->post_count : 0;
	$completed_course_count                         = count( $completed_courses );
	$active_course_count                            = $enrolled_course_count - $completed_course_count;
	$active_course_count < 0 ? $active_course_count = 0 : 0;

	$status_translations = array(
		'publish' => __( 'Published', 'tutor' ),
		'pending' => __( 'Pending', 'tutor' ),
		'trash'   => __( 'Trash', 'tutor' ),
	);

	?>

	<div class="tutor-row tutor-dashboard-cards-container">
		<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-6 tutor-col-lg-4">
			<p>
				<span class="tutor-dashboard-round-icon">
					<i class="tutor-icon-book-open-filled"></i>
				</span>
				<span class="tutor-dashboard-info-val"><?php echo esc_html( $enrolled_course_count ); ?></span>
				<span><?php esc_html_e( 'Enrolled Courses', 'tutor' ); ?></span>
				<span class="tutor-dashboard-info-val"><?php echo esc_html( $enrolled_course_count ); ?></span>
			</p>
		</div>
		<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-6 tutor-col-lg-4">
			<p>
				<span class="tutor-dashboard-round-icon">
					<i class="tutor-icon-college-graduation-filled"></i>
				</span>
				<span class="tutor-dashboard-info-val"><?php echo esc_html( $active_course_count ); ?></span>
				<span><?php esc_html_e( 'Active Courses', 'tutor' ); ?></span>
				<span class="tutor-dashboard-info-val"><?php echo esc_html( $active_course_count ); ?></span>
			</p>
		</div>
		<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-6 tutor-col-lg-4">
			<p>
				<span class="tutor-dashboard-round-icon">
					<i class="tutor-icon-award-filled"></i>
				</span>
				<span class="tutor-dashboard-info-val"><?php echo esc_html( $completed_course_count ); ?></span>
				<span><?php esc_html_e( 'Completed Courses', 'tutor' ); ?></span>
				<span class="tutor-dashboard-info-val"><?php echo esc_html( $completed_course_count ); ?></span>
			</p>
		</div>

		<?php
		if ( current_user_can( tutor()->instructor_role ) ) :
			?>
			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-6 tutor-col-lg-4">
				<p>
					<span class="tutor-dashboard-round-icon">
						<i class="tutor-icon-user-graduate-filled"></i>
					</span>
					<span class="tutor-dashboard-info-val"><?php echo esc_html( $total_students ); ?></span>
					<span><?php esc_html_e( 'Total Students', 'tutor' ); ?></span>
					<span class="tutor-dashboard-info-val"><?php echo esc_html( $total_students ); ?></span>
				</p>
			</div>
			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-6 tutor-col-lg-4">
				<p>
					<span class="tutor-dashboard-round-icon">
						<i class="tutor-icon-box-open-filled"></i>
					</span>
					<span class="tutor-dashboard-info-val"><?php echo esc_html( count( $my_courses ) ); ?></span>
					<span><?php esc_html_e( 'Total Courses', 'tutor' ); ?></span>
					<span class="tutor-dashboard-info-val"><?php echo esc_html( count( $my_courses ) ); ?></span>
				</p>
			</div>
			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-6 tutor-col-lg-4">
				<p>
					<span class="tutor-dashboard-round-icon">
						<i class="tutor-icon-coins-filled"></i>
					</span>
					<span class="tutor-dashboard-info-val"><?php echo tutor_utils()->tutor_price( $earning_sum->instructor_amount ); ?></span>
					<span><?php esc_html_e( 'Total Earnings', 'tutor' ); ?></span>
					<span class="tutor-dashboard-info-val"><?php echo tutor_utils()->tutor_price( $earning_sum->instructor_amount ); ?></span>
				</p>
			</div>
			<?php
		endif;
		?>
	</div>
</div>

<?php
/**
 * Active users in progress courses
 */
$placeholder_img     = tutor()->url . 'assets/images/placeholder.png';
$courses_in_progress = tutor_utils()->get_active_courses_by_user( get_current_user_id() );
?>


<?php if ( tutor_utils()->is_instructor() ) : ?>
	<div class="tutor-frontend-dashboard-course-porgress">
		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-capitalize-text tutor-mb-24">
			<?php esc_html_e( 'In Progress Course', 'tutor' ); ?>
		</div>
		<?php if ( $courses_in_progress && $courses_in_progress->have_posts() ) : ?>
			<?php
			while ( $courses_in_progress->have_posts() ) :
				$courses_in_progress->the_post();
				$tutor_course_img = get_tutor_course_thumbnail_src();
				$course_rating    = tutor_utils()->get_course_rating( get_the_ID() );
				$course_progress  = tutor_utils()->get_course_completed_percent( get_the_ID(), 0, true );
				$completed_number = 0 === (int) $course_progress['completed_count'] ? 1 : (int) $course_progress['completed_count'];
				?>
			<div class="tutor-frontend-dashboard-course-porgress-cards tutor-mb-20">
				<div class="tutor-frontend-dashboard-course-porgress-card tutor-frontend-dashboard-course-porgress-card-horizontal tutor-course-listing-item tutor-course-listing-item-sm tutor-justify-content-start">
					<div class="tutor-course-listing-item-head tutor-d-flex">
						<a href="<?php the_permalink(); ?>" class="tutor-course-listing-thumb-permalink">
							<div class="tutor-course-listing-thumbnail" style="background-image:url(<?php echo empty( esc_url( $tutor_course_img ) ) ? $placeholder_img : esc_url( $tutor_course_img ); ?>)"></div>
						</a>
					</div>
					<div class="tutor-course-listing-item-body tutor-px-32 tutor-pt-24 tutor-pb-20">
						<?php if ( $course_rating ) : ?>
						<div class="list-item-rating tutor-d-flex">
							<div class="tutor-ratings tutor-is-sm">
								<?php tutor_utils()->star_rating_generator( $course_rating->rating_avg ); ?>
								<div class="tutor-rating-text tutor-color-black-60 tutor-fs-6">
									<?php echo esc_html( number_format( $course_rating->rating_avg, 2 ) ); ?>
								</div>
							</div>
						</div>
						<?php endif; ?>
						<div class="list-item-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-8">
							<a href="<?php the_permalink(); ?>">
								<?php the_title(); ?>
							</a>
						</div>
						<div class="list-item-steps tutor-mt-16">
							<span class="tutor-fs-7 tutor-color-muted">
								<?php esc_html_e( 'Completed Lessons:', 'tutor' ); ?>
							</span>
							<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
								<span>
									<?php echo esc_html( $course_progress['completed_count'] ); ?>
								</span>
								<?php esc_html_e( 'of', 'tutor' ); ?>
								<span>
									<?php echo esc_html( $course_progress['total_count'] ); ?>
								</span>
								<?php echo esc_html( _n( 'lesson', 'lessons', $completed_number, 'tutor' ) ); ?>
							</span>
						</div>
						<div class="list-item-progress tutor-mt-32">
							<div class="tutor-fs-6 tutor-color-black-60 tutor-d-flex tutor-align-items-center tutor-justify-content-between">
								<div class="progress-bar tutor-mr-16" style="--progress-value:<?php echo esc_attr( $course_progress['completed_percent'] ); ?>%"><span class="progress-value"></span></div>
								<span class="progress-percentage tutor-fs-7 tutor-color-muted">
									<span class="tutor-fs-7 tutor-fw-medium tutor-color-black ">
										<?php echo esc_html( $course_progress['completed_percent'] . '%' ); ?>
									</span><?php esc_html_e( 'Complete', 'tutor' ); ?>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php
$instructor_course = tutor_utils()->get_courses_for_instructors( get_current_user_id() );

if ( count( $instructor_course ) ) {
	$course_badges = array(
		'publish' => 'success',
		'pending' => 'warning',
		'trash'   => 'danger',
	);

	?>
		<div class="popular-courses-heading-dashboard tutor-fs-5 tutor-fw-medium tutor-color-black tutor-capitalize-text tutor-mb-24 tutor-mt-md-42 tutor-mt-0">
			<?php esc_html_e( 'My Courses', 'tutor' ); ?>
			<a style="float:right" class="tutor-view-all-course" href="<?php echo esc_url( tutor_utils()->tutor_dashboard_url( 'my-courses' ) ); ?>">
				<?php esc_html_e( 'View All', 'tutor' ); ?>
			</a>
		</div>
		<div class="tutor-dashboard-content-inner">
			<table class="tutor-ui-table tutor-ui-table-responsive table-popular-courses">
				<thead>
					<tr>
						<th>
							<span class="tutor-fs-7 tutor-color-black-60">
								<?php esc_html_e( 'Course Name', 'tutor' ); ?>
							</span>
						</th>
						<th class="tutor-table-rows-sorting">
							<div class="inline-flex-center tutor-color-black-60">
								<span class="tutor-fs-7"><?php esc_html_e( 'Enrolled', 'tutor' ); ?></span>
								<span class="tutor-icon-ordering-a-to-z-filled a-to-z-sort-icon tutor-icon-22"></span>
							</div>
						</th>
						<th class="tutor-table-rows-sorting">
							<div class="inline-flex-center tutor-color-black-60">
								<span class="tutor-fs-7"><?php esc_html_e( 'Rating', 'tutor' ); ?></span>
								<span class="tutor-icon-ordering-a-to-z-filled a-to-z-sort-icon tutor-icon-22"></span>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( is_array( $instructor_course ) && count( $instructor_course ) ) : ?>
						<?php
						foreach ( $instructor_course as $course ) :
							$enrolled      = tutor_utils()->count_enrolled_users_by_course( $course->ID );
							$course_status = isset( $status_translations[ $course->post_status ] ) ? $status_translations[ $course->post_status ] : __( $course->post_status, 'tutor' );
							$course_rating = tutor_utils()->get_course_rating( $course->ID );
							$course_badge  = isset( $course_badges[ $course->post_status ] ) ? $course_badges[ $course->post_status ] : 'dark';

							?>
							<tr>
								<td data-th="<?php esc_html_e( 'Course Name', 'tutor' ); ?>" class="column-fullwidth">
									<div class="td-course  tutor-fs-6 tutor-fw-medium  tutor-color-black">
										<a href="<?php echo esc_url( get_the_permalink( $course->ID ) ); ?>" target="_blank">
											<?php esc_html_e( $course->post_title ); ?>
										</a>
									</div>
								</td>
								<td data-th="<?php esc_html_e( 'Enrolled', 'tutor' ); ?>">
									<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
										<?php esc_html_e( $enrolled ); ?>
									</span>
								</td>
								<td data-th="<?php esc_html_e( 'Rating', 'tutor' ); ?>">
									<div class="td-tutor-rating tutor-fs-6 tutor-color-black-60">
										<?php tutor_utils()->star_rating_generator_v2( $course_rating->rating_avg, null, true ); ?>
									</div>
								</td>
							</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="100%" class="column-empty-state">
									<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
								</td>
							</tr>
						<?php endif; ?>
				</tbody>
			</table>
		</div>
	<?php
}
?>
