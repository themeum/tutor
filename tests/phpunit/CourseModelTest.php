<?php namespace TutorTest;

use Tutor\Models\CourseModel;

/**
 * Input class test
 * Run test by: vendor/bin/phpunit --filter=InputClassTest
 *
 * @since 2.1.0
 */
class CourseModelTest extends \WP_UnitTestCase {

	/**
	 * Test cases added with different scenarios
	 *
	 * @return void
	 */
	public function test_has_course_content_access() {

		$lesson_id = self::factory()->post->create(
			array(
				'post_type'   => tutor()->lesson_post_type,
				'post_title'  => 'Test Lesson',
				'post_status' => 'publish',
			)
		);

		$course_id = self::factory()->post->create(
			array(
				'post_type'   => tutor()->course_post_type,
				'post_title'  => 'Test Course',
				'post_status' => 'publish',
			)
		);

		// When user is guest.
		$user_id = 0;

		$args = array(
			'current_post_type' => tutor()->lesson_post_type,
			'current_post_id'   => $lesson_id,
			'course_id'         => $course_id,
			'is_public'         => false,
			'is_enrolled'       => false,
		);

		$res = CourseModel::has_course_content_access( $args );

		$this->assertFalse( $res );

		// Test when user is logged-in.

		$user_id = self::factory()->user->create(
			array(
				'role' => 'subscriber',
			)
		);

		wp_set_current_user( $user_id );

		$res = CourseModel::has_course_content_access( $args );

		$this->assertFalse( $res );

		// Test when course is public & not enrolled.
		$args = array(
			'current_post_type' => tutor()->lesson_post_type,
			'current_post_id'   => $lesson_id,
			'course_id'         => $course_id,
			'is_public'         => true,
			'is_enrolled'       => false,
		);

		$res = CourseModel::has_course_content_access( $args );

		$this->assertTrue( $res );

		// Test when user is logged & accessing protected quiz without enrollment.

		$quiz_id = self::factory()->post->create(
			array(
				'post_type'   => tutor()->quiz_post_type,
				'post_title'  => 'Test quiz',
				'post_status' => 'publish',
			)
		);

		$args = array(
			'current_post_type' => tutor()->quiz_post_type,
			'current_post_id'   => $quiz_id,
			'course_id'         => $course_id,
			'is_public'         => false,
			'is_enrolled'       => false,
		);

		$res = CourseModel::has_course_content_access( $args );
		$this->assertFalse( $res );

		// When enrolled
		$args = array(
			'current_post_type' => tutor()->quiz_post_type,
			'current_post_id'   => $quiz_id,
			'course_id'         => $course_id,
			'is_public'         => false,
			'is_enrolled'       => true,
		);

		$res = CourseModel::has_course_content_access( $args );
		$this->assertTrue( $res );

		// Test google meeting.
		$meet_id = self::factory()->post->create(
			array(
				'post_type'   => tutor()->meet_post_type,
				'post_title'  => 'Test google meet',
				'post_status' => 'publish',
			)
		);

		$args = array(
			'current_post_type' => tutor()->meet_post_type,
			'current_post_id'   => $meet_id,
			'course_id'         => $course_id,
			'is_public'         => false,
			'is_enrolled'       => false,
		);

		$res = CourseModel::has_course_content_access( $args );
		$this->assertFalse( $res );

		// When enrollment is true.
		$args = array(
			'current_post_type' => tutor()->meet_post_type,
			'current_post_id'   => $meet_id,
			'course_id'         => $course_id,
			'is_public'         => false,
			'is_enrolled'       => true,
		);

		$res = CourseModel::has_course_content_access( $args );
		$this->assertTrue( $res );

	}
}
