<?php
/**
 * Template for displaying student dashboard
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $wp_query;

$dashboard_page_slug = '';
if (isset($wp_query->query_vars['tutor_dashboard_page']) && $wp_query->query_vars['tutor_dashboard_page']) {
	$dashboard_page_slug = $wp_query->query_vars['tutor_dashboard_page'];
}
?>

<div class="tutor-wrap tutor-dashboard tutor-dashboard-student">
    <div class="tutor-container">
        <div class="tutor-row">
            <div class="tutor-col-3">
                <ul class="tutor-dashboard-permalinks">
                    <?php
                    $dashboard_pages = tutor_utils()->tutor_dashboard_pages();
                    foreach ($dashboard_pages as $dashboard_key => $dashboard_page){
                        if ($dashboard_key === 'index')
                            $dashboard_key = '';

	                    $menu_title = $dashboard_page;
	                    if (is_array($dashboard_page)){
		                    $menu_title = tutor_utils()->array_get('title', $dashboard_page);
		                    if ( isset($dashboard_page['show_ui']) && ! tutor_utils()->array_get('show_ui', $dashboard_page)){
			                    continue;
		                    }
	                    }

                        $active_class = $dashboard_key == $dashboard_page_slug ? 'active' : '';
                        echo "<li class='{$active_class}'><a href='".tutor_utils()->get_tutor_dashboard_page_permalink($dashboard_key)."'> {$menu_title} </a> </li>";
                    }
                    ?>
                </ul>
            </div>
            <div class="tutor-col-9">
                <div class="tutor-dashboard-content">
                    <?php
                    if ($dashboard_page_slug){
                        tutor_load_template("dashboard.".$wp_query->query_vars['tutor_dashboard_page']);
                    }else{
                        tutor_load_template("dashboard.dashboard");
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

</div>
