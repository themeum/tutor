<?php

/**
 * RestAPI class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.5.0
<<<<<<< HEAD
*/
=======
 */

>>>>>>> fb94df0e0a5e5e26d64a2fc09c76514ab2c68fad

namespace TUTOR;

if (!defined('ABSPATH'))
	exit;

class RestAPI {

<<<<<<< HEAD
	use Custom_Validation;

	const namespace = 'tutor/v1';

	protected $course_post_type;

	private $path;

	private $courseObj;

	private $topicObj;

	private $lessonObj;

	private $annoucementObj;

	private $quizObj;

	private $authorObj;

	private $ratingObj;

	public function __construct() {

		$this->path = plugin_dir_path(TUTOR_FILE);
		//autoloading clases
		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}
		spl_autoload_register(array($this, 'loader'));


		$this->courseObj = new REST_Course;
		$this->topicObj = new REST_Topic;
		$this->lessonObj = new REST_Lesson;
		$this->annoucementObj = new REST_Course_Announcement;
		$this->quizObj = new REST_Quiz;
		$this->authorObj = new REST_Author;
		$this->ratingObj = new REST_Rating;

		add_action('rest_api_init', array($this,'init_routes'));
	}


	private function loader($className):void
	{
		if ( ! class_exists($className)){
			$className = preg_replace(
				array('/([a-z])([A-Z])/', '/\\\/'),
				array('$1$2', DIRECTORY_SEPARATOR),
				$className
			);

			$className = str_replace('TUTOR'.DIRECTORY_SEPARATOR, 'restapi'.DIRECTORY_SEPARATOR, $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) ) {
				require_once $file_name;
			}
		}		
	}

	/*
	init all routes for api
	*/
	public function init_routes()
	{

		//courses
		register_rest_route(
			self::namespace,
			'/courses',
			array(
				'methods'=> "GET",
				'callback'=> array(
					$this->courseObj,'course'
				),
				'permission_callback'=> '__return_true'
			),
		);		

		//courses by terms cat and tag
		register_rest_route(
			self::namespace,
			'/course-by-terms',
			array(
				'methods'=> "POST",
				'callback'=> array(
					$this->courseObj,'course_by_terms'
				),
				'permission_callback'=> '__return_true'
			),
		);		

		//courses by terms cat and tag
		register_rest_route(
			self::namespace,
			'/course-sorting-by-price',
			array(
				'methods'=> "GET",
				'callback'=> array(
					$this->courseObj,'course_sort_by_price'
				),
				'args'=>array(
					'order'=> array(
						'required'=> true,
						'type'=> 'string',
						'validate_callback'=> function($order){
							return $this->validate_order($order);
						}
					),
					'page'=>array(
						'required'=> false,
						'type'=> 'number',
					)
				),
				'permission_callback'=> '__return_true'
			),
		);

		//course details
		register_rest_route(
			self::namespace,
			'/course-detail/(?P<id>\d+)',
			array(
				'methods'=> 'GET',
				'callback'=> array(
					$this->courseObj,'course_detail'
				),
				'args'=> array(
					'id'=>array(
						'validate_callback'=>function($param){
							return is_numeric($param);
						}
					)
				),
				'permission_callback'=> '__return_true'
			),
		);

		//course topic
		register_rest_route(
			self::namespace,
			'/course-topic/(?P<id>\d+)',
			array(
				'methods'=> 'GET',
				'callback'=> array(
					$this->topicObj,'course_topic'
				),
				'args'=> array(
					'id'=>array(
						'validate_callback'=>function($param){
							return is_numeric($param);
						}
					)
				),
				'permission_callback'=> '__return_true'
			),
		);		

		//lesson by topic
		register_rest_route(
			self::namespace,
			'/lesson/(?P<id>\d+)',
			array(
				'methods'=> 'GET',
				'callback'=> array(
					$this->lessonObj,'topic_lesson'
				),
				'args'=> array(
					'id'=>array(
						'validate_callback'=>function($param){
							return is_numeric($param);
						}
					)
				),
				'permission_callback'=> '__return_true'
			),
		);		

		//course annoucement by course id
		register_rest_route(
			self::namespace,
			'/course-annoucement/(?P<id>\d+)',
			array(
				'methods'=> 'GET',
				'callback'=> array(
					$this->annoucementObj,'course_annoucement'
				),
				'args'=> array(
					'id'=>array(
						'validate_callback'=>function($param){
							return is_numeric($param);
						}
					)
				),
				'permission_callback'=> '__return_true'
			),
		);		

		//course annoucement by course id
		register_rest_route(
			self::namespace,
			'/quiz/(?P<id>\d+)',
			array(
				'methods'=> 'GET',
				'callback'=> array(
					$this->quizObj,'quiz_with_settings'
				),
				'args'=> array(
					'id'=>array(
						'validate_callback'=>function($param){
							return is_numeric($param);
						}
					)
				),
				'permission_callback'=> '__return_true'
			),
		);		

		//quiz question answer by quiz id
		register_rest_route(
			self::namespace,
			'/quiz-question-answer/(?P<id>\d+)',
			array(
				'methods'=> 'GET',
				'callback'=> array(
					$this->quizObj,'quiz_question_ans'
				),
				'args'=> array(
					'id'=>array(
						'validate_callback'=>function($param){
							return is_numeric($param);
						}
					)
				),
				'permission_callback'=> '__return_true'
			),
		);			

		//author detail by id
		register_rest_route(
			self::namespace,
			'/author-information/(?P<id>\d+)',
			array(
				'methods'=> 'GET',
				'callback'=> array(
					$this->authorObj,'author_detail'
				),
				'args'=> array(
					'id'=>array(
						'validate_callback'=>function($param){
							return is_numeric($param);
						}
					)
				),
				'permission_callback'=> '__return_true'
			),
		);			

		//reviews by course id
		register_rest_route(
			self::namespace,
			'/course-rating/(?P<id>\d+)',
			array(
				'methods'=> 'GET',
				'callback'=> array(
					$this->ratingObj,'course_rating'
				),
				'args'=> array(
					'id'=>array(
						'validate_callback'=>function($param){
							return is_numeric($param);
						}
					)
				),
				'permission_callback'=> '__return_true'
			),
		);		


	} 

=======
	protected $namespace = 'tutor/v1';
	protected $course_post_type;

	public function __construct() {
		$this->course_post_type = tutor()->course_post_type;

		add_action('rest_api_init', array($this, 'rest_api_init'));
	}


	public function rest_api_init() {
		register_rest_route(
			$this->namespace, 'courses', 
			array(
				'methods' => 'GET', 
				'callback' => array($this, 'courses_api'),
				'permission_callback' => '__return_true'
			)
		);
	}

	public function courses_api() {
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
		if (!empty($a['exclude_ids'])) {
			$exclude_ids = (array) explode(',', sanitize_text_field($a['exclude_ids']));
			if (tutils()->count($exclude_ids)) {
				$exclude_ids_query = "AND ID NOT IN('$exclude_ids')";
			}
		}

		if (!empty($a['id'])) {
			$ids = (array) explode(',', $a['id']);
			if (tutils()->count($ids)) {
				$in_ids_query = "AND ID IN('$ids')";
			}
		}

		if (!empty($a['category'])) {
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


		if (tutils()->count($query)) {
			$results = apply_filters('tutor/api/get_courses', $query);
			wp_send_json_success($results);
		}
		wp_send_json_error();
	}
>>>>>>> fb94df0e0a5e5e26d64a2fc09c76514ab2c68fad
}