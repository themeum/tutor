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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
	const ACCOUNT_PAGE_SLUG = 'account';
	const COURSES_PAGE_SLUG = 'courses';
	const QUIZ_ATTEMPTS_PAGE_SLUG = 'quiz-attempts';
	const MY_QUIZ_ATTEMPTS_SUBPAGE_SLUG = 'my-quiz-attempts';

	/**
	 * Constructor
	 *
	 * @since 1.3.4
	 * @return void
	 */
	public function __construct() {
		add_action( 'tutor_load_template_after', array( $this, 'tutor_load_template_after' ), 10, 2 );
		add_filter( 'should_tutor_load_template', array( $this, 'should_tutor_load_template' ), 10, 2 );
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
				'icon'        => Icon::PROFILE_CIRCLE,
				'icon_active' => Icon::PROFILE_CIRCLE_FILL,
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
			'billing' => array(
				'title'       => esc_html__( 'Billing', 'tutor' ),
				'icon'        => Icon::BILLING,
				'icon_active' => Icon::BILLING,
				'url'         => self::get_account_page_url( 'billing' ),
				'template'    => tutor_get_template( 'dashboard.account.billing' ),
			),
		);

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
				'template'       => tutor_get_template( 'dashboard.quiz-attempts.quiz-reviews' ),
				'requires_param' => 'attempt_id',
			),
			self::COURSES_PAGE_SLUG . '/' . self::MY_QUIZ_ATTEMPTS_SUBPAGE_SLUG => array(
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
}
