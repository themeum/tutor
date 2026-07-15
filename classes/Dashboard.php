<?php
/**
 * Manage Frontend Dashboard
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.3.4
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

use Tutor\Helpers\UrlHelper;

/**
 * Dashboard Class
 *
 * @since 1.3.4
 */
class Dashboard {

	/**
	 * Dashboard page constants
	 *
	 * @since 4.0.0
	 */
	const ACCOUNT_PAGE_SLUG             = 'account';
	const COURSES_PAGE_SLUG             = 'courses';
	const QUIZ_ATTEMPTS_PAGE_SLUG       = 'quiz-attempts';
	const MY_QUIZ_ATTEMPTS_SUBPAGE_SLUG = 'my-quiz-attempts';
	const REVIEW_PAGE_SLUG              = 'reviews';
	const PROFILE_PAGE_SLUG             = 'my-profile';
	const SETTINGS_PAGE_SLUG            = 'settings';
	const Q_AND_A_PAGE_SLUG             = 'question-answer';
	const DISCUSSION_PAGE_SLUG          = 'discussions';
	const PURCHASE_HISTORY_PAGE_SLUG    = 'purchase_history';
	const BILLING_PAGE_SLUG             = 'billing';
	const WISHLIST_PAGE_SLUG            = 'wishlist';
	const ENROLLED_COURSES_PAGE_SLUG    = 'enrolled-courses';

	/**
	 * Constructor
	 *
	 * @since 1.3.4
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'tutor_load_template_after', array( $this, 'tutor_load_template_after' ), 10, 2 );
		add_filter( 'should_tutor_load_template', array( $this, 'should_tutor_load_template' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'redirect_old_dashboard_pages' ) );
		add_filter( 'tutor_dashboard_back_url', array( $this, 'filter_dashboard_back_url' ) );
	}

	/**
	 * Redirect user old dashboard page slugs to new slugs.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function redirect_old_dashboard_pages() {
		$current_url = rtrim( UrlHelper::current(), '/' );

		if ( ! is_admin() ) {
			// Redirect logic here.
			$redirect_mappings = array(
				tutor_utils()->tutor_dashboard_url( self::PROFILE_PAGE_SLUG ) => self::get_account_page_url( 'profile' ),
				tutor_utils()->tutor_dashboard_url( self::REVIEW_PAGE_SLUG ) => self::get_account_page_url( self::REVIEW_PAGE_SLUG ),
				tutor_utils()->tutor_dashboard_url( self::SETTINGS_PAGE_SLUG ) => self::get_account_page_url( self::SETTINGS_PAGE_SLUG ),
				tutor_utils()->tutor_dashboard_url( self::Q_AND_A_PAGE_SLUG ) => tutor_utils()->tutor_dashboard_url( self::DISCUSSION_PAGE_SLUG ),
				tutor_utils()->tutor_dashboard_url( self::PURCHASE_HISTORY_PAGE_SLUG ) => self::get_account_page_url( self::BILLING_PAGE_SLUG ),
				tutor_utils()->tutor_dashboard_url( self::WISHLIST_PAGE_SLUG ) => tutor_utils()->tutor_dashboard_url( 'courses/wishlist' ),
				tutor_utils()->tutor_dashboard_url( self::MY_QUIZ_ATTEMPTS_SUBPAGE_SLUG ) => tutor_utils()->tutor_dashboard_url( 'courses/my-quiz-attempts' ),
				tutor_utils()->tutor_dashboard_url( self::ENROLLED_COURSES_PAGE_SLUG ) => tutor_utils()->tutor_dashboard_url( self::COURSES_PAGE_SLUG ),
				tutor_utils()->get_tutor_dashboard_page_permalink( 'logout' ) => UrlHelper::add_query_params( wp_logout_url(), array( '_wpnonce' => wp_create_nonce( 'log-out' ) ) ),
			);

			if ( ! isset( $redirect_mappings[ $current_url ] ) ) {
				return;
			}

			$redirect_url = $redirect_mappings[ $current_url ];

			if ( tutor_utils()->count( $_GET ) ) {
				$redirect_url = UrlHelper::add_query_params( $redirect_url, $_GET );
			}

			wp_safe_redirect( $redirect_url );
			exit;
		}
	}


	/**
	 * Get account page URL.
	 *
	 * @since 4.0.0
	 *
	 * @param string $page page name.
	 *
	 * @return string
	 */
	public static function get_account_page_url( $page = '' ) {
		$account_page_url = tutor_utils()->tutor_dashboard_url( self::ACCOUNT_PAGE_SLUG );
		if ( empty( $page ) ) {
			return $account_page_url;
		}

		return trailingslashit( $account_page_url ) . $page;
	}

