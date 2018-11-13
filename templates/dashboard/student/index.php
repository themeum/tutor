<?php
global $wp_query;

$dashboard_page_slug = '';
if (isset($wp_query->query_vars['tutor_dashboard_page']) && $wp_query->query_vars['tutor_dashboard_page']) {
	$dashboard_page_slug = $wp_query->query_vars['tutor_dashboard_page'];
}

$current_uid = get_current_user_id();
$user_data = get_userdata($current_uid);

?>


<div class="tutor-student-dashboard-leadinfo">
    <div class="tutor-container">
        <div class="tutor-row">
            <div class="tutor-col-auto">
                <div class="tutor-dashboard-avater">
                    <?php
                    echo get_avatar($current_uid, '200'); ?>
                </div>
            </div>
            <div class="tutor-col">
                <div class="tutor-dashboard-student-info">
                    <h3 class="tutor-student-name">
                        <?php
                        echo esc_html($user_data->display_name);
                        ?>
                    </h3>
                    <ul class="tutor-dashboard-user-role">
                        <li>Admin</li>
                        <li>Student</li>
                    </ul>
                    <h4 class="tutor-designation">Founder & Ceo at Tutor</h4>
                    <h5 class="tutor-dashboard-user-location">
                        <!-- @TODO: Need Location Icon -->
                        <i class="icon icon-star-empty"></i>
                        Boston, MA, United Stats
                    </h5>
                </div>
                <div class="tutor-dashboard-student-meta">
                    <ul>
                        <li>
                            <h4>440</h4>
                            <span>Course Complete</span>
                        </li>
                        <li>
                            <h4>440</h4>
                            <span>Course Complete</span>
                        </li>
                        <li>
                            <h4>440</h4>
                            <span>Course Complete</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="tutor-seperator"></div>
    </div>
</div>

<div class="tutor-wrap tutor-dashboard tutor-dashboard-student">
    <div class="tutor-container">
        <div class="tutor-row">
            <div class="tutor-col-3">
                <ul class="tutor-dashboard-permalinks">
                    <?php
                    $dashboard_pages = tutor_utils()->tutor_student_dashboard_pages();
                    foreach ($dashboard_pages as $dashboard_key => $dashboard_page){
                        if ($dashboard_key === 'index')
                            $dashboard_key = '';
                        $active_class = $dashboard_key == $dashboard_page_slug ? 'active' : '';
                        echo "<li class='{$active_class}'><a href='".tutor_utils()->get_tutor_dashboard_page_permalink($dashboard_key)."'> {$dashboard_page} </a> </li>";
                    }
                    ?>
                </ul>
            </div>
            <div class="tutor-col-9">
                <div class="tutor-dashboard-content">
                    <?php

                    if ($dashboard_page_slug){
                        tutor_load_template("dashboard.student.".$wp_query->query_vars['tutor_dashboard_page']);
                    }else{
                        tutor_load_template("dashboard.student.dashboard");
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

</div>
