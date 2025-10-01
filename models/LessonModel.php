<?php
/**
 * Lesson Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.10
 */

namespace Tutor\Models;

use Tutor\Helpers\QueryHelper;

/**
 * LessonModel Class
 *
 * @since 2.0.10
 */
class LessonModel {

	/**
	 * Get total number of lesson
	 *
	 * @since 2.0.2
	 *
	 * @since 3.7.1 Course ids param added
	 *
	 * @param array $course_ids Array of course ids.
	 *
	 * @return int
	 */
	public static function get_total_lesson( array $course_ids = array() ) {
		global $wpdb;
		$lesson_type = tutor()->lesson_post_type;

		$course_in_clause = '';
		if ( count( $course_ids ) ) {
			$prepare_ids      = QueryHelper::prepare_in_clause( $course_ids );
			$course_in_clause = "AND course.ID IN ({$prepare_ids})";
		}

		$post_status           = QueryHelper::prepare_in_clause( array( 'publish', 'future', 'draft', 'private', 'pending' ) );
		$post_status_in_clause = "AND course.post_status IN ({$post_status})";

		$sql = "SELECT COUNT(DISTINCT lesson.ID)
				FROM {$wpdb->posts} lesson
					INNER JOIN {$wpdb->posts} topic ON lesson.post_parent=topic.ID
					INNER JOIN {$wpdb->posts} course ON topic.post_parent=course.ID
				WHERE 1 = 1
					{$course_in_clause}
					AND lesson.post_type = %s
					AND lesson.post_status = %s
					{$post_status_in_clause}
					AND topic.post_status = %s";

		//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_var( $wpdb->prepare( $sql, $lesson_type, 'publish', 'publish' ) );
		return $result;
	}

	/**
	 * Get total lesson count by a course
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id course id.
	 * @return int
	 */
	public function get_lesson_count_by_course( $course_id = 0 ) {
		$course_id = tutor_utils()->get_post_id( $course_id );
		return count( tutor_utils()->get_course_content_ids_by( tutor()->lesson_post_type, tutor()->course_post_type, $course_id ) );
	}

	/**
	 * Get lesson reading info by key
	 *
	 * @since 1.0.0
	 *
	 * @param int    $lesson_id lesson id.
	 * @param int    $user_id user id.
	 * @param string $key key.
	 *
	 * @return array|bool|mixed
	 */
	public function get_lesson_reading_info( $lesson_id = 0, $user_id = 0, $key = '' ) {
		$lesson_id   = tutor_utils()->get_post_id( $lesson_id );
		$user_id     = tutor_utils()->get_user_id( $user_id );
		$lesson_info = $this->get_lesson_reading_info_full( $lesson_id, $user_id );

		return tutor_utils()->avalue_dot( $key, $lesson_info );
	}

	/**
	 * Get student lesson reading current info
	 *
	 * @since 1.0.0
	 *
	 * @param int $lesson_id lesson id.
	 * @param int $user_id user id.
	 *
	 * @return array|bool|mixed
	 */
	public function get_lesson_reading_info_full( $lesson_id = 0, $user_id = 0 ) {
		$lesson_id = tutor_utils()->get_post_id( $lesson_id );
		$user_id   = tutor_utils()->get_user_id( $user_id );

		$lesson_info = (array) maybe_unserialize( get_user_meta( $user_id, '_lesson_reading_info', true ) );
		return tutor_utils()->avalue_dot( $lesson_id, $lesson_info );
	}

	/**
	 * Update student lesson reading info
	 *
	 * @since 1.0.0
	 *
	 * @param int    $lesson_id lesson id.
	 * @param int    $user_id user id.
	 * @param string $key key.
	 * @param string $value value.
	 *
	 * @return void
	 */
	public static function update_lesson_reading_info( $lesson_id = 0, $user_id = 0, $key = '', $value = '' ) {
		$lesson_id = tutor_utils()->get_post_id( $lesson_id );
		$user_id   = tutor_utils()->get_user_id( $user_id );

		if ( $key ) {
			$lesson_info                       = (array) maybe_unserialize( get_user_meta( $user_id, '_lesson_reading_info', true ) );
			$lesson_info[ $lesson_id ][ $key ] = $value;
			update_user_meta( $user_id, '_lesson_reading_info', $lesson_info );
		}
	}

	/**
	 * Mark lesson complete
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id post id.
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	public static function mark_lesson_complete( $post_id = 0, $user_id = 0 ) {
		$post_id = tutor_utils()->get_post_id( $post_id );
		$user_id = tutor_utils()->get_user_id( $user_id );

		do_action( 'tutor_mark_lesson_complete_before', $post_id, $user_id );
		update_user_meta( $user_id, '_tutor_completed_lesson_id_' . $post_id, tutor_time() );
		do_action( 'tutor_mark_lesson_complete_after', $post_id, $user_id );
	}

	/**
	 * Get lesson details.
	 *
	 * @since 3.7.0
	 *
	 * @param int $lesson_id lesson id.
	 *
	 * @return array
	 */
	public static function get_lesson_details( $lesson_id ) {
		$post = get_post( $lesson_id, ARRAY_A );

		if ( $post ) {
			$post['thumbnail_id'] = get_post_meta( $lesson_id, '_thumbnail_id', true );
			$post['thumbnail']    = get_the_post_thumbnail_url( $lesson_id );
			$post['attachments']  = tutor_utils()->get_attachments( $lesson_id );

			$video = maybe_unserialize( get_post_meta( $lesson_id, '_video', true ) );
			if ( $video ) {
				$source = $video['source'] ?? '';
				if ( 'html5' === $source ) {
					$poster_url            = wp_get_attachment_url( $video['poster'] ?? 0 );
					$source_html5          = wp_get_attachment_url( $video['source_video_id'] ?? 0 );
					$video['poster_url']   = $poster_url;
					$video['source_html5'] = $source_html5;
				}
			}
			$post['video'] = $video;
		} else {
			$post = array();
		}

		$data = apply_filters( 'tutor_lesson_details_response', $post, $lesson_id );

		return $data;
	}
}
