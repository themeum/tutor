<?php
/**
 * Test Course class methods.
 * Run test by: vendor/bin/phpunit --filter=CourseTest
 *
 * @package Tutor\Test
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.8
 */

namespace TutorTest;

use Exception;
use InvalidArgumentException;
use ReflectionMethod;
use TUTOR\Course;

/**
 * CourseTest class
 */
class CourseTest extends \WP_UnitTestCase {

	/**
	 * Course ID fixture
	 *
	 * @var int
	 */
	private $course_id;

	/**
	 * Topic ID 1 fixture
	 *
	 * @var int
	 */
	private $topic_id_1;

	/**
	 * Topic ID 2 fixture
	 *
	 * @var int
	 */
	private $topic_id_2;

	/**
	 * Lesson ID 1 fixture
	 *
	 * @var int
	 */
	private $lesson_id_1;

	/**
	 * Quiz ID 1 fixture
	 *
	 * @var int
	 */
	private $quiz_id_1;

	/**
	 * Lesson ID 2 fixture
	 *
	 * @var int
	 */
	private $lesson_id_2;

	/**
	 * Other course ID fixture (for cross-course / isolation testing)
	 *
	 * @var int
	 */
	private $other_course_id;

	/**
	 * Other topic ID fixture
	 *
	 * @var int
	 */
	private $other_topic_id;

	/**
	 * Other lesson ID fixture
	 *
	 * @var int
	 */
	private $other_lesson_id;

	/**
	 * Set up test fixtures before each test
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		// Create primary course.
		$this->course_id = self::factory()->post->create(
			array(
				'post_type'   => tutor()->course_post_type,
				'post_title'  => 'Test Course',
				'post_status' => 'publish',
			)
		);

		// Create topics under primary course.
		$this->topic_id_1 = self::factory()->post->create(
			array(
				'post_type'   => tutor()->topics_post_type,
				'post_parent' => $this->course_id,
				'post_title'  => 'Test Topic 1',
				'post_status' => 'publish',
			)
		);

		$this->topic_id_2 = self::factory()->post->create(
			array(
				'post_type'   => tutor()->topics_post_type,
				'post_parent' => $this->course_id,
				'post_title'  => 'Test Topic 2',
				'post_status' => 'publish',
			)
		);

		// Create lesson and quiz under Topic 1.
		$this->lesson_id_1 = self::factory()->post->create(
			array(
				'post_type'   => tutor()->lesson_post_type,
				'post_parent' => $this->topic_id_1,
				'post_title'  => 'Test Lesson 1',
				'post_status' => 'publish',
			)
		);

		$this->quiz_id_1 = self::factory()->post->create(
			array(
				'post_type'   => tutor()->quiz_post_type,
				'post_parent' => $this->topic_id_1,
				'post_title'  => 'Test Quiz 1',
				'post_status' => 'publish',
			)
		);

		// Create lesson under Topic 2.
		$this->lesson_id_2 = self::factory()->post->create(
			array(
				'post_type'   => tutor()->lesson_post_type,
				'post_parent' => $this->topic_id_2,
				'post_title'  => 'Test Lesson 2',
				'post_status' => 'publish',
			)
		);

		// Create fixtures for an isolated second course.
		$this->other_course_id = self::factory()->post->create(
			array(
				'post_type'   => tutor()->course_post_type,
				'post_title'  => 'Other Course',
				'post_status' => 'publish',
			)
		);

		$this->other_topic_id = self::factory()->post->create(
			array(
				'post_type'   => tutor()->topics_post_type,
				'post_parent' => $this->other_course_id,
				'post_title'  => 'Other Topic',
				'post_status' => 'publish',
			)
		);

		$this->other_lesson_id = self::factory()->post->create(
			array(
				'post_type'   => tutor()->lesson_post_type,
				'post_parent' => $this->other_topic_id,
				'post_title'  => 'Other Lesson',
				'post_status' => 'publish',
			)
		);
	}

	/**
	 * Helper method to invoke the private validate_course_content_order method via Reflection.
	 *
	 * @param int   $course_id      Course ID.
	 * @param array $sorting_order  Sorting order structure.
	 * @param array $content_parent Optional content parent array.
	 *
	 * @return void
	 */
	private function validate_course_content_order( int $course_id, array $sorting_order, array $content_parent = array() ): void {
		$course = new Course( false );
		$method = new ReflectionMethod( Course::class, 'validate_course_content_order' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}
		$method->invoke( $course, $course_id, $sorting_order, $content_parent );
	}

	/*
	|--------------------------------------------------------------------------
	| 1. Parameter Validation (course_id)
	|--------------------------------------------------------------------------
	*/

