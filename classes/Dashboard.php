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

use Tutor\Helpers\UrlHelper;

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
	 * Account page constants
	 *
	 * @since 4.0.0
	 */
	const ACCOUNT_PAGE_SLUG        = 'account';
	const ACCOUNT_PAGE_QUERY_PARAM = 'subpage';

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

		return UrlHelper::add_query_params( $account_page_url, array( self::ACCOUNT_PAGE_QUERY_PARAM => $page ) );
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
			'profile'  => array(
				'title'       => esc_html__( 'Profile', 'tutor' ),
				'icon'        => Icon::PROFILE_CIRCLE,
				'icon_active' => Icon::PROFILE_CIRCLE_FILL,
				'url'         => self::get_account_page_url( 'profile' ),
				'template'    => tutor_get_template( 'dashboard.account.profile' ),
			),
			'reviews'  => array(
				'title'       => esc_html__( 'Reviews', 'tutor' ),
				'icon'        => Icon::RATINGS,
				'icon_active' => Icon::RATINGS,
				'url'         => self::get_account_page_url( 'reviews' ),
				'template'    => tutor_get_template( 'dashboard.account.reviews' ),
			),
			'settings' => array(
				'title'       => esc_html__( 'Settings', 'tutor' ),
				'icon'        => Icon::SETTING,
				'icon_active' => Icon::SETTING,
				'url'         => self::get_account_page_url( 'settings' ),
				'template'    => tutor_get_template( 'dashboard.account.settings' ),
			),
		);

		return apply_filters( 'tutor_dashboard_account_pages', $pages );
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