	/**
	 * Get account pages.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public static function get_account_pages() {
		$pages = array(
			'profile' => array(
				'title'       => esc_html__( 'Profile', 'tutor' ),
				'icon'        => Icon::USER_CIRCLE,
				'icon_active' => Icon::USER_CIRCLE_FILL,
				'url'         => self::get_account_page_url( 'profile' ),
				'template'    => tutor_get_template( 'dashboard.account.profile' ),
			),
			'reviews' => array(
				'title'       => esc_html__( 'Reviews', 'tutor' ),
				'icon'        => Icon::RATINGS,
				'icon_active' => Icon::RATINGS,
				'url'         => self::get_account_page_url( 'reviews' ),
				'template'    => tutor_get_template( 'dashboard.account.reviews' ),
			),
		);

		if ( User::is_student_view() ) {
			$pages['billing'] = array(
				'title'       => esc_html__( 'Billing', 'tutor' ),
				'icon'        => Icon::BILLING,
				'icon_active' => Icon::BILLING,
				'url'         => self::get_account_page_url( 'billing' ),
				'template'    => tutor_get_template( 'dashboard.account.billing' ),
			);
		}

		if ( User::is_instructor_view() ) {
			$pages['withdrawals'] = array(
				'title'       => esc_html__( 'Withdrawals', 'tutor' ),
				'icon'        => Icon::WALLET,
				'icon_active' => Icon::WALLET,
				'url'         => self::get_account_page_url( 'withdrawals' ),
				'template'    => tutor_get_template( 'dashboard.account.withdrawals' ),
			);
		}

		$pages['settings'] = array(
			'title'       => esc_html__( 'Settings', 'tutor' ),
			'icon'        => Icon::SETTING,
			'icon_active' => Icon::SETTING,
			'url'         => self::get_account_page_url( 'settings' ),
			'template'    => tutor_get_template( 'dashboard.account.settings' ),
		);

		return apply_filters( 'tutor_dashboard_account_pages', $pages );
	}

	/**
	 * Get isolated dashboard pages.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public static function get_isolated_pages() {
		$pages = array(
			self::QUIZ_ATTEMPTS_PAGE_SLUG => array(
				'title'          => esc_html__( 'Quiz attempts', 'tutor' ),
				'template'       => tutor_get_template( 'dashboard.quiz-attempts.quiz-reviews' ),
				'requires_param' => 'attempt_id',
			),
			self::COURSES_PAGE_SLUG . '/' . self::MY_QUIZ_ATTEMPTS_SUBPAGE_SLUG => array(
				'title'          => esc_html__( 'Quiz details', 'tutor' ),
				'template'       => tutor_get_template( 'dashboard.my-quiz-attempts.attempts-details' ),
				'requires_param' => 'attempt_id',
			),
		);

		return apply_filters( 'tutor_dashboard_isolated_pages', $pages );
	}

	/**
	 * Get isolated dashboard page key.
	 *
	 * @since 4.0.0
	 *
	 * @param string $dashboard_page Dashboard page slug.
	 * @param string $dashboard_subpage Dashboard subpage slug.
	 *
	 * @return string
	 */
	public static function get_isolated_page_key( string $dashboard_page = '', string $dashboard_subpage = '' ): string {
		if ( self::QUIZ_ATTEMPTS_PAGE_SLUG === $dashboard_page ) {
			return self::QUIZ_ATTEMPTS_PAGE_SLUG;
		}

		if ( self::COURSES_PAGE_SLUG === $dashboard_page && self::MY_QUIZ_ATTEMPTS_SUBPAGE_SLUG === $dashboard_subpage ) {
			return self::COURSES_PAGE_SLUG . '/' . self::MY_QUIZ_ATTEMPTS_SUBPAGE_SLUG;
		}

		return '';
	}

