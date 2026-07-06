<?php
/**
 * Tutor dashboard sidebar.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://www.themeum.com/
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\SvgIcon;
use TUTOR\Dashboard;
use TUTOR\Icon;
use TUTOR\User;

global $wp_query;

$dashboard_page_slug = '';
$dashboard_page_name = '';
if ( isset( $wp_query->query_vars['tutor_dashboard_page'] ) && $wp_query->query_vars['tutor_dashboard_page'] ) {
	$dashboard_page_slug = $wp_query->query_vars['tutor_dashboard_page'];
	$dashboard_page_name = $wp_query->query_vars['tutor_dashboard_page'];
}
/**
 * Getting dashboard sub pages
 */
if ( isset( $wp_query->query_vars['tutor_dashboard_sub_page'] ) && $wp_query->query_vars['tutor_dashboard_sub_page'] ) {
	$dashboard_page_name = $wp_query->query_vars['tutor_dashboard_sub_page'];
	if ( $dashboard_page_slug ) {
		$dashboard_page_name = $dashboard_page_slug . '/' . $dashboard_page_name;
	}
}
$dashboard_page_name = apply_filters( 'tutor_dashboard_sub_page_template', $dashboard_page_name );
$dashboard_pages     = tutor_utils()->tutor_dashboard_nav_ui_items();

?>
<div class="tutor-dashboard-sidebar">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tutor-dashboard-sidebar-logo">
		<?php
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		$logo_url       = $custom_logo_id ? wp_get_attachment_image_url( $custom_logo_id, 'full' ) : false;

		if ( $logo_url ) :
			?>
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
		<?php else : ?>
			<span class="site-title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
		<?php endif; ?>
	</a>
	<div class="tutor-dashboard-sidebar-nav" role="navigation">
		<ul>
			<?php
			foreach ( $dashboard_pages as $dashboard_key => $dashboard_page ) {
				$menu_title = $dashboard_page;
				$menu_link  = tutor_utils()->get_tutor_dashboard_page_permalink( $dashboard_key );

				if ( is_array( $dashboard_page ) ) {
					$menu_title       = tutor_utils()->array_get( 'title', $dashboard_page );
					$menu_icon_name   = tutor_utils()->array_get( 'icon', $dashboard_page, ( isset( $dashboard_page['icon'] ) ? $dashboard_page['icon'] : '' ) );
					$active_icon_name = tutor_utils()->array_get( 'active_icon', $dashboard_page, ( isset( $dashboard_page['active_icon'] ) ? $dashboard_page['active_icon'] : $menu_icon_name ) );

					// Add new menu item property "url" for custom link.
					if ( isset( $dashboard_page['url'] ) ) {
						$menu_link = $dashboard_page['url'];
					}
				}

				$li_class = "tutor-dashboard-menu-{$dashboard_key}";
				if ( 'index' === $dashboard_key ) {
					$dashboard_key = '';
				}
				$is_active_menu  = $dashboard_key === $dashboard_page_slug;
				$active_class    = $is_active_menu ? 'active' : '';
				$data_no_instant = 'logout' === $dashboard_key ? 'data-no-instant' : '';
				$menu_link       = apply_filters( 'tutor_dashboard_menu_link', $menu_link, $menu_title );
				?>
				<li class="<?php echo esc_attr( $li_class ); ?>">
					<a <?php echo esc_html( $data_no_instant ); ?> href="<?php echo esc_url( $menu_link ); ?>" class='<?php echo esc_attr( $active_class ); ?>'>
						<?php
						SvgIcon::make()
							->name( ( $is_active_menu && ! tutor_utils()->is_kids_mode() ) ? $active_icon_name : $menu_icon_name )
							->size( tutor_utils()->is_kids_mode() ? Size::SIZE_24 : Size::SIZE_20 )
							->render();
						?>
						<span>
							<?php echo esc_html( $menu_title ); ?>
						</span>
					</a>
				</li>
				<?php
			}
			?>
		</ul>
	</div>
	<?php if ( User::is_student_view() ) : ?>
	<a href="<?php echo esc_url( tutor_utils()->course_archive_page_url() ); ?>" target="_blank" class="tutor-see-all-courses">
		<?php esc_html_e( 'Explore Courses', 'tutor' ); ?>
		<?php
		SvgIcon::make()
			->name( Icon::RIGHT_ARROW_UP )
			->size( tutor_utils()->is_kids_mode() ? Size::SIZE_24 : Size::SIZE_20 )
			->flip_rtl()
			->render();
		?>
	</a>
	<?php endif; ?>
</div>
<?php
	tutor_load_template(
		'dashboard.components.sidebar-nav-mobile',
		array(
			'dashboard_pages'     => $dashboard_pages,
			'dashboard_page_slug' => $dashboard_page_slug,
		)
	);
	?>
