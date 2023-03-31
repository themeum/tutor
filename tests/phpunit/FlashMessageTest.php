<?php
namespace TutorTest;

use Tutor\Cache\FlashMessage;

/**
 * FlashMessage test
 */
class FlashMessageTest extends \WP_UnitTestCase {

	private static $flash_msg;

	private $key = 'tutor_flash_message';

	/**
	 * Setup flash object
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		self::$flash_msg = new FlashMessage();
	}

	/**
	 * Test setting up data while creating object
	 *
	 * @return void
	 */
	public function test_data_feeding_with_construct() {
		$flash_msg = new FlashMessage( 'success', FlashMessage::DANGER );

		$expected = array(
			'alert'   => FlashMessage::DANGER,
			'message' => 'success',
		);

		$flash_msg->set_cache();

		$actual    = $flash_msg->get_cache();
		$flash_msg = $this->assertSame( $expected, $actual );
	}

	/**
	 * Test get_cache returns expected result
	 *
	 * @return void
	 */
	public function test_get_cache() {
		$expected = array(
			'alert'   => 'danger',
			'message' => 'success',
		);

		self::$flash_msg->data = $expected;

		self::$flash_msg->set_cache();

		$actual = self::$flash_msg->get_cache();

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test set_cache setting up data
	 *
	 * @return void
	 */
	public function test_set_cache() {
		$expected = array(
			'alert'   => 'danger',
			'message' => 'success',
		);

		self::$flash_msg->data = $expected;

		self::$flash_msg->set_cache();

		$actual = get_transient( $this->key );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test delete_method clears transient as
	 * well as clean up data property
	 *
	 * @return void
	 */
	public function test_delete_cache() {
		$data = array(
			'alert'   => 'danger',
			'message' => 'success',
		);

		self::$flash_msg->data = $data;

		self::$flash_msg->set_cache();

		self::$flash_msg->delete_cache();

		$this->assertFalse( get_transient( $this->key ) );

		$this->assertSame( '', self::$flash_msg->data );
	}

}
