<?php
/**
 * CourseModel Test Suite.
 *
 * @package Tutor\Tests
 */

namespace TutorTest;

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

		// When enrolled.
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

	/**
	 * Helper: Create an instructor user.
	 *
	 * @param string $username Username prefix.
	 * @return int User ID.
	 */
	private function create_instructor_user( string $username = 'inst' ): int {
		$time = microtime( true );
		$id   = wp_insert_user(
			array(
				'user_login' => $username . '_' . $time,
				'user_pass'  => 'password_123',
				'user_email' => $username . '_' . $time . '@example.test',
				'role'       => 'tutor_instructor',
			)
		);
		update_user_meta( $id, '_tutor_instructor_status', 'approved' );
		return (int) $id;
	}

	/**
	 * Helper: Create an administrator user.
	 *
	 * @param string $username Username prefix.
	 * @return int User ID.
	 */
	private function create_admin_user( string $username = 'admin' ): int {
		$time = microtime( true );
		$id   = wp_insert_user(
			array(
				'user_login' => $username . '_' . $time,
				'user_pass'  => 'password_123',
				'user_email' => $username . '_' . $time . '@example.test',
				'role'       => 'administrator',
			)
		);
		return (int) $id;
	}

	/**
	 * Helper: Create a course post.
	 *
	 * @param int    $author_id   Author ID.
	 * @param string $post_status Post status.
	 * @param string $post_type   Post type.
	 * @return int Post ID.
	 */
	private function create_test_course( int $author_id, string $post_status = 'publish', string $post_type = '' ): int {
		if ( empty( $post_type ) ) {
			$post_type = tutor()->course_post_type;
		}
		return (int) wp_insert_post(
			array(
				'post_type'   => $post_type,
				'post_title'  => 'Test Course ' . microtime( true ),
				'post_status' => $post_status,
				'post_author' => $author_id,
			)
		);
	}

	/**
	 * Helper: Set Tutor option in database.
	 *
	 * @param string $key     Option key.
	 * @param bool   $enabled Boolean status.
	 * @return void
	 */
	private function set_tutor_option( string $key, bool $enabled ) {
		$options         = (array) maybe_unserialize( get_option( 'tutor_option' ) );
		$options[ $key ] = $enabled ? 'on' : 'off';
		update_option( 'tutor_option', $options );
	}

	/**
	 * Test: Admin can trash and permanently force-delete courses.
	 *
	 * @return void
	 */
	public function test_admin_can_trash_and_force_delete_course() {
		$admin_id      = $this->create_admin_user();
		$instructor_id = $this->create_instructor_user();
		$course_id     = $this->create_test_course( $instructor_id );

		// Admin can delete (soft trash).
		$this->assertTrue( CourseModel::can_user_delete( $course_id, false, $admin_id ) );
		// Admin can force delete.
		$this->assertTrue( CourseModel::can_user_delete( $course_id, true, $admin_id ) );

		// Soft trash the course.
		$trashed = CourseModel::delete_course( $course_id, false, $admin_id );
		$this->assertTrue( $trashed );
		$this->assertSame( 'trash', get_post_status( $course_id ) );

		// Force delete the course.
		$deleted = CourseModel::delete_course( $course_id, true, $admin_id );
		$this->assertTrue( $deleted );
		$this->assertNull( get_post( $course_id ) );

		wp_delete_user( $admin_id );
		wp_delete_user( $instructor_id );
	}

	/**
	 * Test: Main author can trash course when instructor_can_delete_course setting is enabled.
	 *
	 * @return void
	 */
	public function test_main_author_can_trash_when_setting_enabled() {
		$instructor_id = $this->create_instructor_user();
		$course_id     = $this->create_test_course( $instructor_id );

		$this->set_tutor_option( 'instructor_can_delete_course', true );

		$this->assertTrue( CourseModel::can_user_delete( $course_id, false, $instructor_id ) );

		$trashed = CourseModel::delete_course( $course_id, false, $instructor_id );
		$this->assertTrue( $trashed );
		$this->assertSame( 'trash', get_post_status( $course_id ) );

		wp_delete_post( $course_id, true );
		wp_delete_user( $instructor_id );
	}

	/**
	 * Test: Main author cannot trash course when instructor_can_delete_course setting is disabled.
	 *
	 * @return void
	 */
	public function test_main_author_cannot_trash_when_setting_disabled() {
		$instructor_id = $this->create_instructor_user();
		$course_id     = $this->create_test_course( $instructor_id );

		$this->set_tutor_option( 'instructor_can_delete_course', false );

		$this->assertFalse( CourseModel::can_user_delete( $course_id, false, $instructor_id ) );

		$trashed = CourseModel::delete_course( $course_id, false, $instructor_id );
		$this->assertFalse( $trashed );
		$this->assertSame( 'publish', get_post_status( $course_id ) );

		wp_delete_post( $course_id, true );
		wp_delete_user( $instructor_id );
	}

	/**
	 * Test: Main author cannot permanently force-delete course even if delete setting is enabled.
	 *
	 * @return void
	 */
	public function test_main_author_cannot_force_delete_course() {
		$instructor_id = $this->create_instructor_user();
		$course_id     = $this->create_test_course( $instructor_id );

		$this->set_tutor_option( 'instructor_can_delete_course', true );

		// Main author must never be allowed to force delete.
		$this->assertFalse( CourseModel::can_user_delete( $course_id, true, $instructor_id ) );

		$deleted = CourseModel::delete_course( $course_id, true, $instructor_id );
		$this->assertFalse( $deleted );
		$this->assertNotNull( get_post( $course_id ) );

		wp_delete_post( $course_id, true );
		wp_delete_user( $instructor_id );
	}

	/**
	 * Test: Co-author cannot trash or permanently delete course.
	 *
	 * @return void
	 */
	public function test_co_author_cannot_trash_or_delete_course() {
		$main_author_id = $this->create_instructor_user( 'main' );
		$co_author_id   = $this->create_instructor_user( 'co' );
		$course_id      = $this->create_test_course( $main_author_id );

		// Assign co-author to the course.
		add_user_meta( $co_author_id, '_tutor_instructor_course_id', $course_id );
		update_user_meta( $co_author_id, '_tutor_instructor_status', 'approved' );

		$this->set_tutor_option( 'instructor_can_delete_course', true );

		$this->assertFalse( CourseModel::can_user_delete( $course_id, false, $co_author_id ) );
		$this->assertFalse( CourseModel::can_user_delete( $course_id, true, $co_author_id ) );

		$result = CourseModel::delete_course( $course_id, false, $co_author_id );
		$this->assertFalse( $result );
		$this->assertSame( 'publish', get_post_status( $course_id ) );

		wp_delete_post( $course_id, true );
		wp_delete_user( $main_author_id );
		wp_delete_user( $co_author_id );
	}

	/**
	 * Test: Non-author cannot trash or delete course.
	 *
	 * @return void
	 */
	public function test_non_author_cannot_trash_or_delete_course() {
		$author_id = $this->create_instructor_user( 'author' );
		$other_id  = $this->create_instructor_user( 'other' );
		$course_id = $this->create_test_course( $author_id );

		$this->set_tutor_option( 'instructor_can_delete_course', true );

		$this->assertFalse( CourseModel::can_user_delete( $course_id, false, $other_id ) );
		$this->assertFalse( CourseModel::delete_course( $course_id, false, $other_id ) );
		$this->assertSame( 'publish', get_post_status( $course_id ) );

		wp_delete_post( $course_id, true );
		wp_delete_user( $author_id );
		wp_delete_user( $other_id );
	}

	/**
	 * Test: Admin can update course status to any valid status.
	 *
	 * @return void
	 */
	public function test_admin_can_update_course_status() {
		$admin_id      = $this->create_admin_user();
		$instructor_id = $this->create_instructor_user();
		$course_id     = $this->create_test_course( $instructor_id, 'pending' );

		$statuses = array( 'publish', 'draft', 'pending', 'private' );
		foreach ( $statuses as $status ) {
			$this->assertTrue( CourseModel::can_user_update_status( $course_id, $status, $admin_id ) );
			$updated = CourseModel::update_course_status( $course_id, $status, $admin_id );
			$this->assertTrue( $updated );
			$this->assertSame( $status, get_post_status( $course_id ) );
		}

		wp_delete_post( $course_id, true );
		wp_delete_user( $admin_id );
		wp_delete_user( $instructor_id );
	}

	/**
	 * Test: Main author publishing status check based on instructor_can_publish_course setting.
	 *
	 * @return void
	 */
	public function test_main_author_publish_status_with_setting_enabled_and_disabled() {
		$instructor_id = $this->create_instructor_user();
		$course_id     = $this->create_test_course( $instructor_id, 'draft' );

		// Setting enabled: publishing allowed.
		$this->set_tutor_option( 'instructor_can_publish_course', true );
		$this->assertTrue( CourseModel::can_user_update_status( $course_id, 'publish', $instructor_id ) );
		$updated = CourseModel::update_course_status( $course_id, 'publish', $instructor_id );
		$this->assertTrue( $updated );
		$this->assertSame( 'publish', get_post_status( $course_id ) );

		// Reset to draft.
		wp_update_post(
			array(
				'ID'          => $course_id,
				'post_status' => 'draft',
			)
		);

		// Setting disabled: publishing blocked.
		$this->set_tutor_option( 'instructor_can_publish_course', false );
		$this->assertFalse( CourseModel::can_user_update_status( $course_id, 'publish', $instructor_id ) );
		$updated = CourseModel::update_course_status( $course_id, 'publish', $instructor_id );
		$this->assertFalse( $updated );
		$this->assertSame( 'draft', get_post_status( $course_id ) );

		wp_delete_post( $course_id, true );
		wp_delete_user( $instructor_id );
	}

	/**
	 * Test: Main author can always update status to draft or pending even when publishing is disabled.
	 *
	 * @return void
	 */
	public function test_main_author_can_always_update_to_draft_or_pending() {
		$instructor_id = $this->create_instructor_user();
		$course_id     = $this->create_test_course( $instructor_id, 'publish' );

		$this->set_tutor_option( 'instructor_can_publish_course', false );

		// Updating to pending.
		$this->assertTrue( CourseModel::can_user_update_status( $course_id, 'pending', $instructor_id ) );
		$updated = CourseModel::update_course_status( $course_id, 'pending', $instructor_id );
		$this->assertTrue( $updated );
		$this->assertSame( 'pending', get_post_status( $course_id ) );

		// Updating to draft.
		$this->assertTrue( CourseModel::can_user_update_status( $course_id, 'draft', $instructor_id ) );
		$updated = CourseModel::update_course_status( $course_id, 'draft', $instructor_id );
		$this->assertTrue( $updated );
		$this->assertSame( 'draft', get_post_status( $course_id ) );

		wp_delete_post( $course_id, true );
		wp_delete_user( $instructor_id );
	}

	/**
	 * Test: Main author updating status to trash respects instructor_can_delete_course setting.
	 *
	 * @return void
	 */
	public function test_main_author_update_status_to_trash_respects_delete_setting() {
		$instructor_id = $this->create_instructor_user();
		$course_id     = $this->create_test_course( $instructor_id, 'publish' );

		// When delete is disabled, status change to trash is blocked.
		$this->set_tutor_option( 'instructor_can_delete_course', false );
		$this->assertFalse( CourseModel::can_user_update_status( $course_id, 'trash', $instructor_id ) );
		$updated = CourseModel::update_course_status( $course_id, 'trash', $instructor_id );
		$this->assertFalse( $updated );
		$this->assertSame( 'publish', get_post_status( $course_id ) );

		// When delete is enabled, status change to trash is allowed.
		$this->set_tutor_option( 'instructor_can_delete_course', true );
		$this->assertTrue( CourseModel::can_user_update_status( $course_id, 'trash', $instructor_id ) );
		$updated = CourseModel::update_course_status( $course_id, 'trash', $instructor_id );
		$this->assertTrue( $updated );
		$this->assertSame( 'trash', get_post_status( $course_id ) );

		wp_delete_post( $course_id, true );
		wp_delete_user( $instructor_id );
	}

	/**
	 * Test: Co-author cannot update course status to any status.
	 *
	 * @return void
	 */
	public function test_co_author_cannot_update_course_status() {
		$main_author_id = $this->create_instructor_user( 'main' );
		$co_author_id   = $this->create_instructor_user( 'co' );
		$course_id      = $this->create_test_course( $main_author_id, 'draft' );

		// Assign co-author.
		add_user_meta( $co_author_id, '_tutor_instructor_course_id', $course_id );
		update_user_meta( $co_author_id, '_tutor_instructor_status', 'approved' );

		$this->set_tutor_option( 'instructor_can_publish_course', true );
		$this->set_tutor_option( 'instructor_can_delete_course', true );

		$statuses = array( 'publish', 'draft', 'pending', 'private', 'trash' );
		foreach ( $statuses as $status ) {
			$this->assertFalse( CourseModel::can_user_update_status( $course_id, $status, $co_author_id ) );
			$updated = CourseModel::update_course_status( $course_id, $status, $co_author_id );
			$this->assertFalse( $updated );
		}

		$this->assertSame( 'draft', get_post_status( $course_id ) );

		wp_delete_post( $course_id, true );
		wp_delete_user( $main_author_id );
		wp_delete_user( $co_author_id );
	}

	/**
	 * Test: Bulk delete course validates permissions for each course.
	 *
	 * @return void
	 */
	public function test_bulk_delete_course_permissions() {
		$inst1_id = $this->create_instructor_user( 'inst1' );
		$inst2_id = $this->create_instructor_user( 'inst2' );
		$course_a = $this->create_test_course( $inst1_id );
		$course_b = $this->create_test_course( $inst2_id );

		$this->set_tutor_option( 'instructor_can_delete_course', true );

		// Inst1 attempts to bulk delete both courses. Only course A should be trashed.
		$result = CourseModel::bulk_delete_course( array( $course_a, $course_b ), false, $inst1_id );
		$this->assertTrue( $result );
		$this->assertSame( 'trash', get_post_status( $course_a ) );
		$this->assertSame( 'publish', get_post_status( $course_b ) );

		// Admin can bulk delete any remaining course.
		$admin_id = $this->create_admin_user();
		$result   = CourseModel::bulk_delete_course( array( $course_b ), false, $admin_id );
		$this->assertTrue( $result );
		$this->assertSame( 'trash', get_post_status( $course_b ) );

		wp_delete_post( $course_a, true );
		wp_delete_post( $course_b, true );
		wp_delete_user( $inst1_id );
		wp_delete_user( $inst2_id );
		wp_delete_user( $admin_id );
	}

	/**
	 * Test: Bulk update course status validates permissions per course.
	 *
	 * @return void
	 */
	public function test_bulk_update_course_status_permissions() {
		$inst1_id = $this->create_instructor_user( 'inst1' );
		$inst2_id = $this->create_instructor_user( 'inst2' );
		$course_a = $this->create_test_course( $inst1_id, 'draft' );
		$course_b = $this->create_test_course( $inst2_id, 'draft' );

		$this->set_tutor_option( 'instructor_can_publish_course', true );

		// Inst1 attempts bulk update to publish. Only course A should update.
		$result = CourseModel::bulk_update_course_status( array( $course_a, $course_b ), 'publish', $inst1_id );
		$this->assertTrue( $result );
		$this->assertSame( 'publish', get_post_status( $course_a ) );
		$this->assertSame( 'draft', get_post_status( $course_b ) );

		wp_delete_post( $course_a, true );
		wp_delete_post( $course_b, true );
		wp_delete_user( $inst1_id );
		wp_delete_user( $inst2_id );
	}

	/**
	 * Test: Course bundle post type support.
	 *
	 * @return void
	 */
	public function test_course_bundle_post_type_support() {
		// Enable course-bundle support via filter.
		$filter_bundle = function ( $is_course, $post_type ) {
			return $is_course || 'course-bundle' === $post_type;
		};
		add_filter( 'tutor_check_course_post_type', $filter_bundle, 10, 2 );

		$inst_id   = $this->create_instructor_user();
		$bundle_id = $this->create_test_course( $inst_id, 'draft', 'course-bundle' );

		$this->assertTrue( CourseModel::get_post_types( $bundle_id ) );

		$this->set_tutor_option( 'instructor_can_publish_course', true );
		$this->set_tutor_option( 'instructor_can_delete_course', true );

		// Author can publish bundle when allowed.
		$this->assertTrue( CourseModel::can_user_update_status( $bundle_id, 'publish', $inst_id ) );
		$this->assertTrue( CourseModel::update_course_status( $bundle_id, 'publish', $inst_id ) );
		$this->assertSame( 'publish', get_post_status( $bundle_id ) );

		// Author can trash bundle when allowed.
		$this->assertTrue( CourseModel::can_user_delete( $bundle_id, false, $inst_id ) );
		$this->assertTrue( CourseModel::delete_course( $bundle_id, false, $inst_id ) );
		$this->assertSame( 'trash', get_post_status( $bundle_id ) );

		remove_filter( 'tutor_check_course_post_type', $filter_bundle, 10 );
		wp_delete_post( $bundle_id, true );
		wp_delete_user( $inst_id );
	}

	/**
	 * Test: Delete course dispatches lifecycle hooks.
	 *
	 * @return void
	 */
	public function test_delete_course_dispatches_lifecycle_hooks() {
		$admin_id  = $this->create_admin_user();
		$course_id = $this->create_test_course( $admin_id );

		$before_fired = false;
		$after_fired  = false;

		$before_cb = function ( $id, $force ) use ( &$before_fired, $course_id ) {
			if ( $id === $course_id && ! $force ) {
				$before_fired = true;
			}
		};

		$after_cb = function ( $id, $force ) use ( &$after_fired, $course_id ) {
			if ( $id === $course_id && ! $force ) {
				$after_fired = true;
			}
		};

		add_action( 'tutor_before_delete_course', $before_cb, 10, 2 );
		add_action( 'tutor_after_delete_course', $after_cb, 10, 2 );

		CourseModel::delete_course( $course_id, false, $admin_id );

		$this->assertTrue( $before_fired );
		$this->assertTrue( $after_fired );

		remove_action( 'tutor_before_delete_course', $before_cb, 10 );
		remove_action( 'tutor_after_delete_course', $after_cb, 10 );
		wp_delete_post( $course_id, true );
		wp_delete_user( $admin_id );
	}
}
