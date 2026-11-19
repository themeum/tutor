<?php
/**
 * Utils test class
 */

namespace TutorTest;

use Tutor\Utils;

class UtilsTest extends \WP_UnitTestCase {

	public function test_total_review() {
		$utils  = new Utils();
		$result = $utils->get_total_review();
		$this->assertIsNumeric( $result );
	}

	public function test_get_user_name() {
		$username           = 'admin';
		$user               = new \WP_User();
		$user->user_login   = $username;
		$user->display_name = $username;

		// If someone not set first and last name
		$this->assertEquals( 'admin', tutor_utils()->get_user_name( $user ) );

		// If someone only set first name
		$user->first_name = 'Jhon';
		$this->assertEquals( 'Jhon', tutor_utils()->get_user_name( $user ) );

		// If someone set first and last name both
		$user->last_name = 'Kou';
		$this->assertEquals( 'Jhon Kou', tutor_utils()->get_user_name( $user ) );

		// If someone only set last name
		unset( $user->first_name );
		$this->assertEquals( $username, tutor_utils()->get_user_name( $user ) );
	}

	public function test_add_option_after_an_option_key() {
		$utils = new Utils();

		$arr   = array( 'a' => 'b' );
		$index = $utils->add_option_after( 'foo', $arr, array() );
		$this->assertEquals( null, $index );

		$arr1   = array( array( 'key' => 'a' ), array( 'key' => 'b' ), array( 'key' => 'c' ) );
		$index1 = $utils->add_option_after( 'b', $arr1, array() );
		$this->assertEquals( null, $index1 );

		$arr3     = array( array( 'key' => 'a' ), array( 'key' => 'b' ), array( 'key' => 'd' ) );
		$new_item = array( 'key' => 'c' );
		$index3   = $utils->add_option_after( 'b', $arr3, array( 'key' => 'c' ) );

		$this->assertEquals( 2, $index3 );
		$this->assertEquals( $arr3[ $index3 ]['key'], $new_item['key'] );
	}

	/**
	 * Test the is_assoc function
	 *
	 * @return boolean
	 */
	public function test_is_assoc() {
		$array1 = array( 0, 1, 2 );
		$array2 = array(
			'a' => 'apple',
			'b' => 'banana',
		);

		$this->assertTrue( tutor_utils()->is_assoc( $array2 ) );
		$this->assertFalse( tutor_utils()->is_assoc( $array1 ) );
	}

	/**
	 * Test display name
	 *
	 * @return void
	 */
	public function test_display_name() {
		$args      = array(
			'display_name' => 'john doe',
		);
		$user_id   = Utilities::get_user_id( $args );
		$expected1 = 'john doe';
		$actual1   = tutor_utils()->display_name( $user_id );

		// Test display name.
		$this->assertSame( $expected1, $actual1 );

		// Update user , set display name empty.
		$update_args = array(
			'ID'           => $user_id,
			'display_name' => '',
			'user_login'   => 'abc',
		);
		$user_id = Utilities::get_user_id( $update_args );

		$expected2 = 'abc';
		/**
		 * It should return user_login since display name empty
		 * & first name & last name not been setup
		 */
		$actual2 = tutor_utils()->display_name( $user_id );

		$this->assertSame( $expected2, $actual2 );
	}

	/**
	 * Test avalue_dot function
	 *
	 * @return void
	 */
	public function test_avalue_dot() {
		$array = array(
			'a' => 'apple',
			'b' => 'banana',
		);
		$this->assertEquals( 'apple', tutor_utils()->avalue_dot( 'a', $array ) );
		$this->assertEquals( 'banana', tutor_utils()->avalue_dot( 'b', $array ) );
		$this->assertEquals( false, tutor_utils()->avalue_dot( 'c', $array ) );

		$obj = (object) $array;
		$this->assertEquals( 'apple', tutor_utils()->avalue_dot( 'a', $obj ) );
		$this->assertEquals( 'banana', tutor_utils()->avalue_dot( 'b', $obj ) );
		$this->assertEquals( false, tutor_utils()->avalue_dot( 'c', $obj ) );
	}

	/**
	 * Subtitle tracks are sanitized and only one can be default.
	 *
	 * @return void
	 */
	public function test_filter_video_meta_normalizes_subtitle_tracks() {
		$utils = new Utils();
		$video = $utils->filter_video_meta(
			array(
				'source'    => 'html5',
				'subtitles' => array(
					array(
						'attachment_id' => '12',
						'srclang'       => 'EN',
						'label'         => 'English',
						'default'       => '1',
					),
					array(
						'attachment_id' => '34',
						'srclang'       => 'de',
						'label'         => '',
						'default'       => '1',
					),
					array(
						'attachment_id' => '0',
						'srclang'       => 'fr',
						'label'         => 'French',
						'default'       => '1',
					),
					array(
						'attachment_id' => '56',
						'srclang'       => 'not a lang!',
						'label'         => 'Bad',
						'default'       => '1',
					),
				),
			)
		);

		$this->assertSame( 'html5', $video['source'] );
		$this->assertCount( 2, $video['subtitles'] );
		$this->assertSame( 12, $video['subtitles'][0]['attachment_id'] );
		$this->assertSame( 'en', $video['subtitles'][0]['srclang'] );
		$this->assertTrue( $video['subtitles'][0]['default'] );
		$this->assertSame( 'DE', $video['subtitles'][1]['label'] );
		$this->assertFalse( $video['subtitles'][1]['default'] );
	}

	/**
	 * Subtitle track count is capped to keep video meta bounded.
	 *
	 * @return void
	 */
	public function test_filter_video_meta_caps_subtitle_tracks() {
		$utils  = new Utils();
		$tracks = array();

		for ( $i = 1; $i <= 51; $i++ ) {
			$tracks[] = array(
				'attachment_id' => $i,
				'srclang'       => 'en',
				'label'         => 'English',
				'default'       => '0',
			);
		}

		$video = $utils->filter_video_meta( array( 'subtitles' => $tracks ) );

		$this->assertCount( 50, $video['subtitles'] );
	}

	/**
	 * Invalid subtitle tracks are dropped entirely.
	 *
	 * @return void
	 */
	public function test_filter_video_meta_drops_invalid_subtitle_tracks() {
		$utils = new Utils();
		$video = $utils->filter_video_meta(
			array(
				'subtitles' => array(
					array( 'attachment_id' => 0 ),
					'not a track',
				),
			)
		);

		$this->assertArrayNotHasKey( 'subtitles', $video );
	}
}
