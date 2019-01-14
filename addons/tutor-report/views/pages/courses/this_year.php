<?php
//include TUTOR_REPORT()->path.'views/pages/courses/top_menu.php';



global $wpdb;

$currentYear = date('Y');

$single_course_query = '';
if ($course_id){
	$single_course_query = "AND post_parent = {$course_id}";
}

$enrolledQuery = $wpdb->get_results( "
              SELECT COUNT(ID) as total_enrolled, 
              MONTHNAME(post_date)  as month_name 
              from {$wpdb->posts} 
              WHERE post_type = 'tutor_enrolled' 
              AND YEAR(post_date) = {$currentYear} 
              {$single_course_query}
              GROUP BY MONTH (post_date) 
              ORDER BY MONTH(post_date) ASC ;");

$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
$months = wp_list_pluck($enrolledQuery, 'month_name');
$monthWiseEnrolled = array_combine($months, $total_enrolled);

$emptyMonths = array();
for ($m=1; $m<=12; $m++) {
	$emptyMonths[date('F', mktime(0,0,0,$m, 1, date('Y')))] = 0;
}
$chartData = array_merge($emptyMonths, $monthWiseEnrolled);

/**
 * Getting enrolled courses within this time period
 */
if ( ! $course_id){
	$enrolledProduct = $wpdb->get_results( "
              SELECT COUNT(enrolled.ID) as total_enrolled, 
              DATE(enrolled.post_date)  as date_format,
              course.ID,
              course.post_title 
              
              from {$wpdb->posts} enrolled
              LEFT JOIN {$wpdb->posts} course ON enrolled.post_parent = course.ID
              WHERE enrolled.post_type = 'tutor_enrolled' 
              AND YEAR(enrolled.post_date) = {$currentYear} 
              GROUP BY course.ID
              ORDER BY total_enrolled DESC LIMIT 0,50 ;");
}
?>


<?php
include TUTOR_REPORT()->path.'views/pages/courses/body.php';
?>