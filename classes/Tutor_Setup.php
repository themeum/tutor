<?php
/**
 * Tutor setup class
 *
 * @package Tutor\Setup
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

use Tutor\Helpers\HttpHelper;
use Tutor\Traits\JsonResponse;

defined( 'ABSPATH' ) || exit;

/**
 * Manage setup functionalities
 *
 * @since 1.0.0
 */
class Tutor_Setup {

	use JsonResponse;

	/**
	 * Sample courses JSON URL for onboarding import.
	 *
	 * @since 4.0.0
	 */
	const TUTOR_SAMPLE_COURSES_JSON_URL = 'https://tutor-lms.s3.us-east-1.amazonaws.com/courses/onbaord-courses.json';

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus' ) );
		add_action( 'admin_init', array( $this, 'init_onboarding' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_onboard_scripts' ) );

		add_action( 'wp_ajax_tutor_onboard_setup', array( $this, 'ajax_onboard_setup' ) );
		add_action( 'wp_ajax_tutor_import_sample_courses', array( $this, 'ajax_import_sample_courses' ) );
	}

	/**
	 * Add dashboard page without title
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'tutor-setup', '' );
	}

	/**
	 * Initialize tutor onboarding
	 *
	 * @since 4.0.0 onboarding
	 *
	 * @return void
	 */
	public function init_onboarding() {
		$setup_page = Input::get( 'page', '' );
		if ( 'tutor-setup' === $setup_page ) {
			include tutor()->path . 'views/onboarding.php';
			exit;
		}
	}

	/**
	 * Tutor Onboarding Setup description
	 *
	 * @return void
	 *
	 * @since 4.0.0 onboarding
	 */
	public function ajax_onboard_setup() {
		try {
			tutor_utils()->check_nonce();
			if ( 'tutor_onboard_setup' !== Input::post( 'action', '' ) || ! current_user_can( 'manage_options' ) ) {
				$this->response_bad_request( tutor_utils()->error_message() );
			}

			$options = get_option( 'tutor_option', array() );

			$options['default_theme'] = Input::post( 'default_theme' );
			$options['learning_mode'] = Input::post( 'learning_mode' );

			update_option( 'tutor_option', $options );

			$this->json_response( __( 'Onboard Successfully', 'tutor' ) );
		} catch ( \Exception $e ) {
			$this->json_response(
				__( 'Onboard Failed, Try again!', 'tutor' ),
				null,
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}

	/**
	 * Handle sample courses import.
	 *
	 * @since 4.0.0
	 */
	public function ajax_import_sample_courses() {
		tutor_utils()->check_nonce();
		if ( ! User::can( 'manage_options' ) ) {
			$this->response_bad_request( tutor_utils()->error_message() );
		}

		try {
			( new SampleCourse() )->import( self::TUTOR_SAMPLE_COURSES_JSON_URL );
			$this->json_response( __( 'Sample courses imported successfully', 'tutor' ) );
		} catch ( \Throwable $th ) {
			$this->response_bad_request( tutor_utils()->error_message() );
		}
	}

	/**
	 *  Tutor onboarding enqueue scripts
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_onboard_scripts() {
		$page = Input::get( 'page', '' );
		if ( 'tutor-setup' === $page ) {
			wp_enqueue_style( 'tutor-setup', tutor()->url . 'assets/css/tutor-setup.min.css', array(), TUTOR_VERSION );
			wp_register_script( 'tutor-setup', tutor()->url . 'assets/js/tutor-setup.js', array( 'wp-i18n' ), TUTOR_VERSION, true );

			if ( is_rtl() ) {
				wp_enqueue_style( 'tutor', tutor()->url . 'assets/css/tutor-rtl.min.css', array(), TUTOR_VERSION );
			} else {
				wp_enqueue_style( 'tutor', tutor()->url . 'assets/css/tutor.min.css', array(), TUTOR_VERSION );
			}

			wp_enqueue_script( 'tutor-script', tutor()->url . 'assets/js/tutor.js', array( 'wp-i18n' ), TUTOR_VERSION, true );

			// load google inter font.
			wp_enqueue_style( 'tutor-inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', array(), TUTOR_VERSION );

			wp_localize_script(
				'tutor-setup',
				'_tutorOnboardObject',
				array(
					'tutor_welcome_page' => admin_url( 'admin.php?page=tutor&welcome=1' ),
					'course_data_url'    => 'https://tutor-lms.s3.us-east-1.amazonaws.com/courses/workademy/data.json',
				)
			);
			wp_set_script_translations( 'tutor-setup', 'tutor', tutor()->path . 'languages/' );
		}
	}


	/**
	 * Check if welcome page already visited
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_welcome_page_visited(): bool {
		return false;
	}

	/**
	 * Mark as welcome page visited
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function mark_as_visited() {
		update_option( 'tutor_welcome_page_visited', true );
	}
}
