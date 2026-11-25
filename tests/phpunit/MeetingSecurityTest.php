<?php
/**
 * Meeting Security Tests for Zoom and Google Meet
 *
 * @package Tutor\Tests
 */

namespace TutorTest;

use WP_UnitTestCase;
use TUTOR\User;
use Tutor\Helpers\HttpHelper;

class MeetingSecurityTest extends WP_UnitTestCase {

	protected $admin_id;
	protected $instructor_a;
	protected $instructor_b;
	protected $course_a;
	protected $topic_a;
	protected $course_b;
	protected $topic_b;
	protected $zoom_meeting_a;
	protected $google_meet_a;
	protected $zoom_instance;
	protected $meet_instance;

	public function setUp(): void {
		parent::setUp();

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		// Enable zoom and google-meet addons.
		update_option(
			'tutor_addons_config',
			array(
				'tutor-pro/addons/tutor-zoom/tutor-zoom.php' => array( 'is_enable' => 1 ),
				'tutor-pro/addons/google-meet/google-meet.php' => array( 'is_enable' => 1 ),
			)
		);

		$pro_dir = dirname( __DIR__, 3 ) . '/tutor-pro';
		if ( file_exists( $pro_dir . '/tutor-pro.php' ) ) {
			if ( ! defined( 'TUTOR_PRO_VERSION' ) ) {
				require_once $pro_dir . '/vendor/autoload.php';
				define( 'TUTOR_CORE_REQ_VERSION', '4.2.1' );
				define( 'TUTOR_PRO_VERSION', '4.2.1' );
				define( 'TUTOR_PRO_FILE', $pro_dir . '/tutor-pro.php' );
			}
			if ( file_exists( $pro_dir . '/includes/functions.php' ) ) {
				require_once $pro_dir . '/includes/functions.php';
			}
			require_once $pro_dir . '/addons/tutor-zoom/tutor-zoom.php';
			require_once $pro_dir . '/addons/google-meet/google-meet.php';
		}

		// Register post types if needed
		if ( ! post_type_exists( 'tutor_zoom_meeting' ) ) {
			register_post_type( 'tutor_zoom_meeting', array( 'public' => false, 'supports' => array( 'title', 'editor' ) ) );
		}
		if ( ! post_type_exists( 'tutor-google-meet' ) ) {
			register_post_type( 'tutor-google-meet', array( 'public' => false, 'supports' => array( 'title', 'editor' ) ) );
		}

		// Create admin and instructors.
		$this->admin_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
		$this->instructor_a = $this->factory()->user->create( array( 'role' => tutor()->instructor_role ) );
		$this->instructor_b = $this->factory()->user->create( array( 'role' => tutor()->instructor_role ) );

		// Create Course A and Topic A for Instructor A.
		$this->course_a = $this->factory()->post->create(
			array(
				'post_type'   => tutor()->course_post_type,
				'post_title'  => 'Course A by Instructor A',
				'post_author' => $this->instructor_a,
				'post_status' => 'publish',
			)
		);
		$this->topic_a = $this->factory()->post->create(
			array(
				'post_type'   => tutor()->topics_post_type,
				'post_title'  => 'Topic A in Course A',
				'post_author' => $this->instructor_a,
				'post_parent' => $this->course_a,
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
		$this->topic_b = $this->factory()->post->create(
			array(
				'post_type'   => tutor()->topics_post_type,
				'post_title'  => 'Topic B in Course B',
				'post_author' => $this->instructor_b,
				'post_parent' => $this->course_b,
				'post_status' => 'publish',
			)
		);

		// Create Zoom Meeting A under Topic A.
		$this->zoom_meeting_a = $this->factory()->post->create(
			array(
				'post_type'   => 'tutor_zoom_meeting',
				'post_title'  => 'Zoom Meeting A Secret Title',
				'post_content' => 'Zoom Meeting A Secret Content',
				'post_author' => $this->instructor_a,
				'post_parent' => $this->topic_a,
				'post_status' => 'publish',
			)
		);
		update_post_meta( $this->zoom_meeting_a, '_tutor_zm_for_course', $this->course_a );
		update_post_meta( $this->zoom_meeting_a, '_tutor_zm_for_topic', $this->topic_a );
		update_post_meta( $this->zoom_meeting_a, '_tutor_zm_start_datetime', '2026-11-20 10:00:00' );
		update_post_meta( $this->zoom_meeting_a, '_tutor_zoom_meeting_data', json_encode( array(
			'id'       => '123456789',
			'topic'    => 'Zoom Meeting A Secret Title',
			'duration' => 60,
			'timezone' => 'UTC',
			'password' => 'secret_password_123',
		) ) );

		// Create Google Meet A under Topic A.
		$this->google_meet_a = $this->factory()->post->create(
			array(
				'post_type'   => 'tutor-google-meet',
				'post_title'  => 'Google Meet A Secret Title',
				'post_content' => 'Google Meet A Secret Content',
				'post_author' => $this->instructor_a,
				'post_parent' => $this->topic_a,
				'post_status' => 'publish',
			)
		);
		update_post_meta( $this->google_meet_a, 'tutor-google-meet-start-datetime', '2026-11-20 10:00:00' );
		update_post_meta( $this->google_meet_a, 'tutor-google-meet-end-datetime', '2026-11-20 11:00:00' );
		update_post_meta( $this->google_meet_a, 'tutor-google-meet-event-details', json_encode( array(
			'id'             => 'meet_evt_123',
			'hangoutLink'    => 'https://meet.google.com/abc-defg-hij',
			'start_datetime' => '2026-11-20 10:00:00',
			'end_datetime'   => '2026-11-20 11:00:00',
			'timezone'       => 'UTC',
		) ) );

		if ( class_exists( '\TUTOR_ZOOM\Zoom' ) ) {
			$this->zoom_instance = new \TUTOR_ZOOM\Zoom( false );
		}
		if ( class_exists( '\TutorPro\GoogleMeet\GoogleEvent\Events' ) ) {
			$this->meet_instance = new \TutorPro\GoogleMeet\GoogleEvent\Events();
		}
	}

	public function tearDown(): void {
		$_POST = array();
		wp_set_current_user( 0 );
		parent::tearDown();
	}

	/**
	 * Helper to invoke ajax method capturing json_response (via die / JSON_RESPONSE)
	 */
	private function capture_json_response( callable $callback ) {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST[ tutor()->nonce ]   = wp_create_nonce( tutor()->nonce_action );

		add_filter( 'wp_doing_ajax', '__return_true' );
		$die_handler = function() {
			throw new \WPDieException( 'ajax_die' );
		};
		add_filter( 'wp_die_ajax_handler', fn() => $die_handler );

		// Catch output
		ob_start();
		try {
			$callback();
		} catch ( \WPDieException $e ) {
			// wp_die called by wp_send_json / json_response
		} catch ( \Throwable $t ) {
			// Catch any other termination
		}
		$output = ob_get_clean();

		remove_filter( 'wp_doing_ajax', '__return_true' );

		return json_decode( $output, true );
	}

	/**
	 * Test Zoom: Instructor B cannot fetch details of Instructor A's meeting
	 */
	public function test_zoom_meeting_details_idor_prevented() {
		$this->assertNotNull( $this->zoom_instance, 'Zoom addon should be instantiated' );

		wp_set_current_user( $this->instructor_b );

		// Instructor B passes their own topic (Topic B), but Meeting A from Instructor A
		$_POST['topic_id']   = $this->topic_b;
		$_POST['meeting_id'] = $this->zoom_meeting_a;

		$response = $this->capture_json_response( array( $this->zoom_instance, 'ajax_zoom_meeting_details' ) );

		$this->assertNotEmpty( $response, 'Response should not be empty' );
		$this->assertEquals( HttpHelper::STATUS_FORBIDDEN, $response['status_code'], 'Must return 403 Forbidden for cross-course meeting retrieval' );
	}

	/**
	 * Test Zoom: Instructor A can access their own meeting details
	 */
	public function test_zoom_meeting_details_authorized_owner() {
		$this->assertNotNull( $this->zoom_instance );

		wp_set_current_user( $this->instructor_a );

		$_POST['topic_id']   = $this->topic_a;
		$_POST['meeting_id'] = $this->zoom_meeting_a;

		$response = $this->capture_json_response( array( $this->zoom_instance, 'ajax_zoom_meeting_details' ) );

		$this->assertNotEmpty( $response );
		$this->assertEquals( HttpHelper::STATUS_OK, $response['status_code'] );
		$this->assertEquals( 'Zoom Meeting A Secret Title', $response['data']['post_title'] );
	}

	/**
	 * Test Zoom: Reject invalid post type in ajax_zoom_meeting_details
	 */
	public function test_zoom_meeting_details_rejects_invalid_post_type() {
		$this->assertNotNull( $this->zoom_instance );

		wp_set_current_user( $this->instructor_a );

		// Pass course_a ID as meeting_id
		$_POST['topic_id']   = $this->topic_a;
		$_POST['meeting_id'] = $this->course_a;

		$response = $this->capture_json_response( array( $this->zoom_instance, 'ajax_zoom_meeting_details' ) );

		$this->assertEquals( HttpHelper::STATUS_BAD_REQUEST, $response['status_code'] );
	}

	/**
	 * Test Zoom: Instructor B cannot update Instructor A's meeting in save_meeting
	 */
	public function test_zoom_save_meeting_update_idor_prevented() {
		$this->assertNotNull( $this->zoom_instance );

		wp_set_current_user( $this->instructor_b );

		// Mock account_id
		update_user_meta( $this->instructor_b, 'tutor_zoom_api', json_encode( array( 'account_id' => '123' ) ) );

		// Try to update Meeting A under Topic B
		$_POST['meeting_id'] = $this->zoom_meeting_a;
		$_POST['topic_id']   = $this->topic_b;
		$_POST['course_id']  = $this->course_b;

		$response = $this->capture_json_response( array( $this->zoom_instance, 'tutor_zoom_save_meeting' ) );

		$this->assertEquals( HttpHelper::STATUS_FORBIDDEN, $response['status_code'] );
	}

	/**
	 * Test Zoom: Instructor B cannot create a meeting under Instructor A's topic or course
	 */
	public function test_zoom_save_meeting_create_under_other_course_prevented() {
		$this->assertNotNull( $this->zoom_instance );

		wp_set_current_user( $this->instructor_b );

		update_user_meta( $this->instructor_b, 'tutor_zoom_api', json_encode( array( 'account_id' => '123' ) ) );

		$_POST['meeting_id'] = 0;
		$_POST['topic_id']   = $this->topic_a;
		$_POST['course_id']  = $this->course_a;

		$response = $this->capture_json_response( array( $this->zoom_instance, 'tutor_zoom_save_meeting' ) );

		$this->assertEquals( HttpHelper::STATUS_FORBIDDEN, $response['status_code'] );
	}

	/**
	 * Test Zoom: Instructor B cannot delete Instructor A's meeting
	 */
	public function test_zoom_delete_meeting_idor_prevented() {
		$this->assertNotNull( $this->zoom_instance );

		wp_set_current_user( $this->instructor_b );

		$_POST['meeting_id'] = $this->zoom_meeting_a;
		$_POST['topic_id']   = $this->topic_b;

		$response = $this->capture_json_response( array( $this->zoom_instance, 'tutor_zoom_delete_meeting' ) );

		$this->assertEquals( HttpHelper::STATUS_FORBIDDEN, $response['status_code'] );
		$this->assertNotNull( get_post( $this->zoom_meeting_a ), 'Meeting must not be deleted' );
	}

	/**
	 * Test Google Meet: Instructor B cannot fetch details of Instructor A's meeting
	 */
	public function test_google_meet_details_idor_prevented() {
		$this->assertNotNull( $this->meet_instance, 'Google Meet addon should be instantiated' );

		wp_set_current_user( $this->instructor_b );

		$_POST['topic_id']   = $this->topic_b;
		$_POST['meeting_id'] = $this->google_meet_a;

		$response = $this->capture_json_response( array( $this->meet_instance, 'ajax_google_meet_meeting_details' ) );

		$this->assertNotEmpty( $response );
		$this->assertEquals( HttpHelper::STATUS_FORBIDDEN, $response['status_code'] );
	}

	/**
	 * Test Google Meet: Instructor A can access their own meeting details
	 */
	public function test_google_meet_details_authorized_owner() {
		$this->assertNotNull( $this->meet_instance );

		wp_set_current_user( $this->instructor_a );

		$_POST['topic_id']   = $this->topic_a;
		$_POST['meeting_id'] = $this->google_meet_a;

		$response = $this->capture_json_response( array( $this->meet_instance, 'ajax_google_meet_meeting_details' ) );

		$this->assertNotEmpty( $response );
		$this->assertEquals( HttpHelper::STATUS_OK, $response['status_code'] );
		$this->assertEquals( 'Google Meet A Secret Title', $response['data']['post_title'] );
	}

	/**
	 * Test Google Meet: Reject invalid post type in ajax_google_meet_meeting_details
	 */
	public function test_google_meet_details_rejects_invalid_post_type() {
		$this->assertNotNull( $this->meet_instance );

		wp_set_current_user( $this->instructor_a );

		// Pass course_a ID as meeting_id
		$_POST['topic_id']   = $this->topic_a;
		$_POST['meeting_id'] = $this->course_a;

		$response = $this->capture_json_response( array( $this->meet_instance, 'ajax_google_meet_meeting_details' ) );

		$this->assertEquals( HttpHelper::STATUS_BAD_REQUEST, $response['status_code'] );
	}

	/**
	 * Test Google Meet: Instructor B cannot create a meeting targeting Instructor A's course/topic
	 */
	public function test_google_meet_create_under_other_course_prevented() {
		$this->assertNotNull( $this->meet_instance );

		wp_set_current_user( $this->instructor_b );

		$_POST['object_id'] = $this->topic_a;

		// Mock is_app_permitted
		add_filter( 'tutor_pro_google_meet_is_app_permitted', '__return_true' );

		$response = $this->capture_json_response( array( $this->meet_instance, 'create_meeting' ) );

		$this->assertEquals( HttpHelper::STATUS_FORBIDDEN, $response['status_code'] );
	}

	/**
	 * Test Google Meet: Instructor B cannot update Instructor A's meeting
	 */
	public function test_google_meet_update_other_meeting_prevented() {
		$this->assertNotNull( $this->meet_instance );

		wp_set_current_user( $this->instructor_b );

		$_POST['object_id'] = $this->topic_b;
		$_POST['post-id']   = $this->google_meet_a;

		$response = $this->capture_json_response( array( $this->meet_instance, 'create_meeting' ) );

		$this->assertEquals( HttpHelper::STATUS_FORBIDDEN, $response['status_code'] );
	}

	/**
	 * Test Google Meet: Instructor B cannot delete Instructor A's meeting
	 */
	public function test_google_meet_delete_idor_prevented() {
		$this->assertNotNull( $this->meet_instance );

		wp_set_current_user( $this->instructor_b );

		$_POST['post-id']  = $this->google_meet_a;
		$_POST['event-id'] = 'mock_event_id';

		$response = $this->capture_json_response( array( $this->meet_instance, 'delete_meeting' ) );

		$this->assertEquals( HttpHelper::STATUS_FORBIDDEN, $response['status_code'] );
		$this->assertNotNull( get_post( $this->google_meet_a ), 'Google meet post must not be deleted' );
	}

	/**
	 * Test Google Meet: Delete rejects non-meet post type
	 */
	public function test_google_meet_delete_rejects_invalid_post_type() {
		$this->assertNotNull( $this->meet_instance );

		wp_set_current_user( $this->instructor_a );

		$_POST['post-id']  = $this->course_a;
		$_POST['event-id'] = 'mock_event_id';

		$response = $this->capture_json_response( array( $this->meet_instance, 'delete_meeting' ) );

		$this->assertEquals( HttpHelper::STATUS_BAD_REQUEST, $response['status_code'] );
	}

	/**
	 * Test Admin can access Zoom and Google Meet meeting details
	 */
	public function test_admin_can_access_meetings() {
		wp_set_current_user( $this->admin_id );

		// Zoom
		$_POST['topic_id']   = $this->topic_a;
		$_POST['meeting_id'] = $this->zoom_meeting_a;
		$zoom_res = $this->capture_json_response( array( $this->zoom_instance, 'ajax_zoom_meeting_details' ) );
		$this->assertEquals( HttpHelper::STATUS_OK, $zoom_res['status_code'] );

		// Google Meet
		$_POST['topic_id']   = $this->topic_a;
		$_POST['meeting_id'] = $this->google_meet_a;
		$meet_res = $this->capture_json_response( array( $this->meet_instance, 'ajax_google_meet_meeting_details' ) );
		$this->assertEquals( HttpHelper::STATUS_OK, $meet_res['status_code'] );
	}

	/**
	 * Test Co-instructor can access Zoom and Google Meet meeting details
	 */
	public function test_co_instructor_can_access_meetings() {
		// Add instructor B as co-instructor to course A
		add_user_meta( $this->instructor_b, '_tutor_instructor_course_id', $this->course_a );

		wp_set_current_user( $this->instructor_b );

		// Zoom
		$_POST['topic_id']   = $this->topic_a;
		$_POST['meeting_id'] = $this->zoom_meeting_a;
		$zoom_res = $this->capture_json_response( array( $this->zoom_instance, 'ajax_zoom_meeting_details' ) );
		$this->assertEquals( HttpHelper::STATUS_OK, $zoom_res['status_code'] );

		// Google Meet
		$_POST['topic_id']   = $this->topic_a;
		$_POST['meeting_id'] = $this->google_meet_a;
		$meet_res = $this->capture_json_response( array( $this->meet_instance, 'ajax_google_meet_meeting_details' ) );
		$this->assertEquals( HttpHelper::STATUS_OK, $meet_res['status_code'] );
	}

	/**
	 * Test extend_course_details_response filters out unauthorized meeting data
	 */
	public function test_extend_course_details_response_permissions() {
		wp_set_current_user( $this->instructor_b );

		// Instructor B checks Course A details
		$course_data = array( 'ID' => $this->course_a );
		$zoom_extended = $this->zoom_instance->extend_course_details_response( $course_data );
		$this->assertArrayNotHasKey( 'zoom_meetings', $zoom_extended );

		$meet_extended = $this->meet_instance->extend_course_details_response( $course_data );
		$this->assertArrayNotHasKey( 'google_meet_meetings', $meet_extended );

		// Instructor A checks Course A details
		wp_set_current_user( $this->instructor_a );
		$zoom_extended_auth = $this->zoom_instance->extend_course_details_response( $course_data );
		$this->assertArrayHasKey( 'zoom_meetings', $zoom_extended_auth );

		$meet_extended_auth = $this->meet_instance->extend_course_details_response( $course_data );
		$this->assertArrayHasKey( 'google_meet_meetings', $meet_extended_auth );
	}

	/**
	 * Test Zoom get_meeting_edit_content authorization
	 */
	public function test_zoom_get_meeting_edit_content_permissions() {
		wp_set_current_user( $this->instructor_b );

		// Instructor B attempts to get edit content for Meeting A
		$unauth_content = $this->zoom_instance->get_meeting_edit_content( $this->zoom_meeting_a );
		$this->assertEmpty( $unauth_content );

		// Instructor A gets edit content for Meeting A
		wp_set_current_user( $this->instructor_a );
		$auth_content = $this->zoom_instance->get_meeting_edit_content( $this->zoom_meeting_a );
		$this->assertNotEmpty( $auth_content );
		$this->assertEquals( $this->zoom_meeting_a, $auth_content['meeting_id'] );
	}
}
