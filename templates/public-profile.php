<?php
/**
 * Template for displaying student & instructor Public Profile.
 * It is used for both of instructor and student. Separate file has not been introduced due to complicacy and backward compatibility. -JK
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

 // Get the accessed user data
$user_name = sanitize_text_field( get_query_var( 'tutor_profile_username' ) );
$get_user  = tutor_utils()->get_user_by_login( $user_name );

// Show not found page if user not exists
if ( ! is_object( $get_user ) || ! property_exists( $get_user, 'ID' ) ) {
	wp_redirect( get_home_url() . '/404' );
	exit;
}

// Prepare meta data to render the page based on user and view type
$user_id        = $get_user->ID;
$is_instructor  = isset($_GET['view']) ? $_GET['view']==='instructor' : tutor_utils()->is_instructor($user_id, true);
$layout_key     = $is_instructor ? 'public_profile_layout' : 'student_public_profile_layout';
$profile_layout = tutor_utils()->get_option($layout_key , 'private' );
$user_type		= $is_instructor ? 'instructor' : 'student';

if ( 'private' === $profile_layout ) {
	// Disable profile access then.
	wp_redirect( get_home_url() );
	exit;
}

// Prepare social media URLs od the user
$tutor_user_social_icons = tutor_utils()->tutor_user_social_icons();
foreach ( $tutor_user_social_icons as $key => $social_icon ) {
	$url = get_user_meta( $user_id, $key, true );
	$tutor_user_social_icons[ $key ]['url'] = $url;
}

tutor_utils()->tutor_custom_header();
?>

<?php
	ob_start();
	
	// Rating content.
	if ( $is_instructor ) {
		$instructor_rating = tutor_utils()->get_instructor_ratings( $user_id );
		?>
			<div class="tutor-rating-container">      
				<div class="ratings">
					<span class="rating-generated">
						<?php tutor_utils()->star_rating_generator( $instructor_rating->rating_avg ); ?>
					</span>
					<span class='rating-digits'>
						<?php echo esc_html( number_format( $instructor_rating->rating_avg, 2 ) ); ?>
					</span> 
					<span class='rating-total-meta tutor-fs-7 tutor-color-muted'>
						(<?php echo esc_html( number_format( $instructor_rating->rating_count, 2 ) ); ?>)
					</span>
				</div>
			</div>
		<?php
	}
	$rating_content = ob_get_clean();


	// Social media content
	ob_start();
	foreach ( $tutor_user_social_icons as $key => $social_icon ) {
		$url = $social_icon['url'];
		echo ! empty( $url ) ? '<a href="' . $url . '" target="_blank" rel="noopener noreferrer nofollow" class="' . $social_icon['icon_classes'] . '" title="' . $social_icon['label'] . '"></a>' : '';
	}
	$social_media = ob_get_clean();
?>

<?php do_action( 'tutor_profile/'.$user_type.'/before/wrap' ); ?>
<?php $user_identifier = $is_instructor ? 'tutor-instructor' : 'tutor-student'; ?>
	<div <?php tutor_post_class( 'tutor-full-width-student-profile tutor-page-wrap tutor-user-public-profile tutor-user-public-profile-' . $profile_layout . ' ' . $user_identifier ); ?> >
		<div class="tutor-container photo-area">
			<div class="cover-area">
				<div style="background-image:url(<?php echo tutor_utils()->get_cover_photo_url( $user_id ); ?>)"></div>
				<div></div>
			</div>
			<div class="pp-area">
				<div class="profile-pic" style="background-image:url(<?php echo get_avatar_url( $user_id, array( 'size' => 600 ) ); ?>)"></div>
				
				<div class="profile-name tutor-color-white">
					<div class="profile-rating-media content-for-mobile">
						<?php echo $rating_content; ?>
						<div class="tutor-social-container content-for-desktop">
							<?php echo $social_media; ?>
						</div>
					</div>

					<h3><?php echo $get_user->display_name; ?></h3>
					<?php
					if ( $is_instructor ) {
						$course_count  = tutor_utils()->get_course_count_by_instructor( $user_id );
						$student_count = tutor_utils()->get_total_students_by_instructor( $user_id );
						?>
							<span>
								<span><?php echo $course_count; ?></span> 
								<?php $course_count > 1 ? _e( 'Courses', 'tutor' ) : _e( 'Course', 'tutor' ); ?>
							</span>
							<span>
								<span>•</span>
							</span>
							<span>
								<span><?php echo $student_count; ?></span> 
								<?php $student_count > 1 ? _e( 'Students', 'tutor' ) : _e( 'Student', 'tutor' ); ?>
							</span>
						<?php
					} else {
						$enrolled_course = tutor_utils()->get_enrolled_courses_by_user( $user_id );
						$enrol_count     = is_object( $enrolled_course ) ? $enrolled_course->found_posts : 0;

						$complete_count = tutor_utils()->get_completed_courses_ids_by_user( $user_id );
						$complete_count = $complete_count ? count( $complete_count ) : 0;
						?>
							<span>
								<span><?php echo $enrol_count; ?></span> 
							<?php $enrol_count > 1 ? _e( 'Courses Enrolled', 'tutor' ) : _e( 'Course Enrolled', 'tutor' ); ?>
							</span>
							<span><span>•</span></span>
							<span>
								<span><?php echo $complete_count; ?></span> 
							<?php $complete_count > 1 ? _e( 'Courses Completed', 'tutor' ) : _e( 'Course Completed', 'tutor' ); ?>
							</span>
							<?php
					}
					?>
				</div>

				<div class="tutor-social-container content-for-mobile">
					<?php echo $social_media; ?>
				</div>
				
				<div class="profile-rating-media content-for-desktop">
					<?php echo $rating_content; ?>
					<div class="tutor-social-container content-for-desktop">
						<?php
						foreach ( $tutor_user_social_icons as $key => $social_icon ) {
							$url = $social_icon['url'];
							echo ! empty( $url ) ? '<a href="' . $url . '" target="_blank" rel="noopener noreferrer nofollow" class="' . $social_icon['icon_classes'] . '" title="' . $social_icon['label'] . '"></a>' : '';
						}
						?>
					</div>
				</div>
			</div>
		</div>

		
		<div class="tutor-container" style="overflow:auto">
			<div class="tutor-user-profile-sidebar">
				<?php // tutor_load_template('profile.badge', ['profile_badges'=>(new )]); ?>
			</div>
			<div class="tutor-user-profile-content tutor-d-block tutor-mt-72">
				<h3><?php _e( 'Biography', 'tutor' ); ?></h3>
				<?php tutor_load_template( 'profile.bio' ); ?>
				
				<?php
				if ( $is_instructor ) {
					?>
						<h3><?php _e( 'Courses', 'tutor' ); ?></h3>
						<?php
							add_filter(
								'courses_col_per_row',
								function() {
									return 3;
								}
							);

							tutor_load_template( 'profile.courses_taken' );
						?>
						<?php
				}
				?>
			</div>
		</div>
	</div>
<?php

do_action( 'tutor_profile/'.$user_type.'/after/wrap' );
tutor_utils()->tutor_custom_footer();
