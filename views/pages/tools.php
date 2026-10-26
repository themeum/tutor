<?php
/**
 * Tools page
 *
 * @package Tutor\Views
 * @subpackage Tutor\Tools
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

$pages             = isset( $pages ) && is_array( $pages ) ? $pages : array();
$current_page      = isset( $current_page ) ? sanitize_key( $current_page ) : '';
$current_page_data = '' !== $current_page && isset( $pages[ $current_page ] ) && is_array( $pages[ $current_page ] ) ? $pages[ $current_page ] : array();
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Tools', 'tutor' ); ?></h1>
	<hr class="wp-header-end">

	<nav class="nav-tab-wrapper tutor-nav-tab-wrapper">
		<?php
		if ( tutor_utils()->count( $pages ) ) {
			foreach ( $pages as $key => $page ) {
				$title        = is_array( $page ) ? $page['title'] : $page;
				$active_class = (string) $key === $current_page ? 'nav-tab-item-active' : '';
				$url          = add_query_arg( array( 'sub_page' => $key ) );
				echo '<a href="' . esc_url( $url ) . '" class="nav-tab-item ' . esc_attr( $active_class ) . '">' . esc_html( $title ) . '</a>';
			}
		}
		?>
	</nav>

	<div id="tutor-tools-page-wrap" class="tutor-tools-page-wrap">

		<?php
		do_action( 'tutor_tools_page_' . $current_page . '_before' );

		if ( ! empty( $current_page_data['view_path'] ) && file_exists( $current_page_data['view_path'] ) ) {
			include $current_page_data['view_path'];
		} elseif ( '' !== $current_page && file_exists( tutor()->path . 'views/pages/tools/' . $current_page . '.php' ) ) {
			include tutor()->path . 'views/pages/tools/' . $current_page . '.php';
		} else {
			do_action( 'tutor_tools_page_' . $current_page );
		}

		do_action( 'tutor_tools_page_' . $current_page . '_after' );
		?>
	</div>

</div>
