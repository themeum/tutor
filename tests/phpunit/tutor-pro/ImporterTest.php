<?php
/**
 * Importer Class Unit Test
 *
 * @package Tutor\Test
 *
 * @since 3.6.0
 */

namespace TutorTest;

/**
 * Importer Test Class
 */
class ImporterTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Importer instance
	 *
	 * @since 3.6.0
	 *
	 * @var object
	 */
	private $importer;

	const TUTOR_TEST_PRO_DIRECTORY = '';

	/**
	 * Setup function that runs before the test case runs.
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	protected function setUp(): void {
		global $current_user;
		parent::setUp();

		$importer_path            = self::TUTOR_TEST_PRO_DIRECTORY . '/tools/Importer.php';
		$course_importer_path     = self::TUTOR_TEST_PRO_DIRECTORY . '/tools/CourseImporter.php';
		$assignment_importer_path = self::TUTOR_TEST_PRO_DIRECTORY . '/tools/AssignmentImporter.php';
		$quiz_importer_path       = self::TUTOR_TEST_PRO_DIRECTORY . '/tools/QuizImporter.php';

		if ( file_exists( $importer_path ) ) {
			require_once $importer_path;
		} else {
			echo esc_html( $importer_path . ' does not exists.' );
		}

		if ( file_exists( $course_importer_path ) ) {
			require_once $course_importer_path;
		} else {
			echo esc_html( $course_importer_path . ' does not exists.' );
		}

		if ( file_exists( $assignment_importer_path ) ) {
			require_once $assignment_importer_path;
		} else {
			echo esc_html( $assignment_importer_path . ' does not exists.' );
		}

		if ( file_exists( $quiz_importer_path ) ) {
			require_once $quiz_importer_path;
		} else {
			echo esc_html( $quiz_importer_path . ' does not exists.' );
		}

		$this->importer = new \TutorPro\Tools\Importer();
		wp_set_current_user( 1 );
	}

	/**
	 * Teardown function that runs after the test has ran.
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		unset( $this->importer );

		parent::tearDown();
	}

	/**
	 * Test importing json content with importer.
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	public function test_import_json_content() {
		$json_data = file_get_contents( '' ); // file path.

		$response = $this->importer->import_contents( $json_data );

		$this->assertFalse( is_wp_error( $response ) );
	}
}
