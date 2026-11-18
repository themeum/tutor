<?php
/**
 * Topic Security Tests
 *
 * @package TutorTest
 */

namespace TutorTest;

use WP_UnitTestCase;
use TUTOR\Course;
use Tutor\Helpers\HttpHelper;

/**
 * Class TopicSecurityTest
 */
class TopicSecurityTest extends WP_UnitTestCase {

	/**
	 * Admin user ID
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Instructor A user ID
	 *
	 * @var int
	 */
	protected $instructor_a;

	/**
	 * Instructor B user ID
	 *
	 * @var int
	 */
	protected $instructor_b;

	/**
	 * Course A ID
	 *
	 * @var int
	 */
	protected $course_a;

	/**
	 * Topic A ID
	 *
	 * @var int
	 */
	protected $topic_a;

	/**
	 * Lesson A ID under Topic A
	 *
	 * @var int
	 */
	protected $lesson_a;

	/**
	 * Course B ID
	 *
	 * @var int
	 */
	protected $course_b;

	/**
	 * Topic B ID
	 *
	 * @var int
	 */
	protected $topic_b;

	/**
	 * Course instance
	 *
	 * @var Course
	 */
	protected $course_instance;

	/**
	 * Set up test fixtures
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		$this->course_instance = new Course();

		// Create admin and instructors.
		$this->admin_id     = $this->factory()->user->create( array( 'role' => 'administrator' ) );
		$this->instructor_a = $this->factory()->user->create( array( 'role' => tutor()->instructor_role ) );
		$this->instructor_b = $this->factory()->user->create( array( 'role' => tutor()->instructor_role ) );

		// Create Course A, Topic A, and Lesson A for Instructor A.
		$this->course_a = $this->factory()->post->create(
			array(
				'post_type'   => tutor()->course_post_type,
				'post_title'  => 'Course A by Instructor A',
				'post_author' => $this->instructor_a,
				'post_status' => 'publish',
			)
		);
		$this->topic_a  = $this->factory()->post->create(
			array(
				'post_type'   => tutor()->topics_post_type,
				'post_title'  => 'Topic A in Course A',
				'post_author' => $this->instructor_a,
				'post_parent' => $this->course_a,
				'post_status' => 'publish',
			)
		);
		$this->lesson_a = $this->factory()->post->create(
			array(
				'post_type'   => tutor()->lesson_post_type,
				'post_title'  => 'Lesson A in Topic A',
				'post_author' => $this->instructor_a,
				'post_parent' => $this->topic_a,
				'post_status' => 'publish',
			)
		);

		// Create Course B and Topic B for Instructor B.
		$this->course_b = $this->factory()->post->create(
			array(
				'post_type'   => tutor()->course_post_type,
				'post_title'  => 'Course B by Instructor B',
				'post_author' => $this->instructor_b,
				'post_status' => 'publish',
			)
		);
		$this->topic_b  = $this->factory()->post->create(
			array(
				'post_type'   => tutor()->topics_post_type,
				'post_title'  => 'Topic B in Course B',
				'post_author' => $this->instructor_b,
				'post_parent' => $this->course_b,
				'post_status' => 'publish',
			)
		);
	}

	/**
	 * Tear down test fixtures
	 *
	 * @return void
	 */
	public function tearDown(): void {
		$_POST = array();
		wp_set_current_user( 0 );
		parent::tearDown();
	}

	/**
	 * Helper to invoke ajax method capturing json_response
	 *
	 * @param callable $callback callback function.
	 * @return array
	 */
	private function capture_json_response( callable $callback ) {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST[ tutor()->nonce ]   = wp_create_nonce( tutor()->nonce_action );

		add_filter( 'wp_doing_ajax', '__return_true' );
		$die_handler = function () {
			throw new \WPDieException( 'ajax_die' );
		};
		add_filter( 'wp_die_ajax_handler', fn() => $die_handler );

		ob_start();
		try {
			$callback();
		} catch ( \WPDieException $e ) {
			unset( $e );
		} catch ( \Throwable $t ) {
			unset( $t );
		}
		$output = ob_get_clean();

		remove_filter( 'wp_doing_ajax', '__return_true' );

		return json_decode( $output, true );
	}

	/**
	 * Test tutor_delete_topic IDOR: Instructor B cannot delete Instructor A's topic
	 *
	 * @return void
	 */
	public function test_delete_topic_idor_prevented() {
		wp_set_current_user( $this->instructor_b );

		$_POST['topic_id'] = $this->topic_a;

		$response = $this->capture_json_response( array( $this->course_instance, 'tutor_delete_topic' ) );

		$this->assertNotEmpty( $response );
		$this->assertEquals( HttpHelper::STATUS_FORBIDDEN, $response['status_code'] );
		$this->assertNotNull( get_post( $this->topic_a ), 'Topic A must not be deleted by unauthorized instructor' );
	}

	/**
	 * Test tutor_delete_topic: Cross-course mismatch is prevented
	 *
	 * @return void
	 */
	public function test_delete_topic_cross_course_mismatch_prevented() {
		wp_set_current_user( $this->instructor_a );

		// Topic A belongs to Course A, but Course B is provided in course_id.
		$_POST['topic_id']  = $this->topic_a;
		$_POST['course_id'] = $this->course_b;

		$response = $this->capture_json_response( array( $this->course_instance, 'tutor_delete_topic' ) );

		$this->assertNotEmpty( $response );
		$this->assertEquals( HttpHelper::STATUS_FORBIDDEN, $response['status_code'] );
		$this->assertNotNull( get_post( $this->topic_a ), 'Topic A must not be deleted when course_id does not match' );
	}

