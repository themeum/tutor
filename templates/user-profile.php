<?php
/**
 * Tutor dashboard profile.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use TUTOR\User;

$user             = wp_get_current_user();
$student_details  = get_userdata( $user->ID );
$student_meta     = get_user_meta( $user->ID );
$edit_profile_url = tutor_utils()->tutor_dashboard_url( 'account/settings' );
?>

<div class="tutor-profile-card">
	<div class="tutor-profile-card-header">
		<div class="tutor-avatar tutor-avatar-104 tutor-border tutor-border-2 tutor-border-brand-secondary">
			<img src="<?php echo esc_url( get_avatar_url( $user->ID ) ); ?>" alt="<?php echo esc_attr( $student_details->user_nicename ); ?>" class="tutor-avatar-image">
		</div>
		<?php if ( User::is_student() && User::is_student_view() ) : ?>
		<a href="<?php echo esc_url( $edit_profile_url ); ?>" class="tutor-edit-profile-btn">
			<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
			<span><?php esc_html_e( 'Edit Profile', 'tutor' ); ?></span>
		</a>
		<?php endif; ?>
	</div>
	<div class="tutor-profile-card-body">
		<div class="tutor-profile-card-body-left">
			<div>
				<h3 class="tutor-user-profile-title">
					<?php esc_html( $student_details->display_name ); ?>
				</h3>
				<div class="tutor-user-profile-designation">
					<?php echo esc_html( $student_meta['_tutor_profile_job_title'][0] ?? '' ); ?>
				</div>
				<div class="tutor-user-profile-social">
					<a href="#"><?php tutor_utils()->render_svg_icon( Icon::FACEBOOK ); ?></a>
					<a href="#"><?php tutor_utils()->render_svg_icon( Icon::X ); ?></a>
					<a href="#"><?php tutor_utils()->render_svg_icon( Icon::LINKEDIN ); ?></a>
					<a href="#"><?php tutor_utils()->render_svg_icon( Icon::GITHUB ); ?></a>
					<a href="#"><?php tutor_utils()->render_svg_icon( Icon::GLOBE ); ?></a>
				</div>
			</div>
		</div>
		<div class="tutor-profile-card-body-right">
			<div class="tutor-user-profile-bio">
				<?php echo esc_html( $student_meta['_tutor_profile_bio'][0] ?? '' ); ?>
			</div>
			<ul class="tutor-user-profile-details">
				<li>
					<?php echo esc_html__( 'Username', 'tutor-pro' ); ?> : 
					<span><?php echo esc_html( $student_details->user_login ); ?></span>
				</li>
				<li>
					<?php echo esc_html__( 'Email', 'tutor-pro' ); ?> : 
					<span><?php echo esc_html( $student_details->user_email ); ?></span>
				</li>
				<li>
					<?php echo esc_html__( 'Phone', 'tutor-pro' ); ?> : 
					<span></span>
				</li>
			</ul>
		</div>
		<div class="tutor-profile-member-since">
			<?php tutor_utils()->render_svg_icon( Icon::MEMBER ); ?>
			<?php echo esc_html__( 'Member since', 'tutor-pro' ); ?>
			<?php echo esc_html( tutor_i18n_get_formated_date( $student_details->user_registered, 'F j, Y' ) ); ?>
		</div>
	</div>
</div>
