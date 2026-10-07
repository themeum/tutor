<?php
/**
 * HttpHelper Class Unit Test
 *
 * @package Tutor\Test
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.2.0
 */

namespace TutorTest;

use Tutor\Helpers\HttpHelper;
use WP_Error;

/**
 * HttpHelperTest Class
 *
 * Requests are intercepted with the `pre_http_request` filter, so no real HTTP call is made.
 *
 * @since 4.2.0
 */
class HttpHelperTest extends \WP_UnitTestCase {

	/**
	 * Test request URL.
	 *
	 * @var string
	 */
	const URL = 'https://api.example.com/v1/items';

	/**
	 * Captured request, with `url` and `args` keys.
	 *
	 * @var array|null
	 */
	private $request;

	/**
	 * Fake response returned by the `pre_http_request` filter.
	 *
	 * @var array|WP_Error
	 */
	private $response;

	/**
	 * Intercept HTTP requests before each test.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$this->request  = null;
		$this->response = $this->fake_response();

		add_filter( 'pre_http_request', array( $this, 'intercept_request' ), 10, 3 );
	}

	/**
	 * Remove the request interceptor after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		remove_filter( 'pre_http_request', array( $this, 'intercept_request' ), 10 );

		parent::tear_down();
	}

	/**
	 * Capture the request and short-circuit it with the fake response.
	 *
	 * @param false|array|WP_Error $preempt     Default false.
	 * @param array                $parsed_args Request arguments.
	 * @param string               $url         Request URL.
	 *
	 * @return array|WP_Error
	 */
	public function intercept_request( $preempt, $parsed_args, $url ) {
		$this->request = array(
			'url'  => $url,
			'args' => $parsed_args,
		);

		return $this->response;
	}

