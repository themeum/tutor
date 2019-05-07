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
	if ($dashboard_page_slug){
		$dashboard_page_name = $dashboard_page_slug.'/'.$dashboard_page_name;
	}
}

$user_id = get_current_user_id();
$user = get_user_by('ID', $user_id);

do_action('tutor_dashboard/before/wrap'); ?>

    <div class="tutor-wrap tutor-dashboard tutor-dashboard-student">
        <div class="tutor-container">


            <div class="tutor-row">
                <div class="tutor-col-12">
                    <div class="tutor-dashboard-header">

                        <div class="tutor-dashboard-header-avatar">
                            <img src="<?php echo get_avatar_url($user_id, array('size' => 150)); ?>" />
                        </div>

                        <div class="tutor-dashboard-header-info">

                            <div class="tutor-dashboard-header-display-name">
                                <h4><?php _e('Howdy,', 'tutor'); ?> <strong><?php echo $user->display_name; ?></strong> </h4>
                            </div>

							<?php
							$instructor_rating = tutor_utils()->get_instructor_ratings($user->ID);
							?>

							<?php
							if (current_user_can(tutor()->instructor_role)){
								?>
                                <div class="tutor-dashboard-header-stats">
                                    <div class="tutor-dashboard-header-ratings">
										<?php tutor_utils()->star_rating_generator($instructor_rating->rating_avg); ?>
                                        <span><?php echo esc_html($instructor_rating->rating_avg);  ?></span>
                                        <span> (<?php _e(sprintf('%d Ratings', $instructor_rating->rating_count), 'tutor') ?>) </span>
                                    </div>
                                    <!--<div class="tutor-dashboard-header-notifications">
                                        <?php /*_e('Notification'); */?> <span>9</span>
                                    </div>-->
                                </div>
							<?php } ?>

                        </div>

                        <div class="tutor-dashboard-header-button">
							<?php

							if(current_user_can(tutor()->instructor_role)){
								$button_page_url = add_query_arg(array('post_type'=>'course'),admin_url('post-new.php'));
								$buton_text = __('<i class="tutor-icon-video-camera"></i> &nbsp; Upload A Course', 'tutor');
							}else{
								$button_page_url = tutor_utils()->instructor_register_url();
								$buton_text = __('<i class="tutor-icon-man-user"></i> &nbsp; Become an instructor', 'tutor');
							}
							?>
                            <a class="tutor-btn bordered-btn" href="<?php echo esc_url($button_page_url); ?>">
								<?php echo $buton_text; ?>
                            </a>
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
