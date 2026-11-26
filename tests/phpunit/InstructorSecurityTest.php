<?php
/**
 * Instructor & Enrollment Security Test
 *
 * @package TutorTest
 */

namespace TutorTest;

use TUTOR\Instructor;
use Tutor\Models\EnrollmentModel;

/**
 * Class InstructorSecurityTest
 */
class InstructorSecurityTest extends \WP_UnitTestCase {

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
	 * Test can_apply_for_instructor when option is disabled.
	 *
	 * @return void
	 */
	public function test_can_apply_for_instructor_disabled() {
		update_option( 'tutor_option', array( 'enable_become_instructor_btn' => 'off' ) );
		$this->assertFalse( Instructor::can_apply_for_instructor() );
	}

	/**
	 * Test can_apply_for_instructor when option is enabled.
	 *
	 * @return void
	 */
	public function test_can_apply_for_instructor_enabled() {
		update_option( 'tutor_option', array( 'enable_become_instructor_btn' => 'on' ) );
		$this->assertTrue( Instructor::can_apply_for_instructor() );
	}

	/**
	 * Test can_register_as_instructor when both settings are disabled.
	 *
	 * @return void
	 */
	public function test_can_register_as_instructor_both_disabled() {
		update_option( 'users_can_register', 0 );
		update_option( 'tutor_option', array( 'enable_become_instructor_btn' => 'off' ) );

		$this->assertFalse(
			Instructor::can_register_as_instructor(),
			'Instructor registration must be disabled when both WP registration and Tutor option are disabled.'
		);
	}

	/**
	 * Test can_register_as_instructor when WP registration is enabled but Tutor option is disabled.
	 *
	 * @return void
	 */
	public function test_can_register_as_instructor_wp_enabled_tutor_disabled() {
		update_option( 'users_can_register', 1 );
		update_option( 'tutor_option', array( 'enable_become_instructor_btn' => 'off' ) );

		$this->assertFalse(
			Instructor::can_register_as_instructor(),
			'Instructor registration must be disabled when Tutor enable_become_instructor_btn is off even if WP users_can_register is on.'
		);
	}

	/**
	 * Test can_register_as_instructor when WP registration is disabled but Tutor option is enabled.
	 *
	 * @return void
	 */
	public function test_can_register_as_instructor_wp_disabled_tutor_enabled() {
		update_option( 'users_can_register', 0 );
		update_option( 'tutor_option', array( 'enable_become_instructor_btn' => 'on' ) );

		$this->assertFalse(
			Instructor::can_register_as_instructor(),
			'Instructor registration must be disabled when WP users_can_register is off even if Tutor option is on.'
		);
	}

	/**
	 * Test can_register_as_instructor when both WP registration and Tutor option are enabled.
	 *
	 * @return void
	 */
	public function test_can_register_as_instructor_both_enabled() {
		update_option( 'users_can_register', 1 );
		update_option( 'tutor_option', array( 'enable_become_instructor_btn' => 'on' ) );

		$this->assertTrue(
			Instructor::can_register_as_instructor(),
			'Instructor registration must be enabled when both WP users_can_register and Tutor option are on.'
		);
	}

	/**
	 * Test existing student apply_for_instructor is independent of users_can_register.
	 *
	 * @return void
	 */
	public function test_existing_student_can_apply_when_wp_user_registration_is_disabled() {
		update_option( 'users_can_register', 0 );
		update_option( 'tutor_option', array( 'enable_become_instructor_btn' => 'on' ) );

		$this->assertTrue(
			Instructor::can_apply_for_instructor(),
			'Existing logged-in students should still be eligible to apply as instructors if enable_become_instructor_btn is on.'
		);
	}

	/**
	 * Test register_instructor submission rejects when enable_become_instructor_btn is off.
	 *
	 * @return void
	 */
	public function test_register_instructor_submission_rejected_when_tutor_option_disabled() {
		update_option( 'users_can_register', 1 );
		update_option( 'tutor_option', array( 'enable_become_instructor_btn' => 'off' ) );

		$_POST['tutor_action'] = 'tutor_register_instructor';

		$instructor         = new Instructor( false );
		$initial_user_count = count_users()['total_users'];

		// Calling register_instructor should return early without creating any user.
		$instructor->register_instructor();

		$final_user_count = count_users()['total_users'];
		$this->assertEquals( $initial_user_count, $final_user_count, 'No user should be created when instructor registration is disabled.' );
	}

	/**
	 * Test instructor registration template reflects can_register_as_instructor status.
	 *
	 * @return void
	 */
	public function test_instructor_registration_template_access() {
		// When disabled.
		update_option( 'users_can_register', 1 );
		update_option( 'tutor_option', array( 'enable_become_instructor_btn' => 'off' ) );

		$this->assertFalse( Instructor::can_register_as_instructor() );

		// When enabled.
		update_option( 'users_can_register', 1 );
		update_option( 'tutor_option', array( 'enable_become_instructor_btn' => 'on' ) );

		$this->assertTrue( Instructor::can_register_as_instructor() );
	}
}
