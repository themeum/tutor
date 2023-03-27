<?php
/**
 * Tutor Utils Helper functions
 *
 * @package Tutor\Utils
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

use Tutor\Cache\TutorCache;
use Tutor\Helpers\QueryHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility methods
 *
 * @since 1.0.0
 */
class Utils {

	/**
	 * Compatibility for splitting utils functions to specific model
	 *
	 * @param string $method
	 * @param array  $args
	 * @return void
	 *
	 * @since 2.0.6
	 */
	public function __call( $method, $args ) {
		$classes = array(
			'Tutor\Models\CourseModel',
			'Tutor\Models\LessonModel',
			'Tutor\Models\QuizModel',
			'Tutor\Models\WithdrawModel',
		);

		foreach ( $classes as $class ) {
			if ( method_exists( $obj = new $class(), $method ) ) {
				return $obj->$method( ...$args );
			}
		}
	}

	/**
	 * Check an array is sequential or associative
	 *
	 * @param   array $array The array to check.
	 * @return  bool   true if the array is associative, false if it's sequential.
	 *
	 * @since 2.0.9
	 */
	public function is_assoc( array $array ) {
		return array_keys( $array ) !== range( 0, count( $array ) - 1 );
	}

	/**
	 * Redirect to URL
	 *
	 * @param string $url
	 * @return void
	 *
	 * @since 2.1.0
	 */
	public function redirect_to( string $url, $flash_message = null, $flash_type = 'success' ) {
		$url = trim( $url );
		if ( filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
			wp_die( 'Not a valid URL for redirect' );
		}

		$available_types = array( 'success', 'error' );
		if ( ! empty( $flash_message ) && in_array( $flash_type, $available_types ) ) {
			set_transient( 'tutor_flash_type', $flash_type );
			set_transient( 'tutor_flash_message', $flash_message );
		}

		if ( ! headers_sent() ) {
			wp_safe_redirect( $url );
		} else {
			echo '<script>window.location.href = ' . "'" . esc_url( $url ) . "';" . '</script>';
		}

		exit;
	}

	/**
	 * Handle flash message for redirect_to util helper
	 *
	 * @return void
	 * @since 2.1.0
	 */
	public function handle_flash_message() {
		if ( false !== get_transient( 'tutor_flash_type' ) && false !== get_transient( 'tutor_flash_message' ) ) {
			$type    = get_transient( 'tutor_flash_type' );
			$message = get_transient( 'tutor_flash_message' );
			if ( 'success' === $type && ! empty( $message ) ) {
				?>
				<script type="text/javascript">
					window.onload = function(){
						const { __ } = wp.i18n;
						tutor_toast( __( 'Success!', 'tutor' ), '<?php echo esc_html( $message ); ?>', 'success' )
					};
				</script>
				<?php
			}
			if ( 'error' === $type && ! empty( $message ) ) {
				?>
				<script type="text/javascript">
					window.onload = function(){
						const { __ } = wp.i18n;
						tutor_toast( __( 'Error!', 'tutor' ), '<?php echo esc_html( $message ); ?>', 'error' )
					};
				</script>
				<?php
			}

			// delete flash message
			delete_transient( 'tutor_flash_type' );
			delete_transient( 'tutor_flash_message' );
		}
	}

	/**
	 * Add setting's option after a setting key
	 *
	 * @param string $target_key    setting's key name like 'tutor_version'
	 * @param array  $arr           an multi-dimentional settings option array
	 * @param array  $new_item      new setting array. a 'key' needed
	 *
	 * @return int|null             inserted index number or null
	 *
	 * @since 2.1.0
	 */
	public function add_option_after( string $target_key, array &$arr, array $new_item ) {
		if ( ! is_array( $arr ) || ! is_array( $new_item ) ) {
			return;
		}

		$found_index = null;
		foreach ( $arr as $index => $inner_arr ) {
			if ( is_array( $inner_arr ) && array_key_exists( 'key', $inner_arr ) && $inner_arr['key'] == $target_key ) {
				$found_index = $index;
				break;
			}
		}

		if ( $found_index !== null && array_key_exists( 'key', $new_item ) ) {
			$target_index = $found_index + 1;
			array_splice( $arr, $target_index, 0, array( $new_item ) );
			return $target_index;
		}
	}

	/**
	 * Get human readable file size from file path
	 *
	 * @param string $file_path
	 * @return string
	 *
	 * @since 2.1.0
	 */
	public function get_readable_filesize( string $file_path ) {
		return size_format( file_exists( $file_path ) ? filesize( $file_path ) : 0 );
	}

	private function option_recursive( $array, $key ) {
		foreach ( $array as $option ) {
			$is_array = is_array( $option );

			if ( $is_array && isset( $option['key'], $option['default'] ) && $option['key'] == $key ) {
				$value                               = $option['default'];
				$option['default'] == 'on' ? $value  = true : 0;
				$option['default'] == 'off' ? $value = false : 0;

				return $value;
			}

			$value = $is_array ? $this->option_recursive( $option, $key ) : null;

			if ( ! ( $value === null ) ) {
				return $value;
			}
		}

		return null;
	}

	private function get_option_default( $key, $fallback, $from_options ) {
		if ( ! $from_options ) {
			// Avoid infinity recursion
			return $fallback;
		}

		$tutor_options_array                                      = ( new Options_V2( false ) )->get_setting_fields();
		! is_array( $tutor_options_array ) ? $tutor_options_array = array() : 0;

		$default_value = $this->option_recursive( $tutor_options_array, $key );

		return $default_value === null ? $fallback : $default_value;
	}

	/**
	 * Get option data
	 *
	 * @param string $key
	 * @param bool   $default
	 * @param bool   $type if false return string
	 *
	 * @return array|bool|mixed
	 *
	 * @since v.1.0.0
	 */
	public function get_option( $key, $default = false, $type = true, $from_options = false ) {
		$option = (array) maybe_unserialize( get_option( 'tutor_option' ) );

		if ( empty( $option ) || ! is_array( $option ) ) {
			// If the option array is not yet stored on database, then return default/fallback
			return $this->get_option_default( $key, $default, $from_options );
		}

		// Get option value by option key
		if ( array_key_exists( $key, $option ) ) {
			// Convert off/on switch values to boolean
			$value = $option[ $key ];

			if ( true == $type ) {
				$value == 'off' ? $value = false : 0;
				$value == 'on' ? $value  = true : 0;
			}

			return apply_filters( $key, $value );
		}

		// Access array value via dot notation, such as option->get('value.subvalue')
		if ( strpos( $key, '.' ) ) {
			$option_key_array = explode( '.', $key );

			$new_option = $option;
			foreach ( $option_key_array as $dotKey ) {
				if ( isset( $new_option[ $dotKey ] ) ) {
					$new_option = $new_option[ $dotKey ];
				} else {
					return $this->get_option_default( $key, $default, $from_options );
				}
			}

			// Convert off/on switch values to boolean
			$value = $new_option;

			if ( true == $type ) {
				$value == 'off' ? $value = false : 0;
				$value == 'on' ? $value  = true : 0;
			}

			return apply_filters( $key, $value );
		}

		return $this->get_option_default( $key, $default, $from_options );
	}

	/**
	 * @param null $key
	 * @param bool $value
	 *
	 * Update Option
	 *
	 * @since v.1.0.0
	 */
	public function update_option( $key = null, $value = false ) {
		$option         = (array) maybe_unserialize( get_option( 'tutor_option' ) );
		$option[ $key ] = $value;
		update_option( 'tutor_option', $option );
	}

	/**
	 * @param null  $key
	 * @param array $array
	 *
	 * @return array|bool|mixed
	 *
	 * get array value by dot notation
	 *
	 * @since v.1.0.0
	 *
	 * @update v.1.4.1 (Added default parameter)
	 */
	public function avalue_dot( $key = null, $array = array(), $default = false ) {
		$array = (array) $array;
		if ( ! $key || ! count( $array ) ) {
			return $default;
		}
		$option_key_array = explode( '.', $key );

		$value = $array;

		foreach ( $option_key_array as $dotKey ) {
			if ( isset( $value[ $dotKey ] ) ) {
				$value = $value[ $dotKey ];
			} else {
				return $default;
			}
		}
		return $value;
	}

	/**
	 * @param null  $key
	 * @param array $array
	 *
	 * @return array|bool|mixed
	 *
	 * alias of avalue_dot method of utils
	 *
	 * Get array value by key and recursive array value by dot notation key
	 *
	 * ex: $this->array_get('key.child_key', $array);
	 *
	 * @since v.1.3.3
	 */
	public function array_get( $key = null, $array = array(), $default = false ) {
		return $this->avalue_dot( $key, $array, $default );
	}

