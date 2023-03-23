<?php
/**
 * Unit test for TutorCache Class
 * 
 * @package Tutor\Test
 * @since 2.1.9
 */
namespace TutorTest;

use Tutor\Cache\TutorCache;

/**
 * TutorCacheTest Class
 *
 * @since 2.1.9
 */
class TutorCacheTest extends \WP_UnitTestCase {
    public function test_prevent_to_make_instance() {
        try {
            new TutorCache();
        } catch (\Throwable $e) {
            $this->assertSame( 1, 1 );
        }
    }

	public function test_ensure_same_instance() {
		$instance = TutorCache::getInstance();

		$this->assertSame( $instance, TutorCache::getInstance() );
        $this->assertSame( $instance, TutorCache::getInstance() );
	}

    public function test_data_access_get_set() {
		$expect = array(
            'a' => 1,
            'b' => 2
        );

        /**
         * Simulate run-time
         */
        TutorCache::set('a',1);
        TutorCache::getInstance();
        TutorCache::set('b',2); 

		$this->assertEquals( $expect, TutorCache::get_all() );
        $this->assertEquals( 1, TutorCache::get('a') );
        $this->assertEquals( 2, TutorCache::get('b') );
	}
}
