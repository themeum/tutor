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
			$active_class = ( $key === $active_nav ) ? 'active' : '';
			?>
			<li>
				<a class="<?php echo esc_attr( $active_class ); ?>" href="<?php echo esc_url( $item['url'] ?? '#' ); ?>">
					<?php if ( isset( $item['image'] ) ) : ?>
						<img 
							src="<?php echo esc_url( $item['image'] ); ?>" 
							alt="<?php echo esc_attr( $item['title'] ); ?>" 
							width="20" 
							height="20" 
							class="tutor-avatar tutor-avatar-20 <?php echo ( $key === $active_nav ) ? 'tutor-border tutor-border-brand' : ''; ?>"
						>
					<?php else : ?>
						<?php tutor_utils()->render_svg_icon( ( $key === $active_nav ) ? $item['active_icon'] : $item['icon'], 20, 20 ); ?>
					<?php endif; ?>
					<span><?php echo esc_html( $item['title'] ); ?></span>
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
					foreach ( $dashboard_pages_more_items as $m_key => $m_item ) {
						$active_class = ( $m_key === $active_nav ) ? 'active' : '';
						$icon         = ( $m_key === $active_nav ) ? $m_item['active_icon'] : $m_item['icon'];
						?>
							<li role="none">
								<a role="menuitem" class="<?php echo esc_attr( $active_class ); ?>" href="<?php echo esc_url( $m_item['url'] ?? '#' ); ?>" @click="open = false">
								<?php tutor_utils()->render_svg_icon( $icon, 18, 18 ); ?>
									<span><?php echo esc_html( $m_item['title'] ); ?></span>
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
