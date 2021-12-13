<?php
	// Utillity data
	$is_enrolled           = apply_filters( 'tutor_alter_enroll_status', tutor_utils()->is_enrolled() );
	$lesson_url            = tutor_utils()->get_course_first_lesson();
	$is_administrator      = tutor_utils()->has_user_role( 'administrator' );
	$is_instructor         = tutor_utils()->is_instructor_of_this_course();
	$course_content_access = (bool) get_tutor_option( 'course_content_access_for_ia' );
	$is_privileged_user    = $course_content_access && ( $is_administrator || $is_instructor );
	$tutor_course_sell_by  = apply_filters( 'tutor_course_sell_by', null );
	$is_public             = get_post_meta( get_the_ID(), '_tutor_is_public_course', true ) == 'yes';

	// Monetization info
	$monetize_by              = tutor_utils()->get_option( 'monetize_by' );
	$enable_guest_course_cart = tutor_utils()->get_option( 'enable_guest_course_cart' );
	$is_purchasable           = tutor_utils()->is_course_purchasable();

	// Get login url if
	$is_tutor_login_disabled = ! tutor_utils()->get_option( 'enable_tutor_native_login', null, true, true );
	$auth_url                = $is_tutor_login_disabled ? isset( $_SERVER['REQUEST_SCHEME'] ) ? wp_login_url( $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) : '' : '';

	// Right sidebar meta data
	$sidebar_meta = apply_filters(
		'tutor/course/single/sidebar/metadata',
		array(
			array(
				'icon_class' => 'ttr-level-line',
				'label'      => __( 'Level', 'tutor' ),
				'value'      => get_tutor_course_level( get_the_ID() ),
			),
			array(
				'icon_class' => 'ttr-student-line-1',
				'label'      => __( 'Total Enrolled', 'tutor' ),
				'value'      => tutor_utils()->get_option( 'enable_course_total_enrolled' ) ? tutor_utils()->count_enrolled_users_by_course() : null,
			),
			array(
				'icon_class' => 'ttr-clock-filled',
				'label'      => __( 'Duration', 'tutor' ),
				'value'      => get_tutor_option( 'enable_course_duration' ) ? get_tutor_course_duration_context() : null,
			),
			array(
				'icon_class' => 'ttr-refresh-l',
				'label'      => __( 'Last Updated', 'tutor' ),
				'value'      => get_tutor_option( 'enable_course_update_date' ) ? tutor_get_formated_date( get_option( 'date_format' ), get_the_modified_date() ) : null,
			),
		),
		get_the_ID()
	);

	$login_class = ! is_user_logged_in() ? 'tutor-course-entry-box-login' : '';
	$login_url   = tutor_utils()->get_option( 'enable_tutor_native_login', null, true, true ) ? '' : wp_login_url( tutor()->current_url );
	?>

