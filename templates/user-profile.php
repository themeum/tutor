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
use TUTOR\Dashboard;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Helpers\UrlHelper;
use TUTOR\Input;

$user_id          = Input::get( 'student_id', get_current_user_id(), Input::TYPE_INT );
$student_details  = get_userdata( $user_id );
$student_meta     = get_user_meta( $user_id );
$cover_photo_url  = tutor_utils()->get_cover_photo_url( $user_id );
$edit_profile_url = Dashboard::get_account_page_url( 'settings' ) . '?tab=account';
$phone_number     = $student_meta['phone_number'][0] ?? '';

$social_fields = tutor_utils()->tutor_user_social_icons();
$social_links  = array();

foreach ( $social_fields as $meta_key => $field ) {
	$url                       = $student_meta[ $meta_key ][0] ?? '';
	$social_links[ $meta_key ] = array(
		'url'   => $url,
		'icon'  => $field['svg_icon'] ?? Icon::GLOBE,
		'label' => $field['label'],
	);
}
?>

<div class="tutor-profile-card">
	<div class="tutor-profile-card-header" style="background-image: url(<?php echo esc_attr( $cover_photo_url ); ?>);">
		<?php Avatar::make()->user( $user_id )->size( Size::SIZE_104 )->render(); ?>
		<?php if ( tutor_utils()->is_dashboard_page( 'account/profile' ) ) : ?>
		<a href="<?php echo esc_url( UrlHelper::add_query_params( $edit_profile_url, array( 'back_url' => UrlHelper::current() ) ) ); ?>" class="tutor-edit-profile-btn">
			<?php SvgIcon::make()->name( Icon::EDIT_2 )->render(); ?>
			<span><?php esc_html_e( 'Edit Profile', 'tutor' ); ?></span>
		</a>
		<?php endif; ?>
	</div>
	<div class="tutor-profile-card-body">
		<div class="tutor-profile-card-body-left">
			<div>
				<h3 class="tutor-user-profile-title">
					<?php echo esc_html( $student_details->display_name ); ?>
				</h3>
				<div class="tutor-user-profile-designation">
					<?php echo esc_html( $student_meta['_tutor_profile_job_title'][0] ?? '' ); ?>
				</div>

				<?php
				$has_social = array_filter( array_column( $social_links, 'url' ) );
				if ( $has_social ) :
					?>
				<div class="tutor-user-profile-social">
					<?php foreach ( $social_links as $social_link ) : ?>
						<?php if ( ! empty( $social_link['url'] ) ) : ?>
						<a
							href="<?php echo esc_url( $social_link['url'] ); ?>"
							class="tutor-social-link"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php echo esc_attr( $social_link['label'] ); ?>"
						>
							<?php SvgIcon::make()->name( $social_link['icon'] )->render(); ?>
						</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="tutor-profile-card-body-right">
			<?php
			$bio_text = $student_meta['_tutor_profile_bio'][0] ?? '';
			if ( ! empty( $bio_text ) ) :
				?>
			<div
				class="tutor-bio-wrapper"
				x-data="tutorReadMore({ lines: 4 })"
			>
				<div
					class="tutor-user-profile-bio"
					x-ref="content"
					style="display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 4; overflow: hidden;"
				>
					<?php echo wp_kses_post( $bio_text ); ?>
				</div>

				<button
					type="button"
					class="tutor-bio-toggle"
					x-ref="readMore"
					x-cloak
					x-show="hasOverflow && ! expanded"
					@click="toggle()"
					:aria-expanded="expanded.toString()"
				>
					<?php esc_html_e( '… Read more', 'tutor' ); ?>
				</button>

				<button
					type="button"
					class="tutor-bio-toggle-less"
					x-cloak
					x-show="expanded"
					@click="toggle()"
					:aria-expanded="expanded.toString()"
				>
					<?php esc_html_e( 'Read less', 'tutor' ); ?>
				</button>
			</div>
			<?php endif; ?>

			<ul class="tutor-user-profile-details">
				<li class="tutor-badge tutor-badge-disabled">
					<?php esc_html_e( 'Username', 'tutor' ); ?> :
					<span><?php echo esc_html( $student_details->user_login ); ?></span>
				</li>
				<li class="tutor-badge tutor-badge-disabled">
					<?php esc_html_e( 'Email', 'tutor' ); ?> :
					<span><?php echo esc_html( $student_details->user_email ); ?></span>
				</li>
				<?php if ( ! empty( $phone_number ) ) : ?>
				<li class="tutor-badge tutor-badge-disabled">
					<?php esc_html_e( 'Phone', 'tutor' ); ?> :
					<span><?php echo esc_html( $phone_number ); ?></span>
				</li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="tutor-profile-member-since">
			<?php SvgIcon::make()->name( Icon::MEMBER )->render(); ?>
			<?php esc_html_e( 'Member since', 'tutor' ); ?>
			<?php echo esc_html( tutor_i18n_get_formated_date( $student_details->user_registered, 'F j, Y' ) ); ?>
		</div>
	</div>
</div>
