<?php
/**
 * Uninstall Feedback Tests
 *
 * @package TutorTest
 */

namespace TutorTest;

use WP_UnitTestCase;
use TUTOR\UninstallFeedback;
use Tutor\Core\Input;

/**
 * Class UninstallFeedbackTest
 */
class UninstallFeedbackTest extends WP_UnitTestCase {

	/**
	 * Admin user ID
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Subscriber user ID
	 *
	 * @var int
	 */
	protected $subscriber_id;

	/**
	 * UninstallFeedback instance
	 *
	 * @var UninstallFeedback
	 */
	protected $feedback;

	/**
	 * Set up test fixtures
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		add_filter( 'wp_doing_ajax', '__return_true' );

		$die_handler = function () {
			return function ( $message ) {
				throw new \WPDieException( esc_html( (string) $message ) );
			};
		};
		add_filter( 'wp_die_handler', $die_handler );
		add_filter( 'wp_die_ajax_handler', $die_handler );

		$this->admin_id      = $this->factory()->user->create( array( 'role' => 'administrator' ) );
		$this->subscriber_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->feedback      = new UninstallFeedback();
	}

	/**
	 * Tear down test fixtures
	 *
	 * @return void
	 */
	public function tearDown(): void {
		wp_set_current_user( 0 );
		$_POST = array();
		parent::tearDown();
	}

	/**
	 * Test unauthorized user cannot submit uninstall feedback
	 *
	 * @return void
	 */
	public function test_unauthorized_user_cannot_submit_feedback() {
		wp_set_current_user( $this->subscriber_id );

		$_POST['security']         = wp_create_nonce( 'tutor_uninstall_feedback_nonce' );
		$_POST['uninstall_reason'] = 'could_not_setup';

		ob_start();
		try {
			$this->feedback->ajax_send_uninstall_feedback();
		} catch ( \WPDieException $e ) {
			unset( $e );
		}
		$output = ob_get_clean();

		$response = json_decode( $output, true );
		$this->assertIsArray( $response );
		$this->assertFalse( $response['success'] );
		$this->assertEquals( 'Permission denied.', $response['data']['message'] );
	}

	/**
	 * Test request with invalid nonce is rejected
	 *
	 * @return void
	 */
	public function test_invalid_nonce_rejected() {
		wp_set_current_user( $this->admin_id );

		$_POST['security']         = 'invalid_nonce_value';
		$_POST['uninstall_reason'] = 'could_not_setup';

		ob_start();
		try {
			$this->feedback->ajax_send_uninstall_feedback();
		} catch ( \WPDieException $e ) {
			unset( $e );
		}
		$output = ob_get_clean();

		$response = json_decode( $output, true );
		$this->assertIsArray( $response );
		$this->assertFalse( $response['success'] );
		$this->assertEquals( 'Invalid security token.', $response['data']['message'] );
	}

	/**
	 * Test authorized admin can submit feedback and payload contains all required fields
	 *
	 * @return void
	 */
	public function test_admin_can_submit_feedback_with_complete_payload() {
		wp_set_current_user( $this->admin_id );
		$admin_user = get_userdata( $this->admin_id );

		$_POST['security']         = wp_create_nonce( 'tutor_uninstall_feedback_nonce' );
		$_POST['plugin_slug']      = 'tutor';
		$_POST['uninstall_reason'] = 'found_better_plugin';
		$_POST['reason_detail']    = 'Testing detailed explanation';

		// Intercept remote post to capture payload and prevent external network call.
		$captured_post_args = null;
		add_filter(
			'pre_http_request',
			function ( $response, $parsed_args, $url ) use ( &$captured_post_args ) {
				$captured_post_args = array(
					'url'  => $url,
					'args' => $parsed_args,
				);
				return array(
					'response' => array( 'code' => 200 ),
					'body'     => '{"success":true}',
				);
			},
			10,
			3
		);

		ob_start();
		try {
			$this->feedback->ajax_send_uninstall_feedback();
		} catch ( \WPDieException $e ) {
			unset( $e );
		}
		$output = ob_get_clean();

		$response = json_decode( $output, true );
		$this->assertIsArray( $response, 'Output must be a valid JSON response.' );
		$this->assertTrue( $response['success'], 'AJAX response must indicate success.' );

		$payload = $response['data']['payload'];

		// Verify all 7 required payload fields.
		$this->assertArrayHasKey( 'created_at', $payload );
		$this->assertArrayHasKey( 'updated_at', $payload );
		$this->assertArrayHasKey( 'user_ip', $payload );
		$this->assertArrayHasKey( 'user_email', $payload );
		$this->assertArrayHasKey( 'website_url', $payload );
		$this->assertArrayHasKey( 'plugin_version', $payload );
		$this->assertArrayHasKey( 'uninstall_reason', $payload );

		// Verify field contents.
		$this->assertEquals( 'tutor', $payload['plugin_slug'] );
		$this->assertEquals( TUTOR_VERSION, $payload['plugin_version'] );
		$this->assertEquals( get_site_url(), $payload['website_url'] );
		$this->assertEquals( $admin_user->user_email, $payload['user_email'] );
		$this->assertEquals( 'found_better_plugin', $payload['uninstall_reason'] );
		$this->assertEquals( 'Testing detailed explanation', $payload['reason_detail'] );
		$this->assertNotEmpty( $payload['created_at'] );
		$this->assertNotEmpty( $payload['updated_at'] );

		// Verify external dispatch was invoked with JSON payload.
		$this->assertNotNull( $captured_post_args, 'wp_remote_post must be called to send feedback.' );
		$this->assertEquals( 'https://api.themeum.com/api/v1/tutor/uninstall-feedback', $captured_post_args['url'] );
		$decoded_remote_body = json_decode( $captured_post_args['args']['body'], true );
		$this->assertEquals( $payload['uninstall_reason'], $decoded_remote_body['uninstall_reason'] );
	}

	/**
	 * Test assets only enqueue on plugins.php screen
	 *
	 * @return void
	 */
	public function test_assets_enqueue_only_on_plugins_page() {
		wp_set_current_user( $this->admin_id );

		// Non-plugins hook.
		$this->feedback->enqueue_assets( 'index.php' );
		$this->assertFalse( wp_script_is( 'tutor-uninstall-feedback', 'enqueued' ) );

		// Plugins hook.
		$this->feedback->enqueue_assets( 'plugins.php' );
		$this->assertTrue( wp_script_is( 'tutor-uninstall-feedback', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'tutor-uninstall-feedback', 'enqueued' ) );
	}

	/**
	 * Test modal template renders expected structure and reasons
	 *
	 * @return void
	 */
	public function test_modal_template_renders() {
		ob_start();
		include tutor()->path . 'views/modal/uninstall-feedback-modal.php';
		$output = ob_get_clean();

		$this->assertStringContainsString( 'id="tutor-uninstall-feedback-modal"', $output );
		$this->assertStringContainsString( 'could_not_setup', $output );
		$this->assertStringContainsString( 'found_better_plugin', $output );
		$this->assertStringContainsString( 'missing_feature', $output );
		$this->assertStringContainsString( 'technical_issues', $output );
		$this->assertStringContainsString( 'temporary_deactivation', $output );
		$this->assertStringContainsString( 'other', $output );
		$this->assertStringContainsString( 'tutor-btn-feedback-skip', $output );
		$this->assertStringContainsString( 'tutor-btn-feedback-submit', $output );
		$this->assertStringContainsString( 'tutor-feedback-consent-notice', $output );
	}
}
