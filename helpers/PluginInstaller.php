<?php
/**
 * Helper class to manage plugin installation.
 *
 * @package Tutor\Helper
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.2.0
 */

namespace Tutor\Helpers;

/**
 * PluginInstaller class.
 *
 * @since 3.2.0
 */
class PluginInstaller {

	/**
	 * Install/Upgrade the payment gateway plugin
	 *
	 * @since 3.0.0
	 *
	 * @param string $plugin_url Plugin URL.
	 * @param mixed  $plugin_basename Plugin basename.
	 *
	 * @throws \Exception If the plugin installation fails.
	 *
	 * @return bool
	 */
	public static function install_or_upgrade_plugin( $plugin_url, $plugin_basename = null ) {
		$plugin_dir = $plugin_basename ? dirname( $plugin_basename ) : null;
		if ( ! $plugin_dir ) {
			$plugin_dir = explode( '-', basename( $plugin_url ) )[0];
		}

		$args = array(
			'package'                     => $plugin_url,
			'destination'                 => WP_PLUGIN_DIR . '/' . $plugin_dir,
			'clear_destination'           => true,
			'abort_if_destination_exists' => true,
		);

		// Include necessary WordPress functions for plugin installation.
		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}
		if ( ! class_exists( 'WP_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		// Define the plugin installer class.
		$upgrader = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );

		if ( $plugin_basename ) {
			// Upgrade.
			$upgrade = $upgrader->run( $args );
			if ( is_wp_error( $upgrade ) ) {
				throw new \Exception( $upgrade->get_error_message() );
			} elseif ( ! $upgrade ) {
				return false;
			} else {
				return true;
			}
		} else {
			// Install the plugin.
			$install = $upgrader->install( $plugin_url, $args );
			if ( is_wp_error( $install ) ) {
				throw new \Exception( $install->get_error_message() );
			} elseif ( ! $install ) {
				return false;
			}
		}

		// Activate the plugin after installation.
		$plugin_basename = $upgrader->plugin_info(); // Retrieves the plugin basename.
		if ( $plugin_basename ) {
			$activate = activate_plugin( $plugin_basename );
			if ( is_wp_error( $activate ) ) {
				throw new \Exception( $activate->get_error_message() );
			}
		} else {
			return false;
		}

		return true;
	}

	/**
	 * Fetches the downloadable URL for a WordPress plugin from the WordPress API.
	 *
	 * @param string $plugin_slug The plugin slug.
	 *
	 * @return array An associative array with the status of the request and the download link or error message.
	 * @since 3.2.0
	 */
	public static function get_downloadable_url( $plugin_slug ) {

		$plugin_slug = strtok( $plugin_slug, '/' );
		$url         = "https://api.wordpress.org/plugins/info/1.0/{$plugin_slug}.json";
		$request     = HttpHelper::get( $url );
		$response    = $request->get_json();
		$status      = $request->get_status_code();

		if ( HttpHelper::STATUS_OK === $status && ! empty( $response->download_link ) ) {

			return array(
				'success' => true,
				'message' => $response->download_link,
			);
		}

		$message = $response->error ?? __( 'An error occurred while fetching the plugin download link.', 'tutor' );

		return array(
			'success' => false,
			'message' => $message,
		);
	}
}
