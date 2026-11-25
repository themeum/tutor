<?php
/**
 * Enrollment Security Tests
 *
 * @package TutorTest
 */

namespace TutorTest;

use WP_UnitTestCase;
use Tutor\Models\EnrollmentModel;
use TUTOR\Tutor;

/**
 * Class EnrollmentSecurityTest
 */
class EnrollmentSecurityTest extends WP_UnitTestCase {

	/**
	 * Admin user ID
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Instructor user ID
	 *
	 * @var int
	 */
	protected $instructor_id;

	/**
	 * Student user ID
	 *
	 * @var int
	 */
	protected $student_id;

	/**
	 * Course ID
	 *
	 * @var int
	 */
	protected $course_id;

	/**
	 * Reset in-memory TutorCache data
	 *
	 * @return void
	 */
	private function reset_tutor_cache() {
		$ref = new \ReflectionProperty( \Tutor\Cache\TutorCache::class, 'data' );
		$ref->setAccessible( true );
		$ref->setValue( \Tutor\Cache\TutorCache::getInstance(), array() );
	}

	/**
	 * Set up test fixtures
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Ensure roles and capabilities are provisioned.
		Tutor::manage_tutor_roles_and_permissions();

		// Reset runtime cache.
		$this->reset_tutor_cache();

		// Create users.
		$this->admin_id      = $this->factory()->user->create( array( 'role' => 'administrator' ) );
		$this->instructor_id = $this->factory()->user->create( array( 'role' => tutor()->instructor_role ) );
		$this->student_id    = $this->factory()->user->create( array( 'role' => 'subscriber' ) );

		// Create course.
		$this->course_id = $this->factory()->post->create(
			array(
				'post_type'   => tutor()->course_post_type,
				'post_title'  => 'Security Test Course',
				'post_author' => $this->instructor_id,
				'post_status' => 'publish',
			)
		);
	}

	/**
	 * Tear down test fixtures
	 *
	 * @return void
	 */
	public function tearDown(): void {
		wp_set_current_user( 0 );
		$this->reset_tutor_cache();
		parent::tearDown();
	}

	/**
	 * Test tutor_enrolled post type registration and capabilities
	 *
	 * @return void
	 */
	public function test_tutor_enrolled_post_type_capabilities_configuration() {
		$pto = get_post_type_object( 'tutor_enrolled' );

		$this->assertNotNull( $pto, 'tutor_enrolled post type object must exist' );
		$this->assertTrue( $pto->map_meta_cap, 'map_meta_cap must be enabled' );
		$this->assertEquals( 'tutor_enrolled', $pto->capability_type );

		// Verify meta capabilities.
		$this->assertEquals( 'edit_tutor_enrolled', $pto->cap->edit_post );
		$this->assertEquals( 'delete_tutor_enrolled', $pto->cap->delete_post );
		$this->assertEquals( 'read_tutor_enrolled', $pto->cap->read_post );

		// Verify that primitive capabilities map to manage_tutor.
		$this->assertEquals( 'manage_tutor', $pto->cap->create_posts );
		$this->assertEquals( 'manage_tutor', $pto->cap->edit_posts );
		$this->assertEquals( 'manage_tutor', $pto->cap->edit_others_posts );
		$this->assertEquals( 'manage_tutor', $pto->cap->publish_posts );
		$this->assertEquals( 'manage_tutor', $pto->cap->delete_posts );
		$this->assertEquals( 'manage_tutor', $pto->cap->delete_others_posts );
		$this->assertEquals( 'manage_tutor', $pto->cap->read_private_posts );
	}

	/**
	 * Test instructor cannot create or edit tutor_enrolled posts via core capabilities (e.g. XML-RPC)
	 *
	 * @return void
	 */
	public function test_instructor_cannot_create_or_modify_enrollment_records() {
		wp_set_current_user( $this->instructor_id );

		$pto = get_post_type_object( 'tutor_enrolled' );

		// XML-RPC wp.newPost checks create_posts capability.
		$this->assertFalse(
			current_user_can( $pto->cap->create_posts ),
			'Instructor must not have create_posts capability for tutor_enrolled'
		);

		// Core editing capabilities.
		$this->assertFalse(
			current_user_can( $pto->cap->edit_posts ),
			'Instructor must not have edit_posts capability for tutor_enrolled'
		);
		$this->assertFalse(
			current_user_can( $pto->cap->publish_posts ),
			'Instructor must not have publish_posts capability for tutor_enrolled'
		);

		// Test existing enrollment post modification.
		$enrollment_id = $this->factory()->post->create(
			array(
				'post_type'   => 'tutor_enrolled',
				'post_author' => $this->instructor_id,
				'post_parent' => $this->course_id,
				'post_status' => 'draft',
			)
		);

		$this->assertFalse(
			current_user_can( 'edit_post', $enrollment_id ),
			'Instructor must not be allowed to edit tutor_enrolled post'
		);
		$this->assertFalse(
			current_user_can( 'delete_post', $enrollment_id ),
			'Instructor must not be allowed to delete tutor_enrolled post'
		);
	}

