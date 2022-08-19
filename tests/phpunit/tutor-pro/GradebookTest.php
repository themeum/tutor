<?php
/**
 * Utils test class
 */

namespace TutorProTest;

class GradebookTest extends \WP_UnitTestCase {

	private $gradebook;

	protected function setUp(): void {
		$class = '/Applications/MAMP/htdocs/tutor-v2/wp-content/plugins/tutor-pro/addons/gradebook/classes/GradeBook.php';
		if ( file_exists( $class ) ) {
			include_once $class;
		} else {
			echo $class . ' File does not exists';
		}
		$this->gradebook = new \TUTOR_GB\Gradebook();
	}

    protected function tearDown(): void {
        unset( $this->gradebook );
    }

	public function test_delete_gradebook_on_retake() {
		$result = $this->gradebook->delete_gradebook_on_retake(2040, 1);
		$this->assertEmpty( $result );
	}
}
