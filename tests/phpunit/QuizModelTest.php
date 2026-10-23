<?php
/**
 * QuizModel Unit Test
 *
 * @package Tutor\Test
 * @since 4.2.0
 */

namespace TutorTest;

use Tutor\Models\QuizModel;

/**
 * Unit tests for quiz model functions.
 *
 * Run test: vendor/bin/phpunit --filter=QuizModelTest
 */
class QuizModelTest extends \WP_UnitTestCase {

	/**
	 * Test get_quiz_count_by_course function
	 * Create a course with one topic and one quiz, then verify the count
	 *
	 * @return void
	 */
	public function test_get_quiz_count_by_course() {

		$course_id = self::factory()->post->create(
			array(
				'post_type'   => tutor()->course_post_type,
				'post_status' => 'publish',
				'post_title'  => 'Test Course',
			)
		);

		$topic_id = self::factory()->post->create(
			array(
				'post_type'      => tutor()->topics_post_type,
				'post_status'    => 'publish',
				'post_parent'    => $course_id,
				'post_title'     => 'Topic',
				'comment_status' => 'closed',
			)
		);

		$quiz_id = self::factory()->post->create(
			array(
				'post_type'   => tutor()->quiz_post_type,
				'post_status' => 'publish',
				'post_parent' => $topic_id,
				'post_title'  => 'Quiz',
			)
		);

		$count = QuizModel::get_quiz_count_by_course( $course_id );

		$this->assertEquals( 1, $count );
	}
}
