<?php
/**
 * Importer Class Unit Test
 *
 * @package Tutor\Test
 *
 * @since 3.6.0
 */

namespace TutorTest;

class ImporterTest extends \PHPUnit\Framework\TestCase {

	private $importer;

	const TUTOR_TEST_PRO_DIRECTORY = '';

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
			echo $importer_path . ' ' . 'does not exists.';
		}

		if ( file_exists( $course_importer_path ) ) {
			require_once $course_importer_path;
		} else {
			echo $course_importer_path . ' ' . 'does not exists.';
		}

		if ( file_exists( $assignment_importer_path ) ) {
			require_once $assignment_importer_path;
		} else {
			echo $assignment_importer_path . ' ' . 'does not exists.';
		}

		if ( file_exists( $quiz_importer_path ) ) {
			require_once $quiz_importer_path;
		} else {
			echo $quiz_importer_path . ' ' . 'does not exists.';
		}

		$this->importer = new \TutorPro\Tools\Importer();
		wp_set_current_user( 1 );
	}

	protected function tearDown(): void {
		unset( $this->importer );

		parent::tearDown();
	}


	public function test_import_json_content() {
		$json_data = file_get_contents( '' ); // file path.

		$response = $this->importer->import_contents( $json_data );

		$this->assertFalse( is_wp_error( $response ) );
	}
}