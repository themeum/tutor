<?php
/**
 * Manage Addons
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Addons Class
 *
 * @since 1.0.0
 */
class Addons {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		add_filter( 'tutor_pro_addons_lists_for_display', array( $this, 'tutor_addons_lists_to_show' ) );
	}

	/**
	 * Get tutor addons list
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function tutor_addons_lists_to_show() {
		$addons = array(
			'course-bundle'            => array(
				'name'        => __( 'Course Bundle', 'tutor' ),
				'description' => __( 'Group multiple courses to sell together.', 'tutor' ),
			),
			'subscription'             => array(
				'name'        => __( 'Subscription', 'tutor' ),
				'description' => __( 'Manage subscription', 'tutor' ),
			),
			'social-login'             => array(
				'name'        => __( 'Social Login', 'tutor' ),
				'description' => __( 'Let users register & login through social network like Facebook, Google, etc.', 'tutor' ),
			),
			'content-drip'             => array(
				'name'        => __( 'Content Drip', 'tutor' ),
				'description' => 'Unlock lessons by schedule or when students meet a specific condition.',
			),
			'tutor-multi-instructors'  => array(
				'name'        => __( 'Tutor Multi Instructors', 'tutor' ),
				'description' => 'Collaborate and add multiple instructors to a course.',
			),
			'tutor-assignments'        => array(
				'name'        => __( 'Tutor Assignments', 'tutor' ),
				'description' => 'Assess student learning with assignments.',
			),
			'tutor-course-preview'     => array(
				'name'        => __( 'Tutor Course Preview', 'tutor' ),
				'description' => 'Offer free previews of specific lessons before enrollment.',
			),
			'tutor-course-attachments' => array(
				'name'        => __( 'Tutor Course Attachments', 'tutor' ),
				'description' => 'Add unlimited attachments/ private files to any Tutor course',
			),
			'google-meet'              => array(
				'name'        => __( 'Tutor Google Meet Integration', 'tutor' ),
				'description' => __( 'Host live classes with Google Meet, directly from your lesson page.', 'tutor' ),
			),
			'tutor-report'             => array(
				'name'        => __( 'Tutor Report', 'tutor' ),
				'description' => __('Check your course performance through Tutor Report stats.', 'tutor'),
			),
			'tutor-email'              => array(
				'name'        => __( 'Email', 'tutor' ),
				'description' => __('Send automated and customized emails for various Tutor events.', 'tutor'),
			),
			'calendar'                 => array(
				'name'        => 'Calendar',
				'description' => __('Enable to let students view all your course events in one place.', 'tutor'),
			),
			'tutor-notifications'      => array(
				'name'        => 'Notifications',
				'description' => __('Keep students and instructors notified of course events on their dashboard.', 'tutor'),
			),
			'google-classroom'         => array(
				'name'        => __( 'Google Classroom Integration', 'tutor' ),
				'description' => __( 'Enable to integrate Tutor LMS with Google Classroom.', 'tutor' ),
			),
			'tutor-zoom'               => array(
				'name'        => __( 'Tutor Zoom Integration', 'tutor' ),
				'description' => __( 'Connect Tutor LMS with Zoom to host live online classes. Students can attend live classes right from the lesson page.', 'tutor' ),
			),
			'quiz-import-export'       => array(
				'name'        => __( 'Quiz Export/Import', 'tutor' ),
				'description' => __( 'Save time by exporting/importing quiz data with easy options.', 'tutor' ),
			),
			'enrollments'              => array(
				'name'        => __( 'Enrollment', 'tutor' ),
				'description' => __('Enable to manually enroll students in your courses.', 'tutor'),
			),
			'tutor-certificate'        => array(
				'name'        => __( 'Tutor Certificate', 'tutor' ),
				'description' => __('Enable to award certificates upon course completion.', 'tutor'),
			),
			'gradebook'                => array(
				'name'        => __( 'Gradebook', 'tutor' ),
				'description' => __('Track student progress with a centralized gradebook.', 'tutor'),
			),
			'tutor-prerequisites'      => array(
				'name'        => __( 'Tutor Prerequisites', 'tutor' ),
				'description' => __('Set course prerequisites to guide learning paths effectively.', 'tutor'),
			),
			'buddypress'               => array(
				'name'        => __( 'BuddyPress', 'tutor' ),
				'description' => __('Boost engagement with social features through BuddyPress for Tutor LMS.', 'tutor'),
			),
			'wc-subscriptions'         => array(
				'name'        => __( 'WooCommerce Subscriptions', 'tutor' ),
				'description' => __('Capture Residual Revenue with Recurring Payments.', 'tutor'),
			),
			'pmpro'                    => array(
				'name'        => __( 'Paid Memberships Pro', 'tutor' ),
				'description' => __('Maximize revenue by selling membership access to all of your courses.', 'tutor'),
			),
			'restrict-content-pro'     => array(
				'name'        => __( 'Restrict Content Pro', 'tutor' ),
				'description' => __('Enable to manage content access through Restrict Content Pro. ', 'tutor'),
			),
			'tutor-weglot'             => array(
				'name'        => 'Weglot',
				'description' => __( 'Translate & manage multilingual courses for global reach with full edit control.', 'tutor' ),
			),
			'tutor-wpml'               => array(
				'name'        => __( 'WPML Multilingual CMS', 'tutor' ),
				'description' => __( 'Create multilingual courses, lessons, dashboard and more for a global audience.', 'tutor' ),
			),
		);

		return $addons;
	}
}
