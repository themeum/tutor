<?php #phpcs:ignore
/**
 * User preference manager
 *
 * Handles storing and retrieving per–user preferences (e.g. theme, playback)
 * in a reusable and extensible way using WordPress user meta.
 *
 * @package Tutor\User
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

use Tutor\Helpers\HttpHelper;
use Tutor\Traits\JsonResponse;

/**
 * Class UserPreference
 *
 * @since 4.0.0
 */
class UserPreference {

	use JsonResponse;

	/**
	 * User meta key for storing all Tutor user preferences.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	const META_KEY = 'tutor_user_preferences';

	/**
	 * Available theme options.
	 *
	 * @since 4.0.0
	 *
	 * @var array<string>
	 */
	const THEMES = array( 'light', 'dark', 'system' );

	/**
	 * Register hooks.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $register_hooks Whether to register WordPress hooks.
	 */
	public function __construct( $register_hooks = true ) {
		if ( ! $register_hooks ) {
			return;
		}

		add_action( 'wp_ajax_tutor_save_user_preferences', array( $this, 'tutor_save_user_preferences' ) );
		add_filter( 'body_class', array( $this, 'add_theme_attribute' ) );
		add_action( 'wp_head', array( $this, 'apply_font_scale' ) );
	}

	/**
	 * Get font scale options for UI selects.
	 *
	 * Uses a filter to allow customization of available values.
	 *
	 * @since 4.0.0
	 *
	 * @return array<int,array{label:string,value:int}>
	 */
	public static function get_font_scale_options() {
		$values = apply_filters( 'tutor_user_preference_font_scale_values', array( 70, 80, 90, 100, 110, 120 ) );
		$options = array();
		foreach ( $values as $value ) {
			$value   = (int) $value;
			$options[] = array(
				'label' => $value . '%',
				'value' => $value,
			);
		}
		return $options;
	}


	/**
	 * Apply font scale to the document root.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function apply_font_scale() {
		$prefs          = $this->get_preferences();
		$font_scale     = isset( $prefs['font_scale'] ) ? (int) $prefs['font_scale'] : 100;
		$base_font_size = 16;
		$font_size      = ( $base_font_size * $font_scale ) / 100;
		echo '<style>:root { font-size: ' . esc_attr( $font_size ) . 'px; }</style>';
	}

	/**
	 * Add a theme class to the <body> tag.
	 *
	 * Note: body_class filter only accepts class names, not attributes.
	 *
	 * @since 4.0.0
	 *
	 * @param array $classes Body classes.
	 *
	 * @return array
	 */
	public function add_theme_attribute( $classes ) {
		$prefs = $this->get_preferences();
		$theme = isset( $prefs['theme'] ) ? (string) $prefs['theme'] : 'light';
		if ( ! in_array( $theme, self::THEMES, true ) ) {
			$theme = 'light';
		}
		echo ' data-theme="' . esc_attr( $theme ) . '"';
		return $classes;
	}

	/**
	 * Get default preferences.
	 *
	 * Keep this method very small and declarative so it is easy to extend.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public static function get_default_preferences() {
		return apply_filters(
			'tutor_user_preference_defaults',
			array(
				'auto_play_next' => true,
				'theme'          => 'light',
				'font_scale'     => 100,
			)
		);
	}

	/**
	 * Get merged preferences for a user.
	 *
	 * Stored values override defaults; unknown keys are ignored.
	 *
	 * @since 4.0.0
	 *
	 * @param int $user_id Optional. User ID. Defaults to current user.
	 *
	 * @return array|false
	 */
	public function get_preferences( $user_id = 0 ) {
		$user_id = tutor_utils()->get_user_id( $user_id );
		if ( ! $user_id ) {
			return array();
		}

		$preferences = get_user_meta( $user_id, self::META_KEY, true );
		if ( ! is_array( $preferences ) ) {
			$preferences = array();
		}
		return array_merge( self::get_default_preferences(), $preferences );
	}

	/**
	 * Persist preferences to user meta.
	 *
	 * @since 4.0.0
	 *
	 * @param array<string,mixed> $prefs   Raw preferences to save.
	 * @param int                 $user_id Optional. User ID. Defaults to current user.
	 *
	 * @return array|false Final saved preferences.
	 */
	public function save_preferences( array $prefs, $user_id = 0 ) {
		$user_id = tutor_utils()->get_user_id( $user_id );

		if ( ! $user_id ) {
			return false;
		}

		$current_preferences = $this->get_preferences( $user_id );
		$preferences         = array_merge( $current_preferences, $prefs );

		$preferences = apply_filters( 'tutor_user_preference_data', $preferences, $user_id );

		update_user_meta( $user_id, self::META_KEY, $preferences );

		do_action( 'tutor_user_preference_data', $user_id, $preferences );

		return $preferences;
	}

	/**
	 * AJAX handler: save current user's preferences.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function tutor_save_user_preferences() {
		tutor_utils()->check_nonce();

		if ( ! is_user_logged_in() ) {
			$this->json_response(
				tutor_utils()->error_message( 'forbidden' ),
				null,
				HttpHelper::STATUS_UNAUTHORIZED
			);
		}

		$auto_play_next = Input::post( 'auto_play_next', false, INPUT::TYPE_BOOL );
		$theme          = Input::post( 'theme', 'light', INPUT::TYPE_STRING );
		$font_scale     = Input::post( 'font_scale', 100, INPUT::TYPE_INT );

		$preferences_settings = array(
			'auto_play_next' => $auto_play_next,
			'theme'          => $theme,
			'font_scale'     => $font_scale,
		);

		$preference_data = $this->save_preferences( $preferences_settings, get_current_user_id() );

		if ( false === $preference_data ) {
			$this->json_response(
				'Something went wrong!!',
				null,
				HttpHelper::STATUS_NOT_FOUND
			);
		}

		$this->json_response(
			__( 'Preferences saved successfully', 'tutor' ),
			$preference_data
		);
	}
}
