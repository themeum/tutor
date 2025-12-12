<?php
/**
 * Tutor dashboard nav mobile.
 *
 * @package tutor
 */

use TUTOR\Icon;

$nav_mobile_items = array(
	'home'    => array(
		'title'       => esc_html__( 'Home', 'tutor' ),
		'icon'        => Icon::HOME,
		'active_icon' => Icon::HOME_FILL,
		'url'         => home_url( '/dashboard/' ),
	),
	'explore' => array(
		'title'       => esc_html__( 'Explore', 'tutor' ),
		'icon'        => Icon::EXPLORE,
		'active_icon' => Icon::EXPLORE_FILL,
		'url'         => home_url( '/dashboard/explore/' ),
	),
	'courses' => array(
		'title'       => esc_html__( 'Courses', 'tutor' ),
		'icon'        => Icon::COURSES,
		'active_icon' => Icon::COURSES_FILL,
		'url'         => home_url( '/dashboard/courses/' ),
	),
	'profile' => array(
		'title' => esc_html__( 'Profile', 'tutor' ),
		'image' => 'https://i.pravatar.cc/300',
		'url'   => home_url( '/dashboard/profile/' ),
	),
);

$nav_mobile_more_items = array(
	'discussions' => array(
		'title'       => esc_html__( 'Discussions', 'tutor' ),
		'icon'        => Icon::QA,
		'active_icon' => Icon::QA_FILL,
		'url'         => home_url( '/dashboard/discussions/' ),
	),
	'calendar'    => array(
		'title'       => esc_html__( 'Calendar', 'tutor' ),
		'icon'        => Icon::CALENDAR_2,
		'active_icon' => Icon::CALENDAR_2_FILL,
		'url'         => home_url( '/dashboard/calendar/' ),
	),
	'account'     => array(
		'title'       => esc_html__( 'Account', 'tutor' ),
		'icon'        => Icon::PROFILE_CIRCLE,
		'active_icon' => Icon::PROFILE_CIRCLE_FILL,
		'url'         => home_url( '/dashboard/account/' ),
	),
);

$active_nav = 'profile';

?>
<div class="tutor-dashboard-nav-mobile">
	<ul class="tutor-dashboard-nav-mobile-list">
		<?php
		foreach ( $nav_mobile_items as $key => $item ) {
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
		<li x-data="tutorPopover({ placement: 'top-start', offset: 16 })">
			<button
				type="button"
				x-ref="trigger"
				@click="toggle()"
				:aria-expanded="open ? 'true' : 'false'"
				aria-haspopup="true"
			>
				<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL, 20, 20 ); ?>
				<span><?php esc_html_e( 'More', 'tutor' ); ?></span>
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
					foreach ( $nav_mobile_more_items as $m_key => $m_item ) {
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
				</ul>
			</div>
		</li>
	</ul>
</div>
