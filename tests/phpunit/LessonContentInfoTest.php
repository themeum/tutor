<?php namespace TutorTest;

use TUTOR\Lesson;

class LessonContentInfoTest extends \WP_UnitTestCase {

	private $post;

	/**
	 * Set up test fixtures
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$post_id = $this->factory()->post->create(
			array(
				'post_type'   => tutor()->lesson_post_type,
				'post_title'  => 'Test Lesson',
				'post_status' => 'publish',
			)
		);

		$this->post = get_post( $post_id );
	}


	public function test_get_content_type_info_without_video() {
		$result   = Lesson::get_content_type_info( $this->post );
		$expected = 'Reading';
		$this->assertSame( $result, $expected );
	}

	public function test_get_content_type_info_with_video_time() {
		// Set video meta with runtime.
		$video_data = array(
			'source'         => 'youtube',
			'source_youtube' => 'https://www.youtube.com/watch?v=test123',
			'playtime'       => '00:10:36',
		);
		update_post_meta( $this->post->ID, '_video', $video_data );

		$result = Lesson::get_content_type_info( $this->post );

		$this->assertSame( 'Video - 00:10:36 mins', $result );
	}

	public function test_get_content_type_info_with_video_no_time_set() {
		// Set video meta without runtime
		$video_data = array(
			'source'         => 'youtube',
			'source_youtube' => 'https://www.youtube.com/watch?v=test123',
		);
		update_post_meta( $this->post->ID, '_video', $video_data );

		$result = Lesson::get_content_type_info( $this->post );

		$this->assertSame( 'Video - 00:00:00 mins', $result );
	}

	public function test_get_content_type_info_with_video_false_time_set() {
		// Set video meta with runtime set to zeros/empty
		$video_data = array(
			'source'         => 'youtube',
			'source_youtube' => 'https://www.youtube.com/watch?v=test123',
			'playtime'       => '00:00:00',
		);
		update_post_meta( $this->post->ID, '_video', $video_data );

		$result = Lesson::get_content_type_info( $this->post );

		// Should return "Reading" when playtime is "00:00:00"
		$this->assertSame( 'Video - 00:00:00 mins', $result );
	}
}
