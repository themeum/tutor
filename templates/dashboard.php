<?php
/**
 * Template for displaying student Public Profile
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

get_header();


global $wp_query;

$dashboard_page_slug = '';
$dashboard_page_name = '';
if (isset($wp_query->query_vars['tutor_dashboard_page']) && $wp_query->query_vars['tutor_dashboard_page']) {
	$dashboard_page_slug = $wp_query->query_vars['tutor_dashboard_page'];
	$dashboard_page_name = $wp_query->query_vars['tutor_dashboard_page'];
}
/**
 * Getting dashboard sum pages
 */
if (isset($wp_query->query_vars['tutor_dashboard_sub_page']) && $wp_query->query_vars['tutor_dashboard_sub_page']) {
	$dashboard_page_name = $wp_query->query_vars['tutor_dashboard_sub_page'];
}

$user_id = get_current_user_id();
$user = get_user_by('ID', $user_id);

do_action('tutor_dashboard/before/wrap'); ?>


    <div class="tutor-wrap tutor-dashboard tutor-dashboard-student">
        <div class="tutor-container">


            <div class="tutor-row">
                <div class="tutor-col-12">
                    <div class="tutor-dashboard-header-wrap">
                        <div class="tutor-wrap tutor-dashboard-header">

                            <div class="tutor-dashboard-header-avatar">
                                <a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink(); ?>">
                                    <img src="<?php echo get_avatar_url($user_id); ?>" />
                                </a>
                            </div>

                            <div class="tutor-dashboard-header-info">

                                <div class="tutor-dashboard-header-display-name">
                                    <h4><?php _e('Howdy,', 'tutor'); ?> <strong><?php echo $user->display_name; ?></strong> </h4>
                                </div>

                                <div class="tutor-dashboard-header-stats">

                                    <div class="tutor-dashboard-header-social-wrap">
                                        <a href=""><i class="tutor-icon-facebook"></i> </a>
                                        <a href=""><i class="tutor-icon-twitter"></i> </a>
                                        <a href=""><i class="tutor-icon-youtube"></i> </a>
                                    </div>

                                    <div class="tutor-dashboard-header-ratings">

						                <?php
						                tutor_utils()->star_rating_generator('4.6');
						                ?>
                                        <span>4.6</span>

                                        <span> (<?php _e(sprintf('%d Ratings', 172), 'tutor') ?>) </span>
                                    </div>

                                    <div class="tutor-dashboard-header-notifications">
                                        <p class="tutor-notification-text"><?php _e('Notification'); ?> <span>9</span> </p>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>
            </div>


            <div class="tutor-row">
                <div class="tutor-col-3 tutor-dashboard-left-menu">
                    <ul class="tutor-dashboard-permalinks">
						<?php
						$dashboard_pages = tutor_utils()->tutor_dashboard_pages();
						foreach ($dashboard_pages as $dashboard_key => $dashboard_page){
						    $li_class = "tutor-dashboard-menu-{$dashboard_key}";
							if ($dashboard_key === 'index')
								$dashboard_key = '';
							$active_class = $dashboard_key == $dashboard_page_slug ? 'active' : '';
							echo "<li class='{$li_class}  {$active_class}'><a href='".tutor_utils()->get_tutor_dashboard_page_permalink($dashboard_key)."'> {$dashboard_page} </a> </li>";
						}
						?>
                    </ul>
                </div>

                <div class="tutor-col-9">
                    <div class="tutor-dashboard-content">
						<?php
						if ($dashboard_page_name){
							tutor_load_template("dashboard.".$dashboard_page_name);
						}else{
							tutor_load_template("dashboard.dashboard");
						}
						?>
                    </div>
                </div>
            </div>
        </div>

    </div>





<?php do_action('tutor_dashboard/after/wrap'); ?>

<?php
get_footer();
