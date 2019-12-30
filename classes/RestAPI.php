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
			'post_type'         => $this->course_post_type,
			'post_status'       => 'publish',

			'id'       => '',
			'exclude_ids'       => '',
			'category'       => '',

			'orderby'           => 'ID',
			'order'             => 'DESC',
			'count'     => '10',
		), $_GET);

/*

		$wpdb->query("SELECT SQL_CALC_FOUND_ROWS  wp_posts.ID 
FROM wp_posts  
LEFT JOIN wp_term_relationships ON (wp_posts.ID = wp_term_relationships.object_id) WHERE 1=1  AND ( 
  wp_term_relationships.term_taxonomy_id IN (19,31,32)
) AND wp_posts.post_type = 'courses' AND ((wp_posts.post_status = 'publish')) GROUP BY wp_posts.ID ORDER BY wp_posts.ID DESC LIMIT 0, 10");
*/


		$tax = new \WP_Tax_Query(array(array(
			'taxonomy' => 'course-category',
			'field'    => 'term_id',
			'terms'    => 19,
			'operator' => 'IN',
		)));

		print_r($tax->get_sql($wpdb->posts, 'ID'));

		//die(print_r($a));

		if ( ! empty($a['id'])){
			$ids = (array) explode(',', $a['id']);
			$a['post__in'] = $ids;
		}

		if ( ! empty($a['exclude_ids'])){
			$exclude_ids = (array) explode(',', $a['exclude_ids']);
			$a['post__not_in'] = $exclude_ids;
		}
		if ( ! empty($a['category'])){
			$category = (array) explode(',', $a['category']);

			$a['tax_query'] = array(
				array(
					'taxonomy' => 'course-category',
					'field'    => 'term_id',
					'terms'    => $category,
					'operator' => 'IN',
				),
			);
		}
		$a['posts_per_page'] = (int) $a['count'];


		$query = new \WP_Query($a);

		die();



		if ($query->have_posts()){

			$courses = array();
			while ($query->have_posts()){
				$query->the_post();
				global $post;

				$post = (array) $post;
				$post = wp_array_slice_assoc($post, array('ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'post_type' ));

				//print_r($post);

				//tutils()->get_courses()


				$courses[] = $post;
			}
			wp_send_json_success($courses);

		}else{
			wp_send_json_error();
		}





	}



}