	/**
	 * Check if the current request should use an isolated dashboard page.
	 *
	 * @since 4.0.0
	 *
	 * @param string $dashboard_page Dashboard page slug.
	 * @param string $dashboard_subpage Dashboard subpage slug.
	 *
	 * @return bool
	 */
	public static function is_isolated_page_request( string $dashboard_page = '', string $dashboard_subpage = '' ): bool {
		$page_key       = self::get_isolated_page_key( $dashboard_page, $dashboard_subpage );
		$isolated_pages = self::get_isolated_pages();
		$page_data      = $isolated_pages[ $page_key ] ?? array();

		if ( empty( $page_data ) ) {
			return false;
		}

		$required_param = $page_data['requires_param'] ?? '';

		return '' === $required_param ? true : Input::has( $required_param );
	}

	/**
	 * Load template after
	 *
	 * @since 1.3.4
	 * @return void
	 */
	public function tutor_load_template_after() {
		global $wp_query;

		$tutor_dashboard_page = tutor_utils()->array_get( 'query_vars.tutor_dashboard_page', $wp_query );
		if ( 'create-course' === $tutor_dashboard_page ) {
			wp_reset_query();
		}
	}

	/**
	 * Check template need to load or not
	 *
	 * @since 1.3.4
	 *
	 * @param bool   $bool true or false.
	 * @param string $template template name.
	 *
	 * @return boolean
	 */
	public function should_tutor_load_template( $bool, $template ) {
		if ( 'dashboard.create-course' === $template && ! tutor()->has_pro ) {
			return false;
		}
		return $bool;
	}

	/**
	 * Filter dashboard back URL.
	 *
	 * @since 4.0.0
	 *
	 * @param string $url URL.
	 *
	 * @return string
	 */
	public function filter_dashboard_back_url( string $url ): string {
		if ( ! Input::has( 'back_url', Input::GET_REQUEST ) ) {
			return $url;
		}

		$back_url = Input::get( 'back_url', '' );
		$decoded  = urldecode( $back_url );
		if ( ! str_starts_with( $decoded, home_url() ) ) {
			return $url;
		}

		return esc_url_raw( $decoded );
	}

	/**
	 * Build page metadata from page configuration.
	 *
	 * @since 4.0.0
	 *
	 * @param string $page_slug    Page slug.
	 * @param string $page_subslug Page subpage slug.
	 * @param array  $pages        Page definitions.
	 *
	 * @return array
	 */
	public static function get_page_meta_data( string $page_slug = '', string $page_subslug = '', array $pages = array() ): array {
		$page_data      = array();
		$page_meta_data = array(
			'page_data'  => array(),
			'meta_title' => '',
		);
		$page_key       = trim( $page_slug . '/' . $page_subslug, '/' );

		if ( $page_subslug && isset( $pages[ $page_subslug ] ) ) {
			$page_data = $pages[ $page_subslug ];
		}

		if ( $page_slug && isset( $pages[ $page_slug ] ) ) {
			$page_data = $pages[ $page_slug ];
		}

		if ( $page_key && isset( $pages[ $page_key ] ) ) {
			$page_data = $pages[ $page_key ];
		}

		if ( empty( $page_data ) ) {
			return $page_meta_data;
		}

		$page_title = isset( $page_data['title'] ) ? $page_data['title'] : '';

		if ( $page_subslug ) {
			$slug       = str_replace( array( '-', '_', '/' ), ' ', (string) $page_subslug );
			$page_title = wp_strip_all_tags( ucfirst( $slug ) );
		}

		$site_name  = get_bloginfo( 'name' );
		$meta_title = '';

		if ( '' !== $page_title ) {
			/* translators: 1: current page title, 2: site name. */
			$meta_title = sprintf( __( '%1$s - %2$s', 'tutor' ), $page_title, $site_name );
		}

		$page_meta_data['page_data']  = $page_data;
		$page_meta_data['meta_title'] = $meta_title;

		return $page_meta_data;
	}

	/**
	 * Set the document title for custom frontend templates.
	 *
	 * @since 4.0.0
	 *
	 * @param string $title Document title.
	 *
	 * @return void
	 */
	public static function set_document_title( string $title ): void {
		add_filter(
			'pre_get_document_title',
			static function () use ( $title ) {
				return $title;
			},
			999
		);
	}
}
