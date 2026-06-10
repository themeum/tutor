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
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use TUTOR\Dashboard;

if ( ! tutor_utils()->count( $dashboard_pages ) ) {
	return;
}

$active_nav        = '';
$visible_nav_items = array_slice( $dashboard_pages, 0, 2, true );
$more_nav_items    = array_slice( $dashboard_pages, 2, null, true );

// Insert the Explore link after the first visible navigation item.
$explore_nav_item = array(
	'title' => __( 'Explore', 'tutor' ),
	'icon'  => Icon::EXPLORE,
	'url'   => tutor_utils()->course_archive_page_url(),
);

$visible_nav_items = array_slice( $visible_nav_items, 0, 1, true ) +
		array( 'explore' => $explore_nav_item ) +
		array_slice( $visible_nav_items, 1, null, true );

?>
<div class="tutor-dashboard-nav-mobile" role="navigation">
	<ul class="tutor-dashboard-nav-mobile-list">
		<?php
		foreach ( $visible_nav_items as $key => $item ) {
			if ( 'index' === $key ) {
				$key = '';
			}

			$menu_title = $item;
			$menu_link  = tutor_utils()->get_tutor_dashboard_page_permalink( $key );

			if ( is_array( $item ) ) {
				$menu_title       = tutor_utils()->array_get( 'title', $item );
				$menu_icon_name   = tutor_utils()->array_get( 'icon', $item, ( isset( $item['icon'] ) ? $item['icon'] : '' ) );
				$active_icon_name = tutor_utils()->array_get( 'active_icon', $item, ( isset( $item['active_icon'] ) ? $item['active_icon'] : $menu_icon_name ) );

				// Add new menu item property "url" for custom link.
				if ( isset( $item['url'] ) ) {
					$menu_link = $item['url'];
				}
			}

			$is_active_menu = $key === $dashboard_page_slug;
			$active_class   = $is_active_menu ? 'active' : '';
			$menu_link      = apply_filters( 'tutor_dashboard_menu_link', $menu_link, $menu_title );
			?>
			<li>
				<a class="<?php echo esc_attr( $active_class ); ?>" href="<?php echo esc_url( $menu_link ?? '#' ); ?>">
					<span class="tutor-dashboard-nav-mobile-icon">
						<?php
						SvgIcon::make()
							->name( ( $is_active_menu && ! tutor_utils()->is_kids_mode() ) ? $active_icon_name : $menu_icon_name )
							->size( Size::SIZE_20 )
							->render();
						?>
					</span>
					<span class="tutor-tiny tutor-dashboard-nav-mobile-text"><?php echo esc_html( $menu_title ); ?></span>
				</a>
			</li>
			<?php
		}
		?>
		<li>
			<a href="<?php echo esc_url( Dashboard::get_account_page_url( 'profile' ) ); ?>">
				<div class="tutor-dashboard-nav-mobile-icon">
					<?php
					Avatar::make()
						->user( get_current_user_id() )
						->size( Size::SIZE_20 )
						->render();
					?>
				</div>
				<span class="tutor-tiny tutor-dashboard-nav-mobile-text"><?php esc_html_e( 'Profile', 'tutor' ); ?></span>
			</a>
		</li>
		<?php
		if ( tutor_utils()->count( $more_nav_items ) ) :
			?>
			<li x-data="tutorPopover({ placement: 'top-end', offset: 16 })">
				<button
					type="button"
					x-ref="trigger"
					@click="toggle()"
					:aria-expanded="open ? 'true' : 'false'"
					aria-haspopup="true"
				>
					<span class="tutor-dashboard-nav-mobile-icon">
						<?php SvgIcon::make()->name( Icon::ELLIPSES )->size( 20 )->render(); ?>
					</span>
					<span class="tutor-tiny tutor-dashboard-nav-mobile-text"><?php esc_html_e( 'More', 'tutor' ); ?></span>
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
					x-transition.origin.bottom.right
				>
					<ul>
						<?php
						foreach ( $more_nav_items as $key => $item ) {
							$menu_title = $item;
							$menu_link  = tutor_utils()->get_tutor_dashboard_page_permalink( $key );

							if ( is_array( $item ) ) {
								$menu_title       = tutor_utils()->array_get( 'title', $item );
								$menu_icon_name   = tutor_utils()->array_get( 'icon', $item, ( isset( $item['icon'] ) ? $item['icon'] : '' ) );
								$active_icon_name = tutor_utils()->array_get( 'active_icon', $item, ( isset( $item['active_icon'] ) ? $item['active_icon'] : $menu_icon_name ) );

								// Add new menu item property "url" for custom link.
								if ( isset( $item['url'] ) ) {
									$menu_link = $item['url'];
								}
							}

							$is_active_menu = $key === $dashboard_page_slug;
							$active_class   = $is_active_menu ? 'active' : '';
							$menu_link      = apply_filters( 'tutor_dashboard_menu_link', $menu_link, $menu_title );
							?>
								<li role="none">
									<a role="menuitem" class="<?php echo esc_attr( $active_class ); ?>" href="<?php echo esc_url( $menu_link ?? '#' ); ?>" @click="open = false">
										<?php
										SvgIcon::make()
											->name( ( $is_active_menu && ! tutor_utils()->is_kids_mode() ) ? $active_icon_name : $menu_icon_name )
											->size( tutor_utils()->is_kids_mode() ? Size::SIZE_24 : Size::SIZE_20 )
											->render();
										?>
										<span class="tutor-tiny"><?php echo esc_html( $menu_title ); ?></span>
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
