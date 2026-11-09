<?php
/**
 * Test WordPress Abilities API integration.
 *
 * @package Tutor\Test
 * @since 4.1.1
 */

namespace TutorTest;

use TUTOR\Abilities;

/**
 * Abilities integration tests.
 *
 * @since 4.1.1
 */
class AbilitiesTest extends \WP_UnitTestCase {

	/**
	 * The integration registers its hooks without requiring the Abilities API.
	 *
	 * @return void
	 */
	public function test_registers_abilities_api_hooks(): void {
		$abilities = new Abilities();

		$this->assertNotFalse( has_action( 'wp_abilities_api_categories_init', array( $abilities, 'register_category' ) ) );
		$this->assertNotFalse( has_action( 'wp_abilities_api_init', array( $abilities, 'register_abilities' ) ) );
	}

	/**
	 * Registered abilities are available when the WordPress Abilities API exists.
	 *
	 * @return void
	 */
	public function test_registers_tutor_lms_abilities_when_supported(): void {
		if ( ! function_exists( 'wp_register_ability' ) || ! function_exists( 'wp_get_ability' ) ) {
			$this->markTestSkipped( 'WordPress Abilities API is not available in this test environment.' );
		}

		$abilities = new Abilities();
		$abilities->register_category();
		$abilities->register_abilities();

		$this->assertNotNull( wp_get_ability( 'tutor-lms/site-info' ) );
		$this->assertNotNull( wp_get_ability( 'tutor-lms/list-courses' ) );
		$this->assertNotNull( wp_get_ability( 'tutor-lms/get-course' ) );
		$this->assertNotNull( wp_get_ability( 'tutor-lms/get-course-structure' ) );
		$this->assertNotNull( wp_get_ability( 'tutor-lms/get-student-progress' ) );
	}
}
