<?php
/**
 * Tutor Config
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.7.0
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

/**
 * Config Class
 *
 * @since 3.7.0
 *
 * @property string $path Plugin file path.
 * @property string $url Plugin url.
 * @property string $basename Plugin base name.
 * @property string $basepath Plugin base path.
 * @property string $version Plugin version.
 * @property string $assets_url Assets url.
 * @property string $template_path Template path.
 * @property string $icon_dir  Icons dir.
 * @property string $v2_img_dir Version 2 images dir.
 * @property string $current_url Current url.
 * @property string $nonce_action Nonce action.
 * @property string $nonce Nonce.
 * @property bool   $has_pro Has pro plugin.
 * @property string $course_post_type Course post type.
 * @property string $bundle_post_type Bundle post type.
 * @property string $lesson_post_type Lesson post type.
 * @property string $instructor_role Instructor role.
 * @property string $topics_post_type Topics post type.
 * @property string $announcement_post_type Announcement post type.
 * @property string $assignment_post_type Assignment post type.
 * @property string $enrollment_post_type Enrollment post type.
 * @property string $quiz_post_type Quiz post type.
 * @property string $zoom_post_type Zoom post type.
 * @property string $meet_post_type Meet post type.
 */
class Config extends Singleton {
	/**
	 * All settings.
	 *
	 * @since 3.7.0
	 *
	 * @var array
	 */
	private array $settings = array();

	/**
	 * Constructor function
	 *
	 * @since 3.7.0
	 */
	public function __construct() {
		$path    = plugin_dir_path( TUTOR_FILE );
		$has_pro = defined( 'TUTOR_PRO_VERSION' );

		// Prepare the basepath.
		$home_url  = get_home_url();
		$parsed    = parse_url( $home_url );
		$base_path = ( is_array( $parsed ) && isset( $parsed['path'] ) ) ? $parsed['path'] : '/';
		$base_path = rtrim( $base_path, '/' ) . '/';
		// Get current URL.
		$current_url = trailingslashit( $home_url ) . substr( $_SERVER['REQUEST_URI'], strlen( $base_path ) );//phpcs:ignore

		$this->settings = array(
			'path'                   => $path,
			'url'                    => plugin_dir_url( TUTOR_FILE ),
			'assets_url'             => plugin_dir_url( TUTOR_FILE ) . 'assets/',
			'icon_dir'               => plugin_dir_url( TUTOR_FILE ) . 'assets/images/images-v2/icons/',
			'v2_img_dir'             => plugin_dir_url( TUTOR_FILE ) . 'assets/images/images-v2/',
			'current_url'            => $current_url,
			'basename'               => plugin_basename( TUTOR_FILE ),
			'basepath'               => $base_path,
			'version'                => TUTOR_VERSION,
			'nonce_action'           => 'tutor_nonce_action',
			'nonce'                  => '_tutor_nonce',
			'course_post_type'       => apply_filters( 'tutor_course_post_type', 'courses' ),
			'bundle_post_type'       => apply_filters( 'tutor_bundle_post_type', 'course-bundle' ),
			'lesson_post_type'       => apply_filters( 'tutor_lesson_post_type', 'lesson' ),
			'instructor_role'        => apply_filters( 'tutor_instructor_role', 'tutor_instructor' ),
			'template_path'          => apply_filters( 'tutor_template_path', 'tutor/' ),
			'has_pro'                => apply_filters( 'tutor_has_pro', $has_pro ),
			// @since v2.0.6.
			'topics_post_type'       => apply_filters( 'tutor_topics_post_type', 'topics' ),
			'announcement_post_type' => apply_filters( 'tutor_announcement_post_type', 'tutor_announcements' ),
			'assignment_post_type'   => apply_filters( 'tutor_assignment_post_type', 'tutor_assignments' ),
			'enrollment_post_type'   => apply_filters( 'tutor_enrollment_post_type', 'tutor_enrolled' ),
			'quiz_post_type'         => apply_filters( 'tutor_quiz_post_type', 'tutor_quiz' ),
			'zoom_post_type'         => apply_filters( 'tutor_zoom_meeting_post_type', 'tutor_zoom_meeting' ),
			'meet_post_type'         => apply_filters( 'tutor_google_meeting_post_type', 'tutor-google-meet' ),
		);
	}

	/**
	 * Get a config value as property.
	 *
	 * @since 3.7.0
	 *
	 * @param string $key key.
	 *
	 * @return mixed
	 */
	public function __get( string $key ) {
		if ( ! array_key_exists( $key, $this->settings ) ) {
			tutor_log( "Warning: Config property {$key} does not exist." );
			return null;
		}
		return $this->settings[ $key ];
	}

	/**
	 * Get all settings
	 *
	 * @since 3.7.0
	 *
	 * @return array
	 */
	public function all(): array {
		return $this->settings;
	}
}
