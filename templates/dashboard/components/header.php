<?php
/**
 * Tutor dashboard header.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://www.themeum.com/
 * @since 4.0.0
 */

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use TUTOR\Dashboard;
use TUTOR\Icon;
use TUTOR\Instructors_List;
use TUTOR\User;

$menu_items           = Dashboard::get_account_pages();
$menu_items['logout'] = array(
	'title' => esc_html__( 'Logout', 'tutor' ),
	'icon'  => Icon::LOGOUT,
	'url'   => wp_logout_url( home_url() ),
);

$active_nav       = '';
$user_id          = get_current_user_id();
$display_name     = tutor_utils()->display_name( $user_id );
$edit_profile_url = Dashboard::get_account_page_url( 'settings' ) . '?tab=account';
?>

<div x-data="tutorHeader()" class="tutor-dashboard-header">
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
				<?php
				// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
				do_action( 'tutor_dashboard/before_header_button' );
				?>
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
					x-init="$watch('open', value => { if (window.innerWidth <= 768) { document.body.style.overflow = value ? 'hidden' : ''; } })"
					@resize.window="
						if (window.innerWidth <= 768 && open) { document.body.style.overflow = open ? 'hidden' : ''; }
						else if (window.innerWidth >= 768 && open) { document.body.style.overflow = '' }
					"
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

						<div class="tutor-profile-header-top tutor-flex tutor-hidden tutor-sm-block tutor-items-center tutor-px-7 tutor-py-5">
							<?php
							Button::make()
								->label( __( 'Back', 'tutor' ) )
								->variant( Variant::GHOST )
								->size( Size::X_SMALL )
								->icon( Icon::LEFT, 'left', 20, 20 )
								->icon_only()
								->attr( '@click', 'hide()' )
								->render();
							?>
							<h4 class="tutor-profile-header-title tutor-text-h4 tutor-font-semibold">
								<?php esc_html_e( 'Account', 'tutor' ); ?>
							</h4>
							<?php
							Button::make()
								->variant( Variant::GHOST )
								->size( Size::X_SMALL )
								->icon( Icon::SETTING, 'left', 20, 20 )
								->tag( 'a' )
								->icon_only()
								->attr( 'href', esc_url( Dashboard::get_account_page_url( 'settings' ) ) )
								->render();
							?>
						</div>

						<div class="tutor-user-profile-info tutor-flex tutor-flex-column tutor-sm-px-7 tutor-sm-py-5">
							<div class="tutor-avatar tutor-border tutor-border-brand-secondary">
								<?php echo get_avatar( get_current_user_id(), 48 ); ?>
							</div>
							<div class="tutor-user-profile-meta tutor-flex tutor-flex-column tutor-items-center tutor-gap-1">
								<div class="tutor-text-medium tutor-text-primary tutor-font-semibold">
									<?php echo esc_html( wp_get_current_user()->display_name ); ?>
								</div>
								<div class="tutor-text-tiny tutor-text-secondary">
									<?php echo esc_html( wp_get_current_user()->user_email ); ?>
								</div>
							</div>
							<?php if ( User::is_student() && User::is_student_view() ) : ?>
							<a href="<?php echo esc_url( $edit_profile_url ); ?>" class="tutor-edit-profile-link tutor-hidden tutor-sm-block tutor-">
								<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
							</a>
							<?php endif; ?>
						</div>
						
						<?php
						if ( ! User::is_instructor( $user_id ) && ! User::is_admin( $user_id ) ) {
							$instructor_status = tutor_utils()->instructor_status( 0, false );
							$instructor_status = is_string( $instructor_status ) ? strtolower( $instructor_status ) : '';

							if ( Instructors_List::STATUS_PENDING === $instructor_status ) {
								$applied_on = get_user_meta( $user_id, '_is_tutor_instructor', true );
								$applied_on = tutor_i18n_get_formated_date( $applied_on, get_option( 'date_format' ) );
								?>
								<div class="tutor-w-full tutor-sm-px-7 tutor-surface-l1">
									<div class="tutor-flex tutor-sm-items-center tutor-gap-3 tutor-py-4 tutor-px-4 tutor-surface-warning-hover tutor-rounded-sm">
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
								</div>
								<?php
							} elseif ( Instructors_List::STATUS_BLOCKED !== $instructor_status ) {
								?>
								<div class="tutor-w-full tutor-sm-px-7 tutor-surface-l1">
									<a href="<?php echo esc_url( tutor_utils()->instructor_register_url() ); ?>" class="tutor-btn tutor-btn-primary-soft tutor-btn-small tutor-gap-2 tutor-btn-block">
										<?php tutor_utils()->render_svg_icon( Icon::INSTRUCTOR ); ?>
										<?php esc_html_e( 'Become an Instructor', 'tutor' ); ?>
									</a>
								</div>
								<?php
							}
						}
						if ( User::can_switch_mode( $user_id ) ) {
								$current_mode = User::get_current_view_mode();
								$button_label = User::VIEW_AS_INSTRUCTOR === $current_mode ? esc_html__( 'View as student', 'tutor' ) : esc_html__( 'View as instructor', 'tutor' );
							?>
							<div class="tutor-w-full tutor-sm-px-7 tutor-surface-l1">
								<?php
								Button::make()
								->label( $button_label )
								->size( Size::MEDIUM )
								->variant( Variant::PRIMARY_SOFT )
								->icon( Icon::RELOAD )
								->attr( ':disabled', 'profileSwitchMutation?.isPending' )
								->attr( ':class', "{ 'tutor-btn-loading': profileSwitchMutation?.isPending }" )
								->attr( 'class', 'tutor-w-full' )
								->attr( '@click', "profileSwitchMutation.mutate('{$current_mode}')" )
								->render();
								?>
							</div> 
						<?php } ?>
					</div>
					<div class="tutor-dashboard-header-user-popover-menu-wrapper">
						<ul class="tutor-dashboard-header-user-popover-menu">
							<?php
							foreach ( $menu_items as $key => $item ) {
								$icon = ( $key === $active_nav && isset( $item['icon_active'] ) ) ? $item['icon_active'] : $item['icon'];
								?>
								<li>
									<a href="<?php echo esc_url( $item['url'] ?? '#' ); ?>" class="<?php echo ( $key === $active_nav ) ? 'active' : ''; ?> tutor-small">
										<?php tutor_utils()->render_svg_icon( $icon, 20, 20 ); ?>
										<span><?php echo esc_html( $item['title'] ); ?></span>
									</a>
								</li>
								<?php
							}
							?>
						</ul>
						<div class="tutor-mobile-logout-wrapper tutor-text-center tutor-px-7 tutor-hidden tutor-sm-block">
							<?php
							$logout_btn = $menu_items['logout'] ?? '';
							if ( ! empty( $logout_btn ) ) :
								?>
							<a href="<?php echo esc_url( $logout_btn['url'] ?? '#' ); ?>" class="tutor-btn tutor-small">
								<?php tutor_utils()->render_svg_icon( $icon, 20, 20 ); ?>
								<span><?php echo esc_html( $logout_btn['title'] ); ?></span>
							</a>
							<?php endif; ?>
							<div class="tutor-tiny tutor-text-secondary tutor-py-5">
								<?php
									/* translators: %s: Tutor LMS version number */
									echo esc_html( sprintf( __( 'Version %s', 'tutor' ), TUTOR_VERSION ) );
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
