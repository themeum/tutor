<?php
/**
 * Tutor Course attachments Main Class
 */

namespace TUTOR_REPORT;

use TUTOR\Tutor_Base;

class Report extends Tutor_Base {

	public function __construct() {
		parent::__construct();

		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
		add_action('tutor_admin_register', array($this, 'register_menu'));

		/**
		 * Ajax Action
		 */
		add_action('wp_ajax_tutor_review_delete', array($this, 'tutor_review_delete'));
		add_action('wp_ajax_treport_quiz_atttempt_delete', array($this, 'treport_quiz_atttempt_delete'));
		//Download CSV
		add_action('admin_init', array($this, 'download_course_enrol_csv'));
	}

	public function admin_scripts($page){
		wp_enqueue_style('tutor-report', TUTOR_REPORT()->url.'assets/css/report.css', array(), TUTOR_REPORT()->version);

		/**
		 * Scripts
		 */
		if ($page === 'tutor_page_tutor_report') {
			wp_enqueue_script( 'tutor-cahrt-js', TUTOR_REPORT()->url . 'assets/js/Chart.bundle.min.js', array(), TUTOR_REPORT()->version );
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_script( 'tutor-report', TUTOR_REPORT()->url . 'assets/js/report.js', array( 'tutor-admin' ), TUTOR_REPORT()->version, true );
		}
	}

	public function register_menu(){
		add_submenu_page('tutor', __('Report', 'tutor-report'), __('Report', 'tutor-report'), 'manage_tutor', 'tutor_report', array($this, 'tutor_report') );
	}

	public function tutor_report(){
		include TUTOR_REPORT()->path.'views/pages/report.php';
	}

	public function tutor_review_delete(){
		global $wpdb;

		$review_id = sanitize_text_field($_POST['review_id']);
		$wpdb->delete( $wpdb->comments, array('comment_ID' => $review_id) );
		$wpdb->delete( $wpdb->commentmeta, array('comment_id' => $review_id) );

		wp_send_json_success();
	}

	public function treport_quiz_atttempt_delete(){
		global $wpdb;

		$attempt_id = (int) sanitize_text_field($_POST['attempt_id']);

		$wpdb->delete( $wpdb->comments, array('comment_ID' => $attempt_id) );
		$wpdb->delete( $wpdb->commentmeta, array('comment_id' => $attempt_id) );

		wp_send_json_success();
	}

