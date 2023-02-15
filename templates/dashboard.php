<?php
/**
 * Template for displaying frontend dashboard
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

$is_by_short_code = isset( $is_shortcode ) && true === $is_shortcode;
if ( ! $is_by_short_code && ! defined( 'OTLMS_VERSION' ) ) {
	tutor_utils()->tutor_custom_header();
}

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

$user_id                   = get_current_user_id();
$user                      = get_user_by( 'ID', $user_id );
$enable_profile_completion = tutor_utils()->get_option( 'enable_profile_completion' );
$is_instructor             = tutor_utils()->is_instructor();

// URLS
$current_url  = tutor()->current_url;
$footer_url_1 = trailingslashit( tutor_utils()->tutor_dashboard_url( $is_instructor ? 'my-courses' : '' ) );
$footer_url_2 = trailingslashit( tutor_utils()->tutor_dashboard_url( $is_instructor ? 'question-answer' : 'my-quiz-attempts' ) );

// Footer links
$footer_links = array(
	array(
		'title'      => $is_instructor ? __( 'My Courses', 'tutor' ) : __( 'Dashboard', 'tutor' ),
		'url'        => $footer_url_1,
		'is_active'  => $footer_url_1 == $current_url,
		'icon_class' => 'ttr tutor-icon-dashboard',
	),
	array(
		'title'      => $is_instructor ? __( 'Q&A', 'tutor' ) : __( 'Quiz Attempts', 'tutor' ),
		'url'        => $footer_url_2,
		'is_active'  => $footer_url_2 == $current_url,
		'icon_class' => $is_instructor ? 'ttr  tutor-icon-question' : 'ttr tutor-icon-quiz-attempt',
	),
	array(
		'title'      => __( 'Menu', 'tutor' ),
		'url'        => '#',
		'is_active'  => false,
		'icon_class' => 'ttr tutor-icon-hamburger-o tutor-dashboard-menu-toggler',
	),
);

do_action( 'tutor_dashboard/before/wrap' );
?>

<div class="tutor-wrap tutor-wrap-parent tutor-dashboard tutor-frontend-dashboard tutor-dashboard-student tutor-pb-80">
	<div class="tutor-container">
		<div class="tutor-row tutor-d-flex tutor-justify-between tutor-frontend-dashboard-header">
			<div class="tutor-header-left-side tutor-dashboard-header tutor-col-md-6 tutor-d-flex tutor-align-center" style="border: none;">
				<div class="tutor-dashboard-header-avatar">
					<?php
					echo wp_kses(
						tutor_utils()->get_tutor_avatar( $user_id, 'xl' ),
						tutor_utils()->allowed_avatar_tags()
					);
					?>
				</div>

				<div class="tutor-user-info tutor-ml-24">
					<?php
					$instructor_rating = tutor_utils()->get_instructor_ratings( $user->ID );

					if ( current_user_can( tutor()->instructor_role ) ) {
						?>
						<div class="tutor-fs-4 tutor-fw-medium tutor-color-black tutor-dashboard-header-username">
							<?php echo esc_html( $user->display_name ); ?>
						</div>
						<div class="tutor-dashboard-header-stats">
							<div class="tutor-dashboard-header-ratings">
								<?php tutor_utils()->star_rating_generator_v2( $instructor_rating->rating_avg, $instructor_rating->rating_count, true ); ?>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="tutor-dashboard-header-display-name tutor-color-black">
							<div class="tutor-fs-5 tutor-dashboard-header-greetings">
								<?php esc_html_e( 'Hello', 'tutor' ); ?>,
							</div>
							<div class="tutor-fs-4 tutor-fw-medium tutor-dashboard-header-username">
								<?php echo esc_html( $user->display_name ); ?>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<div class="tutor-header-right-side tutor-col-md-6 tutor-d-flex tutor-justify-end tutor-mt-20 tutor-mt-md-0">
				<div class="tutor-d-flex tutor-align-center">
					<?php
					do_action( 'tutor_dashboard/before_header_button' );
					$instructor_status  = tutor_utils()->instructor_status( 0, false );
					$instructor_status  = is_string( $instructor_status ) ? strtolower( $instructor_status ) : '';
					$rejected_on        = get_user_meta( $user->ID, '_is_tutor_instructor_rejected', true );
					$info_style         = 'vertical-align: middle; margin-right: 7px;';
					$info_message_style = 'display:inline-block; color:#7A7A7A; font-size: 15px;';

					ob_start();
					if ( tutor_utils()->get_option( 'enable_become_instructor_btn' ) ) {
						?>
						<a id="tutor-become-instructor-button" class="tutor-btn tutor-btn-outline-primary" href="<?php echo esc_url( tutor_utils()->instructor_register_url() ); ?>">
							<i class="tutor-icon-user-bold"></i> &nbsp; <?php esc_html_e( 'Become an instructor', 'tutor' ); ?>
						</a>
						<?php
					}
					$become_button = ob_get_clean();

					if ( current_user_can( tutor()->instructor_role ) ) {
						$course_type = tutor()->course_post_type;
						?>
						<?php
						/**
						 * Render create course button based on free & pro
						 *
						 * @since v2.0.7
						 */
						if ( function_exists( 'tutor_pro' ) ) :
							?>
							<?php do_action( 'tutor_course_create_button' ); ?>
							<?php else : ?>
							<a href="<?php echo esc_url( admin_url( "post-new.php?post_type=$course_type" ) ); ?>" class="tutor-btn tutor-btn-outline-primary">
								<i class="tutor-icon-plus-square tutor-my-n4 tutor-mr-8"></i>
								<?php esc_html_e( 'Create a New Course', 'tutor' ); ?>
							</a>
					<?php endif; ?>
						<?php
					} elseif ( 'pending' == $instructor_status ) {
						$on = get_user_meta( $user->ID, '_is_tutor_instructor', true );
						$on = date( 'd F, Y', $on );
						echo '<span style="' . esc_attr( $info_message_style ) . '">
                                    <i class="dashicons dashicons-info tutor-color-warning" style=" ' . esc_attr( $info_style ) . '"></i>',
						esc_html__( 'Your Application is pending as of', 'tutor' ), ' <b>', esc_html( $on ), '</b>',
						'</span>';
					} elseif ( $rejected_on || $instructor_status !== 'blocked' ) {
						echo $become_button; //phpcs:ignore --data escaped above
					}
					?>
				</div>
			</div>
		</div>

		<div class="tutor-row tutor-frontend-dashboard-maincontent">
			<div class="tutor-col-12 tutor-col-md-4 tutor-col-lg-3 tutor-dashboard-left-menu">
				<ul class="tutor-dashboard-permalinks">
					<?php
					$dashboard_pages = tutor_utils()->tutor_dashboard_nav_ui_items();
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
							// Add new menu item property "url" for custom link
							if ( isset( $dashboard_page['url'] ) ) {
								$menu_link = $dashboard_page['url'];
							}
							if ( isset( $dashboard_page['type'] ) && $dashboard_page['type'] == 'separator' ) {
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
							$active_class    = $dashboard_key == $dashboard_page_slug ? 'active' : '';
							$data_no_instant = 'logout' == $dashboard_key ? 'data-no-instant' : '';
							$menu_link = apply_filters( 'tutor_dashboard_menu_link', $menu_link, $menu_title );
							?>
							<li class='tutor-dashboard-menu-item <?php echo esc_attr( $li_class . ' ' . $active_class ); ?>'>
								<a <?php echo esc_html( $data_no_instant ); ?> href="<?php echo esc_url( $menu_link ); ?>" class='tutor-dashboard-menu-item-link tutor-fs-6 tutor-color-black'>
									<?php
									echo wp_kses(
										$menu_icon,
										tutor_utils()->allowed_icon_tags()
									);
									?>
									<span class='tutor-dashboard-menu-item-text tutor-ml-12'>
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

			<div class="tutor-col-12 tutor-col-md-8 tutor-col-lg-9">
				<div class="tutor-dashboard-content">
					<?php

					if ( $dashboard_page_name ) {
						do_action( 'tutor_load_dashboard_template_before', $dashboard_page_name );

						/**
						 * Load dashboard template part from other location
						 *
						 * This filter is basically added for adding templates from respective addons
						 *
						 * @since version 1.9.3
						 */
						$other_location      = '';
						$from_other_location = apply_filters( 'load_dashboard_template_part_from_other_location', $other_location );

						if ( '' == $from_other_location ) {
							tutor_load_template( 'dashboard.' . $dashboard_page_name );
						} else {
							// Load template from other location full abspath.
							include_once $from_other_location;
						}

						do_action( 'tutor_load_dashboard_template_before', $dashboard_page_name );
					} else {
						tutor_load_template( 'dashboard.dashboard' );
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<div id="tutor-dashboard-footer-mobile">
		<div class="tutor-container">
			<div class="tutor-row">
				<?php foreach ( $footer_links as $link ) : ?>
					<a class="tutor-col-4 <?php echo $link['is_active'] ? 'active' : ''; ?>" href="<?php echo esc_url( $link['url'] ); ?>">
						<i class="<?php echo esc_attr( $link['icon_class'] ); ?>"></i>
						<span><?php echo esc_html( $link['title'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>

<?php do_action( 'tutor_dashboard/after/wrap' ); ?>

<?php
if ( ! $is_by_short_code && ! defined( 'OTLMS_VERSION' ) ) {
	tutor_utils()->tutor_custom_footer();
}
