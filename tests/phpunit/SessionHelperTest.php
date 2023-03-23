<?php
/**
 * Session Helper Class Unit Test
 *
 * @package Tutor\Test
 * @since 2.1.9
 */

namespace TutorTest;

use stdClass;
use Tutor\Helpers\SessionHelper;


/**
 * SessionHelperTest Class
 *
 * @since 2.1.9
 */
class SessionHelperTest extends \WP_UnitTestCase {

	/**
	 * A common key for test.
	 *
	 * @var string
	 */
	private static $key = 'test_key';

	/**
	 * Session key default value null if key not exist.
	 *
	 * @return void
	 */
	public function test_session_key_data_not_found() {
		$this->assertSame( null, SessionHelper::get( '_hello' ) );
	}

	/**
	 * Session key default value test if key does not exist.
	 *
	 * @return void
	 */
	public function test_session_key_default_data() {
		$this->assertSame( 'this is default', SessionHelper::get( '_hello', 'this is default' ) );
	}

	/**
	 * Session key unset.
	 *
	 * @return void
	 */
	public function test_session_unset_key_data() {
		$this->assertSame( false, SessionHelper::unset( self::$key ) );

		SessionHelper::set( self::$key, 'hello' );
		$this->assertSame( 'hello', SessionHelper::get( self::$key ) );
		$this->assertSame( true, SessionHelper::unset( self::$key ) );
	}

	/**
	 * Session set and get multiple key data.
	 *
	 * @return void
	 */
	public function test_session_store_multiple_data() {
		SessionHelper::set( 'key_1', 'abc' );
		SessionHelper::set( 'key_2', array( 1, 2 ) );
		$this->assertSame( 'abc', SessionHelper::get( 'key_1' ) );
		$this->assertSame( array( 1, 2 ), SessionHelper::get( 'key_2' ) );
	}

	/**
	 * Session store string data in a key.
	 *
	 * @return void
	 */
	public function test_session_store_string_data() {
		SessionHelper::set( self::$key, 'abc' );
		$this->assertSame( 'abc', SessionHelper::get( self::$key ) );
	}

	/**
	 * Session store array data in a key.
	 *
	 * @return void
	 */
	public function test_session_store_array_data() {
		SessionHelper::set( self::$key, array( 1, 2, 3 ) );
		$this->assertSame( array( 1, 2, 3 ), SessionHelper::get( self::$key ) );
	}

	/**
	 * Session store object data in a key.
	 *
	 * @return void
	 */
	public function test_session_store_object_data() {
		$obj       = new stdClass();
		$obj->name = 'Jhon';
		$obj->age  = 20;

		SessionHelper::set( self::$key, $obj );
		$this->assertEquals( $obj, SessionHelper::get( self::$key ) );
	}
}