	/**
	 * Test tutor_delete_topic: Rejects non-topic post type (e.g. course or lesson)
	 *
	 * @return void
	 */
	public function test_delete_topic_rejects_non_topic_post_type() {
		wp_set_current_user( $this->instructor_a );

		// Pass course_a ID as topic_id.
		$_POST['topic_id'] = $this->course_a;

		$response = $this->capture_json_response( array( $this->course_instance, 'tutor_delete_topic' ) );

		$this->assertNotEmpty( $response );
		$this->assertEquals( HttpHelper::STATUS_BAD_REQUEST, $response['status_code'] );
		$this->assertNotNull( get_post( $this->course_a ), 'Course post must not be deleted via tutor_delete_topic' );

		// Pass lesson_a ID as topic_id.
		$_POST['topic_id'] = $this->lesson_a;

		$response = $this->capture_json_response( array( $this->course_instance, 'tutor_delete_topic' ) );

		$this->assertNotEmpty( $response );
		$this->assertEquals( HttpHelper::STATUS_BAD_REQUEST, $response['status_code'] );
		$this->assertNotNull( get_post( $this->lesson_a ), 'Lesson post must not be deleted via tutor_delete_topic' );
	}

	/**
	 * Test tutor_delete_topic: Rejects empty or invalid topic id
	 *
	 * @return void
	 */
	public function test_delete_topic_rejects_empty_id() {
		wp_set_current_user( $this->instructor_a );

		$_POST['topic_id'] = 0;

		$response = $this->capture_json_response( array( $this->course_instance, 'tutor_delete_topic' ) );

		$this->assertNotEmpty( $response );
		$this->assertEquals( HttpHelper::STATUS_BAD_REQUEST, $response['status_code'] );
	}

	/**
	 * Test tutor_delete_topic: Authorized course author can delete topic
	 *
	 * @return void
	 */
	public function test_delete_topic_authorized_owner_succeeds() {
		wp_set_current_user( $this->instructor_a );

		$_POST['topic_id']  = $this->topic_a;
		$_POST['course_id'] = $this->course_a;

		$response = $this->capture_json_response( array( $this->course_instance, 'tutor_delete_topic' ) );

		$this->assertNotEmpty( $response );
		$this->assertEquals( HttpHelper::STATUS_OK, $response['status_code'] );
		$this->assertNull( get_post( $this->topic_a ), 'Topic A should be deleted' );

		// Lesson A should now have post_parent = 0 and _tutor_course_id_for_lesson meta set.
		$lesson = get_post( $this->lesson_a );
		$this->assertEquals( 0, (int) $lesson->post_parent );
		$this->assertEquals( $this->course_a, (int) get_post_meta( $this->lesson_a, '_tutor_course_id_for_lesson', true ) );
	}

	/**
	 * Test tutor_delete_topic: Administrator can delete any topic
	 *
	 * @return void
	 */
	public function test_delete_topic_admin_succeeds() {
		wp_set_current_user( $this->admin_id );

		$_POST['topic_id']  = $this->topic_b;
		$_POST['course_id'] = $this->course_b;

		$response = $this->capture_json_response( array( $this->course_instance, 'tutor_delete_topic' ) );

		$this->assertNotEmpty( $response );
		$this->assertEquals( HttpHelper::STATUS_OK, $response['status_code'] );
		$this->assertNull( get_post( $this->topic_b ), 'Topic B should be deleted by admin' );
	}

	/**
	 * Test tutor_delete_topic: Co-instructor can delete topic of assigned course
	 *
	 * @return void
	 */
	public function test_delete_topic_co_instructor_succeeds() {
		// Assign Instructor B as co-instructor to Course A.
		add_user_meta( $this->instructor_b, '_tutor_instructor_course_id', $this->course_a );

		wp_set_current_user( $this->instructor_b );

		$_POST['topic_id']  = $this->topic_a;
		$_POST['course_id'] = $this->course_a;

		$response = $this->capture_json_response( array( $this->course_instance, 'tutor_delete_topic' ) );

		$this->assertNotEmpty( $response );
		$this->assertEquals( HttpHelper::STATUS_OK, $response['status_code'] );
		$this->assertNull( get_post( $this->topic_a ), 'Topic A should be deleted by co-instructor' );
	}

	/**
	 * Test tutor_save_topic: Cross-course update prevented
	 *
	 * @return void
	 */
	public function test_save_topic_cross_course_update_prevented() {
		wp_set_current_user( $this->instructor_b );

		// Instructor B tries to update Topic A by passing Topic A with Course B.
		$_POST['title']     = 'Hacked Topic Title';
		$_POST['topic_id']  = $this->topic_a;
		$_POST['course_id'] = $this->course_b;

		$response = $this->capture_json_response( array( $this->course_instance, 'tutor_save_topic' ) );

		// wp_send_json_error was called.
		$this->assertFalse( $response['success'] );
		$topic = get_post( $this->topic_a );
		$this->assertEquals( 'Topic A in Course A', $topic->post_title );
	}
}
