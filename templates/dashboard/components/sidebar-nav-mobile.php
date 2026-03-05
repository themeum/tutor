<?php
/**
 * Tutor dashboard nav mobile.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://www.themeum.com/
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Avatar;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use TUTOR\Icon;
use Tutor\Components\Button;
use TUTOR\Dashboard;

if ( ! tutor_utils()->count( $dashboard_pages ) ) {
	return;
}

$active_nav        = '';
$visible_nav_items = array_slice( $dashboard_pages, 0, 3, true );
$more_nav_items    = array_slice( $dashboard_pages, 3, null, true );

?>
<div class="tutor-dashboard-nav-mobile">
	<ul class="tutor-dashboard-nav-mobile-list">
		<?php
		foreach ( $visible_nav_items as $key => $item ) {
			if ( 'index' === $key ) {
				$key = '';
			}
			$active_class = $key === $dashboard_page_slug ? 'active' : '';
			$menu_link    = tutor_utils()->get_tutor_dashboard_page_permalink( $key );
			// Add new menu item property "url" for custom link.
			if ( isset( $item['url'] ) ) {
				$menu_link = $item['url'];
			}
			$menu_icon_name = tutor_utils()->array_get( 'icon', $item, ( isset( $item['icon'] ) ? $item['icon'] : '' ) );
			?>
			<li>
				<a class="<?php echo esc_attr( $active_class ); ?>" href="<?php echo esc_url( $menu_link ?? '#' ); ?>">
					<?php tutor_utils()->render_svg_icon( $menu_icon_name ); ?>
					<span class="tutor-tiny"><?php echo esc_html( $item['title'] ); ?></span>
				</a>
			</li>
			<?php
		}
		?>
		<li>
			<a href="<?php echo esc_url( Dashboard::get_account_page_url( 'profile' ) ); ?>">
				<div class="tutor-user-avatar-mobile tutor-rounded-sm"><?php echo get_avatar( get_current_user_id(), 16 ); ?></div>
				<!-- <img style="width: 16px; height: 16px; object-fit: cover;" class="tutor-rounded-lg" src="<?php echo esc_url( get_avatar_url( get_current_user_id() ) ); ?>" alt=""> -->
				<span class="tutor-tiny"><?php esc_html_e( 'Profile', 'tutor' ); ?></span>
			</a>
		</li>
		<?php
		if ( tutor_utils()->count( $more_nav_items ) ) :
			?>
			<li x-data="tutorPopover({ placement: 'top-end', offset: 16 })">
				<?php
				// Button::make()
				// ->tag( 'button' )
				// ->label( 'More' )
				// ->size( Size::SMALL )
				// ->variant( Variant::SECONDARY )
				// ->icon( Icon::THREE_DOTS_VERTICAL, 'left', 16, 16 )
				// ->attr( 'type', 'button' )
				// ->attr( 'x-ref', 'trigger' )
				// ->attr( '@click', 'toggle()' )
				// ->attr( ':aria-expanded', "open ? 'true' : 'false'" )
				// ->attr( 'aria-haspopup', 'true' )
				// ->attr( 'aria-label', esc_attr__( 'More', 'tutor' ) )
				// ->attr( 'class', 'tutor-tiny' )
				// ->render();
				?>
				<button
					type="button"
					x-ref="trigger"
					@click="toggle()"
					:aria-expanded="open ? 'true' : 'false'"
					aria-haspopup="true"
				>
					<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL, 16, 16 ); ?>
					<span class="tutor-tiny"><?php esc_html_e( 'More', 'tutor' ); ?></span>
				</button>
				<!-- Popover panel -->
				<div
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					class="tutor-popover tutor-dashboard-nav-mobile-more-popover"
					role="menu"
					aria-label="<?php echo esc_attr( $item['title'] ); ?>"
				>
					<ul>
						<?php
						foreach ( $more_nav_items as $key => $item ) {
							$active_class = ( $key === $active_nav ) ? 'active' : '';
							$icon         = ( $key === $active_nav ) ? $item['active_icon'] : $item['icon'];
							$menu_link    = tutor_utils()->get_tutor_dashboard_page_permalink( $key );
							// Add new menu item property "url" for custom link.
							if ( isset( $item['url'] ) ) {
								$menu_link = $item['url'];
							}
							$menu_icon_name = tutor_utils()->array_get( 'icon', $item, ( isset( $item['icon'] ) ? $item['icon'] : '' ) );
							$active_class   = $key === $dashboard_page_slug ? 'active' : '';
							?>
								<li role="none">
									<a role="menuitem" class="<?php echo esc_attr( $active_class ); ?>" href="<?php echo esc_url( $menu_link ?? '#' ); ?>" @click="open = false">
										<?php tutor_utils()->render_svg_icon( $menu_icon_name ); ?>
										<span class="tutor-tiny"><?php echo esc_html( $item['title'] ); ?></span>
									</a>
								</li>
							<?php
						}
						?>
					</nav>
				</div>
			</li>
		<?php endif ?>
	</ul>
</div>