	/**
	 * Test that an InvalidArgumentException is thrown when course_id is 0.
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_course_id_is_zero(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid course or topic ID' );

		$sorting_order = array(
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array( $this->lesson_id_1 ),
			),
		);

		$this->validate_course_content_order( 0, $sorting_order );
	}

	/*
	|--------------------------------------------------------------------------
	| 2. Provided Topics Validation
	|--------------------------------------------------------------------------
	*/

	/**
	 * Test that an InvalidArgumentException is thrown when no topics are provided.
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_no_topics_provided(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'No topics provided' );

		$this->validate_course_content_order( $this->course_id, array(), array() );
	}

	/**
	 * Test that an InvalidArgumentException is thrown when sorting_order has only 0 or empty topic IDs.
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_sorting_order_has_only_empty_topic_ids(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'No topics provided' );

		$sorting_order = array(
			array(
				'topic_id'   => 0,
				'lesson_ids' => array(),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
	}

	/*
	|--------------------------------------------------------------------------
	| 3. Topic Ownership / Existence Validation ($topic_id_diff)
	|--------------------------------------------------------------------------
	*/

	/**
	 * Test that an Exception is thrown when a provided topic ID does not exist in the database.
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_topic_does_not_exist(): void {
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Invalid topic id provided' );

		$non_existent_topic_id = 99999999;
		$sorting_order         = array(
			array(
				'topic_id'   => $non_existent_topic_id,
				'lesson_ids' => array(),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
	}

	/**
	 * Test that an Exception is thrown when a topic belongs to a different course (IDOR prevention).
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_topic_belongs_to_different_course(): void {
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Invalid topic id provided' );

		$sorting_order = array(
			array(
				'topic_id'   => $this->other_topic_id,
				'lesson_ids' => array(),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
	}

	/**
	 * Test that an Exception is thrown when a topic ID in content_parent belongs to a different course.
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_content_parent_topic_belongs_to_different_course(): void {
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Invalid topic id provided' );

		$sorting_order  = array(
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array( $this->lesson_id_1 ),
			),
		);
		$content_parent = array(
			array(
				'parent_topic_id' => $this->other_topic_id,
				'content_id'      => $this->lesson_id_1,
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order, $content_parent );
	}

	/**
	 * Test that an Exception is thrown when a topic is not published (e.g. draft).
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_topic_is_not_published(): void {
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Invalid topic id provided' );

		$draft_topic_id = self::factory()->post->create(
			array(
				'post_type'   => tutor()->topics_post_type,
				'post_parent' => $this->course_id,
				'post_title'  => 'Draft Topic',
				'post_status' => 'draft',
			)
		);

		$sorting_order = array(
			array(
				'topic_id'   => $draft_topic_id,
				'lesson_ids' => array(),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
	}

	/**
	 * Test that an Exception is thrown when one valid and one invalid topic are provided.
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_one_of_multiple_topics_is_invalid(): void {
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Invalid topic id provided' );

		$sorting_order = array(
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array(),
			),
			array(
				'topic_id'   => $this->other_topic_id,
				'lesson_ids' => array(),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
	}

	/*
	|--------------------------------------------------------------------------
	| 4. Content ID Validation ($content_id_diff)
	|--------------------------------------------------------------------------
	*/

