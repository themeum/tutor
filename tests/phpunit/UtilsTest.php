<?php
/**
 * Utils test class
 */

namespace TutorTest;

use Tutor\Utils; 

class UtilsTest extends \WP_UnitTestCase {

	public function test_total_review() {
        $utils = new Utils();
        $result = $utils->get_total_review();
		$this->assertIsNumeric( $result );
	}
}
