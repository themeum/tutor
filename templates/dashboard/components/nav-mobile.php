<?php
/**
 * Tutor dashboard nav mobile.
 *
 * @package tutor
 */

use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use TUTOR\Icon;
use Tutor\Components\Button;

$active_nav = 'profile';

if ( ! empty( $dashboard_pages ) && is_array( $dashboard_pages ) && empty( $dashboard_pages_more_items ) ) {
	$dashboard_pages_more_items = array_slice( $dashboard_pages, 4, null, true );
	$dashboard_pages            = array_slice( $dashboard_pages, 0, 4, true );
}

?>
<div class="tutor-dashboard-nav-mobile">
	<ul class="tutor-dashboard-nav-mobile-list">
		<?php
		foreach ( $dashboard_pages as $key => $item ) {
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
		<?php
		if ( ! empty( $dashboard_pages_more_items ) && is_array( $dashboard_pages_more_items ) && count( $dashboard_pages_more_items ) > 0 ) :
			?>
			<li x-data="tutorPopover({ placement: 'top-end', offset: 16 })">
				<?php
				Button::make()
					->tag( 'button' )
					->size( Size::SMALL )
					->variant( Variant::SECONDARY )
					->icon_only( true )
					->icon( Icon::THREE_DOTS_VERTICAL, 'left', 20, 20 )
					->attr( 'type', 'button' )
					->attr( 'x-ref', 'trigger' )
					->attr( '@click', 'toggle()' )
					->attr( ':aria-expanded', "open ? 'true' : 'false'" )
					->attr( 'aria-haspopup', 'true' )
					->attr( 'aria-label', esc_attr__( 'More', 'tutor' ) )
					->render();
				?>
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
						foreach ( $dashboard_pages_more_items as $key => $item ) {
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