	/**
	 * Build a response in the format wp_remote_request() returns.
	 *
	 * @param string $body    Response body.
	 * @param int    $code    HTTP status code.
	 * @param array  $headers Response headers.
	 *
	 * @return array
	 */
	private function fake_response( $body = '{"id":1,"name":"Tutor"}', $code = HttpHelper::STATUS_OK, $headers = array( 'content-type' => 'application/json' ) ) {
		return array(
			'headers'  => $headers,
			'body'     => $body,
			'response' => array(
				'code'    => $code,
				'message' => get_status_header_desc( $code ),
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	/**
	 * GET request sends the method and headers without a body.
	 *
	 * @return void
	 */
	public function test_get_sends_method_and_headers() {
		$headers = array( 'Authorization' => 'Bearer token' );

		HttpHelper::get( self::URL, array(), $headers );

		$this->assertSame( self::URL, $this->request['url'] );
		$this->assertSame( HttpHelper::METHOD_GET, $this->request['args']['method'] );
		$this->assertSame( $headers, $this->request['args']['headers'] );
		$this->assertNull( $this->request['args']['body'] );
	}

	/**
	 * GET request appends data to the URL as query parameters.
	 *
	 * @return void
	 */
	public function test_get_appends_data_as_query_params() {
		HttpHelper::get(
			self::URL,
			array(
				'page'     => 2,
				'per_page' => 10,
			)
		);

		$this->assertSame( self::URL . '?page=2&per_page=10', $this->request['url'] );
	}

	/**
	 * GET request keeps existing query parameters on the URL.
	 *
	 * @return void
	 */
	public function test_get_keeps_existing_query_params() {
		HttpHelper::get( self::URL . '?status=active', array( 'page' => 2 ) );

		$this->assertSame( self::URL . '?status=active&page=2', $this->request['url'] );
	}

	/**
	 * Data provider for the methods that send a request body.
	 *
	 * @return array
	 */
	public static function body_method_provider() {
		return array(
			'post'   => array( 'post', HttpHelper::METHOD_POST ),
			'put'    => array( 'put', HttpHelper::METHOD_PUT ),
			'patch'  => array( 'patch', HttpHelper::METHOD_PATCH ),
			'delete' => array( 'delete', HttpHelper::METHOD_DELETE ),
		);
	}

	/**
	 * Body methods send the HTTP method, an array body and headers.
	 *
	 * @dataProvider body_method_provider
	 *
	 * @param string $helper_method HttpHelper method name.
	 * @param string $method        Expected HTTP method.
	 *
	 * @return void
	 */
	public function test_body_methods_send_array_body( $helper_method, $method ) {
		$data    = array( 'name' => 'Tutor' );
		$headers = array( 'Accept' => 'application/json' );

		HttpHelper::$helper_method( self::URL, $data, $headers );

		$this->assertSame( self::URL, $this->request['url'] );
		$this->assertSame( $method, $this->request['args']['method'] );
		$this->assertSame( $data, $this->request['args']['body'] );
		$this->assertSame( $headers, $this->request['args']['headers'] );
	}

	/**
	 * Body methods send a string body (e.g. JSON) as-is.
	 *
	 * @dataProvider body_method_provider
	 *
	 * @param string $helper_method HttpHelper method name.
	 * @param string $method        Expected HTTP method.
	 *
	 * @return void
	 */
	public function test_body_methods_send_string_body_as_is( $helper_method, $method ) {
		$json = wp_json_encode( array( 'name' => 'Tutor' ) );

		HttpHelper::$helper_method( self::URL, $json, array( 'Content-Type' => 'application/json' ) );

		$this->assertSame( $method, $this->request['args']['method'] );
		$this->assertSame( $json, $this->request['args']['body'] );
	}

	/**
	 * Extra arguments are passed through to wp_remote_request().
	 *
	 * @return void
	 */
	public function test_args_are_passed_through() {
		HttpHelper::get( self::URL, array(), array(), array( 'timeout' => 30 ) );
		$this->assertSame( 30, $this->request['args']['timeout'] );

		HttpHelper::post( self::URL, array(), array(), array( 'timeout' => 60 ) );
		$this->assertSame( 60, $this->request['args']['timeout'] );
	}

	/**
	 * Extra arguments override the defaults built from the other parameters.
	 *
	 * @return void
	 */
	public function test_args_override_defaults() {
		HttpHelper::post( self::URL, array( 'a' => 1 ), array(), array( 'body' => 'override' ) );

		$this->assertSame( 'override', $this->request['args']['body'] );
	}

	/**
	 * A successful response is parsed into body, JSON, headers and status code.
	 *
	 * @return void
	 */
	public function test_parses_successful_response() {
		$response = HttpHelper::get( self::URL );

		$this->assertInstanceOf( HttpHelper::class, $response );
		$this->assertFalse( $response->has_error() );
		$this->assertSame( HttpHelper::STATUS_OK, $response->get_status_code() );
		$this->assertSame( '{"id":1,"name":"Tutor"}', $response->get_body() );
		$this->assertSame( array( 'content-type' => 'application/json' ), $response->get_headers() );

		$json = $response->get_json();
		$this->assertIsObject( $json );
		$this->assertSame( 1, $json->id );
		$this->assertSame( 'Tutor', $json->name );
	}

	/**
	 * Invalid JSON body makes get_json() return null.
	 *
	 * @return void
	 */
	public function test_get_json_returns_null_for_invalid_json() {
		$this->response = $this->fake_response( 'not json', HttpHelper::STATUS_OK, array( 'content-type' => 'text/plain' ) );

		$response = HttpHelper::get( self::URL );

		$this->assertSame( 'not json', $response->get_body() );
		$this->assertNull( $response->get_json() );
	}

	/**
	 * A 4xx/5xx response is not a transport error; the status and body are kept.
	 *
	 * @return void
	 */
	public function test_error_status_is_not_a_transport_error() {
		$this->response = $this->fake_response( '{"message":"Not found"}', HttpHelper::STATUS_NOT_FOUND );

		$response = HttpHelper::delete( self::URL );

		$this->assertFalse( $response->has_error() );
		$this->assertSame( HttpHelper::STATUS_NOT_FOUND, $response->get_status_code() );
		$this->assertSame( 'Not found', $response->get_json()->message );
	}

	/**
	 * A WP_Error from the HTTP API is reported as an error.
	 *
	 * @return void
	 */
	public function test_wp_error_is_reported() {
		$this->response = new WP_Error( 'http_request_failed', 'cURL error 28: Operation timed out' );

		$response = HttpHelper::post( self::URL, array( 'a' => 1 ) );

		$this->assertTrue( $response->has_error() );
		$this->assertSame( 'cURL error 28: Operation timed out', $response->get_error_message() );
		$this->assertNull( $response->get_status_code() );
		$this->assertNull( $response->get_body() );
	}
}
