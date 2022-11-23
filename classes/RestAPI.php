<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * RestAPI class
 *
 * @package Tutor\API
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.5.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize REST API
 *
 * @since 1.5.0
 */
class RestAPI {

	/**
	 * Custom validation trait
	 */
	use Custom_Validation;

	/**
	 * API namespace
	 *
	 * @var string
	 */
	private $namespace = 'tutor/v1';

	/**
	 * Course post type
	 *
	 * @var string
	 */
	protected $course_post_type;

	/**
	 * Plugin dir Path
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Course Object
	 *
	 * @var object
	 */
	private $course_obj;

	/**
	 * Topic Object
	 *
	 * @var object
	 */
	private $topic_obj;

	/**
	 * Lesson Object
	 *
	 * @var object
	 */
	private $lesson_obj;

	/**
	 * Announcement Object
	 *
	 * @var object
	 */
	private $announcement_obj;

	/**
	 * Quiz Object
	 *
	 * @var object
	 */
	private $quiz_obj;

	/**
	 * Author Object
	 *
	 * @var object
	 */
	private $author_obj;

	/**
	 * Rating Object
	 *
	 * @var object
	 */
	private $rating_obj;

	/**
	 * Manage dependencies
	 *
	 * @since 1.5.0
	 */
	public function __construct() {

		$this->path = plugin_dir_path( TUTOR_FILE );

		spl_autoload_register( array( $this, 'loader' ) );

		$this->course_obj       = new REST_Course();
		$this->topic_obj        = new REST_Topic();
		$this->lesson_obj       = new REST_Lesson();
		$this->announcement_obj = new REST_Course_Announcement();
		$this->quiz_obj         = new REST_Quiz();
		$this->author_obj       = new REST_Author();
		$this->rating_obj       = new REST_Rating();

		add_action( 'rest_api_init', array( $this, 'init_routes' ) );
	}

	/**
	 * Class loading
	 *
	 * @since 1.5.0
	 *
	 * @param string $class_name class name to load.
	 *
	 * @return void
	 */
	private function loader( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			$class_name = preg_replace(
				array( '/([a-z])([A-Z])/', '/\\\/' ),
				array( '$1$2', DIRECTORY_SEPARATOR ),
				$class_name
			);

			$class_name = str_replace( 'TUTOR' . DIRECTORY_SEPARATOR, 'restapi' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	/**
	 * Initialize routes
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function init_routes() {
		// Courses.
		register_rest_route(
			$this->namespace,
			'/courses',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this->course_obj,
					'course',
				),
				'permission_callback' => '__return_true',
			)
		);

		// Courses by terms cat and tag.
		register_rest_route(
			$this->namespace,
			'/course-by-terms',
			array(
				'methods'             => 'POST',
				'callback'            => array(
					$this->course_obj,
					'course_by_terms',
				),
				'permission_callback' => '__return_true',
			)
		);

		// Courses by terms cat and tag.
		register_rest_route(
			$this->namespace,
			'/course-sorting-by-price',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this->course_obj,
					'course_sort_by_price',
				),
				'args'                => array(
					'order' => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => function ( $order ) {
							return $this->validate_order( $order );
						},
					),
					'page'  => array(
						'required' => false,
						'type'     => 'number',
					),
				),
				'permission_callback' => '__return_true',
			)
		);

		// Course details.
		register_rest_route(
			$this->namespace,
			'/course-detail/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this->course_obj,
					'course_detail',
				),
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => '__return_true',
			)
		);

		// Course topic.
		register_rest_route(
			$this->namespace,
			'/course-topic/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this->topic_obj,
					'course_topic',
				),
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => '__return_true',
			)
		);

		// Lesson by topic.
		register_rest_route(
			$this->namespace,
			'/lesson/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this->lesson_obj,
					'topic_lesson',
				),
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => '__return_true',
			)
		);

		// Course announcement by course id.
		register_rest_route(
			$this->namespace,
			'/course-annoucement/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this->announcement_obj,
					'course_annoucement',
				),
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => '__return_true',
			)
		);

		// Quiz by topic id.
		register_rest_route(
			$this->namespace,
			'/quiz/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this->quiz_obj,
					'quiz_with_settings',
				),
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => '__return_true',
			)
		);

		// Quiz question answer by quiz id.
		register_rest_route(
			$this->namespace,
			'/quiz-question-answer/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this->quiz_obj,
					'quiz_question_ans',
				),
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => '__return_true',
			)
		);

		// Quiz attempt details by quiz id.
		register_rest_route(
			$this->namespace,
			'/quiz-attempt-details/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this->quiz_obj,
					'quiz_attempt_details',
				),
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => '__return_true',
			)
		);

		// Author detail by id.
		register_rest_route(
			$this->namespace,
			'/author-information/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this->author_obj,
					'author_detail',
				),
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => '__return_true',
			)
		);

		// Reviews by course id.
		register_rest_route(
			$this->namespace,
			'/course-rating/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this->rating_obj,
					'course_rating',
				),
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => '__return_true',
			)
		);
	}
}
