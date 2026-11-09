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

	/**
	 * Test EnrollmentModel is_enrollment_paused.
	 *
	 * @return void
	 */
	public function test_enrollment_model_is_enrollment_paused() {
		$course_id = $this->factory->post->create(
			array(
				'post_type'   => 'courses',
				'post_status' => 'publish',
			)
		);

		$this->assertFalse( EnrollmentModel::is_enrollment_paused( $course_id ) );

		update_post_meta( $course_id, '_tutor_course_settings', array( 'pause_enrollment' => 'yes' ) );
		$this->assertTrue( EnrollmentModel::is_enrollment_paused( $course_id ) );

		update_post_meta( $course_id, '_tutor_course_settings', array( 'pause_enrollment' => 'no' ) );
		$this->assertFalse( EnrollmentModel::is_enrollment_paused( $course_id ) );
	}
}
