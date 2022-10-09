<?php namespace TutorTest;

use TUTOR\Input;

/**
 * Input class test
 * Run test by: vendor/bin/phpunit --filter=InputClassTest
 *
 * @since 2.1.0
 */
class InputClassTest extends \WP_UnitTestCase {

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

	public function test_text_input() {
		$unsanitized_val = "<h1>hi</h1> \n   ";
		$_GET['name']    = $unsanitized_val;
		$val             = Input::get( 'name' );
		$this->assertEquals( 'hi', $val );
	}

	public function test_textarea_input() {
		$unsanitized_val = "<h1>hi</h1> \n How Are you?";
		$_GET['name']    = $unsanitized_val;
		$val             = Input::get( 'name', '', Input::TYPE_TEXTAREA );
		
		$expected = "hi \n How Are you?";
		$this->assertEquals( $expected, $val );
	}
}
