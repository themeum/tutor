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
use TUTOR\Input;

$user_id          = Input::get( 'student_id', get_current_user_id(), Input::TYPE_INT );
$student_details  = get_userdata( $user_id );
$student_meta     = get_user_meta( $user_id );
$edit_profile_url = Dashboard::get_account_page_url( 'settings' ) . '?tab=account';
$website_url      = $student_meta['_tutor_profile_website'][0] ?? '';
$github_url       = $student_meta['_tutor_profile_github'][0] ?? '';
$x_url            = $student_meta['_tutor_profile_twitter'][0] ?? '';
$facebook_url     = $student_meta['_tutor_profile_facebook'][0] ?? '';
$linked_in_url    = $student_meta['_tutor_profile_linkedin'][0] ?? '';
$phone_number     = $student_meta['phone_number'][0] ?? '';

$social_links = array(
	'facebook' => array(
		'url'  => $facebook_url,
		'icon' => Icon::FACEBOOK,
	),
	'x'        => array(
		'url'  => $x_url,
		'icon' => Icon::X,
	),
	'linkedin' => array(
		'url'  => $linked_in_url,
		'icon' => Icon::LINKEDIN,
	),
	'github'   => array(
		'url'  => $github_url,
		'icon' => Icon::GITHUB,
	),
	'website'  => array(
		'url'  => $website_url,
		'icon' => Icon::GLOBE,
	),
);
?>

<div class="tutor-profile-card">
	<div class="tutor-profile-card-header">
		<?php Avatar::make()->user( $user_id )->size( Size::SIZE_104 )->render(); ?>
		<?php if ( tutor_utils()->is_dashboard_page( 'account/profile' ) ) : ?>
		<a href="<?php echo esc_url( $edit_profile_url ); ?>" class="tutor-edit-profile-btn">
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
				// Only render the social row if at least one URL is provided.
				$has_social = array_filter( array_column( $social_links, 'url' ) );
				if ( $has_social ) :
					?>
				<div class="tutor-user-profile-social">
					<?php foreach ( $social_links as $network => $social_link ) : ?>
						<?php if ( ! empty( $social_link['url'] ) ) : ?>
						<a
							href="<?php echo esc_url( $link['url'] ); ?>"
							class="tutor-social-link tutor-social-link--<?php echo esc_attr( $network ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php echo esc_attr( ucfirst( $network ) ); ?>"
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
				x-data="{ expanded: false, hasOverflow: false }"
				x-init="
					const checkOverflow = () => {
						const lh = parseFloat( getComputedStyle( $refs.bio ).lineHeight );
						hasOverflow = ! isNaN( lh ) && $refs.bio.scrollHeight > ( lh * 4 );
					};
					checkOverflow();
					const ro = new ResizeObserver( checkOverflow );
					ro.observe( $refs.bio );
					$cleanup( () => ro.disconnect() );
				"
			>
				<div
					class="tutor-user-profile-bio"
					x-ref="bio"
					:class="{ 'tutor-bio-collapsed': ! expanded && hasOverflow }"
				>
					<?php echo wp_kses_post( $bio_text ); ?>
				</div>

				<button
					type="button"
					class="tutor-bio-toggle"
					x-cloak
					x-show="hasOverflow && ! expanded"
					x-on:click="expanded = true"
					aria-expanded="false"
					:aria-expanded="expanded.toString()"
				>
					<?php esc_html_e( '… Read more', 'tutor' ); ?>
				</button>

				<button
					type="button"
					class="tutor-bio-toggle-less"
					x-cloak
					x-show="expanded"
					x-on:click="expanded = false"
					aria-expanded="true"
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
