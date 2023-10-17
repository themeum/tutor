<?php
/**
 * Template for displaying student & instructor Public Profile.
 * It is used for both of instructor and student.
 * Separate file has not been introduced due to complicacy and backward compatibility.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use TUTOR\Input;
use Tutor\Models\CourseModel;

// Get the accessed user data.
$user_name = sanitize_text_field( get_query_var( 'tutor_profile_username' ) );
$get_user  = tutor_utils()->get_user_by_login( $user_name );

// Show not found page if user not exists.
if ( ! is_object( $get_user ) || ! property_exists( $get_user, 'ID' ) ) {
	wp_safe_redirect( get_home_url() . '/404' );
	exit;
}

// Prepare meta data to render the page based on user and view type.
$user_id        = $get_user->ID;
$is_instructor  = Input::has( 'view' ) ? 'instructor' === Input::get( 'view' ) : tutor_utils()->is_instructor( $user_id, true );
$layout_key     = $is_instructor ? 'public_profile_layout' : 'student_public_profile_layout';
$profile_layout = tutor_utils()->get_option( $layout_key, 'private' );
$user_type      = $is_instructor ? 'instructor' : 'student';

if ( 'private' === $profile_layout ) {
	// Disable profile access then.
	wp_safe_redirect( get_home_url() );
	exit;
}

// Prepare social media URLs od the user.
$tutor_user_social_icons = tutor_utils()->tutor_user_social_icons();
foreach ( $tutor_user_social_icons as $key => $social_icon ) {
	$url                                    = get_user_meta( $user_id, $key, true );
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

$rating_content          = ob_get_clean();
$allowed_html_for_rating = array(
	'div'  => array( 'class' => true ),
	'span' => array( 'class' => true ),
	'i'    => array( 'class' => true ),
);

// Social media content.
ob_start();

foreach ( $tutor_user_social_icons as $key => $social_icon ) {
	$url = $social_icon['url'];
	echo ! empty( $url ) ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer nofollow" class="' . esc_attr( $social_icon['icon_classes'] ) . '" title="' . esc_attr( $social_icon['label'] ) . '"></a>' : '';
}

$social_media            = ob_get_clean();
$allowed_html_for_social = array(
	'a' => array(
		'href'   => true,
		'class'  => true,
		'target' => true,
		'rel'    => true,
		'title'  => true,
	),
);

?>

<?php
//phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
do_action( 'tutor_profile/' . $user_type . '/before/wrap' );
?>
<?php $user_identifier = $is_instructor ? 'tutor-instructor' : 'tutor-student'; ?>
	<div <?php tutor_post_class( 'tutor-wrap-parent tutor-full-width-student-profile tutor-page-wrap tutor-user-public-profile tutor-user-public-profile-' . $profile_layout . ' ' . $user_identifier ); ?> >
		<div class="tutor-container photo-area">
			<div class="cover-area">
				<div style="background-image:url(<?php echo esc_url( tutor_utils()->get_cover_photo_url( $user_id ) ); ?>)"></div>
				<div></div>
			</div>
			<div class="pp-area">
				<div class="profile-pic" style="background-image:url(<?php echo esc_url( get_avatar_url( $user_id, array( 'size' => 300 ) ) ); ?>)"></div>

				<div class="profile-name tutor-color-white">
					<div class="profile-rating-media content-for-mobile">
						<?php echo wp_kses( $rating_content, $allowed_html_for_rating ); ?>
						<div class="tutor-social-container content-for-desktop">
							<?php echo wp_kses( $social_media, $allowed_html_for_social ); ?>
						</div>
					</div>

					<h3><?php echo esc_html( $get_user->display_name ); ?></h3>
					<?php
					if ( $is_instructor ) {
						$course_count  = CourseModel::get_course_count_by_instructor( $user_id );
						$student_count = tutor_utils()->get_total_students_by_instructor( $user_id );
						?>
							<span>
								<span><?php echo esc_html( $course_count ); ?></span> 
								<?php $course_count > 1 ? esc_html_e( 'Courses', 'tutor' ) : esc_html_e( 'Course', 'tutor' ); ?>
							</span>
							<span>
								<span>•</span>
							</span>
							<span>
								<span><?php echo esc_html( $student_count ); ?></span> 
								<?php $student_count > 1 ? esc_html_e( 'Students', 'tutor' ) : esc_html_e( 'Student', 'tutor' ); ?>
							</span>
						<?php
					} else {
						$enrolled_course = tutor_utils()->get_enrolled_courses_by_user( $user_id );
						$enrol_count     = is_object( $enrolled_course ) ? $enrolled_course->found_posts : 0;

						$complete_count = tutor_utils()->get_completed_courses_ids_by_user( $user_id );
						$complete_count = $complete_count ? count( $complete_count ) : 0;
						?>
							<span>
								<span><?php echo esc_html( $enrol_count ); ?></span> 
							<?php $enrol_count > 1 ? esc_html_e( 'Courses Enrolled', 'tutor' ) : esc_html_e( 'Course Enrolled', 'tutor' ); ?>
							</span>
							<span><span>•</span></span>
							<span>
								<span><?php echo esc_html( $complete_count ); ?></span> 
							<?php $complete_count > 1 ? esc_html_e( 'Courses Completed', 'tutor' ) : esc_html_e( 'Course Completed', 'tutor' ); ?>
							</span>
							<?php
					}
					?>
				</div>

				<div class="tutor-social-container content-for-mobile">
					<?php echo wp_kses( $social_media, $allowed_html_for_social ); ?>
				</div>

				<div class="profile-rating-media content-for-desktop">
					<?php echo wp_kses( $rating_content, $allowed_html_for_rating ); ?>
					<div class="tutor-social-container content-for-desktop">
					<?php echo wp_kses( $social_media, $allowed_html_for_social ); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="tutor-container" style="overflow:auto">
			<div class="tutor-user-profile-sidebar">

			</div>
			<div class="tutor-user-profile-content tutor-d-block tutor-mt-72">
				<h3><?php esc_html_e( 'Biography', 'tutor' ); ?></h3>
				<?php tutor_load_template( 'profile.bio' ); ?>

				<?php
				if ( $is_instructor ) {
					?>
						<h3><?php esc_html_e( 'Courses', 'tutor' ); ?></h3>
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
//phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
do_action( 'tutor_profile/' . $user_type . '/after/wrap' );
tutor_utils()->tutor_custom_footer();

/**
 * If user not logged in load tutor login modal.
 * It will appear on enroll btn click.
 *
 * @since 2.1.3
 */
if ( ! is_user_logged_in() ) {
	$login_modal = tutor()->path . '/views/modal/login.php';
	if ( file_exists( $login_modal ) ) {
		tutor_load_template_from_custom_path( $login_modal );
	}
}
