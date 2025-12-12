<?php
/**
 * Tutor dashboard sidebar.
 *
 * @package tutor
 */

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
	<div class="tutor-dashboard-sidebar-logo">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
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
	</div>
	<div class="tutor-dashboard-sidebar-nav">
		<ul>
			<?php
			// get reviews settings value.
			$disable = ! get_tutor_option( 'enable_course_review' );
			foreach ( $dashboard_pages as $dashboard_key => $dashboard_page ) {
				/**
				 * If not enable from settings then quit
				 *
				 *  @since v2.0.0
				 */
				if ( $disable && 'reviews' === $dashboard_key ) {
					continue;
				}

				$menu_title = $dashboard_page;
				$menu_link  = tutor_utils()->get_tutor_dashboard_page_permalink( $dashboard_key );
				$separator  = false;
				$menu_icon  = '';

				if ( is_array( $dashboard_page ) ) {
					$menu_title     = tutor_utils()->array_get( 'title', $dashboard_page );
					$menu_icon_name = tutor_utils()->array_get( 'icon', $dashboard_page, ( isset( $dashboard_page['icon'] ) ? $dashboard_page['icon'] : '' ) );
					if ( $menu_icon_name ) {
						$menu_icon = "<span class='{$menu_icon_name} tutor-dashboard-menu-item-icon'></span>";
					}
					// Add new menu item property "url" for custom link.
					if ( isset( $dashboard_page['url'] ) ) {
						$menu_link = $dashboard_page['url'];
					}
					if ( isset( $dashboard_page['type'] ) && 'separator' === $dashboard_page['type'] ) {
						$separator = true;
					}
				}
				if ( $separator ) {
					echo '<li class="tutor-dashboard-menu-divider"></li>';
					if ( $menu_title ) {
						?>
						<li class='tutor-dashboard-menu-divider-header'>
							<?php echo esc_html( $menu_title ); ?>
						</li>
						<?php
					}
				} else {
					$li_class = "tutor-dashboard-menu-{$dashboard_key}";
					if ( 'index' === $dashboard_key ) {
						$dashboard_key = '';
					}
					$active_class    = $dashboard_key === $dashboard_page_slug ? 'active' : '';
					$data_no_instant = 'logout' === $dashboard_key ? 'data-no-instant' : '';
					$menu_link       = apply_filters( 'tutor_dashboard_menu_link', $menu_link, $menu_title );
					?>
					<li>
						<a <?php echo esc_html( $data_no_instant ); ?> href="<?php echo esc_url( $menu_link ); ?>" class='<?php echo esc_attr( $active_class ); ?>'>
							<?php
							tutor_utils()->render_svg_icon( $menu_icon_name )
							?>
							<span>
								<?php echo esc_html( $menu_title ); ?>
							</span>
						</a>
					</li>
					<?php
				}
			}
			?>
		</ul>
	</div>
</div>