	/**
	 * Test administrator has full capabilities on tutor_enrolled
	 *
	 * @return void
	 */
	public function test_administrator_has_enrollment_capabilities() {
		wp_set_current_user( $this->admin_id );

		$pto = get_post_type_object( 'tutor_enrolled' );

		$this->assertTrue(
			current_user_can( $pto->cap->create_posts ),
			'Administrator must have create_posts capability for tutor_enrolled'
		);
		$this->assertTrue(
			current_user_can( $pto->cap->edit_posts ),
			'Administrator must have edit_posts capability for tutor_enrolled'
		);

		$enrollment_id = $this->factory()->post->create(
			array(
				'post_type'   => 'tutor_enrolled',
				'post_author' => $this->student_id,
				'post_parent' => $this->course_id,
				'post_status' => 'completed',
			)
		);

		$this->assertTrue(
			current_user_can( 'edit_post', $enrollment_id ),
			'Administrator must be allowed to edit tutor_enrolled post'
		);
		$this->assertTrue(
			current_user_can( 'delete_post', $enrollment_id ),
			'Administrator must be allowed to delete tutor_enrolled post'
		);
	}

	/**
	 * Test draft enrollment record is ignored by EnrollmentModel::is_enrolled
	 *
	 * @return void
	 */
	public function test_draft_enrollment_ignored_by_is_enrolled() {
		// 1. Create a draft enrollment record (simulating unauthorized draft creation).
		$student_draft = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->factory()->post->create(
			array(
				'post_type'   => 'tutor_enrolled',
				'post_author' => $student_draft,
				'post_parent' => $this->course_id,
				'post_status' => 'draft',
			)
		);

		// Check when is_complete is true (default).
		$enrolled_completed = EnrollmentModel::is_enrolled( $this->course_id, $student_draft, true );
		$this->assertEmpty( $enrolled_completed, 'Draft enrollment must not return true for completed enrollment check' );

		// Check when is_complete is false (any valid enrollment record check).
		$enrolled_any = EnrollmentModel::is_enrolled( $this->course_id, $student_draft, false );
		$this->assertEmpty( $enrolled_any, 'Draft enrollment must not return true even when is_complete is false' );

		// 2. Test pending enrollment record.
		$student_pending = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->factory()->post->create(
			array(
				'post_type'   => 'tutor_enrolled',
				'post_author' => $student_pending,
				'post_parent' => $this->course_id,
				'post_status' => EnrollmentModel::STATUS_PENDING,
			)
		);

		$this->assertEmpty( EnrollmentModel::is_enrolled( $this->course_id, $student_pending, true ) );
		$this->assertNotEmpty( EnrollmentModel::is_enrolled( $this->course_id, $student_pending, false ) );

		// 3. Test completed enrollment record.
		$student_completed = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->factory()->post->create(
			array(
				'post_type'   => 'tutor_enrolled',
				'post_author' => $student_completed,
				'post_parent' => $this->course_id,
				'post_status' => EnrollmentModel::STATUS_COMPLETED,
			)
		);

		$this->assertNotEmpty( EnrollmentModel::is_enrolled( $this->course_id, $student_completed, true ) );
		$this->assertNotEmpty( EnrollmentModel::is_enrolled( $this->course_id, $student_completed, false ) );
	}

	/**
	 * Test legitimate enrollment workflow continues to function
	 *
	 * @return void
	 */
	public function test_legitimate_do_enroll_workflow() {
		$student     = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$enrolled_id = EnrollmentModel::do_enroll( $this->course_id, 0, $student );

		$this->assertGreaterThan( 0, $enrolled_id );
		$enrollment_post = get_post( $enrolled_id );
		$this->assertEquals( 'tutor_enrolled', $enrollment_post->post_type );
		$this->assertEquals( EnrollmentModel::STATUS_COMPLETED, $enrollment_post->post_status );
		$this->assertEquals( $this->course_id, (int) $enrollment_post->post_parent );
		$this->assertEquals( $student, (int) $enrollment_post->post_author );

		$this->reset_tutor_cache();
		$this->assertNotEmpty( EnrollmentModel::is_enrolled( $this->course_id, $student ) );
	}
}
