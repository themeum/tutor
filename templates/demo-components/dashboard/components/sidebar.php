<?php
/**
 * Tutor dashboard sidebar.
 *
 * @package tutor
 */

use TUTOR\Icon;

$menu_items  = array(
	'dashboard'   => array(
		'title'       => esc_html__( 'Dashboard', 'tutor' ),
		'icon'        => Icon::HOME,
		'active_icon' => Icon::HOME_FILL,
		'url'         => home_url( '/dashboard/' ),
	),
	'courses'     => array(
		'title'       => esc_html__( 'Courses', 'tutor' ),
		'icon'        => Icon::COURSES,
		'active_icon' => Icon::COURSES_FILL,
		'url'         => home_url( '/dashboard/courses/' ),
	),
	'notes'       => array(
		'title'       => esc_html__( 'Notes', 'tutor' ),
		'icon'        => Icon::NOTES,
		'active_icon' => Icon::NOTES_FILL,
		'url'         => home_url( '/dashboard/notes/' ),
	),
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
);
$active_menu = 'dashboard';

?>
<div class="tutor-dashboard-sidebar">
	<div class="tutor-dashboard-sidebar-logo">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( tutor()->url . 'assets/images/tutor-logo.png' ); ?>" alt="<?php esc_attr_e( 'Tutor LMS', 'tutor' ); ?>"> 
		</a>
	</div>
	<div class="tutor-dashboard-sidebar-nav">
		<ul>
			<?php
			foreach ( $menu_items as $key => $item ) {
				$active_class = ( $key === $active_menu ) ? 'active' : '';
				$icon         = ( $key === $active_menu ) ? $item['active_icon'] : $item['icon'];
				?>
				<li>
					<a class="<?php echo esc_attr( $active_class ); ?>" href="<?php echo esc_url( $item['url'] ); ?>">
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
