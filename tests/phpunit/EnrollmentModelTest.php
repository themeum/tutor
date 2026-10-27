<?php
/**
 * EnrollmentModel Class Unit Test
 *
 * @package Tutor\Test
 * @since 4.2.0
 */

namespace TutorTest;

use Tutor\Models\EnrollmentModel;

/**
 * Unit tests for EnrollmentModel.
 *
 * Run test: vendor/bin/phpunit --filter=EnrollmentModelTest
 */
class EnrollmentModelTest extends \WP_UnitTestCase {
	/**
	 * Test delete enrollment record.
	 *
	 * @return void
	 */
	public function test_delete_enrollment_record() {
		$user_id     = 1;
		$course_id   = 1;
		$enroll_id_1 = self::factory()->post->create(
			array(
				'post_type'   => EnrollmentModel::POST_TYPE,
				'post_status' => EnrollmentModel::STATUS_COMPLETED,
				'post_author' => $user_id,
				'post_parent' => $course_id,
			)
		);
		$enroll_id_2 = self::factory()->post->create(
			array(
				'post_type'   => EnrollmentModel::POST_TYPE,
				'post_status' => EnrollmentModel::STATUS_COMPLETED,
				'post_author' => $user_id,
				'post_parent' => $course_id,
			)
		);

		$enroll_id_3 = self::factory()->post->create(
			array(
				'post_type'   => EnrollmentModel::POST_TYPE,
				'post_status' => EnrollmentModel::STATUS_COMPLETED,
				'post_author' => $user_id,
				'post_parent' => $course_id,
			)
		);

		$total_enrollments = EnrollmentModel::get_enrolled_data( $user_id, $course_id );

		$this->assertEquals( 3, count( $total_enrollments ) );

		$enrollment = EnrollmentModel::delete_enrollment_record( $user_id, $course_id );
		$this->assertTrue( $enrollment );

		$total_enrollments = EnrollmentModel::get_enrolled_data( $user_id, $course_id );
		$this->assertEquals( 0, count( $total_enrollments ) );
	}

	/**
	 * Test get enrolled data.
	 *
	 * @return void
	 */
	public function test_get_enrolled_data() {
		$user_id   = 1;
		$course_id = 1;

		self::factory()->post->create(
			array(
				'post_type'   => EnrollmentModel::POST_TYPE,
				'post_status' => EnrollmentModel::STATUS_COMPLETED,
				'post_author' => $user_id,
				'post_parent' => $course_id,
			)
		);

		$enrollments = EnrollmentModel::get_enrolled_data( $user_id, $course_id );

		$this->assertEquals( 1, count( $enrollments ) );
	}

	/**
	 * Test update enroll data.
	 *
	 * @return void
	 */
	public function test_update_enroll_data() {
		$user_id   = 1;
		$course_id = 1;

		$enroll_id_1 = self::factory()->post->create(
			array(
				'post_type'   => EnrollmentModel::POST_TYPE,
				'post_status' => EnrollmentModel::STATUS_COMPLETED,
				'post_author' => $user_id,
				'post_parent' => $course_id,
			)
		);

		$update = EnrollmentModel::update_enrollments( EnrollmentModel::STATUS_CANCEL, array( $enroll_id_1 ), false );

		$this->assertTrue( $update );

		$updated_enrollment = EnrollmentModel::get_enrolled_data( $user_id, $course_id, EnrollmentModel::STATUS_CANCEL );
		$this->assertEquals( EnrollmentModel::STATUS_CANCEL, $updated_enrollment[0]->post_status );

		$enroll_id_2 = self::factory()->post->create(
			array(
				'post_type'   => EnrollmentModel::POST_TYPE,
				'post_status' => EnrollmentModel::STATUS_COMPLETED,
				'post_author' => $user_id,
				'post_parent' => $course_id,
			)
		);

		$update = EnrollmentModel::update_enrollments( EnrollmentModel::STATUS_PENDING, array( $enroll_id_1, $enroll_id_2 ), false );

		$this->assertTrue( $update );

		$updated_enrollment = EnrollmentModel::get_enrolled_data( $user_id, $course_id, EnrollmentModel::STATUS_PENDING );
		$this->assertEquals( EnrollmentModel::STATUS_PENDING, $updated_enrollment[0]->post_status );
		$this->assertEquals( EnrollmentModel::STATUS_PENDING, $updated_enrollment[1]->post_status );
	}
}
