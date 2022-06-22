<?php
namespace Tutor\Models;

/**
 * Class Course
 * @since 2.0.6
 */
class Course {
    /**
     * WordPress course type name
     * @var string
     */
    const POST_TYPE         = 'courses';

    const STATUS_PUBLISH    = 'publish';
    const STATUS_DRAFT      = 'draft';

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