	public function download_course_enrol_csv(){
		if ( empty($_GET['tutor_report_action']) || $_GET['tutor_report_action'] !== 'download_course_enrol_csv'){
			return;
		}
		global $wpdb;

		$time_period = 'this_year';
		$course_id = false;
		if ( ! empty($_GET['time_period'])){
			$time_period = sanitize_text_field($_GET['time_period']);
		}
		if ( ! empty($_GET['course_id'])){
			$course_id = (int) sanitize_text_field($_GET['course_id']);
		}
		if ( ! empty($_GET['date_range_from']) && ! empty($_GET['date_range_to'])){
			$time_period = 'date_range';
		}

		$chartData = array();

		$single_course_query = '';
		if ( ! empty($_GET['course_id'])){
			$course_id = (int) sanitize_text_field($_GET['course_id']);
			if ($course_id){
				$single_course_query = "AND post_parent = {$course_id}";
			}
		}

		switch ($time_period){
			case 'this_year';

				$currentYear = date('Y');

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

				break;
			case 'last_year';

				$lastYear = date('Y', strtotime('-1 year'));

				$enrolledQuery = $wpdb->get_results( "
	              SELECT COUNT(ID) as total_enrolled, 
	              MONTHNAME(post_date)  as month_name 
	              from {$wpdb->posts} 
	              WHERE post_type = 'tutor_enrolled' 
	              AND YEAR(post_date) = {$lastYear} 
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

				break;
			case 'last_month';

				$start_date = date("Y-m", strtotime('-1 month'));
				$start_date = $start_date.'-1';
				$end_date = date("Y-m-t", strtotime($start_date));

				/**
				 * Format Date Name
				 */
				$begin = new \DateTime($start_date);
				$end = new \DateTime($end_date.' + 1 day');
				$interval = \DateInterval::createFromDateString('1 day');
				$period = new \DatePeriod($begin, $interval, $end);

				$datesPeriod = array();
				foreach ($period as $dt) {
					$datesPeriod[$dt->format("Y-m-d")] = 0;
				}

				$enrolledQuery = $wpdb->get_results( "
	              SELECT COUNT(ID) as total_enrolled, 
	              DATE(post_date)  as date_format 
	              from {$wpdb->posts} 
	              WHERE post_type = 'tutor_enrolled' 
	              AND (post_date BETWEEN '{$start_date}' AND '{$end_date}')
	              {$single_course_query}
	              GROUP BY date_format
	              ORDER BY post_date ASC ;");

				$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
				$queried_date = wp_list_pluck($enrolledQuery, 'date_format');
				$dateWiseEnrolled = array_combine($queried_date, $total_enrolled);

				$chartData = array_merge($datesPeriod, $dateWiseEnrolled);
				foreach ($chartData as $key => $enrolledCount){
					unset($chartData[$key]);
					$formatDate = date('d M', strtotime($key));
					$chartData[$formatDate] = $enrolledCount;
				}

				break;
			case 'this_month';

				$start_week = date("Y-m-01");
				$end_week = date("Y-m-t");

				/**
				 * Format Date Name
				 */
				$begin = new \DateTime($start_week);
				$end = new \DateTime($end_week.' + 1 day');
				$interval = \DateInterval::createFromDateString('1 day');
				$period = new \DatePeriod($begin, $interval, $end);

				$datesPeriod = array();
				foreach ($period as $dt) {
					$datesPeriod[$dt->format("Y-m-d")] = 0;
				}

				$enrolledQuery = $wpdb->get_results( "
	              SELECT COUNT(ID) as total_enrolled, 
	              DATE(post_date)  as date_format 
	              from {$wpdb->posts} 
	              WHERE post_type = 'tutor_enrolled' 
	              AND (post_date BETWEEN '{$start_week}' AND '{$end_week}')
	              {$single_course_query}
	              GROUP BY date_format
	              ORDER BY post_date ASC ;");

				$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
				$queried_date = wp_list_pluck($enrolledQuery, 'date_format');
				$dateWiseEnrolled = array_combine($queried_date, $total_enrolled);

				$chartData = array_merge($datesPeriod, $dateWiseEnrolled);
				foreach ($chartData as $key => $enrolledCount){
					unset($chartData[$key]);
					$formatDate = date('d M', strtotime($key));
					$chartData[$formatDate] = $enrolledCount;
				}

				break;
			case 'last_week';

				$previous_week = strtotime("-1 week +1 day");
				$start_week = strtotime("last sunday midnight",$previous_week);
				$end_week = strtotime("next saturday",$start_week);
				$start_week = date("Y-m-d",$start_week);
				$end_week = date("Y-m-d",$end_week);

				/**
				 * Format Date Name
				 */
				$begin = new \DateTime($start_week);
				$end = new \DateTime($end_week.' + 1 day');
				$interval = \DateInterval::createFromDateString('1 day');
				$period = new \DatePeriod($begin, $interval, $end);

				$datesPeriod = array();
				foreach ($period as $dt) {
					$datesPeriod[$dt->format("Y-m-d")] = 0;
				}

				$enrolledQuery = $wpdb->get_results( "
	              SELECT COUNT(ID) as total_enrolled, 
	              DATE(post_date)  as date_format 
	              from {$wpdb->posts} 
	              WHERE post_type = 'tutor_enrolled' 
	              AND (post_date BETWEEN '{$start_week}' AND '{$end_week}')
	              {$single_course_query}
	              GROUP BY date_format
	              ORDER BY post_date ASC ;");

				$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
				$queried_date = wp_list_pluck($enrolledQuery, 'date_format');
				$dateWiseEnrolled = array_combine($queried_date, $total_enrolled);

				$chartData = array_merge($datesPeriod, $dateWiseEnrolled);
				foreach ($chartData as $key => $enrolledCount){
					unset($chartData[$key]);
					$formatDate = date('d M', strtotime($key));
					$chartData[$formatDate] = $enrolledCount;
				}


				break;
			case 'this_week';

				$start_week = date("Y-m-d", strtotime("last sunday midnight"));
				$end_week = date("Y-m-d", strtotime("next saturday"));
				/**
				 * Format Date Name
				 */
				$begin = new \DateTime($start_week);
				$end = new \DateTime($end_week.' + 1 day');
				$interval = \DateInterval::createFromDateString('1 day');
				$period = new \DatePeriod($begin, $interval, $end);

				$datesPeriod = array();
				foreach ($period as $dt) {
					$datesPeriod[$dt->format("Y-m-d")] = 0;
				}

				$enrolledQuery = $wpdb->get_results( "
	              SELECT COUNT(ID) as total_enrolled, 
	              DATE(post_date)  as date_format 
	              from {$wpdb->posts} 
	              WHERE post_type = 'tutor_enrolled' 
	              AND (post_date BETWEEN '{$start_week}' AND '{$end_week}')
	              {$single_course_query}
	              GROUP BY date_format
	              ORDER BY post_date ASC ;");

				$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
				$queried_date = wp_list_pluck($enrolledQuery, 'date_format');
				$dateWiseEnrolled = array_combine($queried_date, $total_enrolled);

				$chartData = array_merge($datesPeriod, $dateWiseEnrolled);
				foreach ($chartData as $key => $enrolledCount){
					unset($chartData[$key]);
					$formatDate = date('d M', strtotime($key));
					$chartData[$formatDate] = $enrolledCount;
				}

				break;
			case 'date_range';

				$start_week = sanitize_text_field($_GET['date_range_from']);
				$end_week = sanitize_text_field($_GET['date_range_to']);

				/**
				 * Format Date Name
				 */
				$begin = new \DateTime($start_week);
				$end = new \DateTime($end_week.' + 1 day');
				$interval = \DateInterval::createFromDateString('1 day');
				$period = new \DatePeriod($begin, $interval, $end);

				$datesPeriod = array();
				foreach ($period as $dt) {
					$datesPeriod[$dt->format("Y-m-d")] = 0;
				}

				$enrolledQuery = $wpdb->get_results( "
	              SELECT COUNT(ID) as total_enrolled, 
	              DATE(post_date)  as date_format 
	              from {$wpdb->posts} 
	              WHERE post_type = 'tutor_enrolled' 
	              AND (post_date BETWEEN '{$start_week}' AND '{$end_week}')
	              {$single_course_query}
	              GROUP BY date_format
	              ORDER BY post_date ASC ;");

				$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
				$queried_date = wp_list_pluck($enrolledQuery, 'date_format');
				$dateWiseEnrolled = array_combine($queried_date, $total_enrolled);

				$chartData = array_merge($datesPeriod, $dateWiseEnrolled);
				foreach ($chartData as $key => $enrolledCount){
					unset($chartData[$key]);
					$formatDate = date('d M', strtotime($key));
					$chartData[$formatDate] = $enrolledCount;
				}
				break;
		}

		$this->download_send_headers("tutor_report_course_enroll_" . date("Y-m-d") . ".csv");

		ob_start();
		$df = fopen("php://output", 'w');
		fputcsv($df, array_keys($chartData));
		fputcsv($df, $chartData);
		fclose($df);
		echo ob_get_clean();
		die();
	}

	public function download_send_headers($filename) {
		// disable caching
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");
	}

}