<?php
/**
 * Template for displaying student Public Profile
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
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
 * Getting dashboard sub pages
 */
if (isset($wp_query->query_vars['tutor_dashboard_sub_page']) && $wp_query->query_vars['tutor_dashboard_sub_page']) {
    $dashboard_page_name = $wp_query->query_vars['tutor_dashboard_sub_page'];
    if ($dashboard_page_slug){
        $dashboard_page_name = $dashboard_page_slug.'/'.$dashboard_page_name;
    }
}

$user_id = get_current_user_id();
$user = get_user_by('ID', $user_id);

do_action('tutor_dashboard/before/wrap');
?>

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
                            <?php $instructor_rating = tutor_utils()->get_instructor_ratings($user->ID); ?>
                            <?php
                            if (current_user_can(tutor()->instructor_role)){
                                ?>
                                <div class="tutor-dashboard-header-stats">
                                    <div class="tutor-dashboard-header-ratings">
                                        <?php tutor_utils()->star_rating_generator($instructor_rating->rating_avg); ?>
                                        <span><?php echo esc_html($instructor_rating->rating_avg);  ?></span>
                                        <span> (<?php echo sprintf(__('%d Ratings', 'tutor'), $instructor_rating->rating_count); ?>) </span>
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
                                $course_type = tutor()->course_post_type;
                                ?>
                                <a class="tutor-btn bordered-btn" href="<?php echo apply_filters('frontend_course_create_url', admin_url("post-new.php?post_type=".tutor()->course_post_type)); ?>">
                                    <?php echo sprintf(__('%s Add A New Course ', 'tutor'), '<i class="tutor-icon-checkbox-pen-outline"></i> &nbsp;'); ?>
                                </a>
                                <?php
                            }else{
                                if (tutor_utils()->get_option('enable_become_instructor_btn')) {
                                    ?>
                                    <a class="tutor-btn bordered-btn" href="<?php echo esc_url(tutor_utils()->instructor_register_url()); ?>">
                                        <?php echo sprintf(__("%s Become an instructor", 'tutor'), '<i class="tutor-icon-man-user"></i> &nbsp;'); ?>
                                    </a>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tutor-row">
                <div class="tutor-col-3 tutor-dashboard-left-menu">
                    <ul class="tutor-dashboard-permalinks">
                        <?php
                        $dashboard_pages = tutor_utils()->tutor_dashboard_nav_ui_items();
                        foreach ($dashboard_pages as $dashboard_key => $dashboard_page){
                            $menu_title = $dashboard_page;
                            $menu_link = tutor_utils()->get_tutor_dashboard_page_permalink($dashboard_key);
                            if (is_array($dashboard_page)){
                                $menu_title = tutor_utils()->array_get('title', $dashboard_page);

                                /**
                                 * Add new menu item property "url" for custom link
                                 * @since v 1.5.5
                                 */
                                if (isset($dashboard_page['url'])){
                                    $menu_link = $dashboard_page['url'];
                                }
                            }

                            $li_class = "tutor-dashboard-menu-{$dashboard_key}";
                            if ($dashboard_key === 'index')
                                $dashboard_key = '';
                            $active_class = $dashboard_key == $dashboard_page_slug ? 'active' : '';

                            echo "<li class='{$li_class}  {$active_class}'><a href='".$menu_link."'> {$menu_title} </a> </li>";
                        }
                        ?>
                    </ul>
                </div>

                <div class="tutor-col-9">
                    <div class="tutor-dashboard-content">
                        <?php
                        if ($dashboard_page_name){
                            do_action('tutor_load_dashboard_template_before', $dashboard_page_name);
                            tutor_load_template("dashboard.".$dashboard_page_name);
                            do_action('tutor_load_dashboard_template_before', $dashboard_page_name);
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
