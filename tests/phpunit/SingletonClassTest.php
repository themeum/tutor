<?php
/**
 * Singleton Class Unit Test
 *
 * @package Tutor\Test
 * @since 3.0.0
 */

namespace TutorTest;

use TUTOR\Singleton;

/**
 * MySingletonClass class
 *
 * Run test by: vendor/bin/phpunit --filter=MySingletonClass
 */
class MySingletonClass extends Singleton {
	/**
	 * Hello
	 *
	 * @return void
	 */
	public function hello() {
		echo 'Hello from singleton class';
	}
}

/**
 * SingletonClassTest Class
 *
 * @since 3.0.0
 */
class SingletonClassTest extends \WP_UnitTestCase {
	/**
	 * Test: get_instance return same instance instead of each time new object.
	 *
	 * @return void
	 */
	public function test_ensure_same_class_instance() {
		$obj_1 = MySingletonClass::get_instance();
		$obj_2 = MySingletonClass::get_instance();
		$this->assertSame( $obj_1, $obj_2 );
	}

	/**
	 * Test: prevent create new instance
	 *
	 * @return void
	 */
	public function test_prevent_new_instance_create() {
		$this->expectException( \Error::class );
		new MySingletonClass();
	}

	/**
	 * Test: prevent object clone.
	 *
	 * @return void
	 */
	public function test_prevent_class_object_clone() {
		$this->expectException( \Error::class );
		$obj_1 = MySingletonClass::get_instance();
		$obj_2 = clone $obj_1;
	}
}
