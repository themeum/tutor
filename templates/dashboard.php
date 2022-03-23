<?php

/**
 * Template for displaying frontend dashboard
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$is_by_short_code = isset( $is_shortcode ) && $is_shortcode === true;
if ( ! $is_by_short_code && ! defined( 'OTLMS_VERSION' ) ) {
	tutor_utils()->tutor_custom_header();
}

global $wp_query;

$dashboard_page_slug = '';
$dashboard_page_name = '';
if (isset($wp_query->query_vars['tutor_dashboard_page']) && $wp_query->query_vars['tutor_dashboard_page']) {
	$dashboard_page_slug = $wp_query->query_vars['tutor_dashboard_page'];
	$dashboard_page_name = $wp_query->query_vars['tutor_dashboard_page'];
}
/**
 * Getting dashboard sub pages
 */
if (isset($wp_query->query_vars['tutor_dashboard_sub_page']) && $wp_query->query_vars['tutor_dashboard_sub_page']) {
	$dashboard_page_name = $wp_query->query_vars['tutor_dashboard_sub_page'];
	if ($dashboard_page_slug) {
		$dashboard_page_name = $dashboard_page_slug . '/' . $dashboard_page_name;
	}
}

$user_id                   = get_current_user_id();
$user                      = get_user_by('ID', $user_id);
$enable_profile_completion = tutor_utils()->get_option('enable_profile_completion');
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
		'icon_class' => 'ttr tutor-icon-dashboard-filled',
	),
	array(
		'title'      => $is_instructor ? __( 'Q&A', 'tutor' ) : __( 'Quiz Attempts', 'tutor' ),
		'url'        => $footer_url_2,
		'is_active'  => $footer_url_2 == $current_url,
		'icon_class' => $is_instructor ? 'ttr  tutor-icon-question-filled' : 'ttr tutor-icon-quiz-attempt-filled',
	),
	array(
		'title'      => __('Menu', 'tutor'),
		'url'        => '#',
		'is_active'  => false,
		'icon_class' => 'ttr tutor-icon-menu-line tutor-dashboard-menu-toggler',
	),
);

do_action('tutor_dashboard/before/wrap');
?>