	/**
	 * Test that an Exception is thrown when a content ID does not exist in the database.
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_content_id_does_not_exist(): void {
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Invalid content id provided' );

		$non_existent_lesson_id = 99999999;
		$sorting_order          = array(
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array( $non_existent_lesson_id ),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
	}

	/**
	 * Test that an Exception is thrown when a content ID belongs to another course's topic.
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_content_belongs_to_different_course(): void {
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Invalid content id provided' );

		$sorting_order = array(
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array( $this->other_lesson_id ),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
	}

	/**
	 * Test that an Exception is thrown when content_parent content_id does not belong to the course.
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_content_parent_content_belongs_to_different_course(): void {
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Invalid content id provided' );

		$sorting_order  = array(
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array( $this->lesson_id_1 ),
			),
		);
		$content_parent = array(
			array(
				'parent_topic_id' => $this->topic_id_1,
				'content_id'      => $this->other_lesson_id,
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order, $content_parent );
	}

	/**
	 * Test that an Exception is thrown when a content item is not published (e.g. draft).
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_content_is_not_published(): void {
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Invalid content id provided' );

		$draft_lesson_id = self::factory()->post->create(
			array(
				'post_type'   => tutor()->lesson_post_type,
				'post_parent' => $this->topic_id_1,
				'post_title'  => 'Draft Lesson',
				'post_status' => 'draft',
			)
		);

		$sorting_order = array(
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array( $draft_lesson_id ),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
	}

	/**
	 * Test that an Exception is thrown when content ID is of an unsupported post type (e.g. standard page).
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_content_has_unsupported_post_type(): void {
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Invalid content id provided' );

		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_parent' => $this->topic_id_1,
				'post_title'  => 'Standard WP Page',
				'post_status' => 'publish',
			)
		);

		$sorting_order = array(
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array( $page_id ),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
	}

	/**
	 * Test that an Exception is thrown when one of multiple content IDs is invalid.
	 *
	 * @return void
	 */
	public function test_validate_throws_exception_when_one_of_multiple_content_ids_is_invalid(): void {
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Invalid content id provided' );

		$sorting_order = array(
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array( $this->lesson_id_1, $this->other_lesson_id ),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
	}

	/*
	|--------------------------------------------------------------------------
	| 5. Happy Path / Success Scenarios
	|--------------------------------------------------------------------------
	*/

	/**
	 * Test that validation succeeds with a single valid topic and a single valid lesson.
	 *
	 * @return void
	 */
	public function test_validate_succeeds_with_single_topic_and_lesson(): void {
		$sorting_order = array(
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array( $this->lesson_id_1 ),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
		$this->addToAssertionCount( 1 );
	}

	/**
	 * Test that validation succeeds with multiple topics and mixed content types (lessons and quizzes).
	 *
	 * @return void
	 */
	public function test_validate_succeeds_with_multiple_topics_and_mixed_contents(): void {
		$sorting_order = array(
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array( $this->lesson_id_1, $this->quiz_id_1 ),
			),
			array(
				'topic_id'   => $this->topic_id_2,
				'lesson_ids' => array( $this->lesson_id_2 ),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
		$this->addToAssertionCount( 1 );
	}

	/**
	 * Test that validation succeeds when a topic has an empty lesson_ids array.
	 *
	 * @return void
	 */
	public function test_validate_succeeds_with_topic_having_empty_lesson_ids(): void {
		$sorting_order = array(
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array(),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
		$this->addToAssertionCount( 1 );
	}

	/**
	 * Test that validation succeeds when a topic array omits the lesson_ids key altogether.
	 *
	 * @return void
	 */
	public function test_validate_succeeds_with_topic_omitting_lesson_ids_key(): void {
		$sorting_order = array(
			array(
				'topic_id' => $this->topic_id_1,
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
		$this->addToAssertionCount( 1 );
	}

	/**
	 * Test that validation succeeds when valid content_parent is provided.
	 *
	 * @return void
	 */
	public function test_validate_succeeds_with_valid_content_parent(): void {
		$sorting_order  = array(
			array(
				'topic_id'   => $this->topic_id_2,
				'lesson_ids' => array( $this->lesson_id_2 ),
			),
		);
		$content_parent = array(
			array(
				'parent_topic_id' => $this->topic_id_1,
				'content_id'      => $this->lesson_id_1,
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order, $content_parent );
		$this->addToAssertionCount( 1 );
	}

	/**
	 * Test that duplicate topic IDs and duplicate content IDs in the input are deduplicated without error.
	 *
	 * @return void
	 */
	public function test_validate_succeeds_with_duplicate_ids_in_payload(): void {
		$sorting_order = array(
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array( $this->lesson_id_1, $this->lesson_id_1 ),
			),
			array(
				'topic_id'   => $this->topic_id_1,
				'lesson_ids' => array( $this->lesson_id_1 ),
			),
		);

		$this->validate_course_content_order( $this->course_id, $sorting_order );
		$this->addToAssertionCount( 1 );
	}

	/**
	 * Test that custom content post types registered via tutor_course_contents_post_types filter are accepted.
	 *
	 * @return void
	 */
	public function test_validate_succeeds_with_custom_content_post_type_via_filter(): void {
		$custom_post_type = 'tutor_assignments';

		// Create dummy content of custom post type under Topic 1.
		$custom_content_id = self::factory()->post->create(
			array(
				'post_type'   => $custom_post_type,
				'post_parent' => $this->topic_id_1,
				'post_title'  => 'Test Assignment',
				'post_status' => 'publish',
			)
		);

		// Hook into tutor_course_contents_post_types filter.
		$filter_callback = function ( array $types ) use ( $custom_post_type ): array {
			$types[] = $custom_post_type;
			return $types;
		};
		add_filter( 'tutor_course_contents_post_types', $filter_callback );

		try {
			$sorting_order = array(
				array(
					'topic_id'   => $this->topic_id_1,
					'lesson_ids' => array( $custom_content_id ),
				),
			);

			$this->validate_course_content_order( $this->course_id, $sorting_order );
			$this->addToAssertionCount( 1 );
		} finally {
			remove_filter( 'tutor_course_contents_post_types', $filter_callback );
		}
	}
}
