<?php
/**
 * Tutor dashboard header.
 * Reusable component for displaying the dashboard header.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Avatar;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use TUTOR\Dashboard;
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
use TUTOR\Icon;
use TUTOR\Instructors_List;
use TUTOR\User;
use Tutor\Models\CourseModel;

$menu_items           = Dashboard::get_account_pages();
$menu_items['logout'] = array(
	'title' => esc_html__( 'Logout', 'tutor' ),
	'icon'  => Icon::LOGOUT,
	'url'   => wp_logout_url( tutor_utils()->tutor_dashboard_url() ),
);

$active_nav                   = '';
$user_id                      = get_current_user_id();
$display_name                 = tutor_utils()->display_name( $user_id );
$edit_profile_url             = Dashboard::get_account_page_url( 'settings' ) . '?tab=account';
$is_become_instructor_enabled = tutor_utils()->get_option( 'enable_become_instructor_btn' );

$is_instructor_view = User::is_instructor_view();
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
			<?php
			if ( $is_instructor_view ) :
				$course_post_type       = apply_filters( 'tutor_dashboard_course_list_post_type', array( tutor()->course_post_type ) );
				$active_course_count    = CourseModel::get_courses_by_instructor( $user_id, CourseModel::STATUS_PUBLISH, 0, 0, true, $course_post_type, '' );
				$enrolled_student_count = (int) tutor_utils()->get_total_students_by_instructor( $user_id );
				?>
				<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-tiny">
					<?php
						echo wp_kses(
							sprintf(
								/* translators: %s is the formatted active course count. */
								_n(
									'<span class="tutor-font-medium">%s</span> active course',
									'<span class="tutor-font-medium">%s</span> active courses',
									$active_course_count,
									'tutor'
								),
								esc_html( number_format_i18n( $active_course_count ) )
							),
							array(
								'span' => array(
									'class' => true,
								),
							)
						);
					?>
					<span class="tutor-text-subdued">&bull;</span>
					<?php
						echo wp_kses(
							sprintf(
								/* translators: %s is the formatted enrolled student count. */
								_n(
									'<span class="tutor-font-medium">%s</span> student enrolled',
									'<span class="tutor-font-medium">%s</span> students enrolled',
									$enrolled_student_count,
									'tutor'
								),
								esc_html( number_format_i18n( $enrolled_student_count ) )
							),
							array(
								'span' => array(
									'class' => true,
								),
							)
						);
					?>
				</div>
			<?php endif; ?>
		</div>
		<div class="tutor-dashboard-header-right">
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
					aria-label="<?php esc_attr_e( 'Open user menu', 'tutor' ); ?>"
				>
					<?php Avatar::make()->user( $user_id )->size( Size::SIZE_32 )->render(); ?>
					<?php
						SvgIcon::make()
						->name( Icon::USER_CIRCLE )
						->size( Size::SIZE_20 )
						->attr( 'class', 'tutor-hidden tutor-sm-inline-block' )
						->render();
					?>
				</button>

				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					x-transition.origin.top.right
					class="tutor-popover tutor-dashboard-header-user-popover"
				>
					<div class="tutor-dashboard-header-user-popover-profile">

						<div class="tutor-profile-header-top tutor-hidden tutor-sm-flex tutor-items-center tutor-px-6 tutor-py-5">
							<?php
							Button::make()
								->label( __( 'Back', 'tutor' ) )
								->variant( Variant::GHOST )
								->size( Size::X_SMALL )
								->icon( Icon::LEFT, 'left', 20 )
								->icon_only()
								->flip_rtl()
								->attr( '@click', 'hide()' )
								->render();
							?>
							<h4 class="tutor-profile-header-title tutor-text-h4 tutor-font-semibold tutor-my-none">
								<?php esc_html_e( 'Account', 'tutor' ); ?>
							</h4>
							<?php
							Button::make()
								->variant( Variant::GHOST )
								->size( Size::X_SMALL )
								->icon( Icon::SETTING, 'left', 20 )
								->tag( 'a' )
								->icon_only()
								->attr( 'href', esc_url( Dashboard::get_account_page_url( 'settings' ) ) )
								->render();
							?>
						</div>

						<div class="tutor-user-profile-info">
							<?php Avatar::make()->user( $user_id )->size( Size::SIZE_48 )->bordered()->render(); ?>
							<div class="tutor-user-profile-meta tutor-flex tutor-flex-column tutor-items-center tutor-gap-1">
								<div class="tutor-text-medium tutor-text-primary tutor-font-semibold">
									<?php echo esc_html( wp_get_current_user()->display_name ); ?>
								</div>
								<div class="tutor-text-tiny tutor-text-secondary">
									<?php echo esc_html( wp_get_current_user()->user_email ); ?>
								</div>
							</div>
							<a href="<?php echo esc_url( $edit_profile_url ); ?>" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon tutor-ml-auto tutor-force-hidden tutor-force-sm-flex">
								<?php SvgIcon::make()->name( Icon::EDIT_2 )->size( Size::SIZE_20 )->render(); ?>
							</a>
						</div>

						<?php
						if ( ! User::is_instructor( $user_id ) && ! User::is_admin( $user_id ) ) {
							$instructor_status = tutor_utils()->instructor_status( 0, false );
							$instructor_status = is_string( $instructor_status ) ? strtolower( $instructor_status ) : '';

							if ( Instructors_List::STATUS_PENDING === $instructor_status ) {
								$applied_on = get_user_meta( $user_id, '_is_tutor_instructor', true );
								$applied_on = tutor_i18n_get_formated_date( $applied_on, get_option( 'date_format' ) );
								?>
								<div class="tutor-w-full tutor-sm-px-6">
									<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-py-2 tutor-px-4 tutor-surface-warning-hover-2 tutor-rounded-sm">
										<span class="tutor-flex-center">
											<?php SvgIcon::make()->name( Icon::INFO_OCTAGON )->size( 12 )->color( Color::WARNING )->render(); ?>
										</span>
										<span class="tutor-p3 tutor-text-warning ">
										<?php esc_html_e( 'Application Under Review', 'tutor' ); ?>
										</span>
									</div>
								</div>
								<?php
							} elseif ( Instructors_List::STATUS_BLOCKED !== $instructor_status && $is_become_instructor_enabled ) {
								?>
								<div class="tutor-w-full tutor-sm-px-6">
									<a href="<?php echo esc_url( tutor_utils()->instructor_register_url() ); ?>" class="tutor-btn tutor-btn-primary-soft tutor-btn-x-small tutor-btn-block tutor-profile-switch-button">
										<?php SvgIcon::make()->name( Icon::INSTRUCTOR )->render(); ?>
										<?php esc_html_e( 'Become an Instructor', 'tutor' ); ?>
									</a>
								</div>
								<?php
							}
						}
						if ( User::can_switch_mode( $user_id ) ) {
								$current_mode = User::get_current_view_mode();
								$button_label = User::VIEW_AS_INSTRUCTOR === $current_mode ? esc_html__( 'Switch to Learner', 'tutor' ) : esc_html__( 'Switch to Instructor', 'tutor' );
							?>
							<div class="tutor-w-full tutor-sm-px-6">
								<?php
								Button::make()
								->label( $button_label )
								->size( Size::X_SMALL )
								->variant( Variant::PRIMARY_SOFT )
								->icon( Icon::RELOAD_2 )
								->block()
								->attr( ':disabled', 'profileSwitchMutation?.isPending' )
								->attr( ':class', "{ 'tutor-btn-loading': profileSwitchMutation?.isPending }" )
								->attr( 'class', 'tutor-profile-switch-button' )
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
										<?php SvgIcon::make()->name( $icon )->size( 20 )->flip_rtl( 'logout' === $key ? true : false )->render(); ?>
										<span><?php echo esc_html( $item['title'] ); ?></span>
									</a>
								</li>
								<?php
							}
							?>
						</ul>
						<div class="tutor-mobile-logout-wrapper tutor-text-center tutor-px-6 tutor-pb-7 tutor-hidden tutor-sm-block">
							<?php
							$logout_btn = $menu_items['logout'] ?? '';
							if ( ! empty( $logout_btn ) ) :
								?>
							<a href="<?php echo esc_url( $logout_btn['url'] ?? '#' ); ?>" class="tutor-btn tutor-small">
								<?php SvgIcon::make()->name( $icon )->size( 20 )->render(); ?>
								<span><?php echo esc_html( $logout_btn['title'] ); ?></span>
							</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
