<?php
/**
 * User preference manager
 *
 * Handles storing and retrieving per–user preferences (e.g. theme, playback, font scale)
 * in a reusable and extensible way using WordPress user meta.
 *
 * @package Tutor\UserPreference
 * @author Themeum <support@themeum.com>
 * @link https://www.themeum.com/
 * @since 4.0.0
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

use Tutor\Cache\TutorCache;
use Tutor\Helpers\HttpHelper;
use Tutor\Options_V2;
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
	 * Theme option: light.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	const THEME_LIGHT = 'light';

	/**
	 * Theme option: dark.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	const THEME_DARK = 'dark';

	/**
	 * Theme option: system.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	const THEME_SYSTEM = 'system';

	/**
	 * Default theme value.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	const DEFAULT_THEME = self::THEME_LIGHT;

	/**
	 * Available theme options.
	 *
	 * @since 4.0.0
	 *
	 * @var array<string>
	 */
	const THEMES = array( self::THEME_LIGHT, self::THEME_DARK, self::THEME_SYSTEM );

	/**
	 * Default font scale value.
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	const DEFAULT_FONT_SCALE = 100;

	/**
	 * Available font scale options.
	 *
	 * @since 4.0.0
	 *
	 * @var array<int>
	 */
	const FONT_SCALE_OPTIONS = array( 70, 80, 90, 100, 110, 120 );

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

		add_action( 'wp_ajax_tutor_save_user_preferences', array( $this, 'ajax_save_user_preferences' ) );
		add_action( 'wp_ajax_tutor_reset_user_preferences', array( $this, 'ajax_reset_user_preferences' ) );
		add_filter( 'body_class', array( $this, 'add_theme_attribute' ) );
		add_action( 'wp_head', array( $this, 'apply_font_scale' ) );
	}

	/**
	 * Apply font scale to the document root.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function apply_font_scale() {
		if ( ! tutor_utils()->is_dashboard_page() && ! tutor_utils()->is_learning_area() ) {
			return;
		}
		$prefs          = $this->get_preferences();
		$font_scale     = isset( $prefs['font_scale'] ) ? (int) $prefs['font_scale'] : self::DEFAULT_FONT_SCALE;
		$base_font_size = 16;
		$font_size      = ( $base_font_size * $font_scale ) / self::DEFAULT_FONT_SCALE;
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
		$theme = isset( $prefs['theme'] ) ? (string) $prefs['theme'] : self::DEFAULT_THEME;
		if ( ! in_array( $theme, self::THEMES, true ) ) {
			$theme = self::DEFAULT_THEME;
		}
		echo ' data-tutor-theme="' . esc_attr( $theme ) . '"';
		return $classes;
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
	 * @return array
	 */
	public static function get_preferences( $user_id = 0 ) {
		$user_id = tutor_utils()->get_user_id( $user_id );
		if ( ! $user_id ) {
			return array();
		}

		// Check cache first.
		$cache_key = 'get_preferences_' . $user_id;
		$cached    = TutorCache::get( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		// Get from database.
		$preferences = get_user_meta( $user_id, self::META_KEY, true );
		if ( ! is_array( $preferences ) ) {
			$preferences = array();
		}
		$result = array_merge( self::get_default_preferences(), $preferences );

		// Store in cache.
		TutorCache::set( $cache_key, $result );

		return $result;
	}

	/**
	 * Get a single preference value with default support.
	 *
	 * @since 4.0.0
	 *
	 * @param string     $key      Preference key.
	 * @param mixed      $fallback Default value if not set. If false, use internal defaults.
	 * @param int|string $user_id  Optional user id.
	 *
	 * @return mixed
	 */
	public static function get( $key, $fallback = false, $user_id = 0 ) {
		$prefs = self::get_preferences( $user_id );
		return isset( $prefs[ $key ] ) ? $prefs[ $key ] : $fallback;
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

		// Get from database.
		$current_preferences = get_user_meta( $user_id, self::META_KEY, true );
		if ( ! is_array( $current_preferences ) ) {
			$current_preferences = array();
		}

		$combined_preferences = array_merge( $current_preferences, $prefs );

		$preferences = apply_filters( 'tutor_user_preference_data', $combined_preferences, $user_id );

		update_user_meta( $user_id, self::META_KEY, $preferences );

		do_action( 'tutor_user_preference_after_saved', $user_id, $preferences );

		return $preferences;
	}

	/**
	 * AJAX handler: save current user's preferences.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function ajax_save_user_preferences() {
		tutor_utils()->check_nonce();

		$auto_play_next = Input::post( 'auto_play_next', null );
		$theme          = Input::post( 'theme', null );
		$font_scale     = Input::post( 'font_scale', null );
		$learning_mood  = Input::post( 'learning_mood', null );

		$preferences_settings = array();

		if ( null !== $auto_play_next ) {
			$default_auto_play_next = (bool) tutor_utils()->get_option( 'autoload_next_course_content' );
			$auto_play_next         = 'true' === $auto_play_next ? true : false;
			if ( $auto_play_next !== $default_auto_play_next ) {
				$preferences_settings['auto_play_next'] = $auto_play_next;
			}
		}

		if ( null !== $theme ) {
			$preferences_settings['theme'] = $theme;
		}

		if ( null !== $font_scale ) {
			$preferences_settings['font_scale'] = (int) $font_scale;
		}

		if ( null !== $learning_mood ) {
			// Validate learning_mood against allowed values.
			$allowed_moods = array( Options_V2::LEARNING_MODE_MODERN, Options_V2::LEARNING_MODE_KIDS );
			if ( ! in_array( $learning_mood, $allowed_moods, true ) ) {
				$learning_mood = Options_V2::LEARNING_MODE_MODERN;
			}
			$default_learning_mood = tutor_utils()->get_option( 'learning_mode', Options_V2::LEARNING_MODE_MODERN );
			if ( $learning_mood !== $default_learning_mood ) {
				$preferences_settings['learning_mood'] = $learning_mood;
			}
		}

		if ( empty( $preferences_settings ) ) {
			$this->json_response(
				__( 'No changes detected', 'tutor' ),
				null,
				HttpHelper::STATUS_OK
			);
		}

		$preference_data = $this->save_preferences( $preferences_settings, get_current_user_id() );

		if ( false === $preference_data ) {
			$this->json_response(
				__( 'Failed to save preferences', 'tutor' ),
				null,
				HttpHelper::STATUS_NOT_FOUND
			);
		}

		$this->json_response(
			__( 'Preferences saved successfully', 'tutor' ),
			$preference_data
		);
	}

	/**
	 * AJAX handler: reset current user's preferences back to defaults.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function ajax_reset_user_preferences() {
		tutor_utils()->check_nonce();

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			$this->json_response(
				__( 'Failed to reset preferences', 'tutor' ),
				null,
				HttpHelper::STATUS_NOT_FOUND
			);
		}

		// Delete user meta.
		delete_user_meta( $user_id, self::META_KEY );

		$this->json_response(
			__( 'Preferences reset successfully', 'tutor' ),
			null
		);
	}

	/**
	 * Get default preferences.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	private static function get_default_preferences() {
		// Get defaults with filter.
		$defaults = apply_filters(
			'tutor_user_preference_defaults',
			array(
				'auto_play_next' => (bool) tutor_utils()->get_option( 'autoload_next_course_content' ),
				'theme'          => self::DEFAULT_THEME,
				'font_scale'     => self::DEFAULT_FONT_SCALE,
				'learning_mood'  => tutor_utils()->get_option( 'learning_mode', Options_V2::LEARNING_MODE_MODERN ),
			)
		);

		return $defaults;
	}

	/**
	 * Get theme options for UI selects.
	 *
	 * @since 4.0.0
	 *
	 * @return array<int,array{label:string,value:string}>
	 */
	public static function get_theme_options() {
		return array(
			array(
				'label' => __( 'Light', 'tutor' ),
				'value' => self::THEME_LIGHT,
			),
			array(
				'label' => __( 'Dark', 'tutor' ),
				'value' => self::THEME_DARK,
			),
			array(
				'label' => __( 'System Default', 'tutor' ),
				'value' => self::THEME_SYSTEM,
			),
		);
	}

	/**
	 * Get learning mood options for UI selects.
	 *
	 * @since 4.0.0
	 *
	 * @return array<int,array{label:string,value:string}>
	 */
	public static function get_learning_mood_options() {
		return array(
			array(
				'label' => __( 'Modern', 'tutor' ),
				'value' => Options_V2::LEARNING_MODE_MODERN,
			),
			array(
				'label' => __( 'Kids', 'tutor' ),
				'value' => Options_V2::LEARNING_MODE_KIDS,
			),
		);
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
		$values  = apply_filters( 'tutor_user_preference_font_scale_values', self::FONT_SCALE_OPTIONS );
		$options = array();
		foreach ( $values as $value ) {
			$value     = (int) $value;
			$options[] = array(
				'label' => $value . '%',
				'value' => $value,
			);
		}
		return $options;
	}
}
