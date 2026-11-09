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
}
