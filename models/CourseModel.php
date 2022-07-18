<?php
namespace Tutor\Models;

/**
 * Class CourseModel
 * @since 2.0.6
 */
class CourseModel {
    /**
     * WordPress course type name
     * @var string
     */
    const POST_TYPE         = 'courses';

    const STATUS_PUBLISH    = 'publish';
    const STATUS_DRAFT      = 'draft';
    const STATUS_AUTO_DRAFT = 'auto-draft';
    const STATUS_PENDING    = 'pending';

	/**
	 * Course record count
	 *
	 * @return int
	 * 
	 * @since 2.0.7
	 */
	public static function count( $status = self::STATUS_PUBLISH ) {
		$count_obj = wp_count_posts( self::POST_TYPE );
		if ( 'all' === $status ) {
			return array_sum( (array) $count_obj );
		}
		
		return (int) $count_obj->{$status};
	}

	/**
	 * Get courses
	 * 
	 * @param array $excludes	exclude course ids
	 *
	 * @return array|null|object
	 *
	 * @since 1.0.0
	 */
	public function get_courses( $excludes = array(), $post_status = array( 'publish' ) ) {
		global $wpdb;

		$excludes      = (array) $excludes;
		$exclude_query = '';

		if ( count( $excludes ) ) {
			$exclude_query = implode( "','", $excludes );
		}

		$post_status = array_map(
			function ( $element ) {
				return "'" . $element . "'";
			},
			$post_status
		);

		$post_status      = implode( ',', $post_status );
		$course_post_type = tutor()->course_post_type;

		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID,
					post_author,
					post_title,
					post_name,
					post_status,
					menu_order
			FROM 	{$wpdb->posts}
			WHERE 	post_status IN ({$post_status})
					AND ID NOT IN('$exclude_query')
					AND post_type = %s;
			",
				$course_post_type
			)
		);

		return $query;
	}

	/**
	 * Get courses for instructors
	 * 
	 * @param int $instructor_id	Instructor ID
	 *
	 * @return array|null|object
	 *
	 * @since 1.0.0
	 */
	public function get_courses_for_instructors( $instructor_id = 0 ) {
		$instructor_id    = tutor_utils()->get_user_id( $instructor_id );
		$course_post_type = tutor()->course_post_type;

		$courses = get_posts(
			array(
				'post_type'      => $course_post_type,
				'author'         => $instructor_id,
				'post_status'    => array( 'publish', 'pending' ),
				'posts_per_page' => 5,
			)
		);

		return $courses;
	}

    /**
     * Mark the course as completed
     *
     * @param int $course_id    course id which is completed
     * @param int $user_id      student id who completed the course
     * @return bool
     * 
     * @since 2.0.7
     */
    public static function mark_course_as_completed( $course_id, $user_id ) {
        if ( ! $course_id || ! $user_id ) {
            return false;
        }

        do_action( 'tutor_course_complete_before', $course_id );
		
        /**
		 * Marking course completed at Comment
		 */
		global $wpdb;

		$date = date( 'Y-m-d H:i:s', tutor_time() );

		// Making sure that, hash is unique
		do {
			$hash    = substr( md5( wp_generate_password( 32 ) . $date . $course_id . $user_id ), 0, 16 );
			$hasHash = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(comment_ID) from {$wpdb->comments}
				    WHERE comment_agent = 'TutorLMSPlugin' AND comment_type = 'course_completed' AND comment_content = %s ",
					$hash
				)
			);

		} while ( $hasHash > 0 );

		$data = array(
			'comment_post_ID'  => $course_id,
			'comment_author'   => $user_id,
			'comment_date'     => $date,
			'comment_date_gmt' => get_gmt_from_date( $date ),
			'comment_content'  => $hash, // Identification Hash
			'comment_approved' => 'approved',
			'comment_agent'    => 'TutorLMSPlugin',
			'comment_type'     => 'course_completed',
			'user_id'          => $user_id,
		);

		$wpdb->insert( $wpdb->comments, $data );

		do_action( 'tutor_course_complete_after', $course_id, $user_id );
        
        return true;
    }
}