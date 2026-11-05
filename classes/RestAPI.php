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

use Tutor\Helpers\HttpHelper;

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
	private static $namespace = 'tutor/v1';

	/**
	 * Get the REST API namespace.
	 *
	 * Static so callers do not need the RestAPI instance (e.g. early
	 * rest_pre_dispatch hooks where tutor()->rest_api is unavailable).
	 *
	 * @since 4.2.0
	 *
	 * @return string
	 */
	public static function get_namespace() {
		return self::$namespace;
	}

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
		add_filter( 'rest_request_before_callbacks', array( $this, 'check_permission' ), 10, 3 );
	}

	/**
	 * Check permission before dispatching REST request.
	 *
	 * @todo will remove and prevent by capability where needed.
	 *
	 * @since 4.0.1
	 *
	 * @param mixed            $response response.
	 * @param mixed            $handler handler.
	 * @param \WP_REST_Request $request request.
	 *
	 * @return mixed|\WP_Error
	 */
	public function check_permission( $response, $handler, $request ) {
		$id            = absint( $request['id'] );
		$method        = $request->get_method();
		$write_methods = array(
			HttpHelper::METHOD_POST,
			HttpHelper::METHOD_PUT,
			HttpHelper::METHOD_PATCH,
			HttpHelper::METHOD_DELETE,
		);
		$is_write      = in_array( $method, $write_methods, true );

		if ( ! $id ) {
			return $response;
		}

		$post = get_post( $id );

		if ( ! $post ) {
			return $response;
		}

		$post_types = array(
			tutor()->course_post_type,
			tutor()->bundle_post_type,
		);

		if ( ! in_array( $post->post_type, $post_types, true ) ) {
			return $response;
		}

		// Prefer Tutor manage rules over raw edit_post. For another author's course,
		// edit_post maps to edit_others_tutor_courses, which administrators may lack
		// even though User::is_admin() / can_user_edit_course() should allow the write.
		$can_edit = User::is_admin()
			|| tutor_utils()->can_user_edit_course( get_current_user_id(), $id )
			|| current_user_can( 'edit_post', $id );

		// Prevent write actions which are only allowed for author / course managers.
		if ( $is_write && ! $can_edit ) {
			return new \WP_Error(
				'rest_forbidden',
				tutor_utils()->error_message(),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Non-publish: editors always; readers only when can_view_course_content allows
		// (private + enrolled/public/IA — not draft/pending/trash via enrollment alone).
		if ( 'publish' !== $post->post_status && ! $can_edit && ! RestAuth::can_view_course_content( $id ) ) {
			return new \WP_Error(
				'rest_forbidden',
				tutor_utils()->error_message(),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return $response;
	}

	/**
	 * Class loading
	 *
	 * @since 4.0.8 Only autoloads Tutor REST API classes from the plugin's restapi/
	 * directory. Arbitrary class names must never resolve to files under
	 * the plugin root (e.g. Composer packages used as POP gadgets).
	 *
	 * @since 1.5.0
	 *
	 * @param string $class_name class name to load.
	 *
	 * @return void
	 */
	private function loader( $class_name ) {
		if ( class_exists( $class_name ) ) {
			return;
		}

		// Only handle namespaced Tutor REST classes.
		if ( 0 !== strpos( $class_name, 'TUTOR\\' ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( 'TUTOR\\' ) );
		$relative = str_replace( '\\', DIRECTORY_SEPARATOR, $relative );

		// Block path traversal and unexpected nested paths.
		if ( '' === $relative || false !== strpos( $relative, '..' ) || false !== strpos( $relative, DIRECTORY_SEPARATOR ) ) {
			return;
		}

		$restapi_dir = realpath( $this->path . 'restapi' );
		if ( false === $restapi_dir ) {
			return;
		}

		$file_name = $restapi_dir . DIRECTORY_SEPARATOR . $relative . '.php';
		$real_file = realpath( $file_name );

		if ( false === $real_file || 0 !== strpos( $real_file, $restapi_dir . DIRECTORY_SEPARATOR ) ) {
			return;
		}

		require_once $real_file;
	}

	/**
	 * Integer ID argument schema for REST routes.
	 *
	 * @since 4.2.0
	 *
	 * @return array
	 */
	private function get_id_arg_schema() {
		return array(
			'type'              => 'integer',
			'minimum'           => 1,
			'sanitize_callback' => 'absint',
			'required'          => true,
		);
	}

	/**
	 * Initialize routes
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function init_routes() {
		// Auth: login / refresh / logout.
		register_rest_route(
			self::$namespace,
			'/auth/login',
			array(
				'methods'             => HttpHelper::METHOD_POST,
				'callback'            => array( RestAuth::class, 'login' ),
				'permission_callback' => array( RestAuth::class, 'process_api_request' ),
			)
		);

		register_rest_route(
			self::$namespace,
			'/auth/refresh',
			array(
				'methods'             => HttpHelper::METHOD_POST,
				'callback'            => array( RestAuth::class, 'refresh' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::$namespace,
			'/auth/logout',
			array(
				'methods'             => HttpHelper::METHOD_POST,
				'callback'            => array( RestAuth::class, 'logout' ),
				'permission_callback' => '__return_true',
			)
		);

		// Courses.
		register_rest_route(
			self::$namespace,
			'/courses',
			array(
				'methods'             => HttpHelper::METHOD_GET,
				'callback'            => array(
					$this->course_obj,
					'course',
				),
				'permission_callback' => array( RestAuth::class, 'permission_optional_access_token' ),
			)
		);

		// Course details.
		register_rest_route(
			self::$namespace,
			'/courses/(?P<id>\d+)',
			array(
				'methods'             => HttpHelper::METHOD_GET,
				'callback'            => array(
					$this->course_obj,
					'course_detail',
				),
				'args'                => array(
					'id' => $this->get_id_arg_schema(),
				),
				'permission_callback' => array( RestAuth::class, 'permission_course_detail' ),
			)
		);

		// Course topic.
		register_rest_route(
			self::$namespace,
			'/topics',
			array(
				'methods'             => HttpHelper::METHOD_GET,
				'callback'            => array(
					$this->topic_obj,
					'course_topic',
				),
				'args'                => array(
					'course_id' => $this->get_id_arg_schema(),
				),
				'permission_callback' => array( RestAuth::class, 'permission_topics' ),
			)
		);

		// Lesson by topic.
		register_rest_route(
			self::$namespace,
			'/lessons',
			array(
				'methods'             => HttpHelper::METHOD_GET,
				'callback'            => array(
					$this->lesson_obj,
					'topic_lesson',
				),
				'args'                => array(
					'topic_id' => $this->get_id_arg_schema(),
				),
				'permission_callback' => array( RestAuth::class, 'permission_by_topic' ),
			)
		);

		// Course announcement by course id.
		register_rest_route(
			self::$namespace,
			'/course-announcement/(?P<id>\d+)',
			array(
				'methods'             => HttpHelper::METHOD_GET,
				'callback'            => array(
					$this->announcement_obj,
					'course_announcement',
				),
				'args'                => array(
					'id' => $this->get_id_arg_schema(),
				),
				'permission_callback' => array( RestAuth::class, 'permission_authenticated_course_content' ),
			)
		);

		// Quiz by topic id.
		register_rest_route(
			self::$namespace,
			'/quizzes',
			array(
				'methods'             => HttpHelper::METHOD_GET,
				'callback'            => array(
					$this->quiz_obj,
					'quiz_with_settings',
				),
				'args'                => array(
					'topic_id' => $this->get_id_arg_schema(),
				),
				'permission_callback' => array( RestAuth::class, 'permission_authenticated_by_topic' ),
			)
		);

		// Quiz by quiz id.
		register_rest_route(
			self::$namespace,
			'/quizzes/(?P<id>\d+)',
			array(
				'methods'             => HttpHelper::METHOD_GET,
				'callback'            => array(
					$this->quiz_obj,
					'get_quiz',
				),
				'args'                => array(
					'id' => $this->get_id_arg_schema(),
				),
				'permission_callback' => array( RestAuth::class, 'permission_quiz' ),
			)
		);

		// Quiz question answer by quiz id.
		register_rest_route(
			self::$namespace,
			'/quiz-question-answer/(?P<id>\d+)',
			array(
				'methods'             => HttpHelper::METHOD_GET,
				'callback'            => array(
					$this->quiz_obj,
					'quiz_question_ans',
				),
				'args'                => array(
					'id' => $this->get_id_arg_schema(),
				),
				'permission_callback' => array( RestAuth::class, 'permission_quiz' ),
			)
		);

		// Quiz attempt details by quiz id.
		register_rest_route(
			self::$namespace,
			'/quiz-attempt-details/(?P<id>\d+)',
			array(
				'methods'             => HttpHelper::METHOD_GET,
				'callback'            => array(
					$this->quiz_obj,
					'quiz_attempt_details',
				),
				'args'                => array(
					'id' => $this->get_id_arg_schema(),
				),
				'permission_callback' => array( RestAuth::class, 'permission_quiz' ),
			)
		);

		// Author detail by id.
		register_rest_route(
			self::$namespace,
			'/author-information/(?P<id>\d+)',
			array(
				'methods'             => HttpHelper::METHOD_GET,
				'callback'            => array(
					$this->author_obj,
					'author_detail',
				),
				'args'                => array(
					'id' => $this->get_id_arg_schema(),
				),
				'permission_callback' => array( RestAuth::class, 'permission_public_author' ),
			)
		);

		// Reviews by course id.
		register_rest_route(
			self::$namespace,
			'/course-rating/(?P<id>\d+)',
			array(
				'methods'             => HttpHelper::METHOD_GET,
				'callback'            => array(
					$this->rating_obj,
					'course_rating',
				),
				'args'                => array(
					'id' => $this->get_id_arg_schema(),
				),
				'permission_callback' => array( RestAuth::class, 'permission_optional_access_token' ),
			)
		);

		// Get course content by id.
		register_rest_route(
			self::$namespace,
			'/course-contents/(?P<id>\d+)',
			array(
				'methods'             => HttpHelper::METHOD_GET,
				'callback'            => array(
					$this->course_obj,
					'course_contents',
				),
				'args'                => array(
					'id' => $this->get_id_arg_schema(),
				),
				'permission_callback' => array( RestAuth::class, 'permission_course_content' ),
			)
		);
	}
}
