<?php
global $wp_query;

$dashboard_page_slug = '';
if (isset($wp_query->query_vars['tutor_dashboard_page']) && $wp_query->query_vars['tutor_dashboard_page']) {
	$dashboard_page_slug = $wp_query->query_vars['tutor_dashboard_page'];
}
?>

<div class="tutor-wrap tutor-dashboard tutor-dashboard-student">
	<div class="tutor-dashboard-permalinks">
		<ul>
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
