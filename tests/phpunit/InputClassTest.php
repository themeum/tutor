<?php namespace TutorTest;

use TUTOR\Input;

/**
 * Input class test
 * Run test by: vendor/bin/phpunit --filter=InputClassTest
 *
 * @since 2.1.0
 */
class InputClassTest extends \WP_UnitTestCase {

	/**
	 * Without GET, POST only sanitize data
	 *
	 * @return void
	 */
	public function test_sanitize_method() {
		$this->assertEquals( 'hello', Input::sanitize( ' hello ' ) );
		$this->assertEquals( 'hello world', Input::sanitize( ' hello <br> <h1> world' ) );

		$this->assertEquals( 0, Input::sanitize( ' hello ', 1, Input::TYPE_INT ) );
		$this->assertEquals( true, Input::sanitize( 'on', true, Input::TYPE_BOOL ) );

		// seqential array sanitize
		$this->assertEquals( array(), Input::sanitize( null, null, Input::TYPE_ARRAY ) );
		$this->assertEquals( array(), Input::sanitize( null, array(), Input::TYPE_ARRAY ) );
		$this->assertEquals( array(), Input::sanitize( null, '', Input::TYPE_ARRAY ) );

		$arr    = array( 'a <i>', 'b <h1>', 'c ' );
		$expect = array( 'a', 'b', 'c' );
		$this->assertEquals( $expect, Input::sanitize( $arr, array(), Input::TYPE_ARRAY ) );

		$arr    = array( 'a <i>', '22', 'c ' );
		$expect = array( 'a', 22, 'c' );
		$this->assertEquals( $expect, Input::sanitize( $arr, array(), Input::TYPE_ARRAY ) );

		// assoc array sanitize
		$assoc  = array(
			" name \n " => '<b>Karim</b>',
			'age<br>'   => '33',
		);
		$expect = array(
			'name' => 'Karim',
			'age'  => 33,
		);
		$this->assertEquals( $expect, Input::sanitize( $assoc, array(), Input::TYPE_ARRAY ) );
	}

	public function test_array_value() {
		// default value must be an array
		$this->assertEquals( array(), Input::post( 'person', '', Input::TYPE_ARRAY ) );

		$_POST['ids'] = array( '2', 5, 6 );
		$this->assertEquals( array( 2, 5, 6 ), Input::post( 'ids', array(), Input::TYPE_ARRAY ) );
	}

	public function test_default_value() {
		$this->assertEquals( null, Input::get( 'name' ) );
		$this->assertEquals( null, Input::post( 'name' ) );

		$this->assertEquals( 'jhon', Input::get( 'name', 'jhon' ) );
		$this->assertEquals( 'jhon', Input::post( 'name', 'jhon' ) );
	}

	public function test_bool_value() {
		$val          = 'foo';
		$key          = 'key';
		$_GET[ $key ] = $val;

		// default value check
		$this->assertEquals( false, Input::get( 'has_feature', false, Input::TYPE_BOOL ) );

		// All true value test
		$true_values = array( 'on', 'ON', true, 'true', '1', 1 );
		foreach ( $true_values as $value ) {
			$_GET[ $key ] = $value;
			$this->assertEquals( true, Input::get( $key, false, Input::TYPE_BOOL ) );
		}

		// All false value test
		$false_values = array( '', null, 'false', false, 'off', 'OFF', 'anything', 'blah blah' );
		foreach ( $false_values as $value ) {
			$_GET[ $key ] = $value;
			$this->assertEquals( false, Input::get( $key, false, Input::TYPE_BOOL ) );
		}

	}

	public function test_integer_value() {
		$val          = 'foo';
		$key          = 'key';
		$_GET[ $key ] = $val;

		$this->assertEquals( 0, Input::get( $key, 0, Input::TYPE_INT ) );

		$_GET[ $key ] = '10';
		$this->assertEquals( 10, Input::get( $key, 0, Input::TYPE_INT ) );
	}

	public function test_numeric_value() {
		$val          = 'foo';
		$key          = 'key';
		$_GET[ $key ] = $val;

		$this->assertEquals( 0, Input::get( $key, 0, Input::TYPE_NUMERIC ) );

		$_GET[ $key ] = '10.50';
		$this->assertEquals( 10.50, Input::get( $key, 0, Input::TYPE_NUMERIC ) );

		$_GET[ $key ] = '20';
		$this->assertEquals( 20, Input::get( $key, 0, Input::TYPE_NUMERIC ) );
	}

	public function test_text_value() {
		$unsanitized_val = "<h1>hi</h1> \n   ";
		$_GET['name']    = $unsanitized_val;
		$val             = Input::get( 'name' );
		$this->assertEquals( 'hi', $val );
	}

	public function test_textarea_value() {
		$unsanitized_val = "<h1>hi</h1> \n How Are you?";
		$_GET['name']    = $unsanitized_val;
		$val             = Input::get( 'name', '', Input::TYPE_TEXTAREA );

		$expected = "hi \n How Are you?";
		$this->assertEquals( $expected, $val );
	}

	public function test_kses_post_value() {
		$unsanitized_val = '<h1>hi</h1><script></script><style></style>';
		$_POST['name']   = $unsanitized_val;
		$val             = Input::post( 'name', '', Input::TYPE_KSES_POST );

		$expected = '<h1>hi</h1>';
		$this->assertEquals( $expected, $val );
	}

	public function test_sanitize_array() {

		// Set data to $_POST superglobal.
		$_POST['profile']  = array(
			'first <b>name</b>' => 'hello\ <b>world</b>',
			'website'           => 'abc',
		);

		$_POST['email']    = 'ab\c@___d.com';
		$_POST['website'] = 'hello.com?file=my file.php';

		$sanitization_config = array(
			'email'    => 'sanitize_email',
			'website' => 'sanitize_url'
		);

		$sanitized_data = Input::sanitize_array(
			wp_unslash( $_POST ),
			$sanitization_config
		);

		$expected = array(
			'email'    => 'abc@d.com',
			'website' => 'http://hello.com?file=my%20file.php',
			'profile'  => array(
				'first name' => 'hello world',
				'website'    => 'http://abc',
			),
		);

		$this->assertEquals( $expected, $sanitized_data );
	}
}
