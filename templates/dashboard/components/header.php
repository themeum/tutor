<?php
/**
 * Tutor dashboard sidebar.
 *
 * @package tutor
 */

use TUTOR\Icon;
use TUTOR\Instructors_List;
use TUTOR\User;

$profile_menu_items = array(
	'profile'      => array(
		'title'       => esc_html__( 'Profile', 'tutor' ),
		'icon'        => Icon::PROFILE_CIRCLE,
		'icon_active' => Icon::PROFILE_CIRCLE_FILL,
		'url'         => tutor_utils()->get_profile_url(),
	),
	'certificates' => array(
		'title'       => esc_html__( 'Certificates', 'tutor' ),
		'icon'        => Icon::CERTIFICATE_2,
		'icon_active' => Icon::CERTIFICATE_2,
		'url'         => tutor_utils()->get_certificates_url(),
	),
	'reviews'      => array(
		'title'       => esc_html__( 'Reviews', 'tutor' ),
		'icon'        => Icon::RATINGS,
		'icon_active' => Icon::RATINGS,
		'url'         => tutor_utils()->get_reviews_url(),
	),
	'billing'      => array(
		'title'       => esc_html__( 'Billing', 'tutor' ),
		'icon'        => Icon::BILLING,
		'icon_active' => Icon::BILLING,
		'url'         => tutor_utils()->get_billing_url(),
	),
	'settings'     => array(
		'title'       => esc_html__( 'Settings', 'tutor' ),
		'icon'        => Icon::SETTING,
		'icon_active' => Icon::SETTING,
		'url'         => tutor_utils()->get_settings_url(),
	),
	'logout'       => array(
		'title' => esc_html__( 'Logout', 'tutor' ),
		'icon'  => Icon::LOGOUT,
		'url'   => wp_logout_url( home_url() ),
	),
);

$active_nav = 'profile';

$user_id      = get_current_user_id();
$display_name = tutor_utils()->display_name( $user_id );
?>

<div class="tutor-dashboard-header">
	<div class="tutor-dashboard-header-inner">
		<div class="tutor-dashboard-header-left">
			<div class="tutor-h5 tutor-text-primary tutor-text-medium">
				<span class="tutor-text-subdued">
					<?php echo esc_html_x( 'Hi,', 'greetings', 'tutor' ); ?>
				</span>
				<?php echo esc_html( $display_name . ' 👋' ); ?>
			</div>
			<!-- <ul class="tutor-dashboard-header-user-streaks">
				@TODO
				<li><span class="tutor-font-medium tutor-text-primary">24</span>-day learning streak</li>
				<li>Keep it up!</li>
			</ul> -->
		</div>
		<div class="tutor-dashboard-header-right">
			<!-- @TODO -->
			<!-- <button class="tutor-btn tutor-btn-outline tutor-btn-x-small tutor-gap-2">
				24 
				<?php
				// tutor_utils()->render_svg_icon(
				// Icon::ENERGY,
				// 16,
				// 16,
				// array(
				// 'class' => 'tutor-text-brand',
				// )
				// );
				?>
			</button> -->
			<div>
				<?php do_action( 'tutor_dashboard/before_header_button' ); ?>
			</div>
			<div 
				x-data="tutorPopover({
					placement: 'bottom-end',
					offset: 4,
				})"
				class="tutor-dashboard-header-user"
			>
				<button
					class="tutor-dashboard-header-user-avatar"
					x-ref="trigger"
					@click="toggle()"
					:class="{ 'active': open }"
				>
					<?php echo get_avatar( get_current_user_id(), 32 ); ?>
				</button>

				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					class="tutor-popover tutor-dashboard-header-user-popover"
				>
					<div class="tutor-dashboard-header-user-popover-profile">
						<div class="tutor-avatar tutor-border tutor-border-brand-secondary">
							<?php echo get_avatar( get_current_user_id(), 48 ); ?>
						</div>
						<div class="tutor-flex tutor-flex-column tutor-items-center tutor-gap-1">
							<div class="tutor-text-medium tutor-text-primary tutor-font-semibold">
								<?php echo esc_html( wp_get_current_user()->display_name ); ?>
							</div>
							<div class="tutor-text-tiny tutor-text-secondary">
								<?php echo esc_html( wp_get_current_user()->user_email ); ?>
							</div>
						</div>
						<?php
						if ( ! User::is_instructor() ) {
							$instructor_status = tutor_utils()->instructor_status( 0, false );
							$instructor_status = is_string( $instructor_status ) ? strtolower( $instructor_status ) : '';

							if ( Instructors_List::INSTRUCTOR_PENDING === $instructor_status ) {
								$applied_on = get_user_meta( $user_id, '_is_tutor_instructor', true );
								$applied_on = tutor_i18n_get_formated_date( $applied_on, get_option( 'date_format' ) );
								?>
								<div class="tutor-flex tutor-gap-3 tutor-py-2 tutor-px-4 tutor-surface-l4 tutor-rounded-sm">
									<span class="tutor-pt-1">
										<?php tutor_utils()->render_svg_icon( Icon::INFO_OCTAGON, 16, 16, array( 'class' => 'tutor-icon-warning' ) ); ?>
									</span>
									<span class="tutor-p3 tutor-text-warning">
									<?php
										echo wp_kses_post(
											sprintf(
											/* translators: %s: application date */
												__( 'Your Application is pending as of <span class="tutor-font-medium">%s</span>', 'tutor' ),
												esc_html( $applied_on ),
											)
										);
									?>
									</span>
								</div>
								<?php
							} elseif ( Instructors_List::INSTRUCTOR_BLOCKED !== $instructor_status ) {
								?>
								<a href="<?php echo esc_url( tutor_utils()->instructor_register_url() ); ?>" class="tutor-btn tutor-btn-primary-soft tutor-btn-x-small tutor-gap-2 tutor-btn-block">
									<?php tutor_utils()->render_svg_icon( Icon::INSTRUCTOR ); ?>
									<?php esc_html_e( 'Become an Instructor', 'tutor' ); ?>
								</a>
								<?php
							}
						}
						?>
					</div>
					<ul class="tutor-dashboard-header-user-popover-menu">
						<?php
						foreach ( $profile_menu_items as $key => $item ) {
							$icon = ( $key === $active_nav && isset( $item['icon_active'] ) ) ? $item['icon_active'] : $item['icon'];
							?>
							<li>
								<a href="<?php echo esc_url( $item['url'] ?? '#' ); ?>" class="<?php echo ( $key === $active_nav ) ? 'active' : ''; ?>">
									<?php tutor_utils()->render_svg_icon( $icon, 20, 20 ); ?>
									<span><?php echo esc_html( $item['title'] ); ?></span>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
