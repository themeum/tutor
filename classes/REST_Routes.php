<?php
/*
@REST routes
@Author themeum
*/
namespace TUTOR;
use WP_REST_Request;

if( ! defined('ABSPATH')) 
exit;

class REST_Routes
{
	use REST_Response;

	const namespace = "tutor/v1";

	private $path;

	private $courseObj;

	private $topicObj;

	private $lessonObj;

	private $annoucementObj;

	private $quizObj;

	private $authorObj;

	private $ratingObj;
	
	public function __construct()
	{
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

			$className = str_replace('TUTOR'.DIRECTORY_SEPARATOR, 'classes'.DIRECTORY_SEPARATOR, $className);
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
		// register_rest_route(
		// 	self::namespace,
		// 	'authenticate',
		// 	array(
		// 		'methods'=> 'POST',
		// 		'callback'=> array(
		// 			$this,'/authenticate'
		// 		),	
		// 		'permission_callback'=> '__return_true'
		// 	),
			
		// );

		// //verify token
		// register_rest_route(
		// 	self::namespace,
		// 	'/verify-token',
		// 	array(
		// 		'methods'=> 'GET',
		// 		'callback'=> array(
		// 			$this->jwt_setup_obj,'verify_token'
		// 		),
		// 		'permission_callback'=> '__return_true'
		// 	)
		// );

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

		//course terms
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

	/*

	*/
	public function authenticate( WP_REST_Request $request):object
	{

		$username = $request->get_param('username');
		$password = $request->get_param('password');

		//authenticate with user & pass
		$user = $this->auth_obj->authentication($username,$password);

		//check if error
		if(is_wp_error($user))
		{
			$error_code = $user->get_error_code();
			

			$response = array(
				'status_code'=> $error_code,
				'message'=> strip_tags($user->get_error_message($error_code)),
				'data'=>[]
			);

			return self::send($response);

		}

		// if auth then get jwt
		$payload_data = array(
			'ID' => $user->ID,
			'username' => $username,
		);

		//get array jwt 
		$jwt = $this->jwt_setup_obj->create_token($payload_data);

		$response = array(
			'status_code' => "authenticate_success",
			'message' => __('Authentication success','jwt'),
			'data'=> $jwt
		);

		return self::send($response);
	}	


}


?>