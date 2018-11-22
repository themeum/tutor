<?php
/**
 * Template for displaying student dashboard
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

global $wp_query;

$dashboard_page_slug = '';
if (isset($wp_query->query_vars['dozent_dashboard_page']) && $wp_query->query_vars['dozent_dashboard_page']) {
	$dashboard_page_slug = $wp_query->query_vars['dozent_dashboard_page'];
}
?>

<div class="dozent-wrap dozent-dashboard dozent-dashboard-student">
    <div class="dozent-container">
        <div class="dozent-row">
            <div class="dozent-col-3">
                <ul class="dozent-dashboard-permalinks">
                    <?php
                    $dashboard_pages = dozent_utils()->dozent_student_dashboard_pages();
                    foreach ($dashboard_pages as $dashboard_key => $dashboard_page){
                        if ($dashboard_key === 'index')
                            $dashboard_key = '';
                        $active_class = $dashboard_key == $dashboard_page_slug ? 'active' : '';
                        echo "<li class='{$active_class}'><a href='".dozent_utils()->get_dozent_dashboard_page_permalink($dashboard_key)."'> {$dashboard_page} </a> </li>";
                    }
                    ?>
                </ul>
            </div>
            <div class="dozent-col-9">
                <div class="dozent-dashboard-content">
                    <?php
                    if ($dashboard_page_slug){
                        dozent_load_template("dashboard.student.".$wp_query->query_vars['dozent_dashboard_page']);
                    }else{
                        dozent_load_template("dashboard.student.dashboard");
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

</div>
