<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

$sub_page = 'this_year';
$course_id = false;
if ( ! empty($_GET['time_period'])){
	$sub_page = sanitize_text_field($_GET['time_period']);
}
if ( ! empty($_GET['course_id'])){
	$course_id = (int) sanitize_text_field($_GET['course_id']);
}
if ( ! empty($_GET['date_range_from']) && ! empty($_GET['date_range_to'])){
	$sub_page = 'date_range';
}

include $view_page.$page."/{$sub_page}.php";

?>