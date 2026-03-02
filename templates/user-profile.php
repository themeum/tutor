<?php
/**
 * Tutor dashboard profile.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Avatar;
use Tutor\Components\Constants\Size;
use TUTOR\Icon;
use TUTOR\Input;
use TUTOR\User;

$user_id          = Input::get( 'student_id', get_current_user_id(), Input::TYPE_INT );
$student_details  = get_userdata( $user_id );
$student_meta     = get_user_meta( $user_id );
$edit_profile_url = tutor_utils()->tutor_dashboard_url( 'account/settings' );
$website_url      = $student_meta['_tutor_profile_website'][0] ?? '#';
$github_url       = $student_meta['_tutor_profile_github'][0] ?? '#';
$x_url            = $student_meta['_tutor_profile_twitter'][0] ?? '#';
$facebook_url     = $student_meta['_tutor_profile_facebook'][0] ?? '#';
$linked_in_url    = $student_meta['_tutor_profile_linkedin'][0] ?? '#';
?>

<div class="tutor-profile-card">
	<div class="tutor-profile-card-header">
		<?php Avatar::make()->user( $user_id )->size( Size::SIZE_104 )->render(); ?>
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
					<a href="<?php echo esc_url( $facebook_url ); ?>"><?php tutor_utils()->render_svg_icon( Icon::FACEBOOK ); ?></a>
					<a href="<?php echo esc_url( $x_url ); ?>"><?php tutor_utils()->render_svg_icon( Icon::X ); ?></a>
					<a href="<?php echo esc_url( $linked_in_url ); ?>"><?php tutor_utils()->render_svg_icon( Icon::LINKEDIN ); ?></a>
					<a href="<?php echo esc_url( $github_url ); ?>"><?php tutor_utils()->render_svg_icon( Icon::GITHUB ); ?></a>
					<a href="<?php echo esc_url( $website_url ); ?>"><?php tutor_utils()->render_svg_icon( Icon::GLOBE ); ?></a>
				</div>
			</div>
		</div>
		<div class="tutor-profile-card-body-right">
			<div class="tutor-user-profile-bio">
				<?php echo esc_html( $student_meta['_tutor_profile_bio'][0] ?? '' ); ?>
			</div>
			<ul class="tutor-user-profile-details">
				<li>
					<?php echo esc_html__( 'Username', 'tutor' ); ?> : 
					<span><?php echo esc_html( $student_details->user_login ); ?></span>
				</li>
				<li>
					<?php echo esc_html__( 'Email', 'tutor' ); ?> : 
					<span><?php echo esc_html( $student_details->user_email ); ?></span>
				</li>
				<?php if ( ! empty( $student_meta['phone_number'][0] ) ) : ?>
				<li>
					<?php echo esc_html__( 'Phone', 'tutor' ); ?> : 
					<span><?php echo esc_html( $student_meta['phone_number'][0] ); ?></span>
				</li>
				<?php endif; ?>	
			</ul>
		</div>
		<div class="tutor-profile-member-since">
			<?php tutor_utils()->render_svg_icon( Icon::MEMBER ); ?>
			<?php echo esc_html__( 'Member since', 'tutor' ); ?>
			<?php echo esc_html( tutor_i18n_get_formated_date( $student_details->user_registered, 'F j, Y' ) ); ?>
		</div>
	</div>
</div>