<div class="tutor-course-sidebar-card">
	<!-- Course Entry -->
	<div class="tutor-course-sidebar-card-body tutor-p-30 <?php echo $login_class; ?>" data-login_url="<?php echo $login_url; ?>">
		<?php
		if ( $is_enrolled ) {
			// The user is enrolled anyway. No matter manual, free, purchased, woocommerce, edd, membership
			do_action( 'tutor_course/single/actions_btn_group/before' );

			// Course Info
			$completed_lessons   = tutor_utils()->get_completed_lesson_count_by_course();
			$completed_percent   = tutor_utils()->get_course_completed_percent();
			$is_completed_course = tutor_utils()->is_completed_course();
			$retake_course       = is_single_course() && tutor_utils()->get_option( 'course_retake_feature', false ) && ( $is_completed_course || $completed_percent >= 100 );
			$course_id           = get_the_ID();
			$course_progress     = tutor_utils()->get_course_completed_percent( $course_id, 0, true );
			?>
			<!-- course progress -->
			<?php if ( is_array( $course_progress ) && count( $course_progress ) ) : ?>
				<div class="tutor-course-progress-wrapper tutor-mb-30" style="width: 100%;">
					<span class="color-text-primary text-medium-h6">
						<?php esc_html_e( 'Course Progress', 'tutor' ); ?>
					</span>
					<div class="list-item-progress tutor-mt-16">
						<div class="text-regular-body color-text-subsued tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-between">
							<span class="progress-steps">
								<?php echo esc_html( $course_progress['completed_count'] ); ?>/
								<?php echo esc_html( $course_progress['total_count'] ); ?>
							</span>
							<span class="progress-percentage"> 
								<?php echo esc_html( $course_progress['completed_percent'] . '%' ); ?>
								<?php esc_html_e( 'Complete', 'tutor' ); ?>
							</span>
						</div>
						<div class="progress-bar tutor-mt-10" style="--progress-value:<?php echo esc_attr( $course_progress['completed_percent'] ); ?>%;">
							<span class="progress-value"></span>
						</div>
					</div>
				</div>
			<?php endif; ?>
			<?php
			$start_content = '';

			// Show Start/Continue/Retake Button
			if ( $lesson_url ) {
				$button_class = 'tutor-is-fullwidth tutor-btn ' .
								( $retake_course ? 'tutor-btn-tertiary tutor-is-outline tutor-btn-lg tutor-btn-full' : '' ) .
								' tutor-is-fullwidth tutor-pr-0 tutor-pl-0 ' .
								( $retake_course ? ' tutor-course-retake-button' : '' );

				// Button identifier class
				$button_identifier = 'start-continue-retake-button';
				$tag               = $retake_course ? 'button' : 'a';
				ob_start();
				?>
					<<?php echo $tag; ?> <?php echo $retake_course ? 'disabled="disabled"' : ''; ?> href="<?php echo esc_url( $lesson_url ); ?>" class="<?php echo esc_attr( $button_class . ' ' . $button_identifier ); ?>" data-course_id="<?php echo esc_attr( get_the_ID() ); ?>">
					<?php
					if ( $retake_course ) {
						esc_html_e( 'Retake This Course', 'tutor' );
					} elseif ( $completed_percent <= 0 ) {
						esc_html_e( 'Start Learning', 'tutor' );
					} else {
						esc_html_e( 'Continue Learning', 'tutor' );
					}
					?>
					</<?php echo $tag; ?>>
					<?php
					$start_content = ob_get_clean();
			}
			echo apply_filters( 'tutor_course/single/start/button', $start_content, get_the_ID() );

			// Show Course Completion Button.
			if ( ! $is_completed_course ) {
				ob_start();
				?>
					<form method="post">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

						<input type="hidden" value="<?php echo esc_attr( get_the_ID() ); ?>" name="course_id"/>
						<input type="hidden" value="tutor_complete_course" name="tutor_action"/>

						<button type="submit" class="tutor-mt-25 tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-lg tutor-btn-full" name="complete_course_btn" value="complete_course">
						<?php esc_html_e( 'Complete Course', 'tutor' ); ?>
						</button>
					</form>
					<?php
					echo apply_filters( 'tutor_course/single/complete_form', ob_get_clean() );
			}

			?>
				<div class="text-regular-caption color-text-hints tutor-mt-12 tutor-bs-d-flex tutor-bs-justify-content-center">
					<span class="tutor-icon-26 color-success ttr-purchase-filled tutor-mr-6"></span>
					<span class="tutor-enrolled-info-text">
					<?php esc_html_e( 'You enrolled this course on', 'tutor' ); ?>
						<span class="text-bold-small color-success tutor-ml-3 tutor-enrolled-info-date">
						<?php echo esc_html( tutor_get_formated_date( get_option( 'date_format' ), $is_enrolled->post_date ) ); ?>
						</span>
					</span>
				</div>
				<?php

				do_action( 'tutor_course/single/actions_btn_group/after' );

		} elseif ( $is_privileged_user ) {
			// The user is not enrolled but privileged to access course content
			if ( $lesson_url ) {
				?>
					<a href="<?php echo esc_url( $lesson_url ); ?>" class="tutor-mt-5 tutor-mb-5 tutor-is-fullwidth tutor-btn">
					<?php esc_html_e( 'Continue Lesson', 'tutor' ); ?>
					</a>
					<?php
			} else {
				esc_html_e( 'No Content to Access', 'tutor' );
			}
		} else {
			// The course enroll options like purchase or free enrolment
			$price = apply_filters( 'get_tutor_course_price', null, get_the_ID() );

			if ( tutor_utils()->is_course_fully_booked( null ) ) {
				?>
					<div class="tutor-alert tutor-warning tutor-mt-28">
						<div class="tutor-alert-text">
							<span class="tutor-alert-icon tutor-icon-34 ttr-circle-outline-info-filled tutor-mr-10"></span>
							<span>
							<?php esc_html_e( 'This course is full right now. We limit the number of students to create an optimized and productive group dynamic.', 'tutor' ); ?>
							</span>
						</div>
					</div>
					<?php
			} elseif ( $is_purchasable && $price ) {
				if ( $tutor_course_sell_by ) {
					// Load template based on monetization option
					tutor_load_template( 'single.course.add-to-cart-' . $tutor_course_sell_by );
				} elseif ( $is_public ) {
					// Get the first content url
					$first_lesson_url                       = tutor_utils()->get_course_first_lesson( get_the_ID(), tutor()->lesson_post_type );
					! $first_lesson_url ? $first_lesson_url = tutor_utils()->get_course_first_lesson( get_the_ID() ) : 0;

					?>
						<a href="<?php echo esc_url( $first_lesson_url ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-full">
						<?php esc_html_e( 'Start Learning', 'tutor' ); ?>
						</a>
						<?php
				}
			} else {
				ob_start();
				?>
					<div class="tutor-course-sidebar-card-pricing tutor-bs-d-flex align-items-end tutor-bs-justify-content-between">
						<div>
							<span class="text-bold-h4 color-text-primary"><?php esc_html_e( 'Free', 'tutor' ); ?></span>
						</div>
					</div>
					<div class="tutor-course-sidebar-card-btns">
						<form class="tutor-enrol-course-form" method="post">
						<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
							<input type="hidden" name="tutor_course_id" value="<?php echo esc_attr( get_the_ID() ); ?>">
							<input type="hidden" name="tutor_course_action" value="_tutor_course_enroll_now">
							<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-full tutor-mt-24 tutor-enroll-course-button">
							<?php esc_html_e( 'Enroll Course', 'tutor' ); ?>
							</button>
						</form>
					</div>
					<div class="text-regular-caption color-text-hints tutor-mt-12 tutor-text-center">
					<?php esc_html_e( 'Free acess this course', 'tutor' ); ?>
					</div>
					<?php
					echo apply_filters( 'tutor/course/single/entry-box/free', ob_get_clean(), get_the_ID() );
			}
		}
		?>
	</div>

	<!-- Course Info -->
	<div class="tutor-course-sidebar-card-footer tutor-p-30">
		<ul class="tutor-course-sidebar-card-meta-list tutor-m-0 tutor-pl-0">
			<?php foreach ( $sidebar_meta as $meta ) : ?>
				<?php
				if ( ! $meta['value'] ) {
					continue;}
				?>
				<li class="tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-between">
					<div class="flex-center">
						<span class="tutor-icon-24 <?php echo $meta['icon_class']; ?> color-text-primary"></span>
						<span class="text-regular-caption color-text-hints tutor-ml-5">
							<?php echo esc_html( $meta['label'] ); ?>
						</span>
					</div>
					<div>
						<span class="text-medium-caption color-text-primary">
							<?php echo wp_kses_post( $meta['value'] ); ?>
						</span>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

<?php
if ( ! is_user_logged_in() ) {
	tutor_load_template_from_custom_path( tutor()->path . '/views/modal/login.php' );
}
?>