<div class="tutor-wrap tutor-dashboard tutor-frontend-dashboard tutor-dashboard-student tutor-pb-80">
	<div class="tutor-container">
		<!-- new head part  -->
		<div class="tutor-row tutor-d-flex tutor-justify-content-between tutor-frontend-dashboard-header">
			<div class="tutor-header-left-side tutor-dashboard-header tutor-col-md-6 tutor-d-flex tutor-align-items-center" style="border: none;">
				<div class="tutor-dashboard-header-avatar" style="background-image: url( <?php echo esc_url(get_avatar_url($user_id, array('size' => 150))); ?>)">
				</div>
				<div class="tutor-user-info tutor-ml-24">
					<?php
					$instructor_rating = tutor_utils()->get_instructor_ratings($user->ID);

					if (current_user_can(tutor()->instructor_role)) {
					?>
						<div class="tutor-fs-4 tutor-fw-medium tutor-dashboard-header-username">
							<?php echo esc_html($user->display_name); ?>
						</div>
						<div class="tutor-dashboard-header-stats">
							<div class="tutor-dashboard-header-ratings">
								<?php tutor_utils()->star_rating_generator_v2($instructor_rating->rating_avg, $instructor_rating->rating_count, true); ?>
							</div>
							<!-- <div class="tutor-dashboard-header-notifications">
								<?php /*_e('Notification'); */ ?> <span>9</span>
								</div> -->
						</div>
					<?php
					} else {
					?>
						<div class="tutor-dashboard-header-display-name tutor-color-black">
							<div class="tutor-fs-5 tutor-dashboard-header-greetings">
								Hello,
							</div>
							<div class="tutor-fs-4 tutor-fw-medium tutor-dashboard-header-username">
								<?php echo esc_html($user->display_name); ?>
							</div>
						</div>
					<?php
					}
					?>
				</div>
			</div>
			<div class="tutor-header-right-side tutor-col-md-6 tutor-d-flex tutor-justify-content-end tutor-mt-20 tutor-mt-md-0">
				<div class="tutor-d-flex tutor-align-items-center">
					<?php
					do_action('tutor_dashboard/before_header_button');
					$instructor_status  = tutor_utils()->instructor_status();
					$instructor_status  = is_string($instructor_status) ? strtolower($instructor_status) : '';
					$rejected_on        = get_user_meta($user->ID, '_is_tutor_instructor_rejected', true);
					$info_style         = 'vertical-align: middle; margin-right: 7px;';
					$info_message_style = 'display:inline-block; color:#7A7A7A; font-size: 15px;';

					ob_start();
					if (tutor_utils()->get_option('enable_become_instructor_btn')) {
					?>
						<a id="tutor-become-instructor-button" style="vertical-align:middle" class="tutor-btn tutor-btn-tertiary tutor-is-outline" href="<?php echo esc_url(tutor_utils()->instructor_register_url()); ?>">
							<i class="tutor-icon-man-user-filled tutor-icon-24"></i> &nbsp; <?php _e('Become an instructor', 'tutor'); ?>
						</a>
					<?php
					}
					$become_button = ob_get_clean();

					if (current_user_can(tutor()->instructor_role)) {
						$course_type = tutor()->course_post_type;
					?>
						<a class="tutor-btn tutor-btn-tertiary tutor-is-outline" href="<?php echo esc_url(apply_filters('frontend_course_create_url', admin_url('post-new.php?post_type=' . tutor()->course_post_type))); ?>">
							<i class="tutor-icon-plus-square-filled tutor-icon-24 tutor-mr-12"></i> <?php esc_html_e('Create a New Course', 'tutor'); ?>
						</a>
					<?php
					} elseif ($instructor_status == 'pending') {
						$on = get_user_meta($user->ID, '_is_tutor_instructor', true);
						$on = date('d F, Y', $on);
						echo '<span style="' . $info_message_style . '">
                                    <i class="dashicons dashicons-info color-warning-90" style=" ' . $info_style . '"></i>',
						__('Your Application is pending as of', 'tutor'), ' <b>', $on, '</b>',
						'</span>';
					} elseif ($rejected_on || $instructor_status !== 'blocked') {
						echo $become_button;
					}
					?>
				</div>
			</div>
		</div>
		<!-- new head part end -->
		<!-- Sidebar and Content Part -->
		<div class="tutor-row tutor-frontend-dashboard-maincontent">
			<div class="tutor-col-12 tutor-col-md-4 tutor-col-lg-3 tutor-dashboard-left-menu">
				<ul class="tutor-dashboard-permalinks">
					<?php
					$dashboard_pages = tutor_utils()->tutor_dashboard_nav_ui_items();
					// get reviews settings value.
					$disable = !get_tutor_option('enable_course_review');
					foreach ($dashboard_pages as $dashboard_key => $dashboard_page) {
						/**
						 * If not enable from settings then quit
						 *
						 *  @since v2.0.0
						 */
						if ($disable && 'reviews' === $dashboard_key) {
							continue;
						}

						$menu_title = $dashboard_page;
						$menu_link  = tutor_utils()->get_tutor_dashboard_page_permalink($dashboard_key);
						$separator  = false;
						$menu_icon  = '';

						if (is_array($dashboard_page)) {
							$menu_title     = tutor_utils()->array_get('title', $dashboard_page);
							$menu_icon_name = tutor_utils()->array_get('icon', $dashboard_page);
							if ($menu_icon_name) {
								$menu_icon = "<span class='{$menu_icon_name} tutor-dashboard-menu-item-icon tutor-icon-30'></span>";
							}
							// Add new menu item property "url" for custom link
							if (isset($dashboard_page['url'])) {
								echo $menu_link = $dashboard_page['url'];
							}
							if (isset($dashboard_page['type']) && $dashboard_page['type'] == 'separator') {
								$separator = true;
							}
						}
						if ($separator) {
							echo '<li class="tutor-dashboard-menu-divider"></li>';
							if ($menu_title) {
								echo "<li class='tutor-dashboard-menu-divider-header'>{$menu_title}</li>";
							}
						} else {
							$li_class = "tutor-dashboard-menu-{$dashboard_key}";
							if ($dashboard_key === 'index') {
								$dashboard_key = '';
							}
							$active_class    = $dashboard_key == $dashboard_page_slug ? 'active' : '';
							$data_no_instant = 'logout' == $dashboard_key ? 'data-no-instant' : '';

							echo "<li class='tutor-dashboard-menu-item {$li_class}  {$active_class}'><a {$data_no_instant} href='" . $menu_link . "' class='tutor-dashboard-menu-item-link tutor-fs-6 tutor-color-black'>{$menu_icon} <span class='tutor-dashboard-menu-item-text tutor-ml-12'>{$menu_title}</span></a></li>";
						}
					}
					?>
				</ul>
			</div>

			<div class="tutor-col-12 tutor-col-md-8 tutor-col-lg-9">
				<div class="tutor-dashboard-content">
					<?php

					if ($dashboard_page_name) {
						do_action('tutor_load_dashboard_template_before', $dashboard_page_name);

						/**
						 * Load dashboard template part from other location
						 *
						 * this filter is basically added for adding templates from respective addons
						 *
						 * @since version 1.9.3
						 */
						$other_location      = '';
						$from_other_location = apply_filters('load_dashboard_template_part_from_other_location', $other_location);

						if ($from_other_location == '') {
							tutor_load_template('dashboard.' . $dashboard_page_name);
						} else {
							// load template from other location full abspath
							include_once $from_other_location;
						}

						do_action('tutor_load_dashboard_template_before', $dashboard_page_name);
					} else {
						tutor_load_template('dashboard.dashboard');
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<div id="tutor-dashboard-footer-mobile">
		<div class="tutor-container">
			<div class="tutor-row">
				<?php foreach ($footer_links as $link) : ?>
					<a class="tutor-col-4 <?php echo $link['is_active'] ? 'active' : ''; ?>" href="<?php echo $link['url']; ?>">
						<i class="<?php echo $link['icon_class']; ?>"></i>
						<span><?php echo $link['title']; ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>

<style>
	.site-content {
		overflow-y: hidden;
		padding-bottom: 0;
	}

	.tutor-frontend-dashboard {
		overflow-y: hidden;
	}
</style>

<?php do_action('tutor_dashboard/after/wrap'); ?>

<?php
if ( ! $is_by_short_code && ! defined( 'OTLMS_VERSION' ) ) {
	tutor_utils()->tutor_custom_footer();
}