	/**
	 * @return array
	 *
	 * Get all pages
	 *
	 * @since v.1.0.0
	 */
	public function get_pages() {
		do_action( 'tutor_utils/get_pages/before' );

		$pages    = array();
		$wp_pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'numberposts' => -1,
			)
		);

		if ( is_array( $wp_pages ) && count( $wp_pages ) ) {
			foreach ( $wp_pages as $page ) {
				$pages[ $page->ID ] = $page->post_title;
			}
		}

		do_action( 'tutor_utils/get_pages/after' );

		return $pages;
	}
	/**
	 * @return array
	 *
	 * Get all pages
	 *
	 * @since v.1.0.0
	 */

	public function get_not_translated_pages() {
		do_action( 'tutor_utils/get_pages/before' );

		$pages = array();

		$wp_pages = get_posts(
			array(
				'post_type'        => 'page',
				'suppress_filters' => true,
				'post_status'      => 'publish',
				'numberposts'      => -1,
			)
		);

		if ( is_array( $wp_pages ) && count( $wp_pages ) ) {
			foreach ( $wp_pages as $page ) {
				$translate_id = icl_object_id( $page->ID, 'page', true, ICL_LANGUAGE_CODE );
				if ( $page->ID === $translate_id ) {
					$pages[ $page->ID ] = $page->post_title;
				}
			}
		}

		do_action( 'tutor_utils/get_pages/after' );

		return $pages;
	}

	/**
	 * @return string
	 *
	 * Get course archive URL
	 *
	 * @since v.1.0.0
	 */
	public function course_archive_page_url() {
		$course_post_type = tutor()->course_post_type;
		$course_page_url  = trailingslashit( home_url() ) . $course_post_type;

		$course_archive_page = $this->get_option( 'course_archive_page' );
		if ( $course_archive_page && $course_archive_page !== '-1' ) {
			$course_page_url = get_permalink( $course_archive_page );
		}
		return trailingslashit( $course_page_url );
	}

	/**
	 * Get profile URL.
	 * 
	 * @since 1.0.0
	 * @since 2.1.7 changed param $student_id to $user.
	 * 
	 * @param int|object $student     student ID or object.
	 * @param bool $instructor_view   instractior view.
	 * @param string $fallback_url    fallback URL.
	 * 
	 * @return string
	 */
	public function profile_url( $user = 0, $instructor_view = false, $fallback_url = '#' ) {
		$instructor_profile = $this->get_option( 'public_profile_layout' ) != 'private';
		$student_profile    = $this->get_option( 'student_public_profile_layout' ) != 'private';
		if ( ( $instructor_view && ! $instructor_profile ) || ( ! $instructor_view && ! $student_profile ) ) {
			return $fallback_url;
		}

		$site_url   = trailingslashit( home_url() ) . 'profile/';
		if ( ! is_object( $user ) ) {
			$user = get_userdata( $this->get_user_id( $user ) );
		}

		$user_name = ( is_object( $user ) && isset( $user->user_nicename ) ) ? $user->user_nicename : 'user_name';

		return add_query_arg( array( 'view' => $instructor_view ? 'instructor' : 'student' ), $site_url . $user_name );
	}

	/**
	 * @param string $user_nicename
	 *
	 * @return array|null|object
	 *
	 * Get user by user login
	 *
	 * @since v.1.0.0
	 */
	public function get_user_by_login( $user_nicename = '' ) {
		global $wpdb;
		$user_nicename = sanitize_text_field( $user_nicename );
		$user          = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
							FROM 	{$wpdb->users}
							WHERE 	user_nicename = %s;
							",
				$user_nicename
			)
		);
		return $user;
	}

	/**
	 * @return bool
	 *
	 * Check if WooCommerce Activated
	 *
	 * @since v.1.0.0
	 * @updated @1.5.9
	 */
	public function has_wc() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * @return bool
	 *
	 * determine if EDD plugin activated
	 *
	 * @since v.1.0.0
	 */
	public function has_edd() {
		 return $this->is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' );
	}

	/**
	 * @return bool
	 *
	 * Determine if PMPro is activated
	 *
	 * @since v.1.3.6
	 */
	public function has_pmpro( $check_monetization = false ) {
		$has_pmpro = $this->is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' );
		return $has_pmpro && ( ! $check_monetization || get_tutor_option( 'monetize_by' ) == 'pmpro' );
	}

	public function is_plugin_active( $plugin_path ) {
		$activated_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		$depends           = is_array( $plugin_path ) ? $plugin_path : array( $plugin_path );
		$has_plugin        = count( array_intersect( $depends, $activated_plugins ) ) == count( $depends );

		return $has_plugin;
	}

	public function has_wcs() {
		 $has_wcs = $this->is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' );
		return $has_wcs;
	}

	public function is_addon_enabled( $basename ) {
		if ( $this->is_plugin_active( 'tutor-pro/tutor-pro.php' ) ) {
			$addonConfig = $this->get_addon_config( $basename );

			return (bool) $this->avalue_dot( 'is_enable', $addonConfig );
		}
	}

	/**
	 * @return bool
	 *
	 * checking if BuddyPress exists and activated;
	 *
	 * @since v.1.4.8
	 */
	public function has_bp() {
		$activated_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		$depends           = array( 'buddypress/bp-loader.php' );
		$has_bp            = count( array_intersect( $depends, $activated_plugins ) ) == count( $depends );
		return $has_bp;
	}

	/**
	 * @return mixed
	 *
	 * @since v.1.0.0
	 */
	public function languages() {
		$language_codes = array(
			'en' => 'English',
			'aa' => 'Afar',
			'ab' => 'Abkhazian',
			'af' => 'Afrikaans',
			'am' => 'Amharic',
			'ar' => 'Arabic',
			'as' => 'Assamese',
			'ay' => 'Aymara',
			'az' => 'Azerbaijani',
			'ba' => 'Bashkir',
			'be' => 'Byelorussian',
			'bg' => 'Bulgarian',
			'bh' => 'Bihari',
			'bi' => 'Bislama',
			'bn' => 'Bengali/Bangla',
			'bo' => 'Tibetan',
			'br' => 'Breton',
			'ca' => 'Catalan',
			'co' => 'Corsican',
			'cs' => 'Czech',
			'cy' => 'Welsh',
			'da' => 'Danish',
			'de' => 'German',
			'dz' => 'Bhutani',
			'el' => 'Greek',
			'eo' => 'Esperanto',
			'es' => 'Spanish',
			'et' => 'Estonian',
			'eu' => 'Basque',
			'fa' => 'Persian',
			'fi' => 'Finnish',
			'fj' => 'Fiji',
			'fo' => 'Faeroese',
			'fr' => 'French',
			'fy' => 'Frisian',
			'ga' => 'Irish',
			'gd' => 'Scots/Gaelic',
			'gl' => 'Galician',
			'gn' => 'Guarani',
			'gu' => 'Gujarati',
			'ha' => 'Hausa',
			'hi' => 'Hindi',
			'hr' => 'Croatian',
			'hu' => 'Hungarian',
			'hy' => 'Armenian',
			'ia' => 'Interlingua',
			'ie' => 'Interlingue',
			'ik' => 'Inupiak',
			'in' => 'Indonesian',
			'is' => 'Icelandic',
			'it' => 'Italian',
			'iw' => 'Hebrew',
			'ja' => 'Japanese',
			'ji' => 'Yiddish',
			'jw' => 'Javanese',
			'ka' => 'Georgian',
			'kk' => 'Kazakh',
			'kl' => 'Greenlandic',
			'km' => 'Cambodian',
			'kn' => 'Kannada',
			'ko' => 'Korean',
			'ks' => 'Kashmiri',
			'ku' => 'Kurdish',
			'ky' => 'Kirghiz',
			'la' => 'Latin',
			'ln' => 'Lingala',
			'lo' => 'Laothian',
			'lt' => 'Lithuanian',
			'lv' => 'Latvian/Lettish',
			'mg' => 'Malagasy',
			'mi' => 'Maori',
			'mk' => 'Macedonian',
			'ml' => 'Malayalam',
			'mn' => 'Mongolian',
			'mo' => 'Moldavian',
			'mr' => 'Marathi',
			'ms' => 'Malay',
			'mt' => 'Maltese',
			'my' => 'Burmese',
			'na' => 'Nauru',
			'ne' => 'Nepali',
			'nl' => 'Dutch',
			'no' => 'Norwegian',
			'oc' => 'Occitan',
			'om' => '(Afan)/Oromoor/Oriya',
			'pa' => 'Punjabi',
			'pl' => 'Polish',
			'ps' => 'Pashto/Pushto',
			'pt' => 'Portuguese',
			'qu' => 'Quechua',
			'rm' => 'Rhaeto-Romance',
			'rn' => 'Kirundi',
			'ro' => 'Romanian',
			'ru' => 'Russian',
			'rw' => 'Kinyarwanda',
			'sa' => 'Sanskrit',
			'sd' => 'Sindhi',
			'sg' => 'Sangro',
			'sh' => 'Serbo-Croatian',
			'si' => 'Singhalese',
			'sk' => 'Slovak',
			'sl' => 'Slovenian',
			'sm' => 'Samoan',
			'sn' => 'Shona',
			'so' => 'Somali',
			'sq' => 'Albanian',
			'sr' => 'Serbian',
			'ss' => 'Siswati',
			'st' => 'Sesotho',
			'su' => 'Sundanese',
			'sv' => 'Swedish',
			'sw' => 'Swahili',
			'ta' => 'Tamil',
			'te' => 'Tegulu',
			'tg' => 'Tajik',
			'th' => 'Thai',
			'ti' => 'Tigrinya',
			'tk' => 'Turkmen',
			'tl' => 'Tagalog',
			'tn' => 'Setswana',
			'to' => 'Tonga',
			'tr' => 'Turkish',
			'ts' => 'Tsonga',
			'tt' => 'Tatar',
			'tw' => 'Twi',
			'uk' => 'Ukrainian',
			'ur' => 'Urdu',
			'uz' => 'Uzbek',
			'vi' => 'Vietnamese',
			'vo' => 'Volapuk',
			'wo' => 'Wolof',
			'xh' => 'Xhosa',
			'yo' => 'Yoruba',
			'zh' => 'Chinese',
			'zu' => 'Zulu',
		);

		return apply_filters( 'tutor/utils/languages', $language_codes );
	}

	/**
	 * @param string $value
	 *
	 * Check raw data
	 *
	 * @since v.1.0.0
	 */
	public function print_view( $value = '' ) {
		echo '<pre>';
		print_r( $value );
		echo '</pre>';
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return int
	 *
	 * Get completed lesson total number by a course
	 *
	 * @since v.1.0.0
	 */
	public function get_completed_lesson_count_by_course( $course_id = 0, $user_id = 0 ) {
		global $wpdb;
		$course_id = $this->get_post_id( $course_id );
		$user_id   = $this->get_user_id( $user_id );

		$lesson_ids = $this->get_course_content_ids_by( tutor()->lesson_post_type, tutor()->course_post_type, $course_id );
		$count      = 0;
		if ( count( $lesson_ids ) ) {
			$completed_lesson_meta_ids = array();
			foreach ( $lesson_ids as $lesson_id ) {
				$completed_lesson_meta_ids[] = '_tutor_completed_lesson_id_' . $lesson_id;
			}
			$in_ids = implode( "','", $completed_lesson_meta_ids );

			$prepare_ids = str_replace( "','", '', $in_ids );
			$cache_key = "tutor_get_completed_lesson_count_by{$user_id}_{$prepare_ids}";
			$count = TutorCache::get( $cache_key );

			if ( false === $count ) {
				$count = (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT count(umeta_id)
					FROM	{$wpdb->usermeta}
					WHERE	user_id = %d
							AND meta_key IN ('{$in_ids}')
					",
						$user_id
					)
				);
				TutorCache::set( $cache_key, $count );
			}
		}

		return $count;
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return float|int
	 *
	 * @since v.1.0.0
	 * @updated v.1.6.1
	 */
	public function get_course_completed_percent( $course_id = 0, $user_id = 0, $get_stats = false ) {
		$course_id        = $this->get_post_id( $course_id );
		$user_id          = $this->get_user_id( $user_id );
		$completed_lesson = $this->get_completed_lesson_count_by_course( $course_id, $user_id );
		$course_contents  = $this->get_course_contents_by_id( $course_id );
		$totalContents    = $this->count( $course_contents );
		$totalContents    = $totalContents ? $totalContents : 0;
		$completedCount   = $completed_lesson;

		$quiz_ids 		= array();
		$assignment_ids = array();

		foreach ( $course_contents as $content ) {
			if ( 'tutor_quiz' === $content->post_type ) {
				$quiz_ids[] = (int) $content->ID;
			}
			if ( 'tutor_assignments' === $content->post_type ) {
				$assignment_ids[] = (int) $content->ID;
			}
		}

		global $wpdb;

		if ( count( $quiz_ids ) ) {
			$quiz_ids_str   = QueryHelper::prepare_in_clause( $quiz_ids );

			// Get data from cache.
			$prepare_quiz_ids_str     = str_replace( ',', '_', $quiz_ids_str );
			$quiz_completed_cache_key = "tutor_quiz_completed_{$user_id}_{$prepare_quiz_ids_str}";
			$quiz_completed           = TutorCache::get( $quiz_completed_cache_key );

			if ( false === $quiz_completed ) {
				$quiz_completed = (int) $wpdb->get_var( 
					$wpdb->prepare(
						"SELECT count(quiz_id) completed 
						FROM (
							SELECT  DISTINCT quiz_id, course_id, attempt_status 
							FROM 	{$wpdb->tutor_quiz_attempts} 
							WHERE 	quiz_id IN ({$quiz_ids_str}) 
									AND user_id = % d 
									AND attempt_status != %s
						) a", $user_id, 'attempt_started' )
				);
				// Set cache data.
				TutorCache::set( $quiz_completed_cache_key, $quiz_completed );
			}
			$completedCount += $quiz_completed;
		}
		
		if ( count( $assignment_ids ) ) {
			$assignment_ids_str   = QueryHelper::prepare_in_clause( $assignment_ids );

			// Get data from cache.
			$prepare_assignment_ids_str     = str_replace( ',', '_', $assignment_ids_str );
			$assignment_submitted_cache_key = "tutor_assignment_submitted{$user_id}_{$prepare_assignment_ids_str}";
			$assignment_submitted           = TutorCache::get( $assignment_submitted_cache_key );

			if ( false === $assignment_submitted ) {
				$assignment_submitted = (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT count(*) completed
						FROM 	{$wpdb->comments}
						WHERE 	comment_type = %s
								AND comment_approved = %s
								AND user_id = %d
								AND comment_post_ID IN({$assignment_ids_str});
						",
							'tutor_assignment',
							'submitted',
							$user_id
						)
				);
				TutorCache::set( $assignment_submitted_cache_key, $assignment_submitted );
			}
			$completedCount += $assignment_submitted;
		}

		if ( $this->count( $course_contents ) ) {
			foreach ( $course_contents as $content ) {
				if ( $content->post_type === 'tutor_zoom_meeting' ) {
					/**
					 * count zoom lesson completion for course progress
					 *
					 * @since 2.0.0
					 */
					$is_completed = apply_filters( 'tutor_is_zoom_lesson_done', false, $content->ID, $user_id );
					if ( $is_completed ) {
						$completedCount++;
					}
				} elseif ( $content->post_type === 'tutor-google-meet' ) {
					/**
					 * count zoom lesson completion for course progress
					 *
					 * @since 2.0.0
					 */
					$is_completed = apply_filters( 'tutor_google_meet_lesson_done', false, $content->ID, $user_id );
					if ( $is_completed ) {
						$completedCount++;
					}
				}
			}
		}

		$percent_complete = 0;

		if ( $totalContents > 0 && $completedCount > 0 ) {
			$percent_complete = number_format( ( $completedCount * 100 ) / $totalContents );
		}

		if ( $get_stats ) {
			return array(
				'completed_percent' => $percent_complete,
				'completed_count'   => $completedCount,
				'total_count'       => $totalContents,
			);
		}

		return $percent_complete;
	}

	/**
	 * @param int $course_id
	 *
	 * @return \WP_Query
	 *
	 * Get all topics by given course ID
	 *
	 * @since v.1.0.0
	 */
	public function get_topics( $course_id = 0 ) {
		$course_id = $this->get_post_id( $course_id );

		$args = array(
			'post_type'      => 'topics',
			'post_parent'    => $course_id,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'posts_per_page' => -1,
		);

		$query = new \WP_Query( $args );

		return $query;
	}

	/**
	 * @param $course_ID
	 *
	 * @return int
	 *
	 * Get next topic order id
	 *
	 * @since v.1.0.0
	 */
	public function get_next_topic_order_id( $course_ID, $content_id = null ) {
		global $wpdb;

		if ( $content_id ) {
			$existing_order = get_post_field( 'menu_order', $content_id );

			if ( $existing_order >= 0 ) {
				return $existing_order;
			}
		}

		$last_order = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(menu_order)
			FROM 	{$wpdb->posts}
			WHERE 	post_parent = %d
					AND post_type = %s;
			",
				$course_ID,
				'topics'
			)
		);

		return $last_order + 1;
	}

	/**
	 * @param $topic_ID
	 *
	 * @return int
	 *
	 * Get next course content order id
	 *
	 * @since v.1.0.0
	 */
	public function get_next_course_content_order_id( $topic_ID, $content_id = null ) {
		global $wpdb;

		if ( $content_id ) {
			$existing_order = get_post_field( 'menu_order', $content_id );

			if ( $existing_order >= 0 ) {
				return $existing_order;
			}
		}

		$last_order = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(menu_order)
			FROM	{$wpdb->posts}
			WHERE	post_parent = %d;
			",
				$topic_ID
			)
		);

		return is_numeric( $last_order ) ? $last_order + 1 : 0;
	}

	/**
	 * @param int $topics_id
	 * @param int $limit
	 *
	 * @return \WP_Query
	 *
	 * Get course content by topic
	 *
	 * @since v.1.0.0
	 */
	public function get_course_contents_by_topic( $topics_id = 0, $limit = 10 ) {
		$topics_id        = $this->get_post_id( $topics_id );
		$lesson_post_type = tutor()->lesson_post_type;
		$post_type        = array_unique( apply_filters( 'tutor_course_contents_post_types', array( $lesson_post_type, 'tutor_quiz' ) ) );

		$args = array(
			'post_type'      => $post_type,
			'post_parent'    => $topics_id,
			'posts_per_page' => $limit,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		);

		return new \WP_Query( $args );
	}

	/**
	 * @param string $request_method
	 *
	 * Check actions nonce
	 *
	 * @since v.1.0.0
	 */
	public function checking_nonce( $request_method = null ) {
		! $request_method ? $request_method = sanitize_text_field( $_SERVER['REQUEST_METHOD'] ) : 0;

		$data        = strtolower( $request_method ) === 'post' ? $_POST : $_GET;
		$nonce_value = sanitize_text_field( $this->array_get( tutor()->nonce, $data, null ) );
		$matched     = $nonce_value && wp_verify_nonce( $nonce_value, tutor()->nonce_action );

		if ( ! $matched ) {
			wp_send_json_error( array( 'message' => __( 'Nonce not matched. Action failed!', 'tutor' ) ) );
			exit;
		}
	}

	/**
	 * @param int $course_id
	 *
	 * @return bool
	 *
	 * @since v.1.0.0
	 */
	public function is_course_purchasable( $course_id = 0 ) {

		$course_id  = $this->get_post_id( $course_id );
		$price_type = $this->price_type( $course_id );
		if ( $price_type === 'free' ) {
			$is_paid = apply_filters( 'is_course_paid', false, $course_id );
			if ( ! $is_paid ) {
				return false;
			}
		}

		return apply_filters( 'is_course_purchasable', false, $course_id );
	}

	/**
	 * @param int $course_id
	 *
	 * @return null|string
	 *
	 * get course price in digits format if any
	 *
	 * @since v.1.0.0
	 */
	public function get_course_price( $course_id = 0 ) {
		$price      = null;
		$course_id  = $this->get_post_id( $course_id );
		$product_id = $this->get_course_product_id( $course_id );
		if ( $this->is_course_purchasable( $course_id ) ) {
			$monetize_by = $this->get_option( 'monetize_by' );
			if ( $this->has_wc() && $monetize_by === 'wc' ) {
				$product = wc_get_product( $product_id );
				if ( $product ) {
					$price = $product->get_price();
				}
			} elseif ( 'edd' === $monetize_by && function_exists( 'edd_price' ) ) {
				$download = new \EDD_Download( $product_id );
				$price    = \edd_price( $download->ID, false );
			}
		}
		return apply_filters( 'get_tutor_course_price', $price, $course_id );

	}

	/**
	 * @param int $course_id
	 *
	 * @return object
	 *
	 * Get raw course price and sale price of a course
	 * It could help you to calculate something
	 * Such as Calculate discount by regular price and sale price
	 *
	 * @since v.1.3.1
	 */
	public function get_raw_course_price( $course_id = 0 ) {
		$course_id = $this->get_post_id( $course_id );

		$prices = array(
			'regular_price' => 0,
			'sale_price'    => 0,
		);

		$monetize_by = $this->get_option( 'monetize_by' );

		$product_id = $this->get_course_product_id( $course_id );
		if ( $product_id ) {
			if ( $monetize_by === 'wc' && $this->has_wc() ) {
				$product = wc_get_product( $product_id );
				if ( $product ) {
					$prices['regular_price'] = $product->get_regular_price();
					$prices['sale_price']    = $product->get_sale_price();
				}
			} elseif ( $monetize_by === 'edd' && $this->has_edd() ) {
				$prices['regular_price'] = get_post_meta( $product_id, 'edd_price', true );
				$prices['sale_price']    = get_post_meta( $product_id, 'edd_price', true );
			}
		}

		return (object) $prices;
	}

	/**
	 * @param int $course_id
	 *
	 * @return mixed
	 *
	 * Get the course price type
	 *
	 * @since  v.1.3.5
	 */
	public function price_type( $course_id = 0 ) {
		$course_id = $this->get_post_id( $course_id );

		$price_type = get_post_meta( $course_id, '_tutor_course_price_type', true );
		return $price_type;
	}

	/**
	 * @param int $course_id
	 *
	 * @return array|bool|null|object
	 *
	 * Check if current user has been enrolled or not
	 *
	 * @since v.1.0.0
	 */
	public function is_enrolled( $course_id = 0, $user_id = 0 ) {
		global $wpdb;
		$course_id = $this->get_post_id( $course_id );
		$user_id   = $this->get_user_id( $user_id );
		$cache_key = "tutor_is_enrolled_{$course_id}_{$user_id}";

		do_action( 'tutor_is_enrolled_before', $course_id, $user_id );

		$get_enrolled_info = TutorCache::get( $cache_key );
		if ( false === $get_enrolled_info ) {
			$get_enrolled_info = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT ID,
						post_author,
						post_date,
						post_date_gmt,
						post_title
				FROM 	{$wpdb->posts}
				WHERE 	post_author>0 
						AND post_parent>0
						AND post_type = %s
						AND post_parent = %d
						AND post_author = %d
						AND post_status = %s;
				",
					'tutor_enrolled',
					$course_id,
					$user_id,
					'completed'
				)
			);
			TutorCache::set( $cache_key, $get_enrolled_info );
		}

		if ( $get_enrolled_info ) {
			return apply_filters( 'tutor_is_enrolled', $get_enrolled_info, $course_id, $user_id );
		}

		return false;
	}

	/**
	 * @param int $course_id
	 *
	 * @return array|bool|null|object
	 *
	 * Delete course progress
	 *
	 * @since v.1.9.5
	 */
	public function delete_course_progress( $course_id = 0, $user_id = 0 ) {
		global $wpdb;
		$course_id = $this->get_post_id( $course_id );
		$user_id   = $this->get_user_id( $user_id );

		// Delete Quiz submissions
		$attempts = \Tutor\Models\QuizModel::get_quiz_attempts_by_course_ids( $start = 0, $limit = 99999999, $course_ids = array( $course_id ), $search_filter = '', $course_filter = '', $date_filter = '', $order_filter = '', $user_id = $user_id, false, true );

		if ( is_array( $attempts ) ) {
			$attempt_ids = array_map(
				function ( $attempt ) {
					return is_object( $attempt ) ? $attempt->attempt_id : 0;
				},
				$attempts
			);

			$this->delete_quiz_attempt( $attempt_ids );
		}

		// Delete Course completion row
		$del_where = array(
			'user_id'         => $user_id,
			'comment_post_ID' => $course_id,
			'comment_type'    => 'course_completed',
			'comment_agent'   => 'TutorLMSPlugin',
		);
		$wpdb->delete( $wpdb->comments, $del_where );

		// Delete Completed lesson count
		$lesson_ids = $this->get_course_content_ids_by( tutor()->lesson_post_type, tutor()->course_post_type, $course_id );
		foreach ( $lesson_ids as $id ) {
			delete_user_meta( $user_id, '_tutor_completed_lesson_id_' . $id );
		}

		// Delete other addon-wise stuffs by hook, specially assignment.
		do_action( 'delete_tutor_course_progress', $course_id, $user_id );
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return array|bool|null|object|void
	 *
	 * Has any enrolled for a user in a course
	 *
	 * @since v.1.0.0
	 */
	public function has_any_enrolled( $course_id = 0, $user_id = 0 ) {
		$course_id = $this->get_post_id( $course_id );
		$user_id   = $this->get_user_id( $user_id );

		if ( is_user_logged_in() ) {
			global $wpdb;

			$getEnrolledInfo = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT ID,
						post_author,
						post_date,
						post_date_gmt,
						post_title
				FROM 	{$wpdb->posts}
				WHERE 	post_type = %s
						AND post_parent = %d
						AND post_author = %d;
				",
					'tutor_enrolled',
					$course_id,
					$user_id
				)
			);

			if ( $getEnrolledInfo ) {
				return $getEnrolledInfo;
			}
		}

		return false;
	}


	/**
	 * @param int $enrol_id
	 * @return array|bool|\WP_Post|null
	 *
	 * Get course by enrol id
	 *
	 * @since v.1.6.1
	 */
	public function get_course_by_enrol_id( $enrol_id = 0 ) {
		if ( ! $enrol_id ) {
			return false;
		}

		global $wpdb;

		$course_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_parent
			FROM	{$wpdb->posts}
			WHERE	post_type = %s
					AND ID = %d
			",
				'tutor_enrolled',
				$enrol_id
			)
		);

		if ( $course_id ) {
			return get_post( $course_id );
		}

		return null;
	}

	/**
	 * @param int $lesson_id
	 * @param int $user_id
	 *
	 * @return array|bool|null|object
	 *
	 * Get the course Enrolled confirmation by lesson ID
	 *
	 * @since v.1.0.0
	 */
	public function is_course_enrolled_by_lesson( $lesson_id = 0, $user_id = 0 ) {
		$lesson_id = $this->get_post_id( $lesson_id );
		$user_id   = $this->get_user_id( $user_id );
		$course_id = $this->get_course_id_by( 'lesson', $lesson_id );

		return $this->is_enrolled( $course_id );
	}

	/**
	 * @param int $lesson_id
	 *
	 * @return bool|mixed
	 *
	 * Get the course ID by Lesson
	 *
	 * @since v.1.0.0
	 *
	 * @updated v.1.4.8
	 * Added Legacy Supports
	 */
	public function get_course_id_by_lesson( $lesson_id = 0 ) {
		$lesson_id = $this->get_post_id( $lesson_id );
		$course_id = $this->get_course_id_by( 'lesson', $lesson_id );

		if ( ! $course_id ) {
			$course_id = $this->get_course_id_by_content( $lesson_id );
		}
		if ( ! $course_id ) {
			$course_id = 0;
		}

		return $course_id;
	}

	/**
	 * @param int $course_id
	 *
	 * @return bool|false|string
	 *
	 * Get first lesson of a course
	 *
	 * @since v.1.0.0
	 */
	public function get_course_first_lesson( $course_id = 0, $post_type = null ) {
		global $wpdb;

		$course_id = $this->get_post_id( $course_id );
		$user_id   = get_current_user_id();

		$lessons = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT items.ID
			FROM 	{$wpdb->posts} topic
					INNER JOIN {$wpdb->posts} items
							ON topic.ID = items.post_parent
			WHERE 	topic.post_parent = %d
					AND items.post_status = %s
					" . ( $post_type ? " AND items.post_type='{$post_type}' " : '' ) . '
			ORDER BY topic.menu_order ASC,
					items.menu_order ASC;
			',
				$course_id,
				'publish'
			)
		);

		$first_lesson = false;

		if ( $this->count( $lessons ) ) {
			if ( ! empty( $lessons[0] ) ) {
				$first_lesson = $lessons[0];
			}

			foreach ( $lessons as $lesson ) {
				$is_complete = get_user_meta( $user_id, "_tutor_completed_lesson_id_{$lesson->ID}", true );
				if ( ! $is_complete ) {
					$first_lesson = $lesson;
					break;
				}
			}

			if ( ! empty( $first_lesson->ID ) ) {
				return get_permalink( $first_lesson->ID );
			}
		}

		return false;
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool|array
	 *
	 * @since v.1.0.0
	 */
	public function get_video( $post_id = 0 ) {
		$post_id     = $this->get_post_id( $post_id );
		$attachments = get_post_meta( $post_id, '_video', true );
		if ( $attachments ) {
			$attachments = maybe_unserialize( $attachments );
		}
		return $attachments;
	}

	/**
	 * @param int   $post_id
	 * @param array $video_data
	 *
	 * @return bool
	 *
	 * Update the video Info
	 */
	public function update_video( $post_id = 0, $video_data = array() ) {
		$post_id = $this->get_post_id( $post_id );

		if ( is_array( $video_data ) && count( $video_data ) ) {
			update_post_meta( $post_id, '_video', $video_data );
		}
	}

	/**
	 * Get tutor attachment
	 *
	 * @param int    $post_id
	 * @param string $meta_key
	 *
	 * @return array
	 *
	 * @since v.1.0.0
	 */
	public function get_attachments( $post_id = 0, $meta_key = '_tutor_attachments' ) {
		$post_id         = $this->get_post_id( $post_id );
		$attachments     = maybe_unserialize( get_post_meta( $post_id, $meta_key, true ) );
		$attachments_arr = array();

		if ( is_array( $attachments ) && count( $attachments ) ) {
			foreach ( $attachments as $attachment ) {
				$data              = (array) $this->get_attachment_data( $attachment );
				$attachments_arr[] = (object) apply_filters( 'tutor/posts/attachments', $data );
			}
		}

		return $attachments_arr;
	}

	public function get_attachment_data( $attachment_id ) {
		$url       = wp_get_attachment_url( $attachment_id );
		$file_type = wp_check_filetype( $url );
		$ext       = $file_type['ext'];
		$title     = get_the_title( $attachment_id );

		$file_path  = get_attached_file( $attachment_id );
		$size_bytes = file_exists( $file_path ) ? filesize( $file_path ) : 0;
		$size       = size_format( $size_bytes, 2 );
		$type       = wp_ext2type( $ext );

		$icon       = 'default';
		$font_icons = apply_filters(
			'tutor_file_types_icon',
			array(
				'archive',
				'audio',
				'code',
				'default',
				'document',
				'interactive',
				'spreadsheet',
				'text',
				'video',
				'image',
			)
		);

		if ( $type && in_array( $type, $font_icons ) ) {
			$icon = $type;
		}

		$data = array(
			'post_id'    => $attachment_id,
			'id'         => $attachment_id,
			'url'        => $url,
			'name'       => $title . '.' . $ext,
			'title'      => $title,
			'ext'        => $ext,
			'size'       => $size,
			'size_bytes' => $size_bytes,
			'icon'       => $icon,
		);

		return (object) $data;
	}

	/**
	 * @param $seconds
	 *
	 * @return string
	 *
	 * return seconds to formatted playtime
	 *
	 * @since v.1.0.0
	 */
	public function playtime_string( $seconds ) {
		$sign    = ( ( $seconds < 0 ) ? '-' : '' );
		$seconds = round( abs( $seconds ) );
		$H       = (int) floor( $seconds / 3600 );
		$M       = (int) floor( ( $seconds - ( 3600 * $H ) ) / 60 );
		$S       = (int) round( $seconds - ( 3600 * $H ) - ( 60 * $M ) );
		return $sign . ( $H ? $H . ':' : '' ) . ( $H ? str_pad( $M, 2, '0', STR_PAD_LEFT ) : intval( $M ) ) . ':' . str_pad( $S, 2, 0, STR_PAD_LEFT );
	}

	/**
	 * @param $seconds
	 *
	 * @return array
	 *
	 * Get the playtime in array
	 *
	 * @since v.1.0.0
	 */
	public function playtime_array( $seconds ) {
		$run_time_format = array(
			'hours'   => '00',
			'minutes' => '00',
			'seconds' => '00',
		);

		if ( $seconds <= 0 ) {
			return $run_time_format;
		}

		$playTimeString = $this->playtime_string( $seconds );
		$timeInArray    = explode( ':', $playTimeString );

		$run_time_size = count( $timeInArray );
		if ( $run_time_size === 3 ) {
			$run_time_format['hours']   = $timeInArray[0];
			$run_time_format['minutes'] = $timeInArray[1];
			$run_time_format['seconds'] = $timeInArray[2];
		} elseif ( $run_time_size === 2 ) {
			$run_time_format['minutes'] = $timeInArray[0];
			$run_time_format['seconds'] = $timeInArray[1];
		}

		return $run_time_format;
	}

	/**
	 * @param $seconds
	 *
	 * @return string
	 *
	 * Convert seconds to human readable time
	 *
	 * @since v.1.0.0
	 */
	public function seconds_to_time_context( $seconds ) {
		$sign    = ( ( $seconds < 0 ) ? '-' : '' );
		$seconds = round( abs( $seconds ) );
		$H       = (int) floor( $seconds / 3600 );
		$M       = (int) floor( ( $seconds - ( 3600 * $H ) ) / 60 );
		$S       = (int) round( $seconds - ( 3600 * $H ) - ( 60 * $M ) );

		return $sign . ( $H ? $H . 'h ' : '' ) . ( $H ? str_pad( $M, 2, '0', STR_PAD_LEFT ) : intval( $M ) ) . 'm ' . str_pad( $S, 2, 0, STR_PAD_LEFT ) . 's';
	}

	/**
	 * Get human readable time
	 *
	 * @param string $from                  date time string value. Example: 2022-06-24 22:00:00
	 * @param string $to                    (optional) date time string value. Default value is current.
	 * @param string $format                format you want to print. Default: '%ad %hh %im %ss' Help: https://www.php.net/manual/en/dateinterval.format.php
	 * @param bool   $show_postfix_text     show postfix text like 'ago', 'left'
	 * @return string
	 *
	 * @since 2.0.7
	 */
	public function get_human_readable_time( $from, $to = null, $format = null, $show_postfix_text = true ) {
		$postfix_text = '';
		$wp_tz        = new \DateTimeZone( wp_timezone_string() );
		$fromDateTime = new \DateTime( $from, $wp_tz );
		$toDateTime   = $to === null ? new \DateTime( 'now', $wp_tz ) : new \DateTime( $to, $wp_tz );
		$format       = $format === null ? '%ad %hh %im %ss' : $format;

		if ( $toDateTime > $fromDateTime ) {
			$postfix_text = __( ' ago', 'tutor' );
		} else {
			$postfix_text = __( ' left', 'tutor' );
		}

		$timeSpan     = $toDateTime->diff( $fromDateTime );
		$postfix_text = $show_postfix_text === true ? $postfix_text : '';

		return $timeSpan->format( $format ) . $postfix_text;
	}

	/**
	 * Get video info
	 *
	 * @param int $lesson_id
	 * @return mixed bool return if video does not exits otherwise object return.
	 *
	 * @since v.1.0.0
	 */
	public function get_video_info( $lesson_id = 0 ) {
		$lesson_id = $this->get_post_id( $lesson_id );
		$video     = $this->get_video( $lesson_id );

		if ( ! $video ) {
			return false;
		}

		$info = array(
			'playtime' => '00:00',
		);

		$types = apply_filters(
			'tutor_video_types',
			array(
				'mp4'  => 'video/mp4',
				'webm' => 'video/webm',
				'ogg'  => 'video/ogg',
			)
		);

		$videoSource = $this->avalue_dot( 'source', $video );

		if ( $videoSource === 'html5' ) {
			$sourceVideoID = $this->avalue_dot( 'source_video_id', $video );
			$video_info    = get_post_meta( $sourceVideoID, '_wp_attachment_metadata', true );

			if ( $video_info && in_array( $this->array_get( 'mime_type', $video_info ), $types ) ) {
				$path             = get_attached_file( $sourceVideoID );
				$info['playtime'] = $video_info['length_formatted'];
				$info['path']     = $path;
				$info['url']      = wp_get_attachment_url( $sourceVideoID );
				$info['ext']      = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
				$info['type']     = $types[ $info['ext'] ];
			}
		}

		if ( $videoSource !== 'html5' ) {
			$video          = maybe_unserialize( get_post_meta( $lesson_id, '_video', true ) );
			$runtimeHours   = $this->avalue_dot( 'runtime.hours', $video );
			$runtimeMinutes = $this->avalue_dot( 'runtime.minutes', $video );
			$runtimeSeconds = $this->avalue_dot( 'runtime.seconds', $video );

			$runtimeHours   = $runtimeHours ? $runtimeHours : '00';
			$runtimeMinutes = $runtimeMinutes ? $runtimeMinutes : '00';
			$runtimeSeconds = $runtimeSeconds ? $runtimeSeconds : '00';

			$info['playtime'] = "$runtimeHours:$runtimeMinutes:$runtimeSeconds";
		}

		$info = array_merge( $info, $video );

		return (object) $info;
	}

	public function get_optimized_duration( $duration ) {
		/*
		 if(is_string($duration)){
			strpos($duration, '00:')===0 ? $duration=substr($duration, 3) : 0; // Remove Empty hour
			strpos($duration, '00:')===0 ? $duration=substr($duration, 3) : 0; // Remove empty minute
		} */

		return $this->course_content_time_format( $duration );
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool
	 *
	 * Ensure if attached video is self hosted or not
	 *
	 * @since v.1.0.0
	 */
	public function is_html5_video( $post_id = 0 ) {
		$post_id = $this->get_post_id( $post_id );
		$video   = $this->get_video( $post_id );

		if ( ! $video ) {
			return false;
		}

		$videoSource = $this->avalue_dot( 'source', $video );

		return $videoSource === 'html5';
	}

	/**
	 * @param int $lesson_id
	 * @param int $user_id
	 *
	 * @return bool|mixed
	 *
	 * @since v.1.0.0
	 */
	public function is_completed_lesson( $lesson_id = 0, $user_id = 0 ) {
		$lesson_id    = $this->get_post_id( $lesson_id );
		$user_id      = $this->get_user_id( $user_id );
		$is_completed = get_user_meta( $user_id, '_tutor_completed_lesson_id_' . $lesson_id, true );

		if ( $is_completed ) {
			return $is_completed;
		}

		return false;
	}

	/**
	 * Determine if a course completed
	 *
	 * @since v.1.0.0
	 * 
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 *
	 * @return array|bool|null|object
	 *
	 * @updated v.1.4.9
	 */
	public function is_completed_course( $course_id = 0, $user_id = 0 ) {

		global $wpdb;
		$course_id = $this->get_post_id( $course_id );
		$user_id   = $this->get_user_id( $user_id );

		$cache_key    = "tutor_is_completed_course_{$course_id}_{$user_id}";
		$is_completed = TutorCache::get( $cache_key );

		if ( false === $is_completed ) {
			$is_completed = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT comment_ID,
						comment_post_ID AS course_id,
						comment_author AS completed_user_id,
						comment_date AS completion_date,
						comment_content AS completed_hash
				FROM	{$wpdb->comments}
				WHERE 	comment_agent = %s
						AND comment_type = %s
						AND comment_post_ID = %d
						AND user_id = %d;
				",
					'TutorLMSPlugin',
					'course_completed',
					$course_id,
					$user_id
				)
			);
			TutorCache::set( $cache_key, $is_completed );
		}

		if ( $is_completed ) {
			return apply_filters( 'is_completed_course', $is_completed, $course_id, $user_id );
		}

		return apply_filters( 'is_completed_course', false, $course_id, $user_id );
	}

	/**
	 * @param array $input
	 *
	 * @return array
	 *
	 * Sanitize input array
	 *
	 * @since v.1.0.0
	 */
	public function sanitize_array( $input = array() ) {
		$array = array();

		if ( is_array( $input ) && count( $input ) ) {
			foreach ( $input as $key => $value ) {
				if ( is_array( $value ) ) {
					$array[ $key ] = $this->sanitize_array( $value );
				} else {
					$key           = sanitize_text_field( $key );
					$value         = sanitize_text_field( $value );
					$array[ $key ] = $value;
				}
			}
		}

		return $array;
	}

	/**
	 * @param int $post_id
	 *
	 * @return array|bool
	 *
	 * Determine if has any video in single
	 *
	 * @since v.1.0.0
	 */
	public function has_video_in_single( $post_id = 0 ) {
		if ( is_single() ) {
			$post_id = $this->get_post_id( $post_id );

			$video = $this->get_video( $post_id );
			if ( $video && $this->array_get( 'source', $video ) !== '-1' ) {

				$not_empty = ! empty( $video['source_video_id'] ) ||
					! empty( $video['source_external_url'] ) ||
					! empty( $video['source_youtube'] ) ||
					! empty( $video['source_vimeo'] ) ||
					! empty( $video['source_embedded'] ) ||
					! empty( $video['source_shortcode'] ) ||
					( isset( $video['source_bunnynet'] ) && ! empty( $video['source_bunnynet'] ) );

				return $not_empty ? $video : false;
			}
		}
		return false;
	}

	/**
	 * @param int    $start
	 * @param int    $limit
	 * @param string $search_term
	 * @param int    $course_id
	 *
	 * @return array|null|object
	 *
	 *
	 * Get the enrolled students for all courses.
	 *
	 * Pass course id in 4th parameter to get students course wise.
	 *
	 * @since v.1.0.0
	 */
	public function get_students( $start = 0, $limit = 10, $search_term = '', $course_id = '', $date = '', $order = 'DESC' ) {
		global $wpdb;

		$start       = sanitize_text_field( $start );
		$limit       = sanitize_text_field( $limit );
		$search_term = sanitize_text_field( $search_term );
		$course_id   = sanitize_text_field( $course_id );
		$date        = sanitize_text_field( $date );

		$course_query = '';
		if ( '' !== $course_id ) {
			$course_query = "AND posts.post_parent = {$course_id}";
		}

		$date_query = '';
		if ( '' !== $date ) {
			$date_query = "AND DATE(user.user_registered) = CAST('$date' AS DATE)";
		}

		$order_query     = "ORDER BY posts.post_date {$order}";
		$search_term_raw = $search_term;
		$search_term     = '%' . $wpdb->esc_like( $search_term ) . '%';

		$students = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user.* FROM {$wpdb->posts} AS posts
				INNER JOIN {$wpdb->users} AS user
				 	ON user.ID = posts.post_author
				WHERE posts.post_type = %s
					AND posts.post_status = %s
					{$course_query}
					{$date_query}
					AND (user.display_name LIKE %s OR user.user_email = %s OR user.user_login LIKE %s)
				GROUP BY post_author
				{$order_query}
				LIMIT %d, %d
			",
				'tutor_enrolled',
				'completed',
				$search_term,
				$search_term_raw,
				$search_term,
				$start,
				$limit
			)
		);

		return $students;
	}

	/**
	 * @return int
	 *
	 * @since v.1.0.0
	 *
	 * get the total students
	 * pass course id to get course wise total students
	 *
	 * @since v.1.0.0
	 */
	public function get_total_students( $search_term = '', $course_id = '', $date = '' ): int {
		global $wpdb;

		$search_term = sanitize_text_field( $search_term );
		$course_id   = sanitize_text_field( $course_id );
		$date        = sanitize_text_field( $date );

		$course_query = '';
		if ( '' !== $course_id ) {
			$course_query = "AND posts.post_parent = {$course_id}";
		}

		$date_query = '';
		if ( '' !== $date ) {
			$date_query = "AND DATE(user.user_registered) = CAST('$date' AS DATE)";
		}
		$search_term_raw = $search_term;
		$search_term     = '%' . $wpdb->esc_like( $search_term ) . '%';

		$students = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user.ID FROM {$wpdb->posts} AS posts
				INNER JOIN {$wpdb->users} AS user
				 	ON user.ID = posts.post_author
				WHERE posts.post_type = %s
					AND posts.post_status = %s
					{$course_query}
					{$date_query}
					AND (user.display_name LIKE %s OR user.user_email = %s OR user.user_login LIKE %s)
				GROUP BY user.ID
			",
				'tutor_enrolled',
				'completed',
				$search_term,
				$search_term_raw,
				$search_term
			)
		);

		return is_array( $students ) ? count( $students ) : 0;
	}

	/**
	 * @param int $user_id
	 *
	 * @return array
	 *
	 * Get complete courses ids by user
	 *
	 * @since v.1.0.0
	 */
	public function get_completed_courses_ids_by_user( $user_id = 0 ) {
		global $wpdb;

		$user_id = $this->get_user_id( $user_id );

		$course_ids = (array) $wpdb->get_col(
			$wpdb->prepare(
				"SELECT comment_post_ID AS course_id
			FROM 	{$wpdb->comments}
			WHERE 	comment_agent = %s
					AND comment_type = %s
					AND user_id = %d
					AND comment_post_ID IN (
						select post_parent AS course_id from {$wpdb->posts} where post_type=%s AND post_author = %d
					)
			",
				'TutorLMSPlugin',
				'course_completed',
				$user_id,
				'tutor_enrolled',
				$user_id
			)
		);

		return $course_ids;
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool|\WP_Query
	 *
	 * Return completed courses by user_id
	 *
	 * @since v.1.0.0
	 */
	public function get_courses_by_user( $user_id = 0, $offset = 0, $posts_per_page = -1 ) {
		$user_id    = $this->get_user_id( $user_id );
		$course_ids = $this->get_completed_courses_ids_by_user( $user_id );

		if ( count( $course_ids ) ) {
			$course_post_type = tutor()->course_post_type;
			$course_args      = array(
				'post_type'      => $course_post_type,
				'post_status'    => 'publish',
				'post__in'       => $course_ids,
				'posts_per_page' => $posts_per_page,
				'offset'         => $offset,
			);

			return new \WP_Query( $course_args );
		}

		return false;
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool|\WP_Query
	 *
	 * Get the active course by user
	 *
	 * @since v.1.0.0
	 */
	public function get_active_courses_by_user( $user_id = 0, $offset = 0, $posts_per_page = -1 ) {
		$user_id             = $this->get_user_id( $user_id );
		$course_ids          = $this->get_completed_courses_ids_by_user( $user_id );
		$enrolled_course_ids = $this->get_enrolled_courses_ids_by_user( $user_id );
		$active_courses      = array_diff( $enrolled_course_ids, $course_ids );

		if ( count( $active_courses ) ) {
			$course_post_type = tutor()->course_post_type;
			$course_args      = array(
				'post_type'      => $course_post_type,
				'post_status'    => 'publish',
				'post__in'       => $active_courses,
				'posts_per_page' => $posts_per_page,
				'offset'         => $offset,
			);

			return new \WP_Query( $course_args );
		}

		return false;
	}

	/**
	 * @param int $user_id
	 *
	 * @return array
	 *
	 * Get enrolled course ids by a user
	 *
	 * @since v.1.0.0
	 */
	public function get_enrolled_courses_ids_by_user( $user_id = 0 ) {
		global $wpdb;
		$user_id    = $this->get_user_id( $user_id );
		$course_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT post_parent
			FROM 	{$wpdb->posts}
			WHERE 	post_type = %s
					AND post_status = %s
					AND post_author = %d
				ORDER BY post_date DESC;
			",
				'tutor_enrolled',
				'completed',
				$user_id
			)
		);

		return $course_ids;
	}

	/**
	 * Get single or list of enrolled course data by a user
	 *
	 * @param integer $user_id user id
	 * @param integer $course_id cousrs id
	 * @return object | mixed
	 *
	 * @since 2.0.5
	 */
	public function get_enrolled_data( $user_id = 0, $course_id = 0 ) {
		global $wpdb;
		// If course ID provided, it will return single row data.
		if ( 0 != $course_id ) {
			return $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM 	{$wpdb->posts} 
						WHERE post_type = %s
						AND post_parent = %d
						AND post_status = %s
						AND post_author = %d;",
					'tutor_enrolled',
					$course_id,
					'completed',
					$user_id
				)
			);
		} else {
			// Return all enrolled data by user ID.
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM 	{$wpdb->posts} 
						WHERE post_type = %s
						AND post_status = %s
						AND post_author = %d;",
					'tutor_enrolled',
					'completed',
					$user_id
				)
			);
		}
	}

	/**
	 * Get total enrolled students by course id.
	 *
	 * @since 1.9.9
	 * 
	 * @param int $course_id course id.
	 * @param $period string | optional added since 1.9.9
	 *
	 * @return int
	 */
	public function count_enrolled_users_by_course( $course_id = 0, $period = '' ) {

		$course_id = $this->get_post_id( $course_id );
		// set period wise query
		$period_filter = '';
		if ( 'today' === $period ) {
			$period_filter = 'AND DATE(post_date) = CURDATE()';
		}
		if ( 'monthly' === $period ) {
			$period_filter = 'AND MONTH(post_date) = MONTH(CURDATE()) ';
		}
		if ( 'yearly' === $period ) {
			$period_filter = 'AND YEAR(post_date) = YEAR(CURDATE()) ';
		}

		$cache_key  = "tutor_enroll_count_for_course_{$course_id}_{$period}";
		$course_ids = TutorCache::get( $cache_key );

		if ( false === $course_ids ) {
			global $wpdb;
			$course_ids = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(ID)
				FROM	{$wpdb->posts}
				WHERE 	post_type = %s
						AND post_status = %s
						AND post_parent = %d;
						{$period_filter}
				",
					'tutor_enrolled',
					'completed',
					$course_id
				)
			);

			TutorCache::set( $cache_key, (int) $course_ids );
		}
		
		return (int) $course_ids;
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool|\WP_Query
	 *
	 * Get the enrolled courses by user
	 */
	public function get_enrolled_courses_by_user( $user_id = 0, $post_status = 'publish', $offset = 0, $posts_per_page = -1 ) {
		global $wpdb;

		$user_id    = $this->get_user_id( $user_id );
		$course_ids = array_unique( $this->get_enrolled_courses_ids_by_user( $user_id ) );

		if ( count( $course_ids ) ) {
			$course_post_type = tutor()->course_post_type;
			$course_args      = array(
				'post_type'      => $course_post_type,
				'post_status'    => $post_status,
				'post__in'       => $course_ids,
				'offset'         => $offset,
				'posts_per_page' => $posts_per_page,
			);

			$result = new \WP_Query( $course_args );

			if ( is_object( $result ) && is_array( $result->posts ) ) {

				// Sort courses according to the id list
				$new_array = array();

				foreach ( $course_ids as $id ) {
					foreach ( $result->posts as $post ) {
						$post->ID == $id ? $new_array[] = $post : 0;
					}
				}

				$result->posts = $new_array;
			}

			return $result;
		}

		return false;
	}

	/**
	 * @param int $post_id
	 *
	 * @return string
	 *
	 * Get the video streaming URL by post/lesson/course ID
	 */
	public function get_video_stream_url( $post_id = 0 ) {
		$post_id = $this->get_post_id( $post_id );
		$post    = get_post( $post_id );

		if ( $post->post_type === tutor()->lesson_post_type ) {
			$video_url = trailingslashit( home_url() ) . 'video-url/' . $post->post_name;
		} else {
			$video_info = $this->get_video_info( $post_id );
			$video_url  = $video_info->url;
		}

		return $video_url;
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool|false|int
	 *
	 * Get current post id or given post id
	 *
	 * @since v.1.0.0
	 */
	public function get_post_id( $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
			if ( ! $post_id ) {
				return false;
			}
		}

		return $post_id;
	}

	/**
	 * Get current user ID or given user ID
	 *
	 * @param mixed $user_id user ID.
	 *
	 * @return int  when $user_id = 0, return 0 or current user ID
	 *              otherwise return given ID
	 *
	 * @since 1.0.0
	 */
	public function get_user_id( $user_id = 0 ) {
		if ( ! $user_id ) {
			return get_current_user_id();
		}

		return $user_id;
	}

	/**
	 * Get user name for e-mail salutation
	 *
	 * @param mixed $user user object.
	 * @return string
	 * @since 2.0.9
	 */
	public function get_user_name( $user ) {
		if ( ! is_a( $user, 'WP_User' ) ) {
			return '';
		}
		$name = '';

		if ( empty( trim( $user->first_name ) ) ) {
			$name = $user->user_login;
		} else {
			$name = $user->first_name;
			if ( ! empty( trim( $user->last_name ) ) ) {
				$name .= " {$user->last_name}";
			}
		}

		return $name;
	}

	/**
	 * @param string $url
	 *
	 * @return bool
	 *
	 * Get the Youtube Video ID from URL
	 *
	 * @since v.1.0.0
	 */
	public function get_youtube_video_id( $url = '' ) {
		if ( ! $url ) {
			return false;
		}

		preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match );

		if ( isset( $match[1] ) ) {
			$youtube_id = $match[1];
			return $youtube_id;
		}

		return false;
	}

	/**
	 *
	 * @param int $course_id
	 * @param int $order_id
	 * @param int $user_id
	 *
	 * Saving enroll information to posts table
	 * post_author = enrolled_student_id (wp_users id)
	 * post_parent = enrolled course id
	 *
	 * @type: call when need
	 * @return bool;
	 *
	 * @since v.1.0.0
	 * @updated v.1.4.3
	 *
	 * @return bool
	 */
	public function do_enroll( $course_id = 0, $order_id = 0, $user_id = 0 ) {
		if ( ! $course_id ) {
			return false;
		}

		do_action( 'tutor_before_enroll', $course_id );
		$user_id = $this->get_user_id( $user_id );
		$title   = __( 'Course Enrolled', 'tutor' ) . ' &ndash; ' . date( get_option( 'date_format' ) ) . ' @ ' . date( get_option( 'time_format' ) );

		if ( $course_id && $user_id ) {
			if ( $this->is_enrolled( $course_id, $user_id ) ) {
				return;
			}
		}

		$enrolment_status  = 'completed';
		
		if ( $this->is_course_purchasable( $course_id ) ) {
			$enrolment_status = 'pending';
		}

		$enroll_data = apply_filters(
			'tutor_enroll_data',
			array(
				'post_type'     => 'tutor_enrolled',
				'post_title'    => $title,
				'post_status'   => $enrolment_status,
				'post_author'   => $user_id,
				'post_parent'   => $course_id,
				'post_date_gmt' => current_time( 'mysql', true ),
			)
		);

		// Insert the post into the database.
		$isEnrolled = wp_insert_post( $enroll_data );
		if ( $isEnrolled ) {

			// Run this hook for both of pending and completed enrollment
			do_action( 'tutor_after_enroll', $course_id, $isEnrolled );

			// Run this hook for completed enrollment regardless of payment provider and free/paid mode
			if ( $enroll_data['post_status'] == 'completed' ) {
				do_action( 'tutor_after_enrolled', $course_id, $user_id, $isEnrolled );
			}

			// Mark Current User as Students with user meta data
			update_user_meta( $user_id, '_is_tutor_student', tutor_time() );

			if ( $order_id ) {
				// Mark order for course and user
				$product_id = $this->get_course_product_id( $course_id );
				update_post_meta( $isEnrolled, '_tutor_enrolled_by_order_id', $order_id );
				update_post_meta( $isEnrolled, '_tutor_enrolled_by_product_id', $product_id );
				update_post_meta( $order_id, '_is_tutor_order_for_course', tutor_time() );
				update_post_meta( $order_id, '_tutor_order_for_course_id_' . $course_id, $isEnrolled );
			}
			return true;
		}

		return false;
	}

	/**
	 * @param bool   $enrol_id
	 * @param string $new_status
	 *
	 * Enrol Status change
	 *
	 * @since v.1.6.1
	 */
	public function course_enrol_status_change( $enrol_id = false, $new_status = '' ) {
		if ( ! $enrol_id ) {
			return;
		}

		global $wpdb;

		do_action( 'tutor/course/enrol_status_change/before', $enrol_id, $new_status );
		$wpdb->update( $wpdb->posts, array( 'post_status' => $new_status ), array( 'ID' => $enrol_id ) );
		do_action( 'tutor/course/enrol_status_change/after', $enrol_id, $new_status );
	}

	/**
	 * @param int    $course_id
	 * @param int    $user_id
	 * @param string $cancel_status
	 */
	public function cancel_course_enrol( $course_id = 0, $user_id = 0, $cancel_status = 'canceled' ) {
		$course_id = $this->get_post_id( $course_id );
		$user_id   = $this->get_user_id( $user_id );
		$enrolled  = $this->is_enrolled( $course_id, $user_id );

		if ( $enrolled ) {
			global $wpdb;

			if ( $cancel_status === 'delete' ) {
				$wpdb->delete(
					$wpdb->posts,
					array(
						'post_type'   => 'tutor_enrolled',
						'post_author' => $user_id,
						'post_parent' => $course_id,
					)
				);

				// Delete Related Meta Data
				delete_post_meta( $enrolled->ID, '_tutor_enrolled_by_product_id' );
				$order_id = get_post_meta( $enrolled->ID, '_tutor_enrolled_by_order_id', true );
				if ( $order_id ) {
					delete_post_meta( $enrolled->ID, '_tutor_enrolled_by_order_id' );
					delete_post_meta( $order_id, '_is_tutor_order_for_course' );
					delete_post_meta( $order_id, '_tutor_order_for_course_id_' . $course_id );
				}
			} else {
				$wpdb->update(
					$wpdb->posts,
					array( 'post_status' => $cancel_status ),
					array(
						'post_type'   => 'tutor_enrolled',
						'post_author' => $user_id,
						'post_parent' => $course_id,
					)
				);

				if ( $cancel_status === 'cancel' ) {
					die( $cancel_status );
				}
			}
		}
	}

	/**
	 * @param $order_id
	 *
	 * Complete course enrollment and do some task
	 *
	 * @since v.1.0.0
	 */
	public function complete_course_enroll( $order_id ) {
		if ( ! $this->is_tutor_order( $order_id ) ) {
			return;
		}

		global $wpdb;

		$enrolled_ids_with_course = $this->get_course_enrolled_ids_by_order_id( $order_id );
		if ( $enrolled_ids_with_course ) {
			$enrolled_ids = wp_list_pluck( $enrolled_ids_with_course, 'enrolled_id' );

			if ( is_array( $enrolled_ids ) && count( $enrolled_ids ) ) {
				foreach ( $enrolled_ids as $enrolled_id ) {
					$wpdb->update( $wpdb->posts, array( 'post_status' => 'completed' ), array( 'ID' => $enrolled_id ) );
				}
			}
		}
	}

	/**
	 * @param $order_id
	 *
	 * @return array|bool
	 *
	 * @since v.1.0.0
	 */
	public function get_course_enrolled_ids_by_order_id( $order_id ) {
		global $wpdb;

		// Getting all of courses ids within this order
		$courses_ids = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
			FROM 	{$wpdb->postmeta}
			WHERE	post_id = %d
					AND meta_key LIKE '_tutor_order_for_course_id_%'
			",
				$order_id
			)
		);

		if ( is_array( $courses_ids ) && count( $courses_ids ) ) {
			$course_enrolled_by_order = array();
			foreach ( $courses_ids as $courses_id ) {
				$course_id                  = str_replace( '_tutor_order_for_course_id_', '', $courses_id->meta_key );
				$course_enrolled_by_order[] = array(
					'course_id'   => $course_id,
					'enrolled_id' => $courses_id->meta_value,
					'order_id'    => $courses_id->post_id,
				);
			}
			return $course_enrolled_by_order;
		}
		return false;
	}

	/**
	 * Get wc product in efficient query
	 *
	 * @since v.1.0.0
	 */

	/**
	 * @return array|null|object
	 *
	 * WooCommerce specific utils
	 */
	public function get_wc_products_db( $course_id ) {
		global $wpdb;
		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID,
					post_title
			FROM 	{$wpdb->posts}
			WHERE 	post_status = %s
					AND post_type = %s;
			",
				'publish',
				'product'
			)
		);

		/*
		 $query = $wpdb->get_results($wpdb->prepare(
			"SELECT DISTINCT product.ID, product.post_title
			FROM {$wpdb->posts} product
			LEFT JOIN {$wpdb->postmeta} course_meta ON course_meta.meta_value=product.ID
			WHERE 	product.post_status = 'publish'
				AND product.post_type = 'product'
				AND (
					course_meta.meta_key!='_tutor_course_product_id'
					OR (
						course_meta.meta_key='_tutor_course_product_id'
						AND course_meta.post_id=%d
					)
				)",
			$course_id
		)); */

		return $query;
	}

	/**
	 * @return array|null|object
	 *
	 * Get EDD Products
	 */
	public function get_edd_products() {
		global $wpdb;
		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID,
					post_title
			FROM 	{$wpdb->posts}
			WHERE 	post_status = %s
					AND post_type = %s;
			",
				'publish',
				'download'
			)
		);

		return $query;
	}

	/**
	 * @param int $course_id
	 *
	 * @return int
	 *
	 * Get course productID
	 *
	 * @since v.1.0.0
	 */
	public function get_course_product_id( $course_id = 0 ) {
		$course_id  = $this->get_post_id( $course_id );
		$product_id = (int) get_post_meta( $course_id, '_tutor_course_product_id', true );

		return $product_id;
	}

	/**
	 * @param int $product_id
	 *
	 * @return array|null|object|void
	 *
	 * Get Product belongs with course
	 *
	 * @since v.1.0.0
	 */
	public function product_belongs_with_course( $product_id = 0 ) {
		global $wpdb;

		$query = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
			FROM 	{$wpdb->postmeta}
			WHERE	meta_key = %s
					AND meta_value = %d
			limit 1
			",
				'_tutor_course_product_id',
				$product_id
			)
		);

		return $query;
	}

	/**
	 * #End WooCommerce specific utils
	 *
	 * @since v.1.0.0
	 */
	public function get_enrolled_statuses() {
		return apply_filters(
			'tutor_get_enrolled_statuses',
			array(
				'pending',
				'processing',
				'on-hold',
				'completed',
				'cancelled',
				'refunded',
				'failed',
			)
		);
	}

	/**
	 * @param $order_id
	 *
	 * @return mixed
	 *
	 * determine is this a tutor order
	 *
	 * @since v.1.0.0
	 */
	public function is_tutor_order( $order_id ) {
		return get_post_meta( $order_id, '_is_tutor_order_for_course', true );
	}

	/**
	 * @return mixed
	 *
	 * Tutor Dashboard Pages, supporting for the URL rewriting
	 *
	 * @since v.1.0.0
	 */
	public function tutor_dashboard_pages() {
		$nav_items = apply_filters( 'tutor_dashboard/nav_items', $this->default_menus() );

		$instructor_nav_items = apply_filters( 'tutor_dashboard/instructor_nav_items', $this->instructor_menus() );

		$nav_items = array_merge( $nav_items, $instructor_nav_items );

		$new_navs      = apply_filters(
			'tutor_dashboard/bottom_nav_items',
			array(
				'separator-2' => array(
					'title' => '',
					'type'  => 'separator',
				),
				'settings'    => array(
					'title' => __( 'Settings', 'tutor' ),
					'icon'  => 'tutor-icon-gear',
				),
				'logout'      => array(
					'title' => __( 'Logout', 'tutor' ),
					'icon'  => 'tutor-icon-signout',
				),
			)
		);
		$all_nav_items = array_merge( $nav_items, $new_navs );

		return apply_filters( 'tutor_dashboard/nav_items_all', $all_nav_items );
	}

	public function tutor_dashboard_permalinks() {
		$dashboard_pages = $this->tutor_dashboard_pages();

		$dashboard_permalinks = apply_filters(
			'tutor_dashboard/permalinks',
			array(
				'retrieve-password' => array(
					'title'         => __( 'Retrieve Password', 'tutor' ),
					'login_require' => false,
				),
			)
		);

		$dashboard_pages = array_merge( $dashboard_pages, $dashboard_permalinks );

		return $dashboard_pages;
	}

	/**
	 * @return mixed
	 *
	 * Tutor Dashboard UI nav, only for using in the nav, it's handling user permission based
	 * Dashboard nav items
	 *
	 * @since v.1.3.4
	 */
	public function tutor_dashboard_nav_ui_items() {
		$nav_items = $this->tutor_dashboard_pages();

		foreach ( $nav_items as $key => $nav_item ) {
			if ( is_array( $nav_item ) ) {

				if ( isset( $nav_item['show_ui'] ) && ! $this->array_get( 'show_ui', $nav_item ) ) {
					unset( $nav_items[ $key ] );
				}
				if ( isset( $nav_item['auth_cap'] ) && ! current_user_can( $nav_item['auth_cap'] ) ) {
					unset( $nav_items[ $key ] );
				}
			}
		}

		return apply_filters( 'tutor_dashboard/nav_ui_items', $nav_items );
	}

	/**
	 * @param string $page_key
	 * @param int    $page_id
	 *
	 * @return string
	 *
	 * Get tutor dashboard page single URL
	 *
	 * @since v.1.0.0
	 */
	public function get_tutor_dashboard_page_permalink( $page_key = '', $page_id = 0 ) {
		if ( $page_key === 'index' ) {
			$page_key = '';
		}
		if ( ! $page_id ) {
			$page_id = (int) $this->get_option( 'tutor_dashboard_page_id' );
		}
		return trailingslashit( get_permalink( $page_id ) ) . $page_key;
	}

	/**
	 * @param string $input
	 *
	 * @return array|bool|mixed|string
	 *
	 * Get old input
	 *
	 * @since v.1.0.0
	 * @updated v.1.4.2
	 */
	public function input_old( $input = '', $old_data = null ) {
		if ( ! $old_data ) {
			$old_data = tutor_sanitize_data( $_REQUEST );
		}
		$value = $this->avalue_dot( $input, $old_data );
		if ( $value ) {
			return $value;
		}

		return '';
	}

	/**
	 * @param int $user_id
	 *
	 * @return mixed
	 *
	 * Determine if is instructor or not
	 *
	 * @since v.1.0.0
	 */
	public function is_instructor( $user_id = 0, $is_approved = false ) {
		$user_id = $this->get_user_id( $user_id );
		if ( $is_approved ) {
			$user_status            = get_user_meta( $user_id, '_tutor_instructor_status', true );
			$is_approved_instructor = 'approved' === $user_status ? true : false;
			return $is_approved_instructor && get_user_meta( $user_id, '_is_tutor_instructor', true );
		}
		return get_user_meta( $user_id, '_is_tutor_instructor', true );
	}

	/**
	 * @param int  $user_id
	 * @param bool $status_name
	 *
	 * @return bool|mixed
	 *
	 * Instructor status
	 *
	 * @since v.1.0.0
	 */
	public function instructor_status( $user_id = 0, $status_name = true ) {
		$user_id = $this->get_user_id( $user_id );

		$instructor_status = apply_filters(
			'tutor_instructor_statuses',
			array(
				'pending'  => __( 'Pending', 'tutor' ),
				'approved' => __( 'Approved', 'tutor' ),
				'blocked'  => __( 'Blocked', 'tutor' ),
			)
		);

		$status = get_user_meta( $user_id, '_tutor_instructor_status', true );

		if ( isset( $instructor_status[ $status ] ) ) {
			if ( ! $status_name ) {
				return $status;
			}
			return $instructor_status[ $status ];
		}
		return false;
	}

	/**
	 * Get Total number of instructor
	 *
	 * @param string $search_term
	 * @param string $status (approved | pending | blocked)
	 * @param string $course_id
	 * @param string $date, user_registered date
	 *
	 * @return int
	 *
	 * Get total number of instructors
	 *
	 * @since v.1.0.0
	 */
	public function get_total_instructors( $search_filter = '', $status = array(), $course_id = '', $date = '' ): int {
		global $wpdb;
		$search_filter = sanitize_text_field( $search_filter );
		$course_id     = sanitize_text_field( $course_id );
		$date          = sanitize_text_field( $date );

		$search_term_raw = $search_filter;
		$search_filter   = '%' . $wpdb->esc_like( $search_filter ) . '%';

		$status_query = '';
		if ( is_array( $status ) && count( $status ) ) {
			$status = array_map(
				function ( $str ) {
					return "'{$str}'";
				},
				$status
			);

			$status_query = ' AND inst_status.meta_value IN (' . implode( ',', $status ) . ')';
		}

		$course_query = '';
		if ( '' !== $course_id ) {
			$course_query = "AND umeta.meta_value = $course_id ";
		}

		$date_query = '';
		if ( '' !== $date ) {
			$date       = tutor_get_formated_date( 'Y-m-d', $date );
			$date_query = "AND  DATE(user.user_registered) = CAST('$date' AS DATE)";
		}

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT user.ID )
				FROM 	{$wpdb->users} user
						INNER JOIN {$wpdb->usermeta} user_meta
								ON ( user.ID = user_meta.user_id )
						INNER JOIN {$wpdb->usermeta} inst_status
								ON ( user.ID = inst_status.user_id )
						LEFT JOIN {$wpdb->usermeta} AS umeta
								ON umeta.user_id = user.ID AND umeta.meta_key = '_tutor_instructor_course_id'

				WHERE 	user_meta.meta_key = %s
						AND ( user.display_name LIKE %s OR user.user_email = %s )
						{$status_query}
						{$course_query}
						{$date_query}
			",
				'_is_tutor_instructor',
				$search_filter,
				$search_term_raw
			)
		);
		return $count ? $count : 0;
	}

	/**
	 * Get instructor with optional filters.
	 * Available instructor status ( approved | blocked | pending )
	 *
	 * @param int    $start
	 * @param int    $limit
	 * @param string $search_term
	 *
	 * @return array|null|object
	 *
	 * Get all instructors
	 *
	 * @since v.1.0.0
	 */
	public function get_instructors( $start = 0, $limit = 10, $search_filter = '', $course_filter = '', $date_filter = '', $order_filter = '', $status = null, $cat_ids = array(), $rating = '', $count_only = false ) {
		global $wpdb;

		$search_filter = sanitize_text_field( $search_filter );
		$course_filter = sanitize_text_field( $course_filter );
		$date_filter   = sanitize_text_field( $date_filter );
		$order_filter  = sanitize_text_field( $order_filter );
		$rating        = sanitize_text_field( $rating );

		$search_term_raw = $search_filter;
		$search_filter   = '%' . $wpdb->esc_like( $search_filter ) . '%';
		$course_filter   = $course_filter != '' ? " AND umeta.meta_value = $course_filter " : '';

		if ( '' != $date_filter ) {
			$date_filter = tutor_get_formated_date( 'Y-m-d', $date_filter );
		}

		$date_filter = $date_filter != '' ? " AND  DATE(user.user_registered) = CAST('$date_filter' AS DATE) " : '';

		$category_join  = '';
		$category_where = '';

		if ( $status ) {
			! is_array( $status ) ? $status = array( $status ) : 0;

			$status = array_map(
				function ( $str ) {
					return "'{$str}'";
				},
				$status
			);

			$status = ' AND inst_status.meta_value IN (' . implode( ',', $status ) . ')';
		}

		$cat_ids = array_filter(
			$cat_ids,
			function ( $id ) {
				return is_numeric( $id );
			}
		);

		if ( count( $cat_ids ) ) {

			$category_join =
				"INNER JOIN {$wpdb->posts} course
					ON course.post_author = user.ID
			INNER JOIN {$wpdb->prefix}term_relationships term_rel
					ON term_rel.object_id = course.ID
			INNER JOIN {$wpdb->prefix}term_taxonomy taxonomy
					ON taxonomy.term_taxonomy_id=term_rel.term_taxonomy_id
			INNER JOIN {$wpdb->prefix}terms term
					ON term.term_id=taxonomy.term_id";

			$cat_ids        = implode( ',', $cat_ids );
			$category_where = " AND term.term_id IN ({$cat_ids})";
		}

		// rating wise sorting @since v2.0.0
		$rating        = isset( $_POST['rating_filter'] ) ? $rating : '';
		$rating_having = '';
		if ( '' !== $rating ) {
			$max_rating = (int) $rating + 1;
			if ( 5 === (int) $rating ) {
				$max_rating = 5;
			}
			$rating_having = " HAVING rating >= {$rating} AND rating <= {$max_rating} ";
		}

		/**
		 * Handle Sort by Relevant | New | Popular & Order Shorting
		 * from instructor list backend
		 *
		 * @since v2.0.0
		 */
		$order_query = '';
		if ( 'new' === $order_filter ) {
			$order_query = ' ORDER BY user_meta.meta_value DESC ';
		} elseif ( 'popular' === $order_filter ) {
			$order_query = ' ORDER BY rating DESC ';
		} else {
			$order_query = " ORDER BY user_meta.meta_value {$order_filter} ";
		}

		$limit_offset = $count_only ? '' : " LIMIT {$start}, {$limit} ";
		$select_col   = $count_only ?
						' COUNT(DISTINCT user.ID) ' :
						' DISTINCT user.*, user_meta.meta_value AS instructor_from_date, IFNULL(Avg(cmeta.meta_value), 0) AS rating, inst_status.meta_value AS status ';

		$query = $wpdb->prepare(
			"SELECT {$select_col}
			FROM {$wpdb->users} user
				INNER JOIN {$wpdb->usermeta} user_meta
						ON ( user.ID = user_meta.user_id )
				INNER JOIN {$wpdb->usermeta} inst_status
						ON ( user.ID = inst_status.user_id )
				{$category_join}
				LEFT JOIN {$wpdb->usermeta} AS umeta
					ON umeta.user_id = user.ID AND umeta.meta_key = '_tutor_instructor_course_id'
				LEFT JOIN {$wpdb->comments} AS c
					ON c.comment_post_ID = umeta.meta_value
				LEFT JOIN {$wpdb->commentmeta} AS cmeta
					ON cmeta.comment_id = c.comment_ID
					AND cmeta.meta_key = 'tutor_rating'
			WHERE 	user_meta.meta_key = '_is_tutor_instructor'
				AND ( user.display_name LIKE %s OR user.user_email = %s )
				{$status}
				{$category_where}
				{$course_filter}
				{$date_filter}
			GROUP BY user.ID {$rating_having} {$order_query} {$limit_offset}",
			$search_filter,
			$search_term_raw
		);

		$results = $wpdb->get_results( $query );
		return $count_only ? count( $results ) : $results;
	}

	/**
	 * @param int $course_id
	 *
	 * @return array|bool|null|object
	 *
	 * Get all instructors by course
	 *
	 * @since v.1.0.0
	 */
	public function get_instructors_by_course( $course_id = 0 ) {
		global $wpdb;
		$course_id = $this->get_post_id( $course_id );

		$instructors = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID,
					display_name,
					_user.user_email,
					get_course.meta_value AS taught_course_id,
					tutor_job_title.meta_value AS tutor_profile_job_title,
					tutor_bio.meta_value AS tutor_profile_bio,
					tutor_photo.meta_value AS tutor_profile_photo
				FROM {$wpdb->users} _user
					INNER JOIN {$wpdb->usermeta} get_course
							ON ID = get_course.user_id
						   AND get_course.meta_key = %s
						   AND get_course.meta_value = %d
					LEFT  JOIN {$wpdb->usermeta} tutor_job_title
						    ON ID = tutor_job_title.user_id
						   AND tutor_job_title.meta_key = %s
					LEFT  JOIN {$wpdb->usermeta} tutor_bio
						    ON ID = tutor_bio.user_id
						   AND tutor_bio.meta_key = %s
					LEFT  JOIN {$wpdb->usermeta} tutor_photo
						    ON ID = tutor_photo.user_id
						   AND tutor_photo.meta_key = %s
			",
				'_tutor_instructor_course_id',
				$course_id,
				'_tutor_profile_job_title',
				'_tutor_profile_bio',
				'_tutor_profile_photo'
			)
		);
		// Get main instructor.
		$main_instructor = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT _user.ID,
					display_name,
					_user.user_email,
					course.ID AS taught_course_id,
					tutor_job_title.meta_value AS tutor_profile_job_title,
					tutor_bio.meta_value AS tutor_profile_bio,
					tutor_photo.meta_value AS tutor_profile_photo
				FROM {$wpdb->users} _user
					INNER JOIN {$wpdb->posts} course
							ON _user.ID = course.post_author
						   AND course.ID = %d
					LEFT  JOIN {$wpdb->usermeta} tutor_job_title
						    ON _user.ID = tutor_job_title.user_id
						   AND tutor_job_title.meta_key = %s
					LEFT  JOIN {$wpdb->usermeta} tutor_bio
						    ON _user.ID = tutor_bio.user_id
						   AND tutor_bio.meta_key = %s
					LEFT  JOIN {$wpdb->usermeta} tutor_photo
						    ON _user.ID = tutor_photo.user_id
						   AND tutor_photo.meta_key = %s
			",
				$course_id,
				'_tutor_profile_job_title',
				'_tutor_profile_bio',
				'_tutor_profile_photo'
			)
		);
		if ( is_array( $instructors ) && count( $instructors ) ) {
			// Exclude instructor if already in main instructor.
			$instructors = array_filter(
				$instructors,
				function( $instructor ) use ( $main_instructor ) {
					if ( $instructor->ID !== $main_instructor[0]->ID ) {
						return true;
					}
				}
			);
			return array_merge( $main_instructor, $instructors );
		}
		return $main_instructor;
	}

	/**
	 * @param $instructor_id
	 *
	 * Get total Students by instructor
	 * 1 enrollment = 1 student, so total enrolled for a equivalent total students (Tricks)
	 *
	 * @return int
	 *
	 * @since v.1.0.0
	 */
	public function get_total_students_by_instructor( $instructor_id ) {
		global $wpdb;

		$course_post_type = tutor()->course_post_type;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(enrollment.ID)
			FROM 	{$wpdb->posts} enrollment
					INNER  JOIN {$wpdb->posts} course
							ON enrollment.post_parent=course.ID
			WHERE 	course.post_author = %d
					AND course.post_type = %s
					AND course.post_status = %s
					AND enrollment.post_type = %s
					AND enrollment.post_status = %s;
			",
				$instructor_id,
				$course_post_type,
				'publish',
				'tutor_enrolled',
				'completed'
			)
		);

		return (int) $count;
	}

	/**
	 * Get all students by instructor_id
	 *
	 * @param $instructor_id int | required
	 *
	 * @param $offset int | required
	 *
	 * @param $limit int | required
	 *
	 * @param $search string | optional
	 *
	 * @param $course_id int | optional
	 *
	 * @param $date string | optional
	 *
	 * @return array
	 *
	 * @since 1.9.9
	 */
	public function get_students_by_instructor( int $instructor_id, int $offset, int $limit, $search_filter = '', $course_id = '', $date_filter = '', $order_by = '', $order = '' ): array {
		global $wpdb;
		$instructor_id = sanitize_text_field( $instructor_id );
		$limit         = sanitize_text_field( $limit );
		$offset        = sanitize_text_field( $offset );
		$course_id     = sanitize_text_field( $course_id );
		$date_filter   = sanitize_text_field( $date_filter );
		$search_filter = sanitize_text_field( $search_filter );

		$order_by = 'user.ID';
		if ( 'registration_date' === $order_by ) {
			$order_by = 'enrollment.post_date';
		} elseif ( 'course_taken' === $order_by ) {
			$order_by = 'course_taken';
		} else {
			$order_by = 'user.ID';
		}

		$order = sanitize_text_field( $order );

		if ( '' !== $date_filter ) {
			$date_filter = \tutor_get_formated_date( 'Y-m-d', $date_filter );
		}

		$course_post_type = tutor()->course_post_type;

		$search_term_raw = $search_filter;
		$search_query    = '%' . $wpdb->esc_like( $search_filter ) . '%';
		$course_query    = '';
		$date_query      = '';
		$author_query    = '';

		if ( $course_id ) {
			$course_query = " AND course.ID = $course_id ";
		}
		if ( '' !== $date_filter ) {
			$date_query = " AND DATE(user.user_registered) = CAST( '$date_filter' AS DATE ) ";
		}
		/**
		 * If instructor id set then by only students that belongs to instructor
		 * otherwise get all
		 *
		 * @since v.2.0.0
		 */
		if ( $instructor_id ) {
			$author_query = "AND course.post_author = $instructor_id";
		}

		$students       = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COUNT(enrollment.post_author) AS course_taken, user.*, (SELECT post_date FROM {$wpdb->posts} WHERE post_author = user.ID LIMIT 1) AS enroll_date
				FROM 	{$wpdb->posts} enrollment
					INNER  JOIN {$wpdb->posts} AS course
							ON enrollment.post_parent=course.ID
					INNER  JOIN {$wpdb->users} AS user
							ON user.ID = enrollment.post_author
				WHERE course.post_type = %s
					AND course.post_status = %s
					AND enrollment.post_type = %s
					AND enrollment.post_status = %s
					{$author_query}
					{$course_query}
					{$date_query}
					AND ( user.display_name LIKE %s OR user.user_nicename LIKE %s OR user.user_email = %s OR user.user_login LIKE %s )

				GROUP BY enrollment.post_author
				ORDER BY {$order_by} {$order}
				LIMIT %d, %d
			",
				$course_post_type,
				'publish',
				'tutor_enrolled',
				'completed',
				$search_query,
				$search_query,
				$search_term_raw,
				$search_query,
				$offset,
				$limit
			)
		);
		$total_students = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COUNT(enrollment.post_author) AS course_taken, user.*, enrollment.post_date AS enroll_date
				FROM 	{$wpdb->posts} enrollment
					INNER  JOIN {$wpdb->posts} AS course
							ON enrollment.post_parent=course.ID
					INNER  JOIN {$wpdb->users} AS user
							ON user.ID = enrollment.post_author
				WHERE course.post_type = %s
					AND course.post_status = %s
					AND enrollment.post_type = %s
					AND enrollment.post_status = %s
					AND ( user.display_name LIKE %s OR user.user_nicename LIKE %s OR user.user_email = %s OR user.user_login LIKE %s )
					{$author_query}
					{$course_query}
					{$date_query}
				GROUP BY enrollment.post_author
				ORDER BY {$order_by} {$order}

			",
				$course_post_type,
				'publish',
				'tutor_enrolled',
				'completed',
				$search_query,
				$search_query,
				$search_term_raw,
				$search_query
			)
		);

		return array(
			'students'       => $students,
			'total_students' => count( $total_students ),
		);
	}

	/**
	 * Get all course for a give student & instructor id
	 *
	 * @param $student_id int | required
	 *
	 * @param $instructor_id int | required
	 *
	 * @return array
	 *
	 * @since 1.9.9
	 */
	public function get_courses_by_student_instructor_id( int $student_id, int $instructor_id ): array {
		global $wpdb;
		$course_post_type = tutor()->course_post_type;
		$students         = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT course.*
				FROM 	{$wpdb->posts} enrollment
					INNER  JOIN {$wpdb->posts} AS course
							ON enrollment.post_parent=course.ID
				WHERE 	course.post_author = %d
					AND course.post_type = %s
					AND course.post_status = %s
					AND enrollment.post_type = %s
					AND enrollment.post_status = %s
					AND enrollment.post_author = %d
				ORDER BY course.post_date DESC
			",
				$instructor_id,
				$course_post_type,
				'publish',
				'tutor_enrolled',
				'completed',
				$student_id
			)
		);
		return $students;
	}

	/**
	 * Get total number of completed assignment
	 *
	 * @param $course_id int | required
	 *
	 * @param $student | required
	 *
	 * @since 1.9.9
	 */
	public function get_completed_assignment( int $course_id, int $student_id ): int {
		global $wpdb;
		$course_id  = sanitize_text_field( $course_id );
		$student_id = sanitize_text_field( $student_id );
		$count      = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(ID) FROM {$wpdb->posts}
				INNER JOIN {$wpdb->comments} c ON c.comment_post_ID = ID  AND c.user_id = %d AND c.comment_approved = %s
				WHERE post_parent IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_parent = %d AND post_status = %s)
					AND post_type =%s
					AND post_status = %s
			",
				$student_id,
				'submitted',
				'topics',
				$course_id,
				'publish',
				'tutor_assignments',
				'publish'
			)
		);
		return (int) $count;
	}
	/**
	 * Get total number of completed quiz
	 *
	 * @param $course_id int | required
	 *
	 * @param $student | required
	 *
	 * @since 1.9.9
	 */
	public function get_completed_quiz( int $course_id, int $student_id ): int {
		global $wpdb;
		$course_id  = sanitize_text_field( $course_id );
		$student_id = sanitize_text_field( $student_id );
		$count      = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT quiz_id) AS total
				FROM {$wpdb->prefix}tutor_quiz_attempts
				WHERE course_id = %d
				AND user_id = %d
				AND attempt_status = %s
			",
				$course_id,
				$student_id,
				'attempt_ended'
			)
		);
		return (int) $count;
	}

	/**
	 * @param float $input
	 *
	 * @return float|string
	 *
	 * Get rating format from value
	 *
	 * @since v.1.0.0
	 */
	public function get_rating_value( $input = 0.00 ) {

		if ( $input > 0 ) {
			$input     = number_format( $input, 2 );
			$int_value = (int) $input;
			$fraction  = $input - $int_value;

			if ( $fraction == 0 ) {
				$fraction = 0.00;
			} elseif ( $fraction > 0.5 ) {
				$fraction = 1;
			} else {
				$fraction = 0.5;
			}

			return number_format( ( $int_value + $fraction ), 2 );
		}

		return 0.00;
	}

	/**
	 * @param float $current_rating
	 * @param bool  $echo
	 *
	 * @return string
	 *
	 * Generate star rating based in given rating value
	 *
	 * @since v.1.0.0
	 */
	public function star_rating_generator( $current_rating = 0.00, $echo = true ) {

		$output = '<div class="tutor-ratings-stars">';

		for ( $i = 1; $i <= 5; $i++ ) {
			$intRating = (int) $current_rating;

			if ( $intRating >= $i ) {
				$output .= '<i class="tutor-icon-star-bold" data-rating-value="' . $i . '"></i>';
			} else {
				if ( ( $current_rating - $i ) >= -0.5 ) {
					$output .= '<i class="tutor-icon-star-half-bold" data-rating-value="' . $i . '"></i>';
				} else {
					$output .= '<i class="tutor-icon-star-line" data-rating-value="' . $i . '"></i>';
				}
			}
		}

		$output .= '</div>';

		$output .= '<input type="hidden" name="tutor_rating_gen_input" value="' . $current_rating . '" />';

		if ( $echo ) {
			echo tutor_kses_html( $output );
		}

		return $output;
	}

	public function star_rating_generator_v2( $current_rating, $total_count = null, $show_avg_rate = false, $parent_class = '', $screen_size = '' ) {
		$current_rating = number_format( $current_rating, 2, '.', '' );
		$css_class      = isset( $screen_size ) ? "{$parent_class} tutor-ratings-{$screen_size}" : "{$parent_class}";
		?>
		<div class="tutor-ratings<?php echo esc_attr( $css_class ); ?>">
			<div class="tutor-ratings-stars">
				<?php
				for ( $i = 1; $i <= 5; $i++ ) {
					$class = 'tutor-icon-star-line';

					if ( $i <= round( $current_rating ) ) {
						$class = 'tutor-icon-star-bold';
					}

					// @todo: Add half start later. tutor-icon-star-half-bold
					echo '<span class="' . $class . '"></span>';
				}
				?>
			</div>
			<?php if ( $show_avg_rate && $total_count > 0 ) : ?>
				<div class="tutor-ratings-average">
					<?php echo esc_html( $current_rating ); ?>
				</div>
				<div class="tutor-ratings-count">
					(<?php echo esc_html( $total_count ) . ' ' . ( $total_count > 1 ? esc_html__( 'Ratings', 'tutor' ) : esc_html__( 'Rating', 'tutor' ) ); ?>)
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	public function star_rating_generator_course( $current_rating = 0.00, $echo = true ) {
		$output = '';
		for ( $i = 1; $i <= 5; $i++ ) {
			$intRating = (int) $current_rating;

			if ( $intRating >= $i ) {
				$output .= '<span class="tutor-icon-star-bold" data-rating-value="' . $i . '"></span>';
			} else {
				if ( ( $current_rating - $i ) >= -0.5 ) {
					$output .= '<span class="tutor-icon-star-half-bold" data-rating-value="' . $i . '"></span>';
				} else {
					$output .= '<span class="tutor-icon-star-line" data-rating-value="' . $i . '"></span>';
				}
			}
		}

		if ( $echo ) {
			echo wp_kses(
				$output,
				array(
					'span' => array(
						'class'             => true,
						'data-rating'       => true,
						'data-rating-value' => true,
					),
				)
			);
		}

		return $output;
	}

	/**
	 * @param $string
	 *
	 * @return string
	 *
	 * Split string regardless of ASCI, Unicode
	 */
	public function str_split( $string ) {
		$strlen = mb_strlen( $string );
		while ( $strlen ) {
			$array[] = mb_substr( $string, 0, 1, 'UTF-8' );
			$string  = mb_substr( $string, 1, $strlen, 'UTF-8' );
			$strlen  = mb_strlen( $string );
		}
		return $array;
	}

	/**
	 * Generate avatar for user
	 *
	 * @since 1.0.0
	 * @since 2.1.7          changed param $user_id to $user for reduce query.
	 * 
	 * Get user data using get_userdata API
	 *
	 * @since 2.1.8
	 *
	 * @param integer|object $user user id or object.
	 * @param string         $size size of avatar like sm, md, lg.
	 *
	 * @return string
	 */
	public function get_tutor_avatar( $user = null, $size = '' ) {

		if ( ! $user ) {
			return '';
		}
	
		if ( ! is_object( $user ) ) {
			$user = get_userdata( $user );
		}

		if ( is_a( $user, 'WP_User' ) ) {
			// Get & set user profile photo.
			$profile_photo = get_user_meta( $user->ID, '_tutor_profile_photo', true );
			$user->tutor_profile_photo = $profile_photo;
		}
		
		$name  = is_object( $user ) ? $user->display_name : '';
		$arr   = explode( ' ', trim( $name ) );
		$class = $size ? ' tutor-avatar-' . $size : '';
	
		$output  = '<div class="tutor-avatar' . $class . '">';
		$output .= '<div class="tutor-ratio tutor-ratio-1x1">';
	
		if ( is_object( $user ) && $user->tutor_profile_photo ) {
			$output .= '<img src="' . wp_get_attachment_image_url( $user->tutor_profile_photo, 'thumbnail' ) . '" alt="' . esc_attr( $name ) . '" /> ';
		} else {
			$first_char     = ! empty( $arr[0] ) ? $this->str_split( $arr[0] )[0] : '';
			$second_char    = ! empty( $arr[1] ) ? $this->str_split( $arr[1] )[0] : '';
			$initial_avatar = strtoupper( $first_char . $second_char );
			$output        .= '<span class="tutor-avatar-text">' . $initial_avatar . '</span>';
		}
	
		$output .= '</div>';
		$output .= '</div>';
	
		return apply_filters( 'tutor_text_avatar', $output );
	}

	/**
	 * Get tutor user.
	 * 
	 * @since 1.0.0
	 * 
	 * @param int $user_id user id.
	 *
	 * @return array|null|object|void
	 */
	public function get_tutor_user( $user_id ) {
		$cache_key   = 'tutor_user_' . $user_id;
		$cached_data = TutorCache::get( $cache_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		global $wpdb;

		$user = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ID,
					display_name,
					user_email,
					user_login,
					user_nicename,
					tutor_job_title.meta_value AS tutor_profile_job_title,
					tutor_bio.meta_value AS tutor_profile_bio,
					tutor_photo.meta_value AS tutor_profile_photo
			FROM	{$wpdb->users}
					LEFT  JOIN {$wpdb->usermeta} tutor_job_title
							ON ID = tutor_job_title.user_id
						   AND tutor_job_title.meta_key = '_tutor_profile_job_title'
					LEFT  JOIN {$wpdb->usermeta} tutor_bio
							ON ID = tutor_bio.user_id
						   AND tutor_bio.meta_key = '_tutor_profile_bio'
					LEFT  JOIN {$wpdb->usermeta} tutor_photo
							ON ID = tutor_photo.user_id
						   AND tutor_photo.meta_key = '_tutor_profile_photo'
			WHERE 	ID = %d
			",
				$user_id
			)
		);

		TutorCache::set( $cache_key, $user );

		return $user;
	}

	/**
	 * @param int $course_id
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array|null|object
	 *
	 * get course reviews
	 *
	 * @since v.1.0.0
	 */
	public function get_course_reviews( $course_id = 0, $start = 0, $limit = 10, $count_only = false, $status_in = array( 'approved' ), $include_user_id = 0 ) {
		$course_id = $this->get_post_id( $course_id );
		global $wpdb;

		$limit_offset    = $count_only ? '' : ' LIMIT ' . $limit . ' OFFSET ' . $start;
		$status_in       = '"' . implode( '","', $status_in ) . '"';
		$include_user_id = is_array( $include_user_id ) ? $include_user_id : array( $include_user_id );
		$include_user_id = implode( ',', $include_user_id );

		$select_columns = $count_only ? ' COUNT(DISTINCT _reviews.comment_ID) ' :
			'_reviews.comment_ID,
			_reviews.comment_post_ID,
			_reviews.comment_author,
			_reviews.comment_author_email,
			_reviews.comment_date,
			_reviews.comment_content,
			_reviews.comment_approved AS comment_status,
			_reviews.user_id,
			_rev_meta.meta_value AS rating,
			_reviewer.display_name';

		$query = $wpdb->prepare(
			"SELECT {$select_columns}
			FROM 	{$wpdb->comments} _reviews
					INNER JOIN {$wpdb->commentmeta} _rev_meta
						ON _reviews.comment_ID = _rev_meta.comment_id
					LEFT JOIN {$wpdb->users} _reviewer
						ON _reviews.user_id = _reviewer.ID
			WHERE 	_reviews.comment_post_ID = %d
					AND _reviews.comment_type = 'tutor_course_rating' 
					AND (_reviews.comment_approved IN ({$status_in}) OR _reviews.user_id IN ({$include_user_id}))
					AND _rev_meta.meta_key = 'tutor_rating'
			ORDER BY _reviews.comment_ID DESC {$limit_offset}",
			$course_id
		);

		return $count_only ? $wpdb->get_var( $query ) : $wpdb->get_results( $query );
	}

	/**
	 * Get course rating
	 *
	 * @since 1.0.0
	 *
	 * @param int $course_id course ID.
	 * @return object
	 */
	public function get_course_rating( $course_id = 0 ) {
		global $wpdb;
		$course_id = $this->get_post_id( $course_id );

		$ratings = array(
			'rating_count'   => 0,
			'rating_sum'     => 0,
			'rating_avg'     => 0.00,
			'count_by_value' => array(
				5 => 0,
				4 => 0,
				3 => 0,
				2 => 0,
				1 => 0,
			),
		);

		$rating = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(meta_value) AS rating_count,
					SUM(meta_value) AS rating_sum
			FROM	{$wpdb->comments}
					INNER JOIN {$wpdb->commentmeta}
							ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id
			WHERE 	{$wpdb->comments}.comment_post_ID = %d
					AND {$wpdb->comments}.comment_type = %s
					AND meta_key = %s;
			",
				$course_id,
				'tutor_course_rating',
				'tutor_rating'
			)
		);

		if ( $rating->rating_count ) {
			$avg_rating = number_format( ( $rating->rating_sum / $rating->rating_count ), 2 );

			$stars = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT CAST(commentmeta.meta_value AS SIGNED) AS rating,
						COUNT(commentmeta.meta_value) as rating_count
				FROM	{$wpdb->comments} comments
						INNER JOIN {$wpdb->commentmeta} commentmeta
								ON comments.comment_ID = commentmeta.comment_id
				WHERE	comments.comment_post_ID = %d
						AND comments.comment_type = %s
						AND commentmeta.meta_key = %s
				GROUP BY CAST(commentmeta.meta_value AS SIGNED);
				",
					$course_id,
					'tutor_course_rating',
					'tutor_rating'
				)
			);

			$ratings = array(
				5 => 0,
				4 => 0,
				3 => 0,
				2 => 0,
				1 => 0,
			);
			foreach ( $stars as $star ) {
				$index = (int) $star->rating;
				array_key_exists( $index, $ratings ) ? $ratings[ $index ] = $star->rating_count : 0;
			}

			$ratings = array(
				'rating_count'   => $rating->rating_count,
				'rating_sum'     => $rating->rating_sum,
				'rating_avg'     => $avg_rating,
				'count_by_value' => $ratings,
			);
		}

		return (object) $ratings;
	}

	/**
	 * @param int $user_id
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array|null|object
	 *
	 * Get reviews by a user (Given by the user)
	 *
	 * @since v.1.0.0
	 */
	public function get_reviews_by_user( $user_id = 0, $offset = 0, $limit = null, $get_object = false, $course_id = null, $status_in = array( 'approved' ) ) {
		global $wpdb;

		if ( ! $limit ) {
			$limit = $this->get_option( 'pagination_per_page', 10 );
		}

		$course_filter = '';
		if ( $course_id ) {
			$course_ids    = is_array( $course_id ) ? $course_id : array( $course_id );
			$course_ids    = implode( ',', $course_ids );
			$course_filter = " AND _comment.comment_post_ID IN ($course_ids)";
		}

		$user_filter = '';
		if ( $user_id !== null ) {
			$user_id     = $this->get_user_id( $user_id );
			$user_filter = ' AND _comment.user_id=' . $user_id;
		}

		$status_in     = '"' . implode( '","', $status_in ) . '"';
		$status_filter = ' AND _comment.comment_approved IN (' . $status_in . ')';

		$reviews = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT _comment.comment_ID,
					_comment.comment_post_ID,
					_comment.comment_author,
					_comment.comment_author_email,
					_comment.comment_date,
					_comment.comment_content,
					_comment.comment_approved AS comment_status,
					_comment.user_id,
					_meta.meta_value as rating,
					_course.post_title AS course_title,
					_student.display_name
			FROM 	{$wpdb->comments} _comment
					INNER JOIN {$wpdb->commentmeta} _meta
							ON _comment.comment_ID = _meta.comment_id
					INNER JOIN {$wpdb->posts} _course
							ON _comment.comment_post_ID=_course.ID
					INNER  JOIN {$wpdb->users} _student
							ON _comment.user_id = _student.ID
			WHERE 	_comment.comment_type = %s
					AND _meta.meta_key = %s
					{$user_filter}
					{$course_filter}
					{$status_filter}
			ORDER BY _comment.comment_ID DESC
			LIMIT %d, %d;",
				'tutor_course_rating',
				'tutor_rating',
				$offset,
				$limit
			)
		);

		if ( $get_object ) {
			// Prepare other data for multiple reviews case
			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT({$wpdb->comments}.comment_ID)
						FROM 	{$wpdb->comments}
								INNER JOIN {$wpdb->commentmeta}
										ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id
								INNER  JOIN {$wpdb->users}
										ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
								INNER JOIN {$wpdb->posts} AS course
									ON course.ID = comment_post_ID
						WHERE 	{$wpdb->comments}.user_id = %d
								AND comment_type = %s
								AND meta_key = %s
								AND comment_approved = 'approved'
					",
					$user_id,
					'tutor_course_rating',
					'tutor_rating'
				)
			);

			return (object) array(
				'count'   => $count,
				'results' => $reviews,
			);
		}

		// Return single review for single course
		if ( $course_id && ! is_array( $course_id ) ) {
			return count( $reviews ) ? $reviews[0] : null;
		}

		return $reviews;
	}

	/**
	 * @param int                  $user_id
	 * @param int                  $offset
	 * @param int                  $limit
	 *
	 * @return array|null|object
	 *
	 * Get reviews by instructor (Received by the instructor)
	 *
	 * @since v.1.4.0
	 *
	 * @param $course_id optional
	 *
	 * @param $date_filter optional
	 *
	 * Course id & date filter is sorting with specific course and date
	 *
	 * @since 1.9.9
	 */
	public function get_reviews_by_instructor( $instructor_id = 0, $offset = 0, $limit = 150, $course_id = '', $date_filter = '' ) {
		global $wpdb;
		$instructor_id = sanitize_text_field( $instructor_id );
		$offset        = sanitize_text_field( $offset );
		$limit         = sanitize_text_field( $limit );
		$course_id     = sanitize_text_field( $course_id );
		$date_filter   = sanitize_text_field( $date_filter );
		$instructor_id = $this->get_user_id( $instructor_id );

		$course_query = '';
		$date_query   = '';

		if ( '' !== $course_id ) {
			$course_query = " AND {$wpdb->comments}.comment_post_ID = {$course_id} ";
		}
		if ( '' !== $date_filter ) {
			$date_filter = \tutor_get_formated_date( 'Y-m-d', $date_filter );
			$date_query  = " AND DATE({$wpdb->comments}.comment_date) = CAST( '$date_filter' AS DATE ) ";
		}

		$results = array(
			'count'   => 0,
			'results' => false,
		);

		$cours_ids = (array) $this->get_assigned_courses_ids_by_instructors( $instructor_id );

		if ( $this->count( $cours_ids ) ) {
			$implode_ids = implode( ',', $cours_ids );

			// Count
			$results['count'] = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT({$wpdb->comments}.comment_ID)
				FROM 	{$wpdb->comments}
						INNER JOIN {$wpdb->commentmeta}
								ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id
						INNER JOIN {$wpdb->users}
								ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
				WHERE 	{$wpdb->comments}.comment_post_ID IN({$implode_ids})
						AND comment_type = %s
						AND meta_key = %s
						{$course_query}
						{$date_query}
				",
					'tutor_course_rating',
					'tutor_rating'
				)
			);

			// Results
			$results['results'] = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT {$wpdb->comments}.comment_ID,
						{$wpdb->comments}.comment_post_ID,
						{$wpdb->comments}.comment_author,
						{$wpdb->comments}.comment_author_email,
						{$wpdb->comments}.comment_date,
						{$wpdb->comments}.comment_content,
						{$wpdb->comments}.user_id,
						{$wpdb->commentmeta}.meta_value AS rating,
						{$wpdb->users}.display_name,
						{$wpdb->posts}.post_title as course_title

				FROM 	{$wpdb->comments}
						INNER JOIN {$wpdb->commentmeta}
								ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id
						INNER JOIN {$wpdb->users}
								ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
						INNER JOIN {$wpdb->posts}
								ON {$wpdb->posts}.ID = {$wpdb->comments}.comment_post_ID
				WHERE 	{$wpdb->comments}.comment_post_ID IN({$implode_ids})
						AND comment_type = %s
						AND meta_key = %s
						{$course_query}
						{$date_query}
				ORDER BY comment_ID DESC
				LIMIT %d, %d;
				",
					'tutor_course_rating',
					'tutor_rating',
					$offset,
					$limit
				)
			);
		}

		return (object) $results;
	}

	/**
	 * @param $instructor_id
	 *
	 * @return object
	 *
	 * Get instructors rating
	 *
	 * @since v.1.0.0
	 */
	public function get_instructor_ratings( $instructor_id ) {
		global $wpdb;

		$ratings = array(
			'rating_count' => 0,
			'rating_sum'   => 0,
			'rating_avg'   => 0.00,
		);

		$rating = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(rating.meta_value) as rating_count, SUM(rating.meta_value) as rating_sum
			FROM 	{$wpdb->usermeta} courses
					INNER JOIN {$wpdb->comments} reviews
							ON courses.meta_value = reviews.comment_post_ID
						   AND reviews.comment_type = 'tutor_course_rating'
					INNER JOIN {$wpdb->commentmeta} rating
							ON reviews.comment_ID = rating.comment_id
						   AND rating.meta_key = 'tutor_rating'
			WHERE 	courses.user_id = %d
					AND courses.meta_key = %s
			",
				$instructor_id,
				'_tutor_instructor_course_id'
			)
		);

		if ( $rating->rating_count ) {
			$avg_rating = number_format( ( $rating->rating_sum / $rating->rating_count ), 2 );

			$ratings = array(
				'rating_count' => $rating->rating_count,
				'rating_sum'   => $rating->rating_sum,
				'rating_avg'   => $avg_rating,
			);
		}

		return (object) $ratings;
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return object
	 *
	 * Get course rating by user
	 *
	 * @since v.1.0.0
	 */
	public function get_course_rating_by_user( $course_id = 0, $user_id = 0 ) {
		global $wpdb;

		$course_id = $this->get_post_id( $course_id );
		$user_id   = $this->get_user_id( $user_id );

		$ratings = array(
			'rating' => 0,
			'review' => '',
		);

		$rating = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT meta_value AS rating,
					comment_content AS review
			FROM	{$wpdb->comments}
					INNER JOIN {$wpdb->commentmeta}
							ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id
			WHERE	{$wpdb->comments}.comment_post_ID = %d
					AND user_id = %d
					AND meta_key = %s;
			",
				$course_id,
				$user_id,
				'tutor_rating'
			)
		);

		if ( $rating ) {
			$rating_format = number_format( $rating->rating, 2 );

			$ratings = array(
				'rating' => $rating_format,
				'review' => $rating->review,
			);
		}

		return (object) $ratings;
	}

	/**
	 * @param int $user_id
	 *
	 * @return null|string
	 *
	 * @since v.1.0.0
	 */
	public function count_reviews_wrote_by_user( $user_id = 0 ) {
		global $wpdb;

		$user_id = $this->get_user_id( $user_id );

		$count_reviews = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(comment_ID)
			FROM	{$wpdb->comments}
			WHERE 	user_id = %d
					AND comment_type = %s
			",
				$user_id,
				'tutor_course_rating'
			)
		);

		return $count_reviews;
	}

	/**
	 * @param $size
	 *
	 * @return bool|int|string
	 *
	 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
	 *
	 * @since v.1.0.0
	 */
	function let_to_num( $size ) {
		$l    = substr( $size, -1 );
		$ret  = substr( $size, 0, -1 );
		$byte = 1024;

		switch ( strtoupper( $l ) ) {
			case 'P':
				$ret *= 1024;
				// No break.
			case 'T':
				$ret *= 1024;
				// No break.
			case 'G':
				$ret *= 1024;
				// No break.
			case 'M':
				$ret *= 1024;
				// No break.
			case 'K':
				$ret *= 1024;
				// No break.
		}
		return $ret;
	}

	/**
	 * @return array
	 *
	 * Get Database version
	 *
	 * @since v.1.0.0
	 */
	function get_db_version() {
		 global $wpdb;

		if ( empty( $wpdb->is_mysql ) ) {
			return array(
				'string' => '',
				'number' => '',
			);
		}

		if ( $wpdb->use_mysqli ) {
			$server_info = mysqli_get_server_info($wpdb->dbh); // @codingStandardsIgnoreLine.
		} else {
			$server_info = mysql_get_server_info($wpdb->dbh); // @codingStandardsIgnoreLine.
		}

		return array(
			'string' => $server_info,
			'number' => preg_replace( '/([^\d.]+).*/', '', $server_info ),
		);
	}

	public function help_tip( $tip = '' ) {
		return '<span class="tutor-help-tip" data-tip="' . $tip . '"></span>';
	}

	/**
	 * @param int    $start
	 * @param int    $limit
	 * @param string $search_term
	 *
	 * @return array|null|object
	 *
	 *
	 * Get question and answer query
	 *
	 * @since v.1.0.0
	 */
	public function get_qa_questions( $start = 0, $limit = 10, $search_term = '', $question_id = null, $meta_query = null, $asker_id = null, $question_status = null, $count_only = false, $args = array() ) {
		global $wpdb;

		$user_id            = get_current_user_id();
		$course_type        = tutor()->course_post_type;
		$search_term        = '%' . $wpdb->esc_like( $search_term ) . '%';
		$question_clause    = $question_id ? ' AND _question.comment_ID=' . $question_id : '';
		$order_condition    = ' ORDER BY _question.comment_ID DESC ';
		$meta_clause        = '';
		$in_course_id_query = '';
		$qna_types_caluse   = '';
		$filter_clause      = '';

		// Sanitize args before process.
		$args = Input::sanitize_array( $args );

		/**
		 * Get only assinged  courses questions if current user is not admin
		 */
		// User query
		if ( $asker_id ) {
			$question_clause .= ' AND _question.user_id=' . $asker_id;
		}

		if ( isset( $args['course_id'] ) ) {
			// Get qa for specific course
			$in_course_id_query .= ' AND _question.comment_post_ID=' . $args['course_id'] . ' ';

		} elseif ( ! $asker_id && $question_id === null && ! $this->has_user_role( 'administrator', $user_id ) && current_user_can( tutor()->instructor_role ) ) {
			// If current user is simple instructor (non admin), then get qa from their courses only
			$my_course_ids       = $this->get_course_id_by( 'instructor', $user_id );
			$in_ids              = count( $my_course_ids ) ? implode( ',', $my_course_ids ) : '0';
			$in_course_id_query .= " AND _question.comment_post_ID IN($in_ids) ";
		}

		// Add more filters to the query
		if ( isset( $args['course-id'] ) && is_numeric( $args['course-id'] ) ) {
			$filter_clause .= ' AND _course.ID=' . $args['course-id'];
		}

		if ( isset( $args['date'] ) ) {
			$date           = sanitize_text_field( $args['date'] );
			$filter_clause .= ' AND DATE(_question.comment_date)=\'' . $date . '\'';
		}

		if ( isset( $args['order'] ) ) {
			$order = strtolower( $args['order'] );
			if ( $order == 'asc' || $order == 'desc' ) {
				$order_condition = ' ORDER BY _question.comment_ID ' . $order . ' ';
			}
		}

		// Meta query
		if ( $meta_query ) {
			$meta_array = array();
			foreach ( $meta_query as $key => $value ) {
				$meta_array[] = "_meta.meta_key='{$key}' AND _meta.meta_value='{$value}'";
			}
			$meta_clause .= ' AND ' . implode( ' AND ', $meta_array );
		}

		$asker_prefix    = $asker_id === null ? '' : '_' . $asker_id;
		$exclude_archive = ' AND NOT EXISTS (SELECT meta_key FROM ' . $wpdb->commentmeta . ' WHERE meta_key = \'tutor_qna_archived' . $asker_prefix . '\' AND meta_value=1 AND comment_id = _meta.comment_id) ';

		// Assign read, unread, archived, important identifier
		switch ( $question_status ) {
			case null:
			case 'all':
				if ( ! $question_id ) {
					$qna_types_caluse = $exclude_archive;
				}
				break;

			case 'read':
				$qna_types_caluse = ' AND (_meta.meta_key=\'tutor_qna_read' . $asker_prefix . '\' AND _meta.meta_value=1) ' . $exclude_archive;
				break;

			case 'unread':
				$qna_types_caluse = ' AND (_meta.meta_key=\'tutor_qna_read' . $asker_prefix . '\' AND _meta.meta_value!=1) ' . $exclude_archive;
				break;

			case 'archived':
				$qna_types_caluse = ' AND (_meta.meta_key=\'tutor_qna_archived' . $asker_prefix . '\' AND _meta.meta_value=1) ';
				break;

			case 'important':
				$qna_types_caluse = ' AND (_meta.meta_key=\'tutor_qna_important' . $asker_prefix . '\' AND _meta.meta_value=1) ' . $exclude_archive;
				break;
		}

		$columns_select = $count_only ? 'COUNT(DISTINCT _question.comment_ID)' :
			"DISTINCT _question.comment_ID,
					_question.comment_post_ID,
					_question.comment_author,
					_question.comment_date,
					_question.comment_date_gmt,
					_question.comment_content,
					_question.user_id,
					_user.user_email,
					_user.display_name,
					_course.ID as course_id,
					_course.post_title,
					(	SELECT  COUNT(answers_t.comment_ID)
						FROM 	{$wpdb->comments} answers_t
						WHERE 	answers_t.comment_parent = _question.comment_ID
					) AS answer_count";

		$limit_offset = $count_only ? '' : ' LIMIT ' . $limit . ' OFFSET ' . $start;

		$query = $wpdb->prepare(
			"SELECT  {$columns_select}
			FROM {$wpdb->comments} _question
					INNER JOIN {$wpdb->posts} _course
							ON _question.comment_post_ID = _course.ID
					INNER JOIN {$wpdb->users} _user
							ON _question.user_id = _user.ID
					LEFT JOIN {$wpdb->commentmeta} _meta
							ON _question.comment_ID = _meta.comment_id
					LEFT JOIN {$wpdb->commentmeta} _meta_archive
							ON _question.comment_ID = _meta_archive.comment_id
			WHERE  	_question.comment_type = 'tutor_q_and_a'
					AND _question.comment_parent = 0
					AND _question.comment_content LIKE %s
					{$in_course_id_query}
					{$question_clause}
					{$meta_clause}
					{$qna_types_caluse}
					{$filter_clause}
			{$order_condition}
			{$limit_offset}",
			$search_term
		);
		if ( $count_only ) {
			return $wpdb->get_var( $query );
		}

		$query = $wpdb->get_results( $query );

		// Collect question IDs and create empty meta array placeholder
		$question_ids = array();
		foreach ( $query as $index => $q ) {
			$question_ids[]        = $q->comment_ID;
			$query[ $index ]->meta = array();
		}

		// Assign meta data
		if ( count( $question_ids ) ) {
			$q_ids      = implode( ',', $question_ids );
			$meta_array = $wpdb->get_results(
				"SELECT comment_id, meta_key, meta_value
				FROM {$wpdb->commentmeta}
				WHERE comment_id IN ({$q_ids})"
			);
			// Loop through meta array
			foreach ( $meta_array as $meta ) {
				// Loop through questions
				foreach ( $query as $index => $question ) {

					if ( $query[ $index ]->comment_ID == $meta->comment_id ) {

						$query[ $index ]->meta[ $meta->meta_key ] = $meta->meta_value;
					}
				}
			}
		}

		if ( $question_id ) {
			return isset( $query[0] ) ? $query[0] : null;
		}

		return $query;
	}

	/**
	 * @param $question_id
	 *
	 * @return array|null|object|void
	 *
	 * Get question for Q&A
	 *
	 * @since v.1.0.0
	 */
	public function get_qa_question( $question_id ) {
		return $this->get_qa_questions( 0, 1, '', $question_id );
	}

	/**
	 * @param $question_id
	 *
	 * @return array|null|object
	 *
	 * Get question and asnwer by question
	 */
	public function get_qa_answer_by_question( $question_id ) {
		global $wpdb;
		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT _chat.comment_ID,
					_chat.comment_post_ID,
					_chat.comment_author,
					_chat.comment_date,
					_chat.comment_date_gmt,
					_chat.comment_content,
					_chat.comment_parent,
					_chat.user_id,
					{$wpdb->users}.display_name
			FROM	{$wpdb->comments} _chat
					INNER JOIN {$wpdb->users} ON _chat.user_id = {$wpdb->users}.ID
			WHERE 	comment_type = 'tutor_q_and_a'
					AND ( _chat.comment_ID=%d OR _chat.comment_parent = %d)
			ORDER BY _chat.comment_ID ASC;",
				$question_id,
				$question_id
			)
		);

		return $query;
	}

	/**
	 * Get question and asnwer by answer_id
	 *
	 * @param $answer_id
	 *
	 * @return array|null|object
	 *
	 * @since v1.6.9
	 */
	public function get_qa_answer_by_answer_id( $answer_id ) {
		global $wpdb;
		$answer = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT answer.comment_post_ID,
					answer.comment_content,
					users.display_name,
					question.user_id AS question_by,
					question.comment_content AS question,
					question.comment_ID AS question_id
			FROM   {$wpdb->comments} answer
					INNER JOIN {$wpdb->users} users
							ON answer.user_id = users.id
					INNER JOIN {$wpdb->comments} question
							ON answer.comment_parent = question.comment_ID
			WHERE  	answer.comment_ID = %d
					AND answer.comment_type = %s;
			",
				$answer_id,
				'tutor_q_and_a'
			)
		);

		if ( $answer ) {
			return $answer;
		}

		return false;
	}

	public function unanswered_question_count() {
		global $wpdb;
		/**
		 * q & a unanswered showing wrong number when login as
		 * instructor as it was count unanswered question from all courses
		 * from now on it will check if tutor instructor and count
		 * from instructor's course
		 *
		 * @since version 1.9.0
		 */
		$user_id     = get_current_user_id();
		$course_type = tutor()->course_post_type;

		$in_question_id_query = '';
		/**
		 * Get only assinged  courses questions if current user is a
		 */
		if ( ! current_user_can( 'administrator' ) && current_user_can( tutor()->instructor_role ) ) {

			$get_course_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT ID
				FROM 	{$wpdb->posts}
				WHERE 	post_author = %d
						AND post_type = %s
						AND post_status = %s
				",
					$user_id,
					$course_type,
					'publish'
				)
			);

			$get_assigned_courses_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT meta_value
				FROM	{$wpdb->usermeta}
				WHERE 	meta_key = %s
						AND user_id = %d
				",
					'_tutor_instructor_course_id',
					$user_id
				)
			);

			$my_course_ids = array_unique( array_merge( $get_course_ids, $get_assigned_courses_ids ) );

			if ( $this->count( $my_course_ids ) ) {
				$implode_ids          = implode( ',', $my_course_ids );
				$in_question_id_query = " AND {$wpdb->comments}.comment_post_ID IN($implode_ids) ";
			}
		}

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT({$wpdb->comments}.comment_ID)
			FROM    {$wpdb->comments}
					INNER JOIN {$wpdb->posts}
							ON {$wpdb->comments}.comment_post_ID = {$wpdb->posts}.ID
					INNER JOIN {$wpdb->users}
							ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
			WHERE   {$wpdb->comments}.comment_type = %s
					AND {$wpdb->comments}.comment_approved = %s
					AND {$wpdb->comments}.comment_parent = 0 {$in_question_id_query};
			",
				'tutor_q_and_a',
				'waiting_for_answer'
			)
		);
		return (int) $count;
	}

	/**
	 * @param int $course_id
	 *
	 * @return array|null|object
	 *
	 * Return all of announcements for a course
	 *
	 * @since v.1.0.0
	 */
	public function get_announcements( $course_id = 0 ) {
		$course_id = $this->get_post_id( $course_id );
		global $wpdb;
		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 	{$wpdb->posts}.ID,
						post_author,
						post_date,
						post_date_gmt,
						post_content,
						post_title,
						display_name
			FROM  		{$wpdb->posts}
						INNER JOIN {$wpdb->users}
								ON post_author = {$wpdb->users}.ID
			WHERE   	post_type = %s
						AND post_parent = %d
			ORDER BY 	{$wpdb->posts}.ID DESC;
			",
				'tutor_announcements',
				$course_id
			)
		);
		return $query;
	}

	/**
	 * @param string $content
	 *
	 * @return mixed
	 *
	 * Announcement content
	 *
	 * @since v.1.0.0
	 */
	public function announcement_content( $content = '' ) {
		$search = array( '{user_display_name}' );

		$user_display_name = 'User';
		if ( is_user_logged_in() ) {
			$user              = wp_get_current_user();
			$user_display_name = $user->display_name;
		}

		$replace = array( $user_display_name );

		return str_replace( $search, $replace, $content );
	}

	/**
	 * @param int    $post_id
	 * @param string $option_key
	 * @param bool   $default
	 *
	 * @return array|bool|mixed
	 *
	 * Get the quiz option from meta
	 */
	public function get_quiz_option( $post_id = 0, $option_key = '', $default = false ) {
		$post_id         = $this->get_post_id( $post_id );
		$get_option_meta = maybe_unserialize( get_post_meta( $post_id, 'tutor_quiz_option', true ) );

		if ( ! $option_key && ! empty( $get_option_meta ) ) {
			return $get_option_meta;
		}

		$value = $this->avalue_dot( $option_key, $get_option_meta );
		if ( $value > 0 || $value !== false ) {
			return $value;
		}

		return $default;
	}

	/**
	 * @param int $quiz_id
	 *
	 * @return array|bool|null|object
	 *
	 * Get the questions by quiz ID
	 */
	public function get_questions_by_quiz( $quiz_id = 0 ) {
		$quiz_id = $this->get_post_id( $quiz_id );
		global $wpdb;

		$questions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
			FROM	{$wpdb->prefix}tutor_quiz_questions
			WHERE	quiz_id = %d
			ORDER BY question_order ASC
			",
				$quiz_id
			)
		);

		return ( is_array( $questions ) && count( $questions ) ) ? $questions : false;
	}

	/**
	 * @param null $type
	 *
	 * @return array|mixed
	 *
	 * Get all question types
	 *
	 * @since v.1.0.0
	 */
	public function get_question_types( $type = null ) {
		$types = array(
			'true_false'        => array(
				'name'   => __( 'True/False', 'tutor' ),
				'icon'   => '<span class="tooltip-btn" ><i class="tutor-quiz-type-icon tutor-quiz-type-boolean tutor-icon-circle-half"></i></span>',
				'is_pro' => false,
			),
			'single_choice'     => array(
				'name'   => __( 'Single Choice', 'tutor' ),
				'icon'   => '<span class="tooltip-btn"><i class="tutor-quiz-type-icon tutor-quiz-type-single-choice tutor-icon-mark"></i></span>',
				'is_pro' => false,
			),
			'multiple_choice'   => array(
				'name'   => __( 'Multiple Choice', 'tutor' ),
				'icon'   => '<span class="tooltip-btn"><i class="tutor-quiz-type-icon tutor-quiz-type-multiple-choices tutor-icon-double-mark"></i></span>',
				'is_pro' => false,
			),
			'open_ended'        => array(
				'name'   => __( 'Open Ended', 'tutor' ),
				'icon'   => '<span class="tooltip-btn"></span><i class="tutor-quiz-type-icon tutor-quiz-type-open-ended tutor-icon-text-width"></i></span>',
				'is_pro' => false,
			),
			'fill_in_the_blank' => array(
				'name'   => __( 'Fill In The Blanks', 'tutor' ),
				'icon'   => '<span class="tooltip-btn" ><i class="tutor-quiz-type-icon tutor-quiz-type-fill-blanks tutor-icon-hourglass"></i></span>',
				'is_pro' => false,
			),
			'short_answer'      => array(
				'name'   => __( 'Short Answer', 'tutor' ),
				'icon'   => '<span class="tooltip-btn"><i class="tutor-quiz-type-icon tutor-quiz-type-short-answer tutor-icon-minimize"></i></span>',
				'is_pro' => true,
			),
			'matching'          => array(
				'name'   => __( 'Matching', 'tutor' ),
				'icon'   => '<span class="tooltip-btn"><i class="tutor-quiz-type-icon tutor-quiz-type-matching tutor-icon-arrow-right-left"></i></span>',
				'is_pro' => true,
			),
			'image_matching'    => array(
				'name'   => __( 'Image Matching', 'tutor' ),
				'icon'   => '<span class="tooltip-btn"><i class="tutor-quiz-type-icon tutor-quiz-type-image-matching tutor-icon-images"></i></span>',
				'is_pro' => true,
			),
			'image_answering'   => array(
				'name'   => __( 'Image Answering', 'tutor' ),
				'icon'   => '<span class="tooltip-btn"><i class="tutor-quiz-type-icon tutor-quiz-type-image-answering tutor-icon-camera"></i></span>',
				'is_pro' => true,
			),
			'ordering'          => array(
				'name'   => __( 'Ordering', 'tutor' ),
				'icon'   => '<span class="tooltip-btn"><i class="tutor-quiz-type-icon tutor-quiz-type-ordering tutor-icon-ordering-z-a"></i></span>',
				'is_pro' => true,
			),
		);

		if ( isset( $types[ $type ] ) ) {
			return $types[ $type ];
		}

		return $types;
	}

	public function get_quiz_answer_options_by_question( $question_id ) {
		global $wpdb;

		$answer_options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$wpdb->comments}.comment_ID,
					{$wpdb->comments}.comment_post_ID,
					{$wpdb->comments}.comment_content
			FROM 	{$wpdb->comments}
			WHERE 	{$wpdb->comments}.comment_post_ID = %d
					AND {$wpdb->comments}.comment_type = %s
			ORDER BY {$wpdb->comments}.comment_karma ASC;
			",
				$question_id,
				'quiz_answer_option'
			)
		);

		if ( is_array( $answer_options ) && count( $answer_options ) ) {
			return $answer_options;
		}
		return false;
	}

	/**
	 * @param int $post_id
	 *
	 * @return array|bool|null|object
	 *
	 * @since v.1.0.0
	 */
	public function get_attached_quiz( $post_id = 0 ) {
		global $wpdb;

		$post_id = $this->get_post_id( $post_id );

		$questions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID,
					post_content,
					post_title,
					post_parent
			FROM 	{$wpdb->posts}
			WHERE 	post_type = %s
					AND post_status = %s
					AND post_parent = %d;
			",
				'tutor_quiz',
				'publish',
				$post_id
			)
		);

		if ( is_array( $questions ) && count( $questions ) ) {
			return $questions;
		}

		return false;
	}

	/**
	 * @param $quiz_id
	 *
	 * @return int
	 *
	 * @since v.1.0.0
	 */
	public function total_questions_for_student_by_quiz( $quiz_id ) {
		$quiz_id = $this->get_post_id( $quiz_id );
		global $wpdb;

		$max_questions_count = (int) $this->get_quiz_option( get_the_ID(), 'max_questions_for_answer' );
		$total_question      = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT count(question_id)
			FROM	{$wpdb->tutor_quiz_questions}
			WHERE 	quiz_id = %d;
			",
				$quiz_id
			)
		);

		return min( $max_questions_count, $total_question );
	}

	/**
	 * Determine if there is any started quiz exists.
	 * 
	 * @since 1.0.0
	 * 
	 * @param int $quiz_id quiz id.
	 *
	 * @return array|null|object|void
	 */
	public function is_started_quiz( $quiz_id = 0 ) {
		global $wpdb;

		$quiz_id = $this->get_post_id( $quiz_id );
		$user_id = get_current_user_id();

		$cache_key  = "tutor_is_started_quiz_{$user_id}_{$quiz_id}";
		$is_started = TutorCache::get( $cache_key );
		
		if ( false === $is_started ) {
			$is_started = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT *
				FROM 	{$wpdb->prefix}tutor_quiz_attempts
				WHERE 	user_id =  %d
						AND quiz_id = %d
						AND attempt_status = %s;
				",
					$user_id,
					$quiz_id,
					'attempt_started'
				)
			);
			TutorCache::set( $cache_key, $is_started );
		}

		return $is_started;
	}

	/**
	 * @param $quiz_id
	 *
	 * Method for get the total amount of question for a quiz
	 * Student will answer this amount of question, one quiz have many question
	 * but student will answer a specific amount of questions
	 *
	 * @return int
	 *
	 * @since v.1.0.0
	 */
	public function max_questions_for_take_quiz( $quiz_id ) {
		$quiz_id = $this->get_post_id( $quiz_id );
		global $wpdb;

		$max_questions = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT count(question_id)
			FROM 	{$wpdb->prefix}tutor_quiz_questions
			WHERE 	quiz_id = %d;
			",
				$quiz_id
			)
		);

		$max_mentioned = (int) $this->get_quiz_option( $quiz_id, 'max_questions_for_answer', 10 );

		if ( $max_mentioned < $max_questions ) {
			return $max_mentioned;
		}

		return $max_questions;
	}

	/**
	 * @param int $attempt_id
	 *
	 * @return array|bool|null|object|void
	 *
	 * Get single quiz attempt
	 *
	 * @since v.1.0.0
	 */
	public function get_attempt( $attempt_id = 0 ) {
		global $wpdb;
		if ( ! $attempt_id ) {
			return false;
		}

		$attempt = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
			FROM 	{$wpdb->prefix}tutor_quiz_attempts
			WHERE 	attempt_id = %d;
			",
				$attempt_id
			)
		);

		return $attempt;
	}

	/**
	 * @param $attempt_info
	 *
	 * @return mixed
	 *
	 * Get unserialize attempt info
	 *
	 * @since v.1.0.0
	 */
	public function quiz_attempt_info( $attempt_info ) {
		return maybe_unserialize( $attempt_info );
	}

	/**
	 * @param $quiz_attempt_id
	 * @param array           $attempt_info
	 *
	 * @return bool|int
	 *
	 * Update attempt for various action
	 *
	 * @since v.1.0.0
	 */
	public function quiz_update_attempt_info( $quiz_attempt_id, $attempt_info = array() ) {
		$answers             = $this->avalue_dot( 'answers', $attempt_info );
		$total_marks         = array_sum( wp_list_pluck( $answers, 'question_mark' ) );
		$earned_marks        = $this->avalue_dot( 'marks_earned', $attempt_info );
		$earned_mark_percent = $earned_marks > 0 ? ( number_format( ( $earned_marks * 100 ) / $total_marks ) ) : 0;
		update_comment_meta( $quiz_attempt_id, 'earned_mark_percent', $earned_mark_percent );

		return update_comment_meta( $quiz_attempt_id, 'quiz_attempt_info', $attempt_info );
	}

	/**
	 * @param int $quiz_id
	 *
	 * @return array|null|object
	 *
	 * Get random question by quiz id
	 *
	 * @since v.1.0.0
	 */
	public function get_random_question_by_quiz( $quiz_id = 0 ) {
		global $wpdb;

		$quiz_id    = $this->get_post_id( $quiz_id );
		$is_attempt = $this->is_started_quiz( $quiz_id );

		$tempSql   = " AND question_type = 'matching' ";
		$questions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
			FROM 	{$wpdb->prefix}tutor_quiz_questions
			WHERE 	quiz_id = %d
					{$tempSql}
			ORDER BY RAND()
			LIMIT 0, 1
			",
				$quiz_id
			)
		);

		return $questions;
	}

	/**
	 * @param int $quiz_id
	 *
	 * @return array|null|object
	 *
	 * Get random questions by quiz
	 */
	public function get_random_questions_by_quiz( $quiz_id = 0 ) {
		global $wpdb;

		$quiz_id         = $this->get_post_id( $quiz_id );
		$attempt         = $this->is_started_quiz( $quiz_id );
		$total_questions = (int) $attempt->total_questions;
		if ( ! $attempt ) {
			return false;
		}

		$questions_order = $this->get_quiz_option( get_the_ID(), 'questions_order', 'rand' );

		$order_by = '';
		if ( $questions_order === 'rand' ) {
			$order_by = 'ORDER BY RAND()';
		} elseif ( $questions_order === 'asc' ) {
			$order_by = 'ORDER BY question_id ASC';
		} elseif ( $questions_order === 'desc' ) {
			$order_by = 'ORDER BY question_id DESC';
		} elseif ( $questions_order === 'sorting' ) {
			$order_by = 'ORDER BY question_order ASC';
		}

		$limit = '';
		if ( $total_questions ) {
			$limit = "LIMIT {$total_questions} ";
		}

		$questions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
			FROM 	{$wpdb->prefix}tutor_quiz_questions
			WHERE 	quiz_id = %d
			{$order_by}
			{$limit}
			",
				$quiz_id
			)
		);

		return $questions;
	}

	/**
	 * @param int $user_id
	 *
	 * @return array|bool|null|object
	 *
	 * Get attempts by an user
	 *
	 * @since v.1.0.0
	 */
	public function get_all_quiz_attempts_by_user( $user_id = 0 ) {
		global $wpdb;

		$user_id  = $this->get_user_id( $user_id );
		$attempts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 	*
			FROM 		{$wpdb->prefix}tutor_quiz_attempts
			WHERE 		user_id = %d
			ORDER BY 	attempt_id DESC
			",
				$user_id
			)
		);

		if ( is_array( $attempts ) && count( $attempts ) ) {
			return $attempts;
		}

		return false;
	}

	/**
	 * @param $ids
	 *
	 * @return array|bool|null|object
	 *
	 * Get quiz answers by ids
	 *
	 * @since v.1.0.0
	 */
	public function get_quiz_answers_by_ids( $ids ) {
		$ids = (array) $ids;

		if ( ! count( $ids ) ) {
			return false;
		}

		$in_ids = implode( ',', $ids );

		global $wpdb;

		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT comment_ID,
					comment_content
		 	FROM 	{$wpdb->comments}
			WHERE 	comment_type = %s
					AND comment_ID IN({$in_ids})
			",
				'quiz_answer_option'
			)
		);

		if ( is_array( $query ) && count( $query ) ) {
			return $query;
		}

		return false;
	}

	/**
	 * @param null $level
	 *
	 * @return mixed
	 *
	 * Get the users / students / course levels
	 *
	 * @since v.1.0.0
	 */
	public function course_levels( $level = null ) {
		$levels = apply_filters(
			'tutor_course_level',
			array(
				'all_levels'   => __( 'All Levels', 'tutor' ),
				'beginner'     => __( 'Beginner', 'tutor' ),
				'intermediate' => __( 'Intermediate', 'tutor' ),
				'expert'       => __( 'Expert', 'tutor' ),
			)
		);

		if ( $level ) {
			if ( isset( $levels[ $level ] ) ) {
				return $levels[ $level ];
			} else {
				return '';
			}
		}

		return $levels;
	}

	/**
	 * @return bool|false|string
	 *
	 * Student registration form
	 *
	 * @since v.1.0.0
	 */
	public function student_register_url() {
		$student_register_page = (int) $this->get_option( 'student_register_page' );

		if ( $student_register_page ) {
			return get_the_permalink( $student_register_page );
		}

		return false;
	}

	/**
	 * @return bool|false|string
	 *
	 * Instructor registration form
	 *
	 * @since v.1.2.13
	 */
	public function instructor_register_url() {
		 $instructor_register_page = (int) $this->get_option( 'instructor_register_page' );

		if ( $instructor_register_page ) {
			return get_the_permalink( $instructor_register_page );
		}

		return false;
	}

	/**
	 * @return false|string
	 *
	 * Get frontend dashboard URL
	 */
	public function tutor_dashboard_url( $sub_url = '' ) {
		$page_id = (int) $this->get_option( 'tutor_dashboard_page_id' );
		$page_id = apply_filters( 'tutor_dashboard_page_id', $page_id );
		return trailingslashit( get_the_permalink( $page_id ) ) . $sub_url;
	}

	/**
	 * Get the tutor dashboard page ID
	 *
	 * @return int
	 */
	public function dashboard_page_id() {
		$page_id = (int) $this->get_option( 'tutor_dashboard_page_id' );
		$page_id = apply_filters( 'tutor_dashboard_page_id', $page_id );
		return $page_id;
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return bool
	 *
	 * is_wishlisted();
	 *
	 * @since v.1.0.0
	 */
	public function is_wishlisted( $course_id = 0, $user_id = 0 ) {
		$course_id = $this->get_post_id( $course_id );
		$user_id   = $this->get_user_id( $user_id );
		if ( ! $user_id ) {
			return false;
		}

		global $wpdb;
		$if_added_to_list = (bool) $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
			FROM	{$wpdb->usermeta}
			WHERE 	user_id = %d
					AND meta_key = '_tutor_course_wishlist'
					AND meta_value = %d;
			",
				$user_id,
				$course_id
			)
		);

		return $if_added_to_list;
	}

	/**
	 * @param int $user_id
	 *
	 * @return array|null|object
	 *
	 * Get the wish lists by an user
	 *
	 * @since v.1.0.0
	 */
	public function get_wishlist( $user_id = 0, int $offset = 0, int $limit = PHP_INT_MAX ) {
		global $wpdb;

		$user_id          = $this->get_user_id( $user_id );
		$course_post_type = tutor()->course_post_type;

		$pageposts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT $wpdb->posts.*
	    	FROM 	$wpdb->posts
	    			LEFT JOIN $wpdb->usermeta
						   ON ($wpdb->posts.ID = $wpdb->usermeta.meta_value)
	    	WHERE 	post_type = %s
					AND post_status = %s
					AND $wpdb->usermeta.meta_key = %s
					AND $wpdb->usermeta.user_id = %d
	    	ORDER BY $wpdb->usermeta.umeta_id DESC LIMIT %d, %d;
			",
				$course_post_type,
				'publish',
				'_tutor_course_wishlist',
				$user_id,
				$offset,
				$limit
			),
			OBJECT
		);

		return $pageposts;
	}

	/**
	 * @param int $limit
	 *
	 * @return array|null|object
	 *
	 * Getting popular courses
	 *
	 * @since v.1.0.0
	 */
	public function most_popular_courses( $limit = 10, $user_id = '' ) {
		global $wpdb;
		$limit   = sanitize_text_field( $limit );
		$user_id = sanitize_text_field( $user_id );

		$author_query = '';
		if ( '' !== $user_id ) {
			$author_query = "AND course.post_author = $user_id";
		}

		$courses = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COUNT(enrolled.ID) AS total_enrolled,
					enrolled.post_parent as course_id,
					course.*
			FROM 	{$wpdb->posts} enrolled
					INNER JOIN {$wpdb->posts} course
							ON enrolled.post_parent = course.ID
			WHERE 	enrolled.post_type = %s
					AND enrolled.post_status = %s
					AND course.post_type = %s
					{$author_query}
			GROUP BY course_id
			ORDER BY total_enrolled DESC
			LIMIT 0, %d;
			",
				'tutor_enrolled',
				'completed',
				tutor()->course_post_type,
				$limit
			)
		);

		return $courses;
	}

	/**
	 * @param int $limit
	 *
	 * @return array|bool|null|object
	 *
	 * Get most rated courses lists
	 *
	 * @since v.1.0.0
	 */
	public function most_rated_courses( $limit = 10 ) {
		global $wpdb;

		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 	COUNT(comment_ID) AS total_rating,
						comment_ID,
						comment_post_ID,
						course.*
			FROM 		{$wpdb->comments}
						INNER JOIN {$wpdb->posts} course
								ON comment_post_ID = course.ID
			WHERE 		{$wpdb->comments}.comment_type = %s
						AND {$wpdb->comments}.comment_approved = %s
			GROUP BY 	comment_post_ID
			ORDER BY 	total_rating DESC
			LIMIT 		0, %d
			;",
				'tutor_course_rating',
				'approved',
				$limit
			)
		);

		if ( is_array( $result ) && count( $result ) ) {
			return $result;
		}

		return false;
	}

	/**
	 * @param null $addon_field
	 *
	 * @return bool
	 *
	 * Get Addon config
	 *
	 * @since v.1.0.0
	 */
	public function get_addon_config( $addon_field = null ) {
		if ( ! $addon_field ) {
			return false;
		}

		$addonsConfig = maybe_unserialize( get_option( 'tutor_addons_config' ) );

		if ( isset( $addonsConfig[ $addon_field ] ) ) {
			return $addonsConfig[ $addon_field ];
		}

		return false;
	}

	/**
	 * @return array|false|string
	 *
	 * Get the IP from visitor
	 *
	 * @since v.1.0.0
	 */
	function get_ip() {
		 $ipaddress = '';
		if ( getenv( 'HTTP_CLIENT_IP' ) ) {
			$ipaddress = getenv( 'HTTP_CLIENT_IP' );
		} elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
		} elseif ( getenv( 'HTTP_X_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED' );
		} elseif ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
		} elseif ( getenv( 'HTTP_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED' );
		} elseif ( getenv( 'REMOTE_ADDR' ) ) {
			$ipaddress = getenv( 'REMOTE_ADDR' );
		} else {
			$ipaddress = 'UNKNOWN';
		}
		return $ipaddress;
	}

	/**
	 * @return array $array
	 *
	 * Get the social icons
	 *
	 * @since v.1.0.4
	 */
	public function tutor_social_share_icons() {
		$icons = array(
			'facebook' => array(
				'share_class' => 's_facebook',
				'icon_html'   => '<i class="tutor-valign-middle tutor-icon-brand-facebook"></i>',
				'text'        => __( 'Facebook', 'tutor' ),
				'color'       => '#3877EA',
			),
			'twitter'  => array(
				'share_class' => 's_twitter',
				'icon_html'   => '<i class="tutor-valign-middle tutor-icon-brand-twitter"></i>',
				'text'        => __( 'Twitter', 'tutor' ),
				'color'       => '#4CA0EB',
			),
			'linkedin' => array(
				'share_class' => 's_linkedin',
				'icon_html'   => '<i class="tutor-valign-middle tutor-icon-brand-linkedin"></i>',
				'text'        => __( 'Linkedin', 'tutor' ),
				'color'       => '#3967B6',
			),
		);

		return apply_filters( 'tutor_social_share_icons', $icons );
	}

	/**
	 * @return array $array
	 *
	 * Get the user social icons
	 *
	 * @since v.1.3.7
	 */
	public function tutor_user_social_icons() {
		 $icons = array(
			 '_tutor_profile_facebook' => array(
				 'label'        => __( 'Facebook', 'tutor' ),
				 'placeholder'  => 'https://facebook.com/username',
				 'icon_classes' => 'tutor-icon-brand-facebook',
			 ),
			 '_tutor_profile_twitter'  => array(
				 'label'        => __( 'Twitter', 'tutor' ),
				 'placeholder'  => 'https://twitter.com/username',
				 'icon_classes' => 'tutor-icon-brand-twitter',
			 ),
			 '_tutor_profile_linkedin' => array(
				 'label'        => __( 'Linkedin', 'tutor' ),
				 'placeholder'  => 'https://linkedin.com/username',
				 'icon_classes' => 'tutor-icon-brand-linkedin',
			 ),
			 '_tutor_profile_website'  => array(
				 'label'        => __( 'Website', 'tutor' ),
				 'placeholder'  => 'https://example.com/',
				 'icon_classes' => 'tutor-icon-earth',
			 ),
			 '_tutor_profile_github'   => array(
				 'label'        => __( 'Github', 'tutor' ),
				 'placeholder'  => 'https://github.com/username',
				 'icon_classes' => 'tutor-icon-brand-github',
			 ),
		 );

		 return apply_filters( 'tutor_user_social_icons', $icons );
	}

	/**
	 * @param array $array
	 *
	 * @return bool
	 *
	 * count method with check is_array
	 *
	 * @since v.1.0.4
	 */
	public function count( $array = array() ) {
		if ( is_array( $array ) && count( $array ) ) {
			return count( $array );
		}

		return false;
	}

	/**
	 * @return array
	 *
	 * get all screen ids
	 *
	 * @since v.1.1.2
	 */
	public function tutor_get_screen_ids() {
		$screen_ids = array(
			'edit-course',
			'course',
			'edit-course-category',
			'edit-course-tag',
			'tutor-lms_page_tutor-students',
			'tutor-lms_page_tutor-instructors',
			'tutor-lms_page_question_answer',
			'tutor-lms_page_tutor_quiz_attempts',
			'tutor-lms_page_tutor-addons',
			'tutor-lms_page_tutor-status',
			'tutor-lms_page_tutor_report',
			'tutor-lms_page_tutor_settings',
			'tutor-lms_page_tutor_emails',
		);

		return apply_filters( 'tutor_get_screen_ids', $screen_ids );
	}

	/**
	 * @return mixed
	 *
	 * get earning transaction completed status
	 *
	 * @since v.1.1.2
	 */
	public function get_earnings_completed_statuses() {
		return apply_filters(
			'tutor_get_earnings_completed_statuses',
			array(
				'wc-completed',
				'completed',
				'complete',
			)
		);
	}

	/**
	 * @param int   $user_id
	 * @param array $date_filter
	 *
	 * @return array|null|object
	 *
	 * Get all time earning sum for an instructor with all commission
	 *
	 * @since v.1.1.2
	 */
	public function get_earning_sum( $user_id = 0, $date_filter = array() ) {
		global $wpdb;

		$user_id    = $this->get_user_id( $user_id );
		$date_query = '';

		if ( $this->count( $date_filter ) ) {
			extract( $date_filter );

			if ( ! empty( $dataFor ) ) {
				if ( $dataFor === 'yearly' ) {
					if ( empty( $year ) ) {
						$year = date( 'Y' );
					}
					$date_query = "AND YEAR(created_at) = {$year} ";
				}
			} else {
				$date_query = " AND (created_at BETWEEN '{$start_date}' AND '{$end_date}') ";
			}
		}

		$complete_status = $this->get_earnings_completed_statuses();
		$complete_status = "'" . implode( "','", $complete_status ) . "'";

		$earning_sum = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT SUM(course_price_total) AS course_price_total,
                    SUM(course_price_grand_total) AS course_price_grand_total,
                    SUM(instructor_amount) AS instructor_amount,
                    (SELECT SUM(amount)
					FROM 	{$wpdb->prefix}tutor_withdraws
					WHERE 	user_id = {$user_id}
							AND status != 'rejected'
					) AS withdraws_amount,
                    SUM(admin_amount) AS admin_amount,
                    SUM(deduct_fees_amount)  AS deduct_fees_amount
            FROM 	{$wpdb->prefix}tutor_earnings
            WHERE 	user_id = %d
					AND order_status IN({$complete_status})
					{$date_query}
			",
				$user_id
			)
		);

		// TODO: need to check
		// (SUM(instructor_amount) - (SELECT withdraws_amount) ) as balance,

		if ( $earning_sum->course_price_total ) {
			$earning_sum->balance = $earning_sum->instructor_amount - $earning_sum->withdraws_amount;
		} else {
			$earning_sum = (object) array(
				'course_price_total'       => 0,
				'course_price_grand_total' => 0,
				'instructor_amount'        => 0,
				'withdraws_amount'         => 0,
				'balance'                  => 0,
				'admin_amount'             => 0,
				'deduct_fees_amount'       => 0,
			);
		}

		return $earning_sum;
	}

	/**
	 * @param int   $user_id
	 * @param array $date_filter
	 *
	 * @return array|null|object
	 *
	 * Get earning statements
	 *
	 * @since v.1.1.2
	 */
	public function get_earning_statements( $user_id = 0, $filter_data = array() ) {
		global $wpdb;

		$user_sql = '';
		if ( $user_id ) {
			$user_sql = " AND user_id='{$user_id}' ";
		}

		$date_query       = '';
		$query_by_status  = '';
		$pagination_query = '';

		/**
		 * Query by Date Filter
		 */
		if ( $this->count( $filter_data ) ) {
			extract( $filter_data );

			if ( ! empty( $dataFor ) ) {
				if ( $dataFor === 'yearly' ) {
					if ( empty( $year ) ) {
						$year = date( 'Y' );
					}
					$date_query = "AND YEAR(created_at) = {$year} ";
				}
			} else {
				$date_query = " AND (created_at BETWEEN '{$start_date}' AND '{$end_date}') ";
			}

			/**
			 * Query by order status related to this earning transaction
			 */
			if ( ! empty( $statuses ) ) {
				if ( $this->count( $statuses ) ) {
					$status          = "'" . implode( "','", $statuses ) . "'";
					$query_by_status = "AND order_status IN({$status})";
				} elseif ( $statuses === 'completed' ) {
					$get_earnings_completed_statuses = $this->get_earnings_completed_statuses();
					if ( $this->count( $get_earnings_completed_statuses ) ) {
						$status          = "'" . implode( "','", $get_earnings_completed_statuses ) . "'";
						$query_by_status = "AND order_status IN({$status})";
					}
				}
			}

			if ( ! empty( $per_page ) ) {
				$offset           = (int) ! empty( $offset ) ? $offset : 0;
				$pagination_query = " LIMIT {$offset}, {$per_page}  ";
			}
		}

		/**
		 * Delete duplicated earning rows that were created due to not checking if already added while creating new.
		 * New entries will check before insert.
		 *
		 * @since v1.9.7
		 */
		if ( ! get_option( 'tutor_duplicated_earning_deleted', false ) ) {

			// Get the duplicated order IDs
			$del_rows  = array();
			$order_ids = $wpdb->get_col(
				"SELECT order_id
				FROM (SELECT order_id, COUNT(order_id) AS cnt
						FROM {$wpdb->prefix}tutor_earnings
						GROUP BY order_id) t
				WHERE cnt>1"
			);

			if ( is_array( $order_ids ) && count( $order_ids ) ) {
				$order_ids_string = implode( ',', $order_ids );
				$earnings         = $wpdb->get_results(
					"SELECT earning_id, course_id FROM {$wpdb->prefix}tutor_earnings
					WHERE order_id IN ({$order_ids_string})
					ORDER BY earning_id ASC"
				);

				$excluded_first = array();
				foreach ( $earnings as $earning ) {
					if ( ! in_array( $earning->course_id, $excluded_first ) ) {
						// Exclude first course ID from deletion
						$excluded_first[] = $earning->course_id;
						continue;
					}

					$del_rows[] = $earning->earning_id;
				}
			}

			if ( count( $del_rows ) ) {
				$ids = implode( ',', $del_rows );
				$wpdb->query( "DELETE FROM {$wpdb->prefix}tutor_earnings WHERE earning_id IN ({$ids})" );
			}

			update_option( 'tutor_duplicated_earning_deleted', true );
		}

		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 	earning_tbl.*,
						course.post_title AS course_title
			FROM 		{$wpdb->prefix}tutor_earnings earning_tbl
						LEFT JOIN {$wpdb->posts} course
						   	   ON earning_tbl.course_id = course.ID
			WHERE 		1 = %d {$user_sql} {$date_query} {$query_by_status}
			ORDER BY 	created_at DESC {$pagination_query}
			",
				1
			)
		);

		$query_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT 	COUNT(earning_tbl.earning_id)
			FROM 		{$wpdb->prefix}tutor_earnings earning_tbl
            WHERE 		1 = %d {$user_sql} {$date_query} {$query_by_status}
			ORDER BY 	created_at DESC
			",
				1
			)
		);

		return (object) array(
			'count'   => $query_count,
			'results' => $query,
		);
	}

	/**
	 * @param int $price
	 *
	 * @return int|string
	 *
	 * Get the price format
	 *
	 * @since v.1.1.2
	 */
	public function tutor_price( $price = 0 ) {
		if ( function_exists( 'wc_price' ) ) {
			return wc_price( $price );
		} elseif ( function_exists( 'edd_currency_filter' ) ) {
			return edd_currency_filter( edd_format_amount( $price ) );
		} else {
			return number_format_i18n( $price );
		}
	}

	/**
	 * @return mixed
	 *
	 * Get currency symbol from activated plugin, WC,EDD
	 *
	 * @since  v.1.3.4
	 */
	public function currency_symbol() {
		 $enable_tutor_edd = $this->get_option( 'enable_tutor_edd' );
		$monetize_by       = $this->get_option( 'monetize_by' );

		$symbol = '&#36;';
		if ( $enable_tutor_edd && function_exists( 'edd_currency_symbol' ) ) {
			$symbol = edd_currency_symbol();
		}

		if ( $monetize_by === 'wc' && function_exists( 'get_woocommerce_currency_symbol' ) ) {
			$symbol = get_woocommerce_currency_symbol();
		}

		return apply_filters( 'get_tutor_currency_symbol', $symbol );
	}

	/**
	 * @param int $instructor_id
	 *
	 * Add Instructor role to any user by user iD
	 */
	public function add_instructor_role( $instructor_id = 0 ) {
		if ( ! $instructor_id ) {
			return;
		}
		do_action( 'tutor_before_approved_instructor', $instructor_id );

		update_user_meta( $instructor_id, '_is_tutor_instructor', tutor_time() );
		update_user_meta( $instructor_id, '_tutor_instructor_status', 'approved' );
		update_user_meta( $instructor_id, '_tutor_instructor_approved', tutor_time() );

		$instructor = new \WP_User( $instructor_id );
		$instructor->add_role( tutor()->instructor_role );

		do_action( 'tutor_after_approved_instructor', $instructor_id );
	}

	/**
	 * @param int $instructor_id
	 *
	 * Remove instructor role by instructor id
	 */
	public function remove_instructor_role( $instructor_id = 0 ) {
		if ( ! $instructor_id ) {
			return;
		}

		do_action( 'tutor_before_blocked_instructor', $instructor_id );
		delete_user_meta( $instructor_id, '_is_tutor_instructor' );
		update_user_meta( $instructor_id, '_tutor_instructor_status', 'blocked' );

		$instructor = new \WP_User( $instructor_id );
		$instructor->remove_role( tutor()->instructor_role );
		do_action( 'tutor_after_blocked_instructor', $instructor_id );
	}

	/**
	 * @param int $user_id
	 *
	 * @return array|null|object
	 *
	 * Get purchase history by customer id
	 */
	public function get_orders_by_user_id( $user_id = 0, $period = '', $start_date = '', $end_date = '', $offset = '', $per_page = '' ) {
		global $wpdb;

		$user_id     = $this->get_user_id( $user_id );
		$monetize_by = $this->get_option( 'monetize_by' );

		$post_type = '';
		$user_meta = '';

		if ( $monetize_by === 'wc' ) {
			$post_type = 'shop_order';
			$user_meta = '_customer_user';
		} elseif ( $monetize_by === 'edd' ) {
			$post_type = 'edd_payment';
			$user_meta = '_edd_payment_user_id';
		}

		$period_query = '';

		if ( '' !== $period ) {
			if ( 'today' === $period ) {
				$period_query = ' AND  DATE(post_date) = CURDATE() ';
			} elseif ( 'monthly' === $period ) {
				$period_query = ' AND  MONTH(post_date) = MONTH(CURDATE()) ';
			} else {
				$period_query = ' AND  YEAR(post_date) = YEAR(CURDATE()) ';
			}
		}

		if ( '' !== $start_date and '' !== $end_date ) {
			$period_query = " AND  DATE(post_date) BETWEEN CAST('$start_date' AS DATE) AND CAST('$end_date' AS DATE) ";
		}

		$offset_limit_query = '';
		if ( '' !== $offset && '' !== $per_page ) {
			$offset_limit_query = "LIMIT $offset, $per_page";
		}

		$orders = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$wpdb->posts}.*
			FROM	{$wpdb->posts}
					INNER JOIN {$wpdb->postmeta} customer
							ON id = customer.post_id
						   AND customer.meta_key = '{$user_meta}'
					INNER JOIN {$wpdb->postmeta} tutor_order
							ON id = tutor_order.post_id
						   AND tutor_order.meta_key = '_is_tutor_order_for_course'
			WHERE	post_type = %s
					AND customer.meta_value = %d
					{$period_query}
			ORDER BY {$wpdb->posts}.id DESC
			{$offset_limit_query}
			",
				$post_type,
				$user_id
			)
		);

		return $orders;
	}

	/**
	 * @param int $user_id
	 *
	 * @return array|null|object
	 *
	 * Get total purchase history by customer id
	 */
	public function get_total_orders_by_user_id( $user_id, $period, $start_date, $end_date ) {
		global $wpdb;

		$user_id     = $this->get_user_id( $user_id );
		$monetize_by = $this->get_option( 'monetize_by' );

		$post_type = '';
		$user_meta = '';

		if ( $monetize_by === 'wc' ) {
			$post_type = 'shop_order';
			$user_meta = '_customer_user';
		} elseif ( $monetize_by === 'edd' ) {
			$post_type = 'edd_payment';
			$user_meta = '_edd_payment_user_id';
		}

		$period_query = '';

		if ( '' !== $period ) {
			if ( 'today' === $period ) {
				$period_query = ' AND  DATE(post_date) = CURDATE() ';
			} elseif ( 'monthly' === $period ) {
				$period_query = ' AND  MONTH(post_date) = MONTH(CURDATE()) ';
			} else {
				$period_query = ' AND  YEAR(post_date) = YEAR(CURDATE()) ';
			}
		}

		if ( '' !== $start_date and '' !== $end_date ) {
			$period_query = " AND  DATE(post_date) BETWEEN CAST('$start_date' AS DATE) AND CAST('$end_date' AS DATE) ";
		}

		$orders = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$wpdb->posts}.*
			FROM	{$wpdb->posts}
					INNER JOIN {$wpdb->postmeta} customer
							ON id = customer.post_id
						   AND customer.meta_key = '{$user_meta}'
					INNER JOIN {$wpdb->postmeta} tutor_order
							ON id = tutor_order.post_id
						   AND tutor_order.meta_key = '_is_tutor_order_for_course'
			WHERE	post_type = %s
					AND customer.meta_value = %d
					{$period_query}
			ORDER BY {$wpdb->posts}.id DESC
			",
				$post_type,
				$user_id
			)
		);

		return $orders;
	}

	/**
	 * Export purchased course data
	 */
	public function export_purchased_course_data( $order_id = '', $purchase_date = '' ) {
		global $wpdb;

		$purchased_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT tutor_order.*, course.post_title
   			FROM {$wpdb->prefix}tutor_earnings AS tutor_order
   			INNER JOIN {$wpdb->posts} AS course
     			ON course.ID = tutor_order.course_id
  			WHERE tutor_order.order_id = %d",
				$order_id
			)
		);

		return $purchased_data;
	}

	/**
	 * @param null $status
	 *
	 * @return string
	 *
	 * Get status contact formatted for order
	 *
	 * @since v.1.3.1
	 */
	public function order_status_context( $status = null ) {
		$status      = str_replace( 'wc-', '', $status );
		$status_name = ucwords( str_replace( '-', ' ', $status ) );

		return '<span class="label-order-status label-status-' . $status . '">' . $status_name . '</span>';
	}

	/**
	 * Depricated since v1.9.8
	 * This function is redundant and will be removed later
	 */
	public function get_course_id_by_assignment( $assignment_id = 0 ) {
		$assignment_id = $this->get_post_id( $assignment_id );
		return $this->get_course_id_by( 'assignment', $assignment_id );
	}

	/**
	 * @param int    $assignment_id
	 * @param string $option_key
	 * @param bool   $default
	 *
	 * @return array|bool|mixed
	 *
	 * Get assignment options
	 *
	 * @since v.1.3.3
	 */
	public function get_assignment_option( $assignment_id = 0, $option_key = '', $default = false ) {
		$assignment_id   = $this->get_post_id( $assignment_id );
		$get_option_meta = maybe_unserialize( get_post_meta( $assignment_id, 'assignment_option', true ) );

		if ( ! $option_key && ! empty( $get_option_meta ) ) {
			return $get_option_meta;
		}

		$value = $this->avalue_dot( $option_key, $get_option_meta );

		if ( $value ) {
			return $value;
		}

		return $default;
	}

	/**
	 * @param int $assignment_id
	 * @param int $user_id
	 *
	 * @return int
	 *
	 * Is running any assignment submitting
	 *
	 * @since v.1.3.3
	 */
	public function is_assignment_submitting( $assignment_id = 0, $user_id = 0 ) {
		global $wpdb;

		$assignment_id = $this->get_post_id( $assignment_id );
		$user_id       = $this->get_user_id( $user_id );

		$is_running_submit = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT comment_ID
			FROM 	{$wpdb->comments}
			WHERE	comment_type = %s
					AND comment_approved = %s
					AND user_id = %d
					AND comment_post_ID = %d;
			",
				'tutor_assignment',
				'submitting',
				$user_id,
				$assignment_id
			)
		);

		return $is_running_submit;
	}

	/**
	 * Determine if any assignment submitted by user to a assignment.
	 * 
	 * @since 1.3.3
	 * 
	 * @param int $assignment_id assignment id.
	 * @param int $user_id user id.
	 *
	 * @return array|null|object
	 */
	public function is_assignment_submitted( $assignment_id = 0, $user_id = 0 ) {
		global $wpdb;

		$assignment_id = $this->get_post_id( $assignment_id );
		$user_id       = $this->get_user_id( $user_id );

		$cache_key     = "tutor_is_assignment_submitted_{$user_id}_{$assignment_id}";
		$has_submitted = TutorCache::get( $cache_key );

		if ( false === $has_submitted ) {
			$has_submitted = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT *
				FROM 	{$wpdb->comments}
				WHERE 	comment_type = %s
						AND comment_approved = %s
						AND user_id = %d
						AND comment_post_ID = %d;
				",
					'tutor_assignment',
					'submitted',
					$user_id,
					$assignment_id
				)
			);
			TutorCache::set( $cache_key, $has_submitted );
		}

		return $has_submitted;
	}

	public function get_assignment_submit_info( $assignment_submitted_id = 0 ) {
		global $wpdb;

		$assignment_submitted_id = $this->get_post_id( $assignment_submitted_id );

		$submitted_info = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
			FROM 	{$wpdb->comments}
			WHERE	comment_ID = %d
					AND comment_type = %s
					AND comment_approved = %s;
			",
				$assignment_submitted_id,
				'tutor_assignment',
				'submitted'
			)
		);

		return $submitted_info;
	}

	/**
	 * Depricated since v1.9.8
	 * It is redundant and will be removed later
	 */
	public function get_total_assignments() {
		global $wpdb;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(comment_ID)
			FROM 	{$wpdb->comments}
			WHERE	comment_type = %s
					AND comment_approved = %s;
			",
				'tutor_assignment',
				'submitted'
			)
		);

		return (int) $count;
	}

	/**
	 * Depricated since v1.9.8
	 * It is redundant and will be removed later
	 */
	public function get_assignments() {
		 global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
			FROM 	{$wpdb->comments}
			WHERE 	comment_type = %s
					AND comment_approved = %s;
			",
				'tutor_assignment',
				'submitted'
			)
		);

		return $results;
	}

	/**
	 * @param int $user_id
	 *
	 * @return array
	 *
	 * Get all courses id assigned or owned by an instructors
	 *
	 * @since v.1.3.3
	 */
	public function get_assigned_courses_ids_by_instructors( $user_id = 0 ) {
		global $wpdb;
		$user_id          = $this->get_user_id( $user_id );
		$course_post_type = tutor()->course_post_type;

		$get_assigned_courses_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT meta.meta_value
			FROM {$wpdb->usermeta} meta
				INNER JOIN {$wpdb->posts} course ON meta.meta_value=course.ID
				WHERE meta.meta_key = '_tutor_instructor_course_id'
					AND meta.user_id = %d GROUP BY meta_value",
				$user_id
			)
		);

		return $get_assigned_courses_ids;
	}

	/**
	 * @param int $parent
	 *
	 * @return array
	 *
	 * Get course categories in array with child
	 *
	 * @since v.1.3.4
	 */
	public function get_course_categories( $parent = 0 ) {
		$args = apply_filters(
			'tutor_get_course_categories_args',
			array(
				'taxonomy'   => 'course-category',
				'hide_empty' => false,
				'parent'     => $parent,
			)
		);

		$terms = get_terms( $args );

		$children = array();
		foreach ( $terms as $term ) {
			$term->children             = $this->get_course_categories( $term->term_id );
			$children[ $term->term_id ] = $term;
		}

		return $children;
	}

	/**
	 * @param int $parent
	 *
	 * @return array
	 *
	 * Get course tags in array with child
	 *
	 * @since v.1.9.3
	 */
	public function get_course_tags() {
		$args = apply_filters(
			'tutor_get_course_tags_args',
			array(
				'taxonomy'   => 'course-tag',
				'hide_empty' => false,
			)
		);

		$terms = get_terms( $args );

		$children = array();
		foreach ( $terms as $term ) {
			$term->children             = array();
			$children[ $term->term_id ] = $term;
		}

		return $children;
	}

	/**
	 * @param int $parent_id
	 *
	 * @return array|int|\WP_Error
	 *
	 * Get course categories terms in raw array
	 *
	 * @since v.1.3.5
	 */
	public function get_course_categories_term( $parent_id = 0 ) {
		$args = apply_filters(
			'tutor_get_course_categories_terms_args',
			array(
				'taxonomy'   => 'course-category',
				'parent'     => $parent_id,
				'hide_empty' => false,
			)
		);

		$terms = get_terms( $args );

		return $terms;
	}

	/**
	 * @return mixed
	 *
	 * Get back url from the request
	 * @since v.1.3.4
	 */
	public function referer() {
		 $url = $this->array_get( '_wp_http_referer', $_REQUEST );
		return apply_filters( 'tutor_referer_url', $url );
	}

	/**
	 * @param int $course_id
	 *
	 * @return false|string
	 *
	 * Get the frontend dashboard course edit page
	 *
	 * @since v.1.3.4
	 */
	public function course_edit_link( $course_id = 0 ) {
		$course_id = $this->get_post_id( $course_id );

		$url = admin_url( "post.php?post={$course_id}&action=edit" );
		if ( tutor()->has_pro ) {
			$url = $this->tutor_dashboard_url( 'create-course/?course_ID=' . $course_id );
		}

		return $url;
	}

	public function get_assignments_by_instructor( $instructor_id = 0, $filter_data = array() ) {
		global $wpdb;

		$instructor_id        = $this->get_user_id( $instructor_id );
		$course_ids           = $this->get_assigned_courses_ids_by_instructors( $instructor_id );
		$assignment_post_type = 'tutor_assignments';

		$in_course_ids = implode( "','", $course_ids );

		$pagination_query = $date_query = '';
		$sort_query       = 'ORDER BY ID DESC';
		if ( $this->count( $filter_data ) ) {
			extract( $filter_data );

			if ( ! empty( $course_id ) ) {
				$in_course_ids = $course_id;
			}
			if ( ! empty( $date_filter ) ) {
				$date_filter = tutor_get_formated_date( 'Y-m-d', $date_filter );
				$date_query  = " AND DATE(post_date) = '{$date_filter}'";
			}
			if ( ! empty( $order_filter ) ) {
				$sort_query = " ORDER BY ID {$order_filter} ";
			}
			if ( ! empty( $per_page ) ) {
				$offset           = (int) ! empty( $offset ) ? $offset : 0;
				$pagination_query = " LIMIT {$offset}, {$per_page}  ";
			}
		}

		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(ID)
			FROM 	{$wpdb->postmeta} post_meta
					INNER JOIN {$wpdb->posts} assignment
							ON post_meta.post_id = assignment.ID
						   AND post_meta.meta_key = '_tutor_course_id_for_assignments'
			WHERE 	post_type = %s
					AND assignment.post_parent>0
					AND post_meta.meta_value IN('$in_course_ids')
					{$date_query}
			",
				$assignment_post_type
			)
		);

		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
			FROM	{$wpdb->postmeta} post_meta
					INNER JOIN {$wpdb->posts} assignment
							ON post_meta.post_id = assignment.ID
						   AND post_meta.meta_key = '_tutor_course_id_for_assignments'
			WHERE 	post_type = %s
					AND assignment.post_parent>0
					AND post_meta.meta_value IN('$in_course_ids')
					{$date_query}
					{$sort_query}
					{$pagination_query}
			",
				$assignment_post_type
			)
		);

		return (object) array(
			'count'   => $count,
			'results' => $query,
		);
	}

	/**
	 * @param int $course_id
	 *
	 * @return bool|object
	 *
	 * Get assignments by course id
	 */
	public function get_assignments_by_course( $course_id = 0 ) {
		if ( ! $course_id ) {
			return false;
		}
		global $wpdb;

		$assignment_post_type = 'tutor_assignments';

		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT 	COUNT(ID)
			FROM 		{$wpdb->postmeta} post_meta
 						INNER JOIN {$wpdb->posts} assignment
					 			ON post_meta.post_id = assignment.ID
						   	   AND post_meta.meta_key = '_tutor_course_id_for_assignments'
 			WHERE		post_type = %s
			 			AND post_meta.meta_value = %d
			ORDER BY 	ID DESC;
			",
				$assignment_post_type,
				$course_id
			)
		);

		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
			FROM 	{$wpdb->postmeta} post_meta
 					INNER JOIN {$wpdb->posts} assignment
					 		ON post_meta.post_id = assignment.ID
						   AND post_meta.meta_key = '_tutor_course_id_for_assignments'
 			WHERE	post_type = %s
			 		AND post_meta.meta_value = %d
			ORDER BY ID DESC;
			",
				$assignment_post_type,
				$course_id
			)
		);

		return (object) array(
			'count'   => $count,
			'results' => $query,
		);
	}

	/**
	 * @return bool
	 *
	 * Determine if script debug
	 *
	 * @since v.1.3.4
	 */
	public function is_script_debug() {
		 return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
	}

	/**
	 * Check lesson edit access by instructor
	 *
	 * @since  v.1.4.0
	 */
	public function has_lesson_edit_access( $lesson_id = 0, $instructor_id = 0 ) {
		$lesson_id     = $this->get_post_id( $lesson_id );
		$instructor_id = $this->get_user_id( $instructor_id );

		if ( user_can( $instructor_id, tutor()->instructor_role ) ) {
			$permitted_course_ids = $this->get_assigned_courses_ids_by_instructors();
			$course_id            = $this->get_course_id_by( 'lesson', $lesson_id );

			if ( in_array( $course_id, $permitted_course_ids ) ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Get total Enrolments
	 *
	 * @since v.1.4.0
	 */
	public function get_total_enrolments( $status, $search_term = '', $course_id = '', $date = '' ) {
		global $wpdb;
		$status      = sanitize_text_field( $status );
		$course_id   = sanitize_text_field( $course_id );
		$date        = sanitize_text_field( $date );
		$search_term = sanitize_text_field( $search_term );

		$search_term_raw = $search_term;
		$search_term     = '%' . $wpdb->esc_like( $search_term ) . '%';

		// add course id in where clause.
		$course_query = '';
		if ( '' !== $course_id ) {
			$course_query = "AND course.ID = $course_id";
		}

		// add date in where clause.
		$date_query = '';
		if ( '' !== $date ) {
			$date_query = "AND DATE(enrol.post_date) = CAST('$date' AS DATE) ";
		}

		// add status in where clause.
		if ( 'approved' === $status ) {
			$status = 'completed';
		} elseif ( 'cancelled' === $status ) {
			$status = 'cancel';
		} else {
			$status = '';
		}
		$status_query = "AND enrol.post_status IN ('completed', 'cancel') ";
		if ( '' !== $status ) {
			$status_query = "AND enrol.post_status = '$status' ";
		}

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(enrol.ID)
			FROM 	{$wpdb->posts} enrol
					INNER JOIN {$wpdb->posts} course
							ON enrol.post_parent = course.ID
					INNER JOIN {$wpdb->users} student
							ON enrol.post_author = student.ID
			WHERE 	enrol.post_type = %s
					{$status_query}
					{$course_query}
					{$date_query}
					AND ( enrol.ID LIKE %s OR student.display_name LIKE %s OR student.user_email = %s OR course.post_title LIKE %s );
			",
				'tutor_enrolled',
				$search_term,
				$search_term,
				$search_term_raw,
				$search_term
			)
		);

		return (int) $count;
	}

	public function get_enrolments( $status, $start = 0, $limit = 10, $search_term = '', $course_id = '', $date = '', $order = 'DESC' ) {
		global $wpdb;
		$status      = sanitize_text_field( $status );
		$course_id   = sanitize_text_field( $course_id );
		$date        = sanitize_text_field( $date );
		$search_term = sanitize_text_field( $search_term );

		$search_term_raw = $search_term;
		$search_term     = '%' . $wpdb->esc_like( $search_term ) . '%';

		// add course id in where clause.
		$course_query = '';
		if ( '' !== $course_id ) {
			$course_query = "AND course.ID = $course_id";
		}

		// add date in where clause.
		$date_query = '';
		if ( '' !== $date ) {
			$date_query = "AND DATE(enrol.post_date) = CAST('$date' AS DATE) ";
		}

		// add status in where clause.
		if ( 'approved' === $status ) {
			$status = 'completed';
		} elseif ( 'cancelled' === $status ) {
			$status = 'cancel';
		} else {
			$status = '';
		}
		// default will return approved & cancelled status record.
		$status_query = "AND enrol.post_status IN ('completed', 'cancel') ";
		if ( '' !== $status ) {
			$status_query = "AND enrol.post_status = '$status' ";
		}

		$enrolments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT enrol.ID AS enrol_id,
					enrol.post_author AS student_id,
					enrol.post_date AS enrol_date,
					enrol.post_title AS enrol_title,
					enrol.post_status AS status,
					enrol.post_parent AS course_id,
					course.post_title AS course_title,
					course.guid,
					student.user_nicename,
					student.user_email,
					student.display_name
			FROM 	{$wpdb->posts} enrol
					INNER JOIN {$wpdb->posts} course
							ON enrol.post_parent = course.ID
					INNER JOIN {$wpdb->users} student
							ON enrol.post_author = student.ID
			WHERE 	enrol.post_type = %s
					{$status_query}
					{$course_query}
					{$date_query}
					AND ( enrol.ID LIKE %s OR student.display_name LIKE %s OR student.user_email = %s OR course.post_title LIKE %s )
			ORDER BY enrol_id {$order}
			LIMIT 	%d, %d;
			",
				'tutor_enrolled',
				$search_term,
				$search_term,
				$search_term_raw,
				$search_term,
				$start,
				$limit
			)
		);

		return $enrolments;
	}

	/**
	 * @param int $post_id
	 *
	 * @return false|string
	 *
	 * @since v.1.4.0
	 */
	public function get_current_url( $post_id = 0 ) {
		$page_id = $this->get_post_id( $post_id );

		if ( $page_id ) {
			return get_the_permalink( $page_id );
		} else {
			global $wp;
			$wp->parse_request();
			$current_url = home_url( $wp->request );
			return $current_url;
		}
	}

	/**
	 * @param int $rating_id
	 *
	 * @return object
	 *
	 * Get rating by rating id|comment_ID
	 *
	 * @since v.1.4.0
	 */

	public function get_rating_by_id( $rating_id = 0 ) {
		global $wpdb;

		$ratings = array(
			'rating' => 0,
			'review' => '',
		);

		$rating = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT meta_value AS rating,
					comment_content AS review
			FROM 	{$wpdb->comments}
					INNER JOIN {$wpdb->commentmeta}
							ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id
			WHERE 	{$wpdb->comments}.comment_ID = %d;
			",
				$rating_id
			)
		);

		if ( $rating ) {
			$rating_format = number_format( $rating->rating, 2 );

			$ratings = array(
				'rating' => $rating_format,
				'review' => $rating->review,
			);
		}
		return (object) $ratings;
	}

	/**
	 * @param int  $course_id
	 * @param null $key
	 * @param bool $default
	 *
	 * @return array|bool|mixed
	 *
	 * Get course settings by course ID
	 */
	public function get_course_settings( $course_id = 0, $key = null, $default = false ) {
		$course_id     = $this->get_post_id( $course_id );
		$settings_meta = get_post_meta( $course_id, '_tutor_course_settings', true );
		$settings      = (array) maybe_unserialize( $settings_meta );

		return $this->array_get( $key, $settings, $default );
	}

	/**
	 * @param int  $lesson_id
	 * @param null $key
	 * @param bool $default
	 *
	 * @return array|bool|mixed
	 *
	 * Get Lesson content drip settings
	 *
	 * @since v.1.4.0
	 */
	public function get_item_content_drip_settings( $lesson_id = 0, $key = null, $default = false ) {
		$lesson_id     = $this->get_post_id( $lesson_id );
		$settings_meta = get_post_meta( $lesson_id, '_content_drip_settings', true );
		$settings      = (array) maybe_unserialize( $settings_meta );

		return $this->array_get( $key, $settings, $default );
	}

	/**
	 * @param null $post
	 *
	 * @return bool
	 *
	 * Get previous ID
	 */
	public function get_course_previous_content_id( $current_id, $exclude_type = array() ) {
		$course_id = $this->get_course_id_by_content( $current_id );
		$topics    = $this->get_topics( $course_id );

		$content_ids = array();

		foreach ( $topics->posts as $topic ) {
			$contents = $this->get_course_contents_by_topic( $topic->ID, -1 );

			foreach ( $contents->posts as $content ) {
				if ( ! in_array( $content->post_type, $exclude_type ) ) {
					$content_ids[] = $content->ID;
				}
			}
		}

		foreach ( $content_ids as $key => $content_id ) {
			if ( $current_id == $content_id ) {
				if ( ! empty( $content_ids[ $key - 1 ] ) ) {
					return $content_ids[ $key - 1 ];
				}
			}
		}

		return false;
	}

	/**
	 * @param null $post
	 *
	 * @return int
	 *
	 * Get Course iD by any course content
	 */
	public function get_course_id_by_content( $post ) {
		return $this->get_course_id_by_subcontent( is_numeric( $post ) ? $post : $post->ID );
	}

	/**
	 * @param int $course_id
	 *
	 * @return array|null|object
	 *
	 * Get Course contents by Course ID
	 *
	 * @since v.1.4.1
	 */
	public function get_course_contents_by_id( $course_id = 0 ) {
		global $wpdb;

		$course_id = $this->get_post_id( $course_id );

		$cache_key = "tutor_get_course_contents_by_{$course_id}";

		$contents = TutorCache::get( $cache_key );
		if ( false === $contents ) {
			$contents = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT items.*
				FROM 	{$wpdb->posts} topic
						INNER JOIN {$wpdb->posts} items
								ON topic.ID = items.post_parent
				WHERE 	topic.post_parent = %d
						AND items.post_status = %s
				ORDER BY topic.menu_order ASC,
						items.menu_order ASC;
				",
					$course_id,
					'publish'
				)
			);
			TutorCache::set( $cache_key, $contents );
		}
		return $contents;
	}

	/**
	 * @param string $grade_for
	 *
	 * @return array|null|object
	 *
	 * Get Gradebooks lists by type
	 *
	 * @since v.1.4.2
	 */
	public function get_gradebooks() {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->tutor_gradebooks} ORDER BY grade_point DESC " );
		return $results;
	}


	/**
	 * @param int $quiz_id
	 * @param int $user_id
	 *
	 * @return array|bool|null|object
	 *
	 * Get Attempt row by grade method settings
	 *
	 * @since v.1.4.2
	 */
	public function get_quiz_attempt( $quiz_id = 0, $user_id = 0 ) {
		global $wpdb;

		$quiz_id = $this->get_post_id( $quiz_id );
		$user_id = $this->get_user_id( $user_id );

		$attempt = false;

		$quiz_grade_method = get_tutor_option( 'quiz_grade_method', 'highest_grade' );
		$from_string       = "FROM {$wpdb->tutor_quiz_attempts} WHERE quiz_id = %d AND user_id = %d AND attempt_status != 'attempt_started' ";

		if ( $quiz_grade_method === 'highest_grade' ) {

			$attempt = $wpdb->get_row( $wpdb->prepare( "SELECT * {$from_string} ORDER BY earned_marks DESC LIMIT 1; ", $quiz_id, $user_id ) );
		} elseif ( $quiz_grade_method === 'average_grade' ) {

			$attempt = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT {$wpdb->tutor_quiz_attempts}.*,
						COUNT(attempt_id) AS attempt_count,
						AVG(total_marks) AS total_marks,
						AVG(earned_marks) AS earned_marks {$from_string}
				",
					$quiz_id,
					$user_id
				)
			);
		} elseif ( $quiz_grade_method === 'first_attempt' ) {

			$attempt = $wpdb->get_row( $wpdb->prepare( "SELECT * {$from_string} ORDER BY attempt_id ASC LIMIT 1; ", $quiz_id, $user_id ) );
		} elseif ( $quiz_grade_method === 'last_attempt' ) {

			$attempt = $wpdb->get_row( $wpdb->prepare( "SELECT * {$from_string} ORDER BY attempt_id DESC LIMIT 1; ", $quiz_id, $user_id ) );
		}

		return $attempt;
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return string
	 *
	 * Print Course Status Context
	 *
	 * @since v.1.4.2
	 */
	public function course_progress_status_context( $course_id = 0, $user_id = 0 ) {
		$course_id    = $this->get_post_id( $course_id );
		$user_id      = $this->get_user_id( $user_id );
		$is_completed = $this->is_completed_course( $course_id, $user_id );

		$html = '';
		if ( $is_completed ) {
			$html = '<span class="course-completion-status course-completed"><i class="tutor-icon-mark"></i> ' . __( 'Completed', 'tutor' ) . ' </span>';
		} else {
			$is_in_progress = $this->get_completed_lesson_count_by_course( $course_id, $user_id );
			if ( $is_in_progress ) {
				$html = '<span class="course-completion-status course-inprogress"><i class="tutor-icon-refresh-o"></i> ' . __( 'In Progress', 'tutor' ) . ' </span>';
			} else {
				$html = '<span class="course-completion-status course-not-taken"><i class="tutor-icon-spinner"></i> ' . __( 'Not Taken', 'tutor' ) . ' </span>';
			}
		}
		return $html;
	}

	/**
	 * @param $user
	 * @param $new_pass
	 *
	 * Reset Password
	 *
	 * @since v.1.4.3
	 */
	public function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );

		wp_set_password( $new_pass, $user->ID );

		$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
		$rp_path   = isset( $_SERVER['REQUEST_URI'] ) ? current( explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : ''; // WPCS: input var ok, sanitization ok.

		setcookie( $rp_cookie, ' ', tutor_time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		wp_password_change_notification( $user );
	}

	/**
	 * @return array
	 *
	 * Get tutor pages, required to show dashboard, and others forms
	 *
	 * @since v.1.4.3
	 */
	public function tutor_pages() {
		$pages = apply_filters(
			'tutor_pages',
			array(
				'tutor_dashboard_page_id'  => __( 'Dashboard Page', 'tutor' ),
				'instructor_register_page' => __( 'Instructor Registration Page', 'tutor' ),
				'student_register_page'    => __( 'Student Registration Page', 'tutor' ),
			)
		);

		$new_pages = array();
		foreach ( $pages as $key => $page ) {
			$page_id = (int) get_tutor_option( $key );

			$wp_page_name = '';
			$wp_page      = get_post( $page_id );
			$page_exists  = (bool) $wp_page;
			$page_visible = false;

			if ( $wp_page ) {
				$wp_page_name = $wp_page->post_title;
				$page_visible = $wp_page->post_status === 'publish';
			} else {
				$page_id = 0;
			}

			$new_pages[] = array(
				'option_key'   => $key,
				'page_name'    => $page,
				'wp_page_name' => $wp_page_name,
				'page_id'      => $page_id,
				'page_exists'  => $page_exists,
				'page_visible' => $page_visible,
			);
		}

		return $new_pages;
	}

	/**
	 * @param int $course_id
	 *
	 * @return array|null|object
	 *
	 * Get Course prev next lession contents by content ID
	 *
	 * @since v.1.4.9
	 */
	public function get_course_prev_next_contents_by_id( $content_id = 0 ) {

		$course_id       = $this->get_course_id_by_content( $content_id );
		$course_contents = $this->get_course_contents_by_id( $course_id );
		$previous_id     = 0;
		$next_id         = 0;

		if ( $this->count( $course_contents ) ) {
			$ids = wp_list_pluck( $course_contents, 'ID' );

			$i = 0;
			foreach ( $ids as $key => $id ) {
				$previous_i = $key - 1;
				$next_i     = $key + 1;

				if ( $id == $content_id ) {
					if ( isset( $ids[ $previous_i ] ) ) {
						$previous_id = $ids[ $previous_i ];
					}
					if ( isset( $ids[ $next_i ] ) ) {
						$next_id = $ids[ $next_i ];
					}
				}
				$i++;
			}
		}

		return (object) array(
			'previous_id' => $previous_id,
			'next_id'     => $next_id,
		);
	}

	/**
	 * Get a subset of the items from the given array.
	 *
	 * @param array        $array
	 * @param array|string $keys
	 *
	 * @return array|bool
	 *
	 * @since v.1.5.2
	 */
	public function array_only( $array = array(), $keys = null ) {
		if ( ! $this->count( $array ) || ! $keys ) {
			return false;
		}

		return array_intersect_key( $array, array_flip( (array) $keys ) );
	}

	/**
	 * @param int $instructor_id
	 * @param int $course_id
	 *
	 * @return bool|int
	 *
	 * Is instructor of this course
	 *
	 * @since v.1.6.4
	 */
	public function is_instructor_of_this_course( $instructor_id = 0, $course_id = 0 ) {
		global $wpdb;

		$instructor_id = $this->get_user_id( $instructor_id );
		$course_id     = $this->get_post_id( $course_id );

		if ( ! $instructor_id || ! $course_id ) {
			return false;
		}

		$cache_key  = "tutor_is_instructor_of_the_course_{$instructor_id}_{$course_id}";
		$instructor = TutorCache::get( $cache_key );

		if ( false === $instructor ) {
			$instructor = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT umeta_id
				FROM   {$wpdb->usermeta}
				WHERE  user_id = %d
					AND meta_key = '_tutor_instructor_course_id'
					AND meta_value = %d
				",
					$instructor_id,
					$course_id
				)
			);
			TutorCache::set( $cache_key, $instructor );
		}

		if ( is_array( $instructor ) && count( $instructor ) ) {
			return $instructor;
		}

		return false;
	}

	/**
	 * @param int $user_id
	 *
	 * @return array|object
	 *
	 * User profile completion
	 *
	 * @since v.1.6.6
	 */
	public function user_profile_completion( $user_id = 0 ) {
		$user_id           = $this->get_user_id( $user_id );
		$instructor        = $this->is_instructor( $user_id );
		$instructor_status = get_user_meta( $user_id, '_tutor_instructor_status', true );

		$settings_url          = $this->tutor_dashboard_url( 'settings' );
		$withdraw_settings_url = $this->tutor_dashboard_url( 'settings/withdraw-settings' );

		$required_fields = array(
			'_tutor_profile_photo' => __( 'Set Your Profile Photo', 'tutor' ),
			'_tutor_profile_bio'   => __( 'Set Your Bio', 'tutor' ),
		);

		// Add payment method as a required on if current user is an approved instructor
		if ( 'approved' == $instructor_status ) {
			$required_fields['_tutor_withdraw_method_data'] = __( 'Set Withdraw Method', 'tutor' );
		}

		// url where user should redirect for profile completion.
		$profile_completion_urls = array(
			'_tutor_profile_photo'        => $settings_url,
			'_tutor_profile_bio'          => $settings_url,
			'_tutor_withdraw_method_data' => $withdraw_settings_url,
		);
		foreach ( $required_fields as $key => $field ) {
			$required_fields[ $key ] = array(
				'text'   => $field,
				'is_set' => get_user_meta( $user_id, $key, true ) ? true : false,
				'url'    => $profile_completion_urls[ $key ],
			);
		}

		// Apply fitlers on the list
		return apply_filters( 'tutor/user/profile/completion', $required_fields );
	}

	/**
	 * @param int $enrol_id
	 *
	 * @return array|object
	 *
	 * Get enrollment by enrol_id
	 *
	 * @since v1.6.9
	 */
	public function get_enrolment_by_enrol_id( $enrol_id = 0 ) {
		global $wpdb;

		$enrolment = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT enrol.id          AS enrol_id,
					enrol.post_author AS student_id,
					enrol.post_date   AS enrol_date,
					enrol.post_title  AS enrol_title,
					enrol.post_status AS status,
					enrol.post_parent AS course_id,
					course.post_title AS course_title,
					student.user_nicename,
					student.user_email,
					student.display_name,
					student.ID
			FROM   {$wpdb->posts} enrol
					INNER JOIN {$wpdb->posts} course
							ON enrol.post_parent = course.id
					INNER JOIN {$wpdb->users} student
							ON enrol.post_author = student.id
			WHERE  enrol.id = %d;
		",
				$enrol_id
			)
		);

		if ( $enrolment ) {
			return $enrolment;
		}

		return false;
	}

	/**
	 * Get students list based on course id
	 *
	 * @param integer $course_id
	 * @param string  $field_name
	 * @param boolean $all  if all is false it will return only $field_name column
	 *
	 * @return array  of objects for student list or array
	 */
	public function get_students_data_by_course_id( $course_id = 0, $field_name = 'ID', $all = false ) {

		global $wpdb;
		$course_id = $this->get_post_id( $course_id );

		$student_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT student.{$field_name}, student.display_name as display_name, student.user_login as username, student.user_email
			FROM   	{$wpdb->posts} enrol
					INNER JOIN {$wpdb->users} student
						    ON enrol.post_author = student.id
			WHERE  	enrol.post_type = %s
					AND enrol.post_parent = %d
					AND enrol.post_status = %s;
			",
				'tutor_enrolled',
				$course_id,
				'completed'
			)
		);
		if ( $all ) {
			return $student_data;
		}
		return array_column( $student_data, $field_name );
	}

	public function get_students_all_data_by_course_id( $course_id = 0 ) {

		global $wpdb;
		$course_id = $this->get_post_id( $course_id );

		$student_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
			FROM   	{$wpdb->posts} enrol
					INNER JOIN {$wpdb->users} student
						    ON enrol.post_author = student.id
			WHERE  	enrol.post_type = %s
					AND enrol.post_parent = %d
					AND enrol.post_status = %s;
			",
				'tutor_enrolled',
				$course_id,
				'completed'
			)
		);

		return array_column( $student_data, $field_name );
	}

	/**
	 * @param int $course_id
	 *
	 * @return array
	 *
	 * @since v1.6.9
	 *
	 * Get students email by course id
	 */
	public function get_student_emails_by_course_id( $course_id = 0 ) {
		return $this->get_students_data_by_course_id( $course_id, 'user_email' );
	}

	/*
	*requie post id & user id
	*return single comment post
	*/
	public function get_single_comment_user_post_id( $post_id, $user_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'comments';
		$query = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
			FROM 	$table
			WHERE 	comment_post_ID = %d
					AND user_id = %d
			LIMIT 	1
			",
				$post_id,
				$user_id
			)
		);
		return $query ? $query : false;
	}

	/**
	 * @param int $course_id
	 *
	 * @return bool
	 *
	 * @since v1.7.5
	 *
	 * Check if course is in wc cart
	 */
	public function is_course_added_to_cart( $course_or_product_id = 0, $is_product_id = false ) {

		switch ( $this->get_option( 'monetize_by' ) ) {
			case 'wc':
				global $woocommerce;
				$product_id = $is_product_id ? $course_or_product_id : $this->get_course_product_id( $course_or_product_id );

				if ( $woocommerce->cart ) {
					foreach ( $woocommerce->cart->get_cart() as $key => $val ) {
						if ( $product_id == $val['product_id'] ) {
							return true;
						}
					}
				}
				break;
		}
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool
	 *
	 * @since v1.7.5
	 *
	 * Get profile pic url
	 */
	public function get_cover_photo_url( $user_id ) {
		$cover_photo_src = tutor()->url . 'assets/images/cover-photo.jpg';
		$cover_photo_id  = get_user_meta( $user_id, '_tutor_cover_photo', true );
		if ( $cover_photo_id ) {
			$url                               = wp_get_attachment_image_url( $cover_photo_id, 'full' );
			! empty( $url ) ? $cover_photo_src = $url : 0;
		}

		return $cover_photo_src;
	}

	/**
	 * @return int
	 *
	 * @since v1.7.9
	 *
	 * Return the course ID(s) by lession, quiz, answer etc.
	 */
	public function get_course_id_by( $content, $object_id ) {
		$cache_key = "tutor_get_course_id_by_{$content}_{$object_id}";
		$course_id = TutorCache::get( $cache_key );

		if ( false === $course_id ) {
			global $wpdb;
			switch ( $content ) {
				case 'course':
					$course_id = $object_id;
					break;
	
				case 'zoom_meeting':
				case 'tutor_gm_course':
				case 'topic':
				case 'announcement':
					$course_id = wp_get_post_parent_id( $object_id );
					break;
	
				case 'zoom_lesson':
				case 'tutor_gm_topic':
				case 'lesson':
				case 'quiz':
				case 'assignment':
					$topic_id = wp_get_post_parent_id( $object_id );
					if ( ! $topic_id ) {
						$course_id = $wpdb->get_var(
							$wpdb->prepare(
								"SELECT meta_value
							FROM {$wpdb->prefix}postmeta
							WHERE post_id=%d AND meta_key='_tutor_course_id_for_lesson'",
								$object_id
							)
						);
					} else {
						$course_id = wp_get_post_parent_id( $topic_id );
					}
					break;
	
				case 'assignment_submission':
					$course_id = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT DISTINCT _course.ID
						FROM {$wpdb->posts} _course
							INNER JOIN {$wpdb->posts} _topic ON _topic.post_parent=_course.ID
							INNER JOIN {$wpdb->posts} _assignment ON _assignment.post_parent=_topic.ID
							INNER JOIN {$wpdb->comments} _submission ON _submission.comment_post_ID=_assignment.ID
						WHERE _submission.comment_ID=%d;",
							$object_id
						)
					);
					break;
	
				case 'question':
					$course_id = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT topic.post_parent
						FROM 	{$wpdb->posts} topic
								INNER JOIN {$wpdb->posts} quiz
										ON quiz.post_parent=topic.ID
								INNER JOIN {$wpdb->prefix}tutor_quiz_questions question
										ON question.quiz_id=quiz.ID
						WHERE 	question.question_id = %d;
						",
							$object_id
						)
					);
					break;
	
				case 'quiz_answer':
					$course_id = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT topic.post_parent
						FROM 	{$wpdb->posts} topic
								INNER JOIN {$wpdb->posts} quiz
										ON quiz.post_parent=topic.ID
								INNER JOIN {$wpdb->prefix}tutor_quiz_questions question
										ON question.quiz_id=quiz.ID
								INNER JOIN {$wpdb->prefix}tutor_quiz_question_answers answer
										ON answer.belongs_question_id=question.question_id
						WHERE 	answer.answer_id = %d;
						",
							$object_id
						)
					);
					break;
	
				case 'attempt':
					$course_id = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT course_id
						FROM 	{$wpdb->prefix}tutor_quiz_attempts
						WHERE 	attempt_id=%d;
						",
							$object_id
						)
					);
					break;
	
				case 'attempt_answer':
					$course_id = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT course_id
						FROM 	{$wpdb->prefix}tutor_quiz_attempts
						WHERE 	attempt_id = (SELECT quiz_attempt_id FROM {$wpdb->prefix}tutor_quiz_attempt_answers WHERE attempt_answer_id=%d)
						",
							$object_id
						)
					);
					break;
	
				case 'review':
				case 'qa_question':
					$question = get_comment( $object_id );
					if ( is_a( $question, 'WP_Comment' ) ) {
						$course_id = $question->comment_post_ID;
					}
					break;
	
				case 'instructor':
					$course_ids = get_user_meta( $object_id, '_tutor_instructor_course_id' );
	
					! is_array( $course_ids ) ? $course_ids = array() : 0;
					$course_id                              = array_filter(
						$course_ids,
						function ( $id ) {
							return ( $id && is_numeric( $id ) );
						}
					);
					break;
			}
			
			TutorCache::set( $cache_key, $course_id );
		}

		return $course_id;
	}


	/**
	 * @return int
	 *
	 * @since v1.7.9
	 *
	 * Return the course ID(s) by lession, quiz, answer etc.
	 */
	public function get_course_id_by_subcontent( $content_id ) {
		$mapping = array(
			'tutor_assignments'  => 'assignment',
			'tutor_quiz'         => 'quiz',
			'lesson'             => 'lesson',
			'tutor_zoom_meeting' => 'zoom_meeting',
			'tutor_zoom_lesson'  => 'zoom_lesson',
			'tutor_gm_course'    => 'tutor_gm_course',
			'tutor_gm_topic'     => 'tutor_gm_topic',
			'topics'             => 'topic',
		);

		$content_type = get_post_field( 'post_type', $content_id );

		// Differentiate standalone zoom meeting and zoom lesson
		if ( $content_type == 'tutor_zoom_meeting' ) {
			$parent_id   = wp_get_post_parent_id( $content_id );
			$parent_type = get_post_field( 'post_type', $parent_id );

			$content_type = $parent_type == tutor()->course_post_type ? 'tutor_zoom_meeting' : 'tutor_zoom_lesson';
		}
		if ( $content_type == 'tutor-google-meet' ) {
			$parent_id   = wp_get_post_parent_id( $content_id );
			$parent_type = get_post_field( 'post_type', $parent_id );

			$content_type = $parent_type == tutor()->course_post_type ? 'tutor_gm_course' : 'tutor_gm_topic';
		}
		return $this->get_course_id_by( $mapping[ $content_type ], $content_id );
	}

	/**
	 * @return bool
	 *
	 * @since v1.7.7
	 *
	 * Check if user can create, edit, delete various tutor contents such as lesson, quiz, answer etc.
	 */
	public function can_user_manage( $content, $object_id, $user_id = 0, $allow_current_admin = true ) {
		$user_id   = (int) $this->get_user_id( $user_id );
		$course_id = $this->get_course_id_by( $content, $object_id );

		if ( $course_id ) {
			if ( $allow_current_admin && current_user_can( 'administrator' ) ) {
				// Admin has access to everything
				return true;
			}

			$instructors    = $this->get_instructors_by_course( $course_id );
			$instructor_ids = is_array( $instructors ) ? array_map(
				function ( $instructor ) {
					return (int) $instructor->ID;
				},
				$instructors
			) : array();

			$is_listed = in_array( $user_id, $instructor_ids );

			if ( $is_listed ) {
				return true;
			}
		}

		global $wpdb;
		switch ( $content ) {
			case 'review':
			case 'qa_question':
				// just check if own content. Instructor privilege already checked in the earlier blocks
				$id = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT comment_ID
					FROM {$wpdb->comments} WHERE user_id = %d AND comment_ID=%d",
						$user_id,
						$object_id
					)
				);

				return $id ? true : false;
		}

		return false;
	}

	/**
	 * @return bool
	 *
	 * @since v1.7.9
	 *
	 * Check if user has access for content like lesson, quiz, assignment etc.
	 */
	public function has_enrolled_content_access( $content, $object_id = 0, $user_id = 0 ) {
		$user_id   = $this->get_user_id( $user_id );
		$object_id = $this->get_post_id( $object_id );
		$course_id = $this->get_course_id_by( $content, $object_id );

		do_action( 'tutor_before_enrolment_check', $course_id, $user_id );

		if ( $this->is_enrolled( $course_id, $user_id ) || $this->has_user_course_content_access( $user_id, $course_id ) ) {
			return true;
		}

		// Check Lesson edit access to support page builders (eg: Oxygen)
		if ( current_user_can( tutor()->instructor_role ) && $this->has_lesson_edit_access() ) {
			return true;
		}

		return false;
	}

	/**
	 * @return date
	 *
	 * @since v1.8.0
	 *
	 * Return the assignment deadline date based on duration and assignment creation date
	 */
	public function get_assignment_deadline_date( $assignment_id, $format = null, $fallback = null ) {

		! $format ? $format = 'j F, Y, g:i a' : 0;

		$value = $this->get_assignment_option( $assignment_id, 'time_duration.value' );
		$time  = $this->get_assignment_option( $assignment_id, 'time_duration.time' );

		if ( ! $value ) {
			return $fallback;
		}

		$publish_date = get_post_field( 'post_date', $assignment_id );

		$date = date_create( $publish_date );
		date_add( $date, date_interval_create_from_date_string( $value . ' ' . $time ) );

		return date_format( $date, $format );
	}

	/**
	 * @return array
	 *
	 * @since v1.8.2
	 *
	 * Get earning chart data
	 */
	public function get_earning_chart( $user_id, $start_date, $end_date ) {
		global $wpdb;

		/**
		 * Format Date Name
		 */
		$begin    = new \DateTime( $start_date );
		$end      = new \DateTime( $end_date );
		$interval = \DateInterval::createFromDateString( '1 day' );
		$period   = new \DatePeriod( $begin, $interval, $end );

		$datesPeriod = array();
		foreach ( $period as $dt ) {
			$datesPeriod[ $dt->format( 'Y-m-d' ) ] = 0;
		}

		// Get statuses
		$complete_status = $this->get_earnings_completed_statuses();
		$statuses        = $complete_status;
		$complete_status = "'" . implode( "','", $complete_status ) . "'";

		$salesQuery = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT  SUM(instructor_amount) AS total_earning,
					DATE(created_at) AS date_format
			FROM	{$wpdb->prefix}tutor_earnings
			WHERE 	user_id = %d
					AND order_status IN({$complete_status})
					AND (created_at BETWEEN %s AND %s)
			GROUP BY date_format
			ORDER BY created_at ASC;
			",
				$user_id,
				$start_date,
				$end_date
			)
		);

		$total_earning = wp_list_pluck( $salesQuery, 'total_earning' );
		$queried_date  = wp_list_pluck( $salesQuery, 'date_format' );
		$dateWiseSales = array_combine( $queried_date, $total_earning );
		$chartData     = array_merge( $datesPeriod, $dateWiseSales );

		foreach ( $chartData as $key => $salesCount ) {
			unset( $chartData[ $key ] );
			$formatDate               = date( 'd M', strtotime( $key ) );
			$chartData[ $formatDate ] = $salesCount;
		}

		$statements  = $this->get_earning_statements( $user_id, compact( 'start_date', 'end_date', 'statuses' ) );
		$earning_sum = $this->get_earning_sum( $user_id, compact( 'start_date', 'end_date' ) );

		return array(
			'chartData'   => $chartData,
			'statements'  => $statements,
			'statuses'    => $statuses,
			'begin'       => $begin,
			'end'         => $end,
			'earning_sum' => $earning_sum,
			'datesPeriod' => $datesPeriod,
		);
	}

	/**
	 * @return array
	 *
	 * @since v1.8.2
	 *
	 * Get earning chart data yearly
	 */
	public function get_earning_chart_yearly( $user_id, $year ) {
		global $wpdb;

		$complete_status = $this->get_earnings_completed_statuses();
		$statuses        = $complete_status;
		$complete_status = "'" . implode( "','", $complete_status ) . "'";

		$salesQuery = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SUM(instructor_amount) AS total_earning,
					MONTHNAME(created_at)  AS month_name
			FROM  	{$wpdb->prefix}tutor_earnings
			WHERE	user_id = %d
					AND order_status IN({$complete_status})
					AND YEAR(created_at) = %s
			GROUP BY MONTH (created_at)
			ORDER BY MONTH(created_at) ASC;
			",
				$user_id,
				$year
			)
		);

		$total_earning  = wp_list_pluck( $salesQuery, 'total_earning' );
		$months         = wp_list_pluck( $salesQuery, 'month_name' );
		$monthWiseSales = array_combine( $months, $total_earning );

		$dataFor = 'yearly';

		/**
		 * Format yearly
		 */
		$emptyMonths = array();
		for ( $m = 1; $m <= 12; $m++ ) {
			$emptyMonths[ date( 'F', mktime( 0, 0, 0, $m, 1, date( 'Y' ) ) ) ] = 0;
		}

		$chartData   = array_merge( $emptyMonths, $monthWiseSales );
		$statements  = $this->get_earning_statements( $user_id, compact( 'year', 'dataFor', 'statuses' ) );
		$earning_sum = $this->get_earning_sum( $user_id, compact( 'year', 'dataFor' ) );

		return array(
			'chartData'   => $chartData,
			'statements'  => $statements,
			'earning_sum' => $earning_sum,
		);
	}

	/**
	 * @return object
	 *
	 * @since v1.8.4
	 *
	 * Return object from vendor package
	 */
	function get_package_object() {
		 $params = func_get_args();

		$is_pro     = $params[0];
		$class      = $params[1];
		$class_args = array_slice( $params, 2 );
		$root_path  = $is_pro ? tutor_pro()->path : tutor()->path;

		require_once $root_path . '/vendor/autoload.php';

		$reflector = new \ReflectionClass( $class );
		$object    = $reflector->newInstanceArgs( $class_args );

		return $object;
	}

	/**
	 * @return boolean
	 *
	 * @since v1.8.9
	 *
	 * Check if user has specific role
	 */
	public function has_user_role( $roles, $user_id = 0 ) {

		// Prepare the user ID and roles array
		! $user_id ? $user_id         = get_current_user_id() : 0;
		! is_array( $roles ) ? $roles = array( $roles ) : 0;

		// Get the user data and it's role array
		$user      = get_userdata( $user_id );
		$role_list = ( is_object( $user ) && is_array( $user->roles ) ) ? $user->roles : array();

		// Check if at least one role exists
		$without_roles = array_diff( $roles, $role_list );
		return count( $roles ) > count( $without_roles );
	}

	/**
	 * @return boolean
	 *
	 * @since v1.8.9
	 *
	 * Check if user can edit course
	 */
	public function can_user_edit_course( $user_id, $course_id ) {
		return $this->has_user_role( array( 'administrator', 'editor' ) ) || $this->is_instructor_of_this_course( $user_id, $course_id );
	}


	/**
	 * @return boolean
	 *
	 * @since v1.9.0
	 *
	 * Check if course member limit full
	 */
	public function is_course_fully_booked( $course_id = 0 ) {

		$total_enrolled   = $this->count_enrolled_users_by_course( $course_id );
		$maximum_students = (int) $this->get_course_settings( $course_id, 'maximum_students' );

		return $maximum_students && $maximum_students <= $total_enrolled;
	}

	function is_course_booked( $course_id = 0 ) {

		$total_enrolled   = $this->count_enrolled_users_by_course( $course_id );
		$maximum_students = (int) $this->get_course_settings( $course_id, 'maximum_students' );

		$total_booked = 100 / $maximum_students * $total_enrolled;

		return $total_booked;
	}

	/**
	 * @return boolean
	 *
	 * @since v1.9.2
	 *
	 * Check if current screen is under tutor dashboard
	 */
	public function is_tutor_dashboard( $subpage = null ) {

		// To Do: Add subpage check later

		if ( function_exists( 'is_admin' ) && is_admin() ) {
			$screen = get_current_screen();
			return is_object( $screen ) && $screen->parent_base == 'tutor';
		}

		return false;
	}

	/**
	 * @return boolean
	 *
	 * @since v1.9.4
	 *
	 * Check if current screen tutor frontend dashboard
	 */
	public function is_tutor_frontend_dashboard( $subpage = null ) {

		global $wp_query;
		if ( $wp_query->is_page ) {
			$dashboard_page = $this->array_get( 'tutor_dashboard_page', $wp_query->query_vars );

			if ( $subpage ) {
				return $dashboard_page == $subpage;
			}

			if ( $wp_query->queried_object && $wp_query->queried_object->ID ) {
				$d_id = apply_filters( 'tutor_dashboard_page_id_filter', $this->get_option( 'tutor_dashboard_page_id' ) );
				return $wp_query->queried_object->ID == $d_id;
			}
		}

		return false;
	}

	public function get_unique_slug( $slug, $post_type = null, $num_assigned = false ) {

		global $wpdb;
		$existing_slug = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_name
			FROM {$wpdb->posts}
			WHERE post_name=%s" . ( $post_type ? " AND post_type='{$post_type}' LIMIT 1" : '' ),
				$slug
			)
		);

		if ( ! $existing_slug ) {
			return $slug;
		}

		if ( ! $num_assigned ) {
			$new_slug = $slug . '-' . 2;
		} else {
			$new_slug = explode( '-', $slug );
			$number   = end( $new_slug ) + 1;

			array_pop( $new_slug );

			$new_slug = implode( '-', $new_slug ) . '-' . $number;
		}

		return $this->get_unique_slug( $new_slug, $post_type, true );
	}

	/**
	 * Get post content ids
	 *
	 * @param string $content_type like: lesson, quiz.
	 * @param string $ancestor_type like: course, topics
	 * @param string $ancestor_ids ancestor like course or topic
	 *
	 * @return array of ID cols
	 */
	public function get_course_content_ids_by( $content_type, $ancestor_type, $ancestor_ids ) {
		global $wpdb;
		$ids = array();

		// Convert single id to array
		! is_array( $ancestor_ids ) ? $ancestor_ids = array( $ancestor_ids ) : 0;
		$ancestor_ids                               = implode( ',', $ancestor_ids );

		$prepare_ancestor_ids = str_replace( ',', '_', $ancestor_ids );
		$cache_key 			  = "tutor_get_content_ids_{$content_type}_{$ancestor_type}_{$prepare_ancestor_ids}";
		$ids 				  = TutorCache::get( $cache_key );

		if ( false === $ids ) {
			switch ( $content_type ) {

				// Get lesson, quiz, assignment IDs
				case tutor()->lesson_post_type:
				case 'tutor_quiz':
				case 'tutor_assignments':
					switch ( $ancestor_type ) {

							// Get lesson, quiz, assignment IDs by course ID
						case tutor()->course_post_type:
							$content_ids = $wpdb->get_col(
								$wpdb->prepare(
									"SELECT content.ID FROM {$wpdb->posts} course
									INNER JOIN {$wpdb->posts} topic ON course.ID=topic.post_parent
									INNER JOIN {$wpdb->posts} content ON topic.ID=content.post_parent
								WHERE course.ID IN ({$ancestor_ids}) AND content.post_type=%s",
									$content_type
								)
							);

							// Assign id array to the variable
							is_array( $content_ids ) ? $ids = $content_ids : 0;
							break 2;
					}
					break;

				default:
					switch ( $ancestor_type ) {
						// Get lesson, quiz, assignment IDs by course ID
						case 'topic':
							$content_ids = $wpdb->get_col(
								"SELECT content.ID FROM {$wpdb->posts} content
								INNER JOIN {$wpdb->posts} topic ON topic.ID=content.post_parent
								WHERE topic.ID IN ({$ancestor_ids})"
							);

							is_array( $content_ids ) ? $ids = $content_ids : 0;
							break;
					}
			}
			TutorCache::set( $cache_key, $ids );
		}

		return $ids;
	}

	/**
	 * Get course element list
	 *
	 * @param string $content_type, content type like: lesson, assignment, quiz
	 * @param string $ancestor_type, content type like: lesson, assignment, quiz
	 * @param int    $ancestor_ids, post_parent id
	 * @return array
	 * @since v2.0.0
	 */
	public function get_course_content_list( string $content_type, string $ancestor_type, string $ancestor_ids ) {
		global $wpdb;
		$ids = array();
		// Convert single id to array
		! is_array( $ancestor_ids ) ? $ancestor_ids = array( $ancestor_ids ) : 0;
		$ancestor_ids                               = implode( ',', $ancestor_ids );
		switch ( $content_type ) {
				// Get lesson, quiz, assignment IDs
			case tutor()->lesson_post_type:
			case 'tutor_quiz':
			case 'tutor_assignments':
				switch ( $ancestor_type ) {
						// Get lesson, quiz, assignment IDs by course ID
					case tutor()->course_post_type:
						$content_ids = $wpdb->get_results(
							$wpdb->prepare(
								"SELECT content.* FROM {$wpdb->posts} course
									INNER JOIN {$wpdb->posts} topic ON course.ID = topic.post_parent
									INNER JOIN {$wpdb->posts} content ON topic.ID = content.post_parent AND content.post_type = %s
								WHERE course.ID IN ({$ancestor_ids})
							",
								$content_type
							)
						);

						// Assign id array to the variable
						$ids = $content_ids;
						break 2;
				}
		}

		return $ids;
	}

	/**
	 * @return array
	 *
	 * Sanitize array key abd values recursively
	 *
	 * @since v2.0.0
	 */
	public function sanitize_recursively( $array, $skip = array() ) {
		$new_array = array();
		if ( is_array( $array ) && ! empty( $array ) ) {
			foreach ( $array as $key => $value ) {
				$key = is_numeric( $key ) ? $key : sanitize_text_field( $key );
				if ( in_array( $key, $skip ) ) {
					$new_array[ $key ] = wp_kses_post( $value );
					continue;
				} elseif ( is_array( $value ) ) {
					$new_array[ $key ] = $this->sanitize_recursively( $value );
					continue;
				}
				// Leave numeric as it is
				$new_array[ $key ] = is_numeric( $value ) ? $value : sanitize_text_field( $value );
			}
		}
		return $array;
	}

	/**
	 * Get all courses along with topics & course materials for current student
	 *
	 * @since 1.9.10
	 *
	 * @return array
	 */
	public function course_with_materials(): array {
		$user_id          = get_current_user_id();
		$enrolled_courses = $this->get_enrolled_courses_by_user( $user_id );

		if ( false === $enrolled_courses ) {
			return array();
		}
		$data = array();
		foreach ( $enrolled_courses->posts as $key => $course ) {
			// push courses
			array_push( $data, array( 'course' => array( 'title' => $course->post_title ) ) );
			$topics = $this->get_topics( $course->ID );

			if ( ! is_null( $topics ) || count( $topics->posts ) ) {
				foreach ( $topics->posts as $topic_key => $topic ) {
					$materials = $this->get_course_contents_by_topic( $topic->ID, -1 );
					if ( count( $materials->posts ) || ! is_null( $materials->posts ) ) {
						$topic->materials = $materials->posts;
					}
					// push topics
					array_push( $data[ $key ]['course'], array( 'topics' => $topic ) );
				}
			}
		}
		return $data;
	}

	public function get_course_duration( $course_id, $return_array, $texts = array(
		'h' => 'hr',
		'm' => 'min',
		's' => 'sec',
	) ) {
		$duration        = maybe_unserialize( get_post_meta( $course_id, '_course_duration', true ) );
		$durationHours   = $this->avalue_dot( 'hours', $duration );
		$durationMinutes = $this->avalue_dot( 'minutes', $duration );
		$durationSeconds = $this->avalue_dot( 'seconds', $duration );

		if ( $return_array ) {
			return array(
				'duration'        => $duration,
				'durationHours'   => $durationHours,
				'durationMinutes' => $durationMinutes,
				'durationSeconds' => $durationSeconds,
			);
		}

		if ( ! $durationHours && ! $durationMinutes && ! $durationSeconds ) {
			return '';
		}

		return $durationHours . $texts['h'] . ' ' .
			$durationMinutes . $texts['m'] . ' ' .
			$durationSeconds . $texts['s'];
	}

	/**
	 * Prepare free addons data
	 */
	public function prepare_free_addons_data() {
		$addons       = apply_filters( 'tutor_pro_addons_lists_for_display', array() );
		$plugins_data = $addons;

		if ( is_array( $addons ) && count( $addons ) ) {
			foreach ( $addons as $base_name => $addon ) {

				$addonConfig = $this->get_addon_config( $base_name );

				$addons_path = trailingslashit( tutor()->path . "assets/addons/{$base_name}" );
				$addons_url  = trailingslashit( tutor()->url . "assets/addons/{$base_name}" );

				$thumbnailURL = tutor()->url . 'assets/images/tutor-plugin.png';
				if ( file_exists( $addons_path . 'thumbnail.png' ) ) {
					$thumbnailURL = $addons_url . 'thumbnail.png';
				} elseif ( file_exists( $addons_path . 'thumbnail.jpg' ) ) {
					$thumbnailURL = $addons_url . 'thumbnail.jpg';
				} elseif ( file_exists( $addons_path . 'thumbnail.svg' ) ) {
					$thumbnailURL = $addons_url . 'thumbnail.svg';
				}

				$plugins_data[ $base_name ]['url'] = $thumbnailURL;
			}
		}

		$prepared_addons = array();
		foreach ( $plugins_data as $tutor_addon ) {
			array_push( $prepared_addons, $tutor_addon );
		}

		return $prepared_addons;
	}

	/**
	 * Get completed assignment number
	 *
	 * @param int $course_id course id | required.
	 * @param int $student_id student id | required.
	 * @return int
	 */
	public function get_submitted_assignment_count( int $assignment_id, int $student_id ): int {
		global $wpdb;
		$assignments = $wpdb->get_var(
			$wpdb->prepare(
				" SELECT COUNT(*) FROM {$wpdb->posts} AS assignment
				INNER JOIN {$wpdb->posts} AS topic
					ON topic.ID = assignment.post_parent
				INNER JOIN {$wpdb->posts} AS course
					ON course.ID = topic.post_parent
				INNER JOIN {$wpdb->comments} AS submit
					ON submit.comment_post_ID = assignment.ID
				WHERE assignment.post_type = %s
					AND assignment.ID = %d
					AND submit.user_id = %d
			",
				'tutor_assignments',
				$assignment_id,
				$student_id
			)
		);
		return $assignments;
	}

	/**
	 * Get completed assignment number
	 *
	 * @param int $course_id course id | required.
	 * @param int $student_id student id | required.
	 * @return int
	 */
	public function count_completed_assignment( int $course_id, int $student_id ): int {
		global $wpdb;
		$count = $wpdb->get_var(
			$wpdb->prepare(
				" SELECT COUNT(*) FROM {$wpdb->posts} AS assignment
				INNER JOIN {$wpdb->posts} AS topic
					ON topic.ID = assignment.post_parent
				INNER JOIN {$wpdb->posts} AS course
					ON course.ID = topic.post_parent
				INNER JOIN {$wpdb->comments} AS submit
					ON submit.comment_post_ID = assignment.ID
				WHERE assignment.post_type = %s
					AND course.ID = %d
					AND submit.user_id = %d
			",
				'tutor_assignments',
				$course_id,
				$student_id
			)
		);
		return $count ? $count : 0;
	}

	/*
	 * Empty state template
	 *
	 * @param string $title
	 *
	 * @return mixed|html
	 */
	public function tutor_empty_state( string $title = 'No data yet!' ) {
		?>
		<div class="tutor-empty-state td-empty-state tutor-p-32 tutor-text-center">
			<img src="<?php echo esc_url( tutor()->url . 'assets/images/emptystate.svg' ); ?>" alt="<?php esc_attr_e( $title ); ?>" width="85%" />
			<div class="tutor-fs-6 tutor-color-secondary tutor-text-center">
				<?php echo esc_html( $title, 'tutor' ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get tutor TOC page link
	 * Settings > General > Terms and Conditions Page
	 *
	 * @return null | string
	 *
	 * @since 2.0.5
	 */
	function get_toc_page_link() {
		$tutor_toc_page_id   = (int) get_tutor_option( 'tutor_toc_page_id' );
		$tutor_toc_page_link = null;

		if ( ! in_array( $tutor_toc_page_id, array( 0, -1 ) ) ) {
			$tutor_toc_page_link = get_page_link( $tutor_toc_page_id );
		}

		return $tutor_toc_page_link;
	}

	/**
	 * Translate dynamic text, dynamic text is not translate while potting
	 * that's why define key here to make it translate able. It will put text in the pot file while compilling.
	 *
	 * @param string $key, pass key to get translate text | required.
	 * @return string
	 * @since v2.0.0
	 */
	public function translate_dynamic_text( $key, $add_badge = false, $badge_tag = 'span' ): string {
		$old_key = $key;
		$key     = trim( strtolower( $key ) );

		$key_value = tutor_get_translate_text();

		if ( $add_badge && isset( $key_value[ $key ] ) ) {
			return '<' . $badge_tag . ' class="tutor-badge-label label-' . $key_value[ $key ]['badge'] . '">' .
				$key_value[ $key ]['text'] .
				'</' . $badge_tag . '>';
		}

		// Revert to linear textual array
		$key_value = array_map(
			function ( $kv ) {
				return $kv['text'];
			},
			$key_value
		);

		return isset( $key_value[ $key ] ) ? $key_value[ $key ] : $old_key;
	}

	/**
	 * Show character as asterisk symbol for email
	 * it will replace character with asterisk till @ symbol
	 *
	 * @param string $email | required.
	 * @return string
	 * @since v2.0.0
	 */
	function asterisks_email( string $email ): string {
		if ( '' === $email ) {
			return '';
		}
		$mail_part    = explode( '@', $email );
		$mail_part[0] = str_repeat( '*', strlen( $mail_part[0] ) );
		return $mail_part[0] . $mail_part[1];
	}

	/**
	 * Show some character as asterisk symbol
	 * it will replace character with asterisk from the beginning and ending
	 *
	 * @param string $text | required.
	 * @return string
	 * @since v2.0.0
	 */
	function asterisks_center_text( string $str ): string {
		if ( '' === $str ) {
			return '';
		}
		$str_length = strlen( $str );
		return substr( $str, 0, 2 ) . str_repeat( '*', $str_length - 2 ) . substr( $str, $str_length - 2, 2 );
	}

	/**
	 * Report frequencies that will be shown on the dropdown
	 *
	 * @return array
	 * @since v2.0.0
	 */
	public function report_frequencies() {
		$frequencies = array(
			'alltime'     => __( 'All Time', 'tutor-pro' ),
			'today'       => __( 'Today', 'tutor-pro' ),
			'last30days'  => __( 'Last 30 Days', 'tutor-pro' ),
			'last90days'  => __( 'Last 90 Days', 'tutor-pro' ),
			'last365days' => __( 'Last 365 Days', 'tutor-pro' ),
			'custom'      => __( 'Custom', 'tutor-pro' ),
		);
		return $frequencies;
	}

	/**
	 * Add interval days with today date. For ex: 10 days add with today
	 *
	 * @param string $interval | required.
	 * @since v2.0.0
	 */
	public function add_days_with_today( $interval ) {
		$today    = date_create( date( 'Y-m-d' ) );
		$add_days = date_add( $today, date_interval_create_from_date_string( $interval ) );
		return $add_days;
	}

	/**
	 * Subtract interval days from today date. For ex: 10 days back from today
	 *
	 * @param string $interval | required.
	 * @since v2.0.0
	 */
	public function sub_days_with_today( $interval ) {
		$today    = date_create( date( 'Y-m-d' ) );
		$add_days = date_sub( $today, date_interval_create_from_date_string( $interval ) );
		return $add_days;
	}

	/**
	 * Get renderable column list for tables based on context
	 *
	 * @since v2.0.0
	 */
	public function get_table_columns_from_context( $page_key, $context, $contexts, $filter_hook = null ) {

		$fields                 = array();
		$columns                = $contexts[ $page_key ]['columns'];
		$filter_hook ? $columns = apply_filters( $filter_hook, $contexts[ $page_key ]['columns'] ) : 0;

		$allowed                         = $contexts[ $page_key ]['contexts'][ $context ];
		is_string( $allowed ) ? $allowed = $contexts[ $page_key ]['contexts'][ $allowed ] : 0; // By reference

		if ( $allowed === true ) {
			$fields = $columns;
		} else {
			foreach ( $columns as $key => $column ) {
				in_array( $key, $allowed ) ? $fields[ $key ] = $column : 0;
			}
		}

		return $fields;
	}

	/**
	 * Check a user has attempted a quiz
	 *
	 * @param string $user_id | user that taken course.
	 * @param string $quiz_id | quiz id that need to check wheather attempted or not.
	 * @return bool | true if attempted otherwise false.
	 */
	public function has_attempted_quiz( $user_id, $quiz_id, $row = false ) {
		global $wpdb;
		// Sanitize data
		$user_id   = sanitize_text_field( $user_id );
		$quiz_id   = sanitize_text_field( $quiz_id );
		$attempted = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT quiz_id
				FROM {$wpdb->tutor_quiz_attempts}
				WHERE user_id = %d
					AND quiz_id = %d
			",
				$user_id,
				$quiz_id
			)
		);
		return $attempted ? true : false;
	}

	/**
	 * Course nav items
	 *
	 * @since v2.0.0
	 *
	 * Course curriculum tab removed, content shifted
	 * in the Course Info tab
	 *
	 * @since v2.0.5
	 */
	public function course_nav_items() {
		/**
		 * If current user has course content then enrollment is not
		 * required
		 *
		 * @since v2.0.6
		 */
		$is_require_enrollment = ! $this->has_user_course_content_access();
		$array                 = array(
			'info'          => array(
				'title'  => __( 'Course Info', 'tutor' ),
				'method' => 'tutor_course_info_tab',
			),
			'reviews'       => array(
				'title'  => __( 'Reviews', 'tutor' ),
				'method' => 'tutor_course_target_reviews_html',
			),
			'questions'     => array(
				'title'             => __( 'Q&A', 'tutor' ),
				'method'            => 'tutor_course_question_and_answer',
				'require_enrolment' => $is_require_enrollment,
			),
			'announcements' => array(
				'title'             => __( 'Announcements', 'tutor' ),
				'method'            => 'tutor_course_announcements',
				'require_enrolment' => $is_require_enrollment,
			),
		);
		return $array;
	}

	public function second_to_formated_time( $seconds, $type = null ) {

		$dtF = new \DateTime( '@0' );
		$dtT = new \DateTime( "@$seconds" );
		// pr($dtF->diff( $dtT ));

		switch ( $type ) {

			case 'days':
				$format = '%ad %hh';
				break;

			case 'hours':
				$format = '%d' > 0 ? '%hh %im  %ss' : '%im %ss';
				$format = '%h' > 0 ? '%im  %ss' : $format;
				break;

			case 'minutes':
				$format = '%im  %ss';
				break;

			default:
				$format = '%im  %ss';
				break;
		}

		return $dtF->diff( $dtT )->format( $format );

		/*
		return $_attempt_duration = human_readable_duration( gmdate( 't:i:s', $seconds ) );
		return str_replace( array( ' hour', ' minute', ' second', 's', ',' ), array( 'H', 'M', 'S', '', '' ), $_attempt_duration ); */
	}

	public function seconds_to_time( $input_seconds ) {
		$seconds_in_a_minute = 60;
		$seconds_in_an_hour  = 60 * $seconds_in_a_minute;
		$seconds_in_a_day    = 24 * $seconds_in_an_hour;
	
		// Extract days
		$days = floor( $input_seconds / $seconds_in_a_day );
	
		// Extract hours
		$hour_seconds	= $input_seconds % $seconds_in_a_day;
		$hours			= floor( $hour_seconds / $seconds_in_an_hour );
	
		// Extract minutes
		$minute_seconds = $hour_seconds % $seconds_in_an_hour;
		$minutes		= floor( $minute_seconds / $seconds_in_a_minute );
	
		// Extract the remaining seconds
		$remaining_seconds	= $minute_seconds % $seconds_in_a_minute;
		$seconds			= ceil( $remaining_seconds );
	
		// Format and return
		$time_parts = array();
		$sections  = array(
			'day'    => (int) $days,
			'hour'   => (int) $hours,
			'minute' => (int) $minutes,
			'second' => (int) $seconds,
		);
	
		foreach ( $sections as $unit => $value ) {
			if ( $value > 0 ) {
				$unit_name		= $unit . ( $value == 1 ? '' : 's' );
				$time_parts[]	= $value . ' ' . $this->translate_dynamic_text( $unit_name );
			}
		}
	
		return implode( ', ', $time_parts );
	}

	/**
	 * Get quiz time duration in seconds
	 *
	 * @param string $time_type | supported time type : seconds, minutes, hours, days, weeks
	 * @param int    $time_value | quiz duration
	 *
	 * @return int | quiz time duration in seconds
	 */
	public function quiz_time_duration_in_seconds( string $time_type, int $time_value ): int {
		if ( 'seconds' === $time_type ) {
			return (int) $time_value;
		}
		$time_unit_seconds = 0;
		switch ( $time_type ) {
			case 'minutes':
				$time_unit_seconds = 60;
			case 'hours':
				$time_unit_seconds = 3600;
			case 'days':
				$time_unit_seconds = 24 * 3600;
			case 'weeks':
				$time_unit_seconds = 7 * 86400;
			default:
				break;
		}
		$quiz_duration_in_seconds = $time_unit_seconds * $time_value;
		return (int) $quiz_duration_in_seconds;
	}

	/**
	 * Get all contents (lesosn, assignment, zoom, quiz etc) that belong to this topic
	 *
	 * @param int $topic_id | topic id.
	 *
	 * @return array of objects on success | false on failure
	 *
	 * @since v2.0.0
	 */
	public function get_contents_by_topic( int $topic_id ) {
		global $wpdb;
		$topic_id = sanitize_text_field( $topic_id );
		$contents = $wpdb->get_results(
			$wpdb->prepare(
				" SELECT content.ID, content.post_title, content.post_type
				FROM {$wpdb->posts} AS topics
					INNER JOIN {$wpdb->posts} AS content
						ON content.post_parent = topics.ID
				WHERE topics.post_type = 'topics'
					AND topics.ID = %d
					AND content.post_status = %s
			",
				$topic_id,
				'publish'
			)
		);
		return $contents;
	}

	/**
	 * Get total number of contents & completed contents that
	 * belongs to this topic
	 *
	 * @param int $topic_id | all contents will be checked that belong to this topic.
	 *
	 * @return array counted number of contents & completed contents number.
	 *
	 * @since v2.0.0
	 */
	public function count_completed_contents_by_topic( int $topic_id ): array {
		$topic_id  = sanitize_text_field( $topic_id );
		$contents  = $this->get_contents_by_topic( $topic_id );
		$user_id   = get_current_user_id();
		$completed = 0;

		$lesson_post_type      = 'lesson';
		$quiz_post_type        = 'tutor_quiz';
		$assignment_post_type  = 'tutor_assignments';
		$zoom_lesson_post_type = 'tutor_zoom_meeting';
		$google_meet_post_type = 'tutor-google-meet';

		if ( $contents ) {
			foreach ( $contents as $content ) {
				switch ( $content->post_type ) {
					case $lesson_post_type:
						$is_lesson_completed = $this->is_completed_lesson( $content->ID, $user_id );
						if ( $is_lesson_completed ) {
							$completed++;
						}
						break;
					case $quiz_post_type:
						$has_attempt = $this->has_attempted_quiz( $user_id, $content->ID );
						if ( $has_attempt ) {
							$completed++;
						}
						break;
					case $assignment_post_type:
						$is_assignment_completed = $this->is_assignment_submitted( $content->ID, $user_id );
						if ( $is_assignment_completed ) {
							$completed++;
						}
						break;
					case $zoom_lesson_post_type:
						if ( \class_exists( '\TUTOR_ZOOM\Zoom' ) ) {
							$is_zoom_lesson_completed = \TUTOR_ZOOM\Zoom::is_zoom_lesson_done( '', $content->ID, $user_id );
							if ( $is_zoom_lesson_completed ) {
								$completed++;
							}
						}
						break;
					case $google_meet_post_type:
						if ( \class_exists( '\TutorPro\GoogleMeet\Frontend\Frontend' ) ) {
							if ( \TutorPro\GoogleMeet\Validator\Validator::is_addon_enabled() ) {
								$is_completed = \TutorPro\GoogleMeet\Frontend\Frontend::is_lesson_completed( false, $content->ID, $user_id );
								if ( $is_completed ) {
									$completed++;
								}
							}
						}
						break;
					default:
						break;
				}
			}
		}
		return array(
			'contents'  => is_array( $contents ) ? count( $contents ) : 0,
			'completed' => $completed,
		);
	}

	/**
	 * Text message for the list tables that will be visible
	 * if no record found or filter data not found
	 *
	 * @return string | not found text
	 *
	 * @since v2.0.0
	 */
	public function not_found_text(): string {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$course   = isset( $_GET['course-id'] ) ? true : false;
		$date     = isset( $_GET['date'] ) ? true : false;
		$search   = isset( $_GET['search'] ) ? true : false;
		$category = isset( $_GET['category'] ) ? true : false;
		$text     = array(
			'normal' => __( 'No Data Available in this Section', 'tutor' ),
			'filter' => __( 'No Data Found from your Search/Filter', 'tutor' ),
		);

		if ( $course || $date || $search || $category ) {
			return $text['filter'];
		} else {
			return $text['normal'];
		}
	}

	/**
	 * Separation of all menu items for providing ease of usage
	 *
	 * @return array, array of menu items
	 *
	 * @since v.2.0.0
	 */
	public function instructor_menus(): array {
		return array(
			'separator-1'   => array(
				'title'    => __( 'Instructor', 'tutor' ),
				'auth_cap' => tutor()->instructor_role,
				'type'     => 'separator',
			),
			'create-course' => array(
				'title'    => __( 'Create Course', 'tutor' ),
				'show_ui'  => false,
				'auth_cap' => tutor()->instructor_role,
			),
			'my-courses'    => array(
				'title'    => __( 'My Courses', 'tutor' ),
				'auth_cap' => tutor()->instructor_role,
				'icon'     => 'tutor-icon-rocket',
			),
			'announcements' => array(
				'title'    => __( 'Announcements', 'tutor' ),
				'auth_cap' => tutor()->instructor_role,
				'icon'     => 'tutor-icon-bullhorn',
			),
			'withdraw'      => array(
				'title'    => __( 'Withdrawals', 'tutor' ),
				'auth_cap' => tutor()->instructor_role,
				'icon'     => 'tutor-icon-wallet',
			),
			'quiz-attempts' => array(
				'title'    => __( 'Quiz Attempts', 'tutor' ),
				'auth_cap' => tutor()->instructor_role,
				'icon'     => 'tutor-icon-quiz-o',
			),
		);
	}


	/**
	 * Separation of all menu items for providing ease of usage
	 *
	 * @return array, array of menu items
	 *
	 * @since v.2.0.0
	 */
	public function default_menus(): array {
		return array(
			'index'            => array(
				'title' => __( 'Dashboard', 'tutor' ),
				'icon'  => 'tutor-icon-dashboard',
			),
			'my-profile'       => array(
				'title' => __( 'My Profile', 'tutor' ),
				'icon'  => 'tutor-icon-user-bold',
			),
			'enrolled-courses' => array(
				'title' => __( 'Enrolled Courses', 'tutor' ),
				'icon'  => 'tutor-icon-mortarboard-o',
			),
			'wishlist'         => array(
				'title' => __( 'Wishlist', 'tutor' ),
				'icon'  => 'tutor-icon-bookmark-bold',
			),
			'reviews'          => array(
				'title' => __( 'Reviews', 'tutor' ),
				'icon'  => 'tutor-icon-star-bold',
			),
			'my-quiz-attempts' => array(
				'title' => __( 'My Quiz Attempts', 'tutor' ),
				'icon'  => 'tutor-icon-quiz-attempt',
			),
			'purchase_history' => array(
				'title' => __( 'Order History', 'tutor' ),
				'icon'  => 'tutor-icon-cart-bold',
			),
			'question-answer'  => array(
				'title' => __( 'Question & Answer', 'tutor' ),
				'icon'  => 'tutor-icon-question',
			),
		);
	}

	/**
	 * Default config for tutor text editor
	 *
	 * Modify default param from here and pass to render_text_editor() method
	 *
	 * @param $args array  array of arguments.
	 *
	 * @return array | default config
	 */
	public function text_editor_config( $args = array() ) {
		$default_args = array(
			'textarea_name'     => 'tutor-global-text-editor',
			'plugins'           => 'image',
			'tinymce'           => array(
				'toolbar1' => 'bold,italic,underline,link,unlink,removeformat,image,bullist',
				'toolbar2' => '',
				'toolbar3' => '',
			),
			'file_picker_types' => 'image',
			'media_buttons'     => false,
			'drag_drop_upload'  => false,
			'quicktags'         => false,
			'elementpath'       => false,
			'wpautop'           => false,
			'statusbar'         => false,
			'editor_height'     => 112,
			'editor_css'        => '<style>
				#wp-tutor-global-text-editor-wrap div.mce-toolbar-grp {
					background-color: #fff;
				}
			</style>',
		);
		return wp_parse_args( $args, $default_args );
	}

	public function get_video_sources( bool $key_title_only ) {

		$video_sources = array(
			'html5'        => array(
				'title' => __( 'HTML 5 (mp4)', 'tutor' ),
				'icon'  => 'html5',
			),
			'external_url' => array(
				'title' => __( 'External URL', 'tutor' ),
				'icon'  => 'external_url',
			),
			'youtube'      => array(
				'title' => __( 'Youtube', 'tutor' ),
				'icon'  => 'youtube',
			),
			'vimeo'        => array(
				'title' => __( 'Vimeo', 'tutor' ),
				'icon'  => 'vimeo',
			),
			'embedded'     => array(
				'title' => __( 'Embedded', 'tutor' ),
				'icon'  => 'embedded',
			),
			'shortcode'    => array(
				'title' => __( 'Shortcode', 'tutor' ),
				'icon'  => 'code',
			),
		);
		$video_sources = apply_filters( 'tutor_preferred_video_sources', $video_sources );

		if ( $key_title_only ) {
			foreach ( $video_sources as $key => $data ) {
				$video_sources[ $key ] = $data['title'];
			}
		}
		return $video_sources;
	}

	/**
	 * Convert date to wp timezone compatible date. Timezone will be get from settings
	 *
	 * @param string $date | string date time to convert.
	 *
	 * @return string | date time
	 *
	 * NOTE: date_i18n translate able string is not supported
	 */
	public function convert_date_into_wp_timezone( string $date ): string {
		$date = new \DateTime( $date );
		$date->setTimezone( wp_timezone() );
		return $date->format( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) );
	}

	/**
	 * Tutor Custom Header
	 */
	public function tutor_custom_header() {
		global $wp_version;
		if ( version_compare( $wp_version, '5.9', '>=' ) && function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			?>
			<!doctype html>
				<html <?php language_attributes(); ?>>
				<head>
					<meta charset="<?php bloginfo( 'charset' ); ?>">
					<?php wp_head(); ?>
				</head>
				<body <?php body_class(); ?>>
				<?php wp_body_open(); ?>
					<div class="wp-site-blocks">
					<?php
						$theme      = wp_get_theme();
						$theme_slug = $theme->get( 'TextDomain' );
						echo do_blocks( '<!-- wp:template-part {"slug":"header","theme":"' . $theme_slug . '","tagName":"header","className":"site-header","layout":{"inherit":true}} /-->' );
		} else {
			get_header();
		}
	}

	/**
	 * Tutor Custom Header
	 */
	public function tutor_custom_footer() {
		global $wp_version;
		if ( version_compare( $wp_version, '5.9', '>=' ) && function_exists( 'wp_is_block_theme' ) && true === wp_is_block_theme() ) {
			$theme      = wp_get_theme();
			$theme_slug = $theme->get( 'TextDomain' );
			echo do_blocks( '<!-- wp:template-part {"slug":"footer","theme":"' . $theme_slug . '","tagName":"footer","className":"site-footer","layout":{"inherit":true}} /-->' );
			echo '</div>';
			wp_footer();
			echo '</body>';
			echo '</html>';
		} else {
			get_footer();
		}
	}

	public function can_user_retake_course() {
		if ( ! $this->is_enrolled() ) {
			return false;
		}

		$completed_lessons   = $this->get_completed_lesson_count_by_course();
		$completed_percent   = $this->get_course_completed_percent();
		$is_completed_course = $this->is_completed_course();
		$retake_course       = $this->get_option( 'course_retake_feature', false ) && ( $is_completed_course || $completed_percent >= 100 );

		return $retake_course;
	}


	/**
	 * Clean unnecessary html code from the content
	 *
	 * @return string
	 *
	 * @since v.2.0.1
	 */

	public function clean_html_content( $content = '', $allowed = array() ) {

		$default = array(
			'div'    => array(
				'class' => 1,
				'style' => 1,
			),
			'b'      => array( 'style' => 1 ),
			'strong' => array( 'style' => 1 ),
			'i'      => array( 'style' => 1 ),
			'u'      => array( 'style' => 1 ),
			'h1'     => array( 'style' => 1 ),
			'h2'     => array( 'style' => 1 ),
			'h3'     => array( 'style' => 1 ),
			'h4'     => array( 'style' => 1 ),
			'h5'     => array( 'style' => 1 ),
			'h6'     => array( 'style' => 1 ),
			'a'      => array(
				'href'   => array(
					'minlen' => 3,
					'maxlen' => 100,
				),
				'target' => 1,
				'style'  => 1,
			),
			'p'      => array( 'style' => 1 ),
			'img'    => array(
				'src'   => 1,
				'alt'   => 1,
				'style' => 1,
			),
			'pre'    => array( 'style' => 1 ),
			'ul'     => array( 'style' => 1 ),
			'ol'     => array( 'style' => 1 ),
			'li'     => array( 'style' => 1 ),
		);

		$allowed = wp_parse_args( $allowed, $default );

		return wp_kses( $content, $allowed );
	}

	/**
	 * Get predefined icon
	 *
	 * @return string
	 *
	 * @since v.2.0.2
	 */

	public function get_svg_icon( $name = '' ) {

		$json = tutor()->path . 'assets/images/icons.json';

		if ( file_exists( $json ) ) {
			$icons = json_decode( file_get_contents( $json ), true );
			$icon  = isset( $icons[ $name ] ) ? $icons[ $name ] : '';

			if ( isset( $icon['viewBox'] ) && isset( $icon['path'] ) ) {
				$html = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="' . esc_attr( $icon['viewBox'] ) . '"><path fill="currentColor" d="' . esc_attr( $icon['path'] ) . '" /></svg>';
				return $html;
			}
		}
	}

	/**
	 * Conver Hex to RGB
	 *
	 * @return string
	 *
	 * @since 2.0.2
	 */

	public function hex2rgb( string $color ) {

		$default = '0, 0, 0';

		if ( $color === '' ) {
			return '';
		}

		if ( strpos( $color, 'var(--' ) === 0 ) {
			return preg_replace( '/[^A-Za-z0-9_)(\-,.]/', '', $color );
		}

		// convert hex to rgb
		if ( $color[0] == '#' ) {
			$color = substr( $color, 1 );
		} else {
			return $default;
		}

		// Check if color has 6 or 3 characters and get values
		if ( strlen( $color ) == 6 ) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}

		$rgb = array_map( 'hexdec', $hex );

		return implode( ', ', $rgb );
	}

	public function get_course_builder_screen() {
		// Add course editor identifier class
		if ( is_admin() ) {
			$screen = get_current_screen();
			if ( is_object( $screen ) && $screen->base == 'post' && $screen->id == tutor()->course_post_type ) {
				return $screen->is_block_editor ? 'gutenberg' : 'classic';
			}
		} elseif ( $this->is_tutor_frontend_dashboard( 'create-course' ) ) {
			return 'frontend';
		}

		return null;
	}

	/**
	 * Get total number of course
	 *
	 * @return int
	 * @since 2.0.2
	 */
	public function get_total_course() {
		global $wpdb;
		$course_post_type = tutor()->course_post_type;

		$sql = "SELECT COUNT(ID) 
		FROM {$wpdb->posts} 
		WHERE post_type = %s 
		AND post_status = %s";

		return $wpdb->get_var( $wpdb->prepare( $sql, $course_post_type, 'publish' ) );
	}

	/**
	 * Get total number of enrolled course
	 *
	 * @return int
	 * @since 2.0.2
	 */
	public function get_total_enrolled_course() {
		global $wpdb;

		$sql = "SELECT COUNT(DISTINCT enroll.ID)
		FROM {$wpdb->posts} enroll INNER JOIN {$wpdb->posts} course ON enroll.post_parent=course.ID
		WHERE enroll.post_type = 'tutor_enrolled'
		AND enroll.post_status = 'completed'
        AND course.post_type=%s";

		return $wpdb->get_var( $wpdb->prepare( $sql, tutor()->course_post_type ) );
	}

	/**
	 * Get total number of quiz
	 *
	 * @return int
	 * @since 2.0.2
	 */
	public function get_total_quiz() {
		global $wpdb;

		$sql = "SELECT COUNT(DISTINCT quiz.ID) 
			FROM {$wpdb->posts} quiz
				INNER JOIN {$wpdb->posts} topic ON quiz.post_parent=topic.ID 
				INNER JOIN {$wpdb->posts} course ON topic.post_parent=course.ID 
			WHERE course.post_type=%s
				AND quiz.post_type='tutor_quiz'";

		return $wpdb->get_var( $wpdb->prepare( $sql, tutor()->course_post_type ) );
	}

	/**
	 * Get total number of question
	 *
	 * @return int
	 * @since 2.0.2
	 */
	public function get_total_question() {
		global $wpdb;

		$sql = "SELECT COUNT(DISTINCT question.question_id) 
				FROM {$wpdb->prefix}tutor_quiz_questions question 
					INNER JOIN {$wpdb->posts} quiz ON question.quiz_id=quiz.ID 
					INNER JOIN {$wpdb->posts} topic ON quiz.post_parent=topic.ID 
					INNER JOIN {$wpdb->posts} course ON topic.post_parent=course.ID 
				WHERE course.post_type=%s
				 	AND quiz.post_type='tutor_quiz'";

		return $wpdb->get_var( $wpdb->prepare( $sql, tutor()->course_post_type ) );
	}

	/**
	 * Get total number of review
	 *
	 * @return int
	 * @since 2.0.2
	 */
	public function get_total_review() {
		global $wpdb;

		$sql = "SELECT COUNT(comment_ID)
		FROM {$wpdb->comments}
		WHERE comment_type = %s
		AND comment_approved = %s ";

		return $wpdb->get_var( $wpdb->prepare( $sql, 'tutor_course_rating', 'approved' ) );
	}

	private function assign_child_count( array $course_meta, $post_type ) {
		global $wpdb;
		$id_array = array_keys( $course_meta );

		if ( ! count( $id_array ) ) {
			return $course_meta;
		}

		$course_ids = implode( ',', $id_array );

		$results = $wpdb->get_results(
			"SELECT ID, post_parent AS course_id 
			FROM {$wpdb->posts} 
			WHERE post_parent IN ({$course_ids}) 
				AND post_type='{$post_type}' 
				AND post_status IN ('completed', 'publish', 'approved')"
		);

		foreach ( $results as $result ) {
			$course_meta[ $result->course_id ][ $post_type ]++;
		}

		return $course_meta;
	}


	public function get_course_meta_data( $course_id ) {
		global $wpdb;

		// Prepare course IDs to get quiz count based on
		$course_ids = is_array( $course_id ) ? $course_id : array( $course_id );
		$course_ids = array_map(
			function( $id ) {
				return (int) $id;
			},
			$course_ids
		);
		$course_ids = implode( ',', $course_ids );

		if ( empty( $course_ids ) ) {
			return array();
		}

		// Get course meta
		$results = $wpdb->get_results(
			"SELECT DISTINCT course.ID AS course_id, 
					content.ID AS content_id,
					content.post_type AS content_type
			FROM {$wpdb->posts} course
				LEFT JOIN {$wpdb->posts} topic ON course.ID=topic.post_parent
				INNER JOIN {$wpdb->posts} content ON topic.ID=content.post_parent
				LEFT JOIN {$wpdb->posts} enrollment ON course.ID=enrollment.post_parent
			WHERE topic.post_parent IN ($course_ids)"
		);

		// Count contents by course IDs
		$course_meta = array();
		foreach ( $results as $result ) {
			// Create course key
			if ( ! array_key_exists( $result->course_id, $course_meta ) ) {
				$course_meta[ $result->course_id ] = array(
					'tutor_assignments' => array(),
					'tutor_quiz'        => array(),
					'lesson'            => array(),
					'topics'            => 0,
					'tutor_enrolled'    => 0,
				);
			}

			// Create content key
			if ( ! array_key_exists( $result->content_type, $course_meta[ $result->course_id ] ) ) {
				$course_meta[ $result->course_id ][ $result->content_type ] = array();
			}

			if ( $result->content_id ) {
				$course_meta[ $result->course_id ][ $result->content_type ][] = $result->content_id;
			}
		}

		// Unify counts
		foreach ( $course_meta as $index => $meta ) {
			foreach ( $meta as $key => $ids ) {
				$course_meta[ $index ][ $key ] = is_numeric( $ids ) ? $ids : count( array_unique( $ids ) );
			}
		}

		$course_meta = $this->assign_child_count( $course_meta, 'tutor_enrolled' );
		$course_meta = $this->assign_child_count( $course_meta, 'topics' );

		// Return single count if the course id was single
		if ( ! is_array( $course_id ) ) {
			return isset( $course_meta[ $course_id ] ) ? $course_meta[ $course_id ] : 0;
		}

		return $course_meta;
	}

	/**
	 * Get local time from unix/gmt date
	 *
	 * @param string $time
	 * @param string $date_format
	 * @return string
	 */
	public function get_local_time_from_unix( $time, $date_format = null ) {
		$output_format = $date_format ? $date_format : get_option( 'date_format' ) . ', ' . get_option( 'time_format' );
		return get_date_from_gmt( $time, $output_format );
	}

	/**
	 * Execute bulk action for enrollment list ex: complete | cancel
	 *
	 * @param string $status hold status for updating.
	 * @param array  $enrollment_ids ids that need to update.
	 * @return bool
	 * @since v2.0.3
	 */
	public function update_enrollments( string $status, array $enrollment_ids ): bool {
		global $wpdb;
		$enrollment_ids_in = implode( ',', $enrollment_ids );
		$status            = 'complete' === $status ? 'completed' : $status;
		$post_table        = $wpdb->posts;
		$update            = $wpdb->query(
			$wpdb->prepare(
				" UPDATE {$post_table}
				SET post_status = %s
				WHERE ID IN ($enrollment_ids_in)
			",
				$status
			)
		);

		// Clear course progress if cancelled
		if ( $status == 'cancelled' || $status == 'cancel' ) {
			foreach ( $enrollment_ids as $id ) {
				$course_id  = get_post_field( 'post_parent', $id );
				$student_id = get_post_field( 'post_author', $id );

				if ( $course_id && $student_id ) {
					$this->delete_course_progress( $course_id, $student_id );
				}
			}
		}

		// Run action hook
		foreach ( $enrollment_ids as $id ) {
			do_action( 'tutor_enrollment/after/' . $status, $id );
		}

		return true;
	}

	/**
	 * Format course content time duration
	 * For ex: lesson video play time, quiz time, assignment time etc.
	 *
	 * @param string $time_duration
	 *
	 * @return string
	 */
	public function course_content_time_format( string $time_duration ): string {
		$new_formatted_time  = '';
		$time_duration_array = explode( ':', $time_duration );
		if ( is_array( $time_duration_array ) && count( $time_duration_array ) ) {
			$count_fraction = count( $time_duration_array );
			$first_fraction = (int) $time_duration_array[0];
			if ( 3 === $count_fraction && $first_fraction < 1 ) {
				unset( $time_duration_array[0] );
			}
			foreach ( $time_duration_array as $key => $value ) {
				// If exists hour fraction but not 00 then skip it.
				$new_formatted_time .= sprintf( '%02d', $value ) . ':';
			}
		}
		return rtrim( $new_formatted_time, ':' );
	}

	/**
	 * Check if the user has special acess to course
	 *
	 * @since v2.0.6
	 */
	public function has_user_course_content_access( $user_id = 0, $course_id = 0 ) {
		$user_id   = $this->get_user_id( $user_id );
		$course_id = $this->get_post_id( $course_id );

		$is_administrator = $this->has_user_role( 'administrator', $user_id );
		$is_instructor    = $this->is_instructor_of_this_course( $user_id, $course_id );

		$course_content_access = (bool) get_tutor_option( 'course_content_access_for_ia' );
		$has_access            = $course_content_access && ( $is_administrator || $is_instructor );

		return $has_access;
	}

	/**
	 * Get current page slug
	 *
	 * @since 2.1.3
	 *
	 * @return string current page slug
	 */
	public function get_current_page_slug() {
		global $wp_query;
		$current_page = '';
		$query_vars   = $wp_query->query_vars;
		if ( is_admin() && Input::has( 'page' ) ) {
			$current_page = Input::get( 'page' );
		} else {
			$current_page = isset( $query_vars['tutor_dashboard_page'] ) ? sanitize_text_field( $query_vars['tutor_dashboard_page'] ) : '';
		}
		return $current_page;
	}

	/**
	 * Get allowed tags for avatar, useful while using
	 * wp_kses
	 *
	 * @since 2.1.4
	 *
	 * @param array|optional $tags additional tags.
	 *
	 * @return array allowed tags
	 */
	public function allowed_avatar_tags( array $tags = array() ):array {
		$defaults = array(
			'a'   => array(
				'href'   => true,
				'class'  => true,
				'id'     => true,
				'target' => true,
			),
			'img' => array(
				'src'   => true,
				'class' => true,
				'id'    => true,
				'title' => true,
				'alt'   => true,
			),
			'div' => array(
				'class' => true,
				'id'    => true,
			),
			'span' => array(
				'class' => true,
				'id'    => true,
			),
		);
		return wp_parse_args( $tags , $defaults );
	}

	/**
	 * Get allowed tags for avatar, useful while using
	 * wp_kses
	 *
	 * @since 2.1.4
	 *
	 * @param array|optional $tags additional tags.
	 *
	 * @return array allowed tags
	 */
	public function allowed_icon_tags( array $tags = array() ):array {
		$defaults = array(
			'span' => array(
				'class' => true,
				'id'    => true,
			),
			'i'    => array(
				'class' => true,
				'id'    => true,
			),
		);
		return wp_parse_args( $tags , $defaults );
	}

	/**
	 * Get user name to display
	 * 
	 * It will return display name if not empty, if empty
	 * then it will return first name & last name or if display
	 * name & user same it will return first & last name (if ot emtpy)
	 * if first & last name empty then it will return user_login name
	 *
	 * @since 2.1.6
	 *
	 * @param integer $user_id
	 *
	 * @return string
	 */
	public function display_name( int $user_id ): string {
		$name = '';
		$user_data = get_userdata( $user_id );

		if ( is_a( $user_data, 'WP_User' ) ) {
			$display_name = $user_data->display_name;
			$user_name 	  = $user_data->user_login;
			$custom_name  = trim( trim( $user_data->first_name ) . ' ' . trim( $user_data->last_name ) );

			if ( $display_name ) {
				$name = $display_name === $user_name && $custom_name ? $custom_name : $display_name;
			} else {
				$name = $custom_name ? $custom_name : $user_name;
			}
		}
		return $name;
	}

	/**
	 * Get error message by error code
	 *
	 * @since 2.1.9
	 *
	 * @param string $key error code.
	 *
	 * @return string error message
	 */
	public function error_message( $key = '401' ) {
		$error_message = __( 'Something went wrong', 'tutor' ); 

		$error_messages = apply_filters(
			'tutor_default_error_messages',
			array(
				'401' => __( 'You are not authorzied to perform this action', 'tutor' ),
			)
		);

		if ( array_key_exists( $key, $error_messages ) ) {
			$error_message = $error_messages[ $key ];
		}

		return $error_message;
	}
}
