<?php
namespace Tutor\Models;

/**
 * Class LessonModel
 *
 * @since 2.0.10
 */
class LessonModel {

	/**
	 * Get total number of lesson
	 *
	 * @return int
	 * @since 2.0.2
	 */
	public static function get_total_lesson() {
		global $wpdb;
		$lesson_type = tutor()->lesson_post_type;

		$sql = "SELECT COUNT(DISTINCT lesson.ID)
				FROM {$wpdb->posts} lesson
					INNER JOIN {$wpdb->posts} topic ON lesson.post_parent=topic.ID
					INNER JOIN {$wpdb->posts} course ON topic.post_parent=course.ID
				WHERE lesson.post_type = %s
					AND lesson.post_status = %s
					AND course.post_status = %s
					AND topic.post_status = %s";

		return $wpdb->get_var( $wpdb->prepare( $sql, $lesson_type, 'publish', 'publish', 'publish' ) );
	}

	/**
	 * Get total lesson count by a course
	 *
	 * @param int $course_id
	 * @return int
	 * @since 1.0.0
	 */
	public function get_lesson_count_by_course( $course_id = 0 ) {
		$course_id = tutor_utils()->get_post_id( $course_id );
		return count( tutor_utils()->get_course_content_ids_by( tutor()->lesson_post_type, tutor()->course_post_type, $course_id ) );
	}

    /**
     * Get lesson reading info by key
     * 
	 * @param int    $lesson_id
	 * @param int    $user_id
	 * @param string $key
	 *
	 * @return array|bool|mixed
	 *
	 * @since 1.0.0
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
	 * @param int $lesson_id
	 * @param int $user_id
	 *
	 * @return array|bool|mixed
	 *
	 * @since 1.0.0
	 */
	public function get_lesson_reading_info_full( $lesson_id = 0, $user_id = 0 ) {
		$lesson_id = tutor_utils()->get_post_id( $lesson_id );
		$user_id   = tutor_utils()->get_user_id( $user_id );

		$lesson_info = (array) maybe_unserialize( get_user_meta( $user_id, '_lesson_reading_info', true ) );
		return tutor_utils()->avalue_dot( $lesson_id, $lesson_info );
	}
}
