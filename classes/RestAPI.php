<?php
/**
 * RestAPI class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.5.0
 */


namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class RestAPI {

	protected $namespace = 'tutor/v1';
	protected $course_post_type;

	public function __construct() {
		$this->course_post_type = tutor()->course_post_type;

		add_action('rest_api_init', array($this, 'rest_api_init'));

	}


	public function rest_api_init(){
		register_rest_route( $this->namespace, 'courses', array( 'methods' => 'GET', 'callback' => array($this, 'courses_api') ) );
	}

	public function courses_api(){
		global $wpdb;

		$a = array_merge(array(
			'post_type'     => $this->course_post_type,
			'post_status'   => 'publish',

			'id'            => '',
			'exclude_ids'   => '',
			'category'      => '',

			'orderby'       => 'ID',
			'order'         => 'DESC',
			'count'         => '10',
		), $_GET);

		$limit = (int) $a['count'];
		$exclude_ids_query = '';
		$in_ids_query = '';
		$tax_join = '';
		$tax_where = '';

		$orderby = sanitize_text_field($a['orderby']);
		$order = sanitize_text_field($a['order']);

		/**
		 * Exclude Course IDS
		 */
		if ( ! empty($a['exclude_ids'])){
			$exclude_ids = (array) explode(',', sanitize_text_field($a['exclude_ids']));
			if (tutils()->count($exclude_ids)){
				$exclude_ids_query = "AND ID NOT IN('$exclude_ids')";
			}
		}

		if ( ! empty($a['id'])){
			$ids = (array) explode(',', $a['id']);
			if (tutils()->count($ids)){
				$in_ids_query = "AND ID IN('$ids')";
			}
		}

		if ( ! empty($a['category'])){
			$category = (array) explode(',', $a['category']);
			$tax = new \WP_Tax_Query(
				array(
					array(
						'taxonomy' => 'course-category',
						'field'    => 'term_id',
						'terms'    => $category,
						'operator' => 'IN',
					)
				)
			);

			$tax_sql = $tax->get_sql($wpdb->posts, 'ID');
			$tax_join = tutils()->array_get('join', $tax_sql);
			$tax_where = tutils()->array_get('where', $tax_sql);
		}

		$course_post_type = tutor()->course_post_type;
		$query = $wpdb->get_results("SELECT ID, post_author, post_title 
			from {$wpdb->posts} 
			
			{$tax_join}
			
			WHERE 1=1 AND post_status = 'publish'
			{$exclude_ids_query}
			{$in_ids_query}
			{$tax_where}
			AND post_type = '{$course_post_type}' ORDER BY {$orderby} {$order} LIMIT {$limit} ", ARRAY_A);


		if (tutils()->count($query)){
			$results = apply_filters('tutor/api/get_courses', $query);
			wp_send_json_success($results);
		}
		wp_send_json_error();


	}



}