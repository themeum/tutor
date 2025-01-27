<?php
/**
 * Template importer
 *
 * @package tutor
 */

namespace TUTOR;

use Droip\ExportImport\TemplateImport;
use Tutor\Traits\JsonResponse;
use TutorLMSDroip\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Ajax class
 */
class TemplateImporter {
	use JsonResponse;

	/**
	 * Register default hooks and actions for WordPress
	 */
	public function __construct() {
		add_action( 'wp_ajax_tutor_template_list', array( $this, 'tutor_template_list' ) );
		add_action( 'wp_ajax_install_plugins', array( $this, 'install_plugins' ) );
		add_action( 'wp_ajax_import_droip_template', array( $this, 'import_droip_template' ) );
		add_action( 'wp_ajax_process_droip_template', array( $this, 'process_droip_template' ) );
	}

	/**
	 * AJAX callback to install a plugin.
	 */
	public function tutor_template_list() {
		$template_list = self::get_template_list();
		ob_start();
		require_once tutor()->path . 'views/templates/_templates-list.php';
		$contents = ob_get_clean();
		$this->json_response( 'Successfully fetched!', $contents );
	}

	/**
	 * AJAX callback to install a plugin.
	 */
	public function install_plugins() {
		// $nonce_value = Input::post( 'nonce_value' );
		tutor_utils()->check_nonce();
		// if ( ! wp_verify_nonce( $nonce_value, 'tutorowl_nonce' ) ) {
		// return $this->response( false, 'Invalid nonce!', 'error' );
		// }
		// $plugin_name = $_POST['plugin_name'];  //phpcs:ignore
		$plugin_name = Input::post('plugin_name'); //phpcs:ignore
		$plugin_info = REQUIRED_PLUGINS[ $plugin_name ];
		$this->installing_plugin( $plugin_info );
	}


	/**
	 * Install plugin.
	 *
	 * @param array $plugin_info install plugin details.
	 *
	 * @return array
	 */
	public function installing_plugin( $plugin_info ) {
		sleep( 1 );
		try {
			if ( ! class_exists( 'WP_Upgrader' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			}
			if ( 'plugin' === $plugin_info['type'] ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

				$is_install_plugin = $this->is_plugin_installed( $plugin_info['path'] );
				if ( ! $is_install_plugin ) {
					$upgrader = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );

					$installed = $upgrader->install( $plugin_info['src'] );
					if ( is_wp_error( $installed ) ) {
						return $this->response( false, 'Plugin installation error!', 'error' );
					}
				}
				$activate = activate_plugin( $plugin_info['path'], '', false, false );
				if ( is_wp_error( $activate ) ) {
					return $this->response( false, 'Plugin activation error!', 'error' );
				}
				return $this->response( true, 'Plugin installed successfully!', 'success' );

			} elseif ( 'theme' === $plugin_info['type'] ) {
				require_once ABSPATH . 'wp-admin/includes/theme-install.php';

				$is_theme_installed = wp_get_theme( $plugin_info['base'] )->exists();

				if ( ! $is_theme_installed ) {
					$upgrader = new \Theme_Upgrader( new \WP_Ajax_Upgrader_Skin() );

					$installed = $upgrader->install( $plugin_info['src'] );
					if ( is_wp_error( $installed ) ) {
						return $this->response( false, 'Theme installation error!', 'error' );
					}
				}

				switch_theme( $plugin_info['base'] );

				if ( wp_get_theme()->get_stylesheet() !== $plugin_info['base'] ) {
					return $this->response( false, 'Theme activation error!', 'error' );
				}

				return $this->response( true, 'Theme installed and activated successfully.', 'success' );
			}
		} catch ( \Throwable $th ) {
			return $this->response( false, 'Something went wrong!', 'error' );
		}
	}


	/**
	 * Add comment or reply
	 *
	 * @return Array
	 */
	public function import_droip_template() {
		try {
			$template_id          = $_POST['template_id'];  //phpcs:ignore
			$template_list        = self::get_template_list();
			$template_to_download = $template_list[ $template_id ];
			$url                  = $template_to_download['src'];
			// $url                = TUTOROWL_PATH . $template_to_download['src'];
			$tutor_license_info = get_option( 'tutor_license_info' );

			$template_import = new TemplateImport();
			$is_import       = $template_import->import( $url, 'tutor_' . $template_id, true );

			if ( $is_import ) {
				return $this->response( true, 'Content imported', 'done' );
			} else {
				return $this->response( false, 'Content importing error!', 'error' );
			}
		} catch ( \Throwable $th ) {
			return $this->response( false, 'Something went wrong!', 'error' );
		}
	}

	/**
	 * Process_droip_template description
	 *
	 * @return  array  description
	 */
	public function process_droip_template() {
		$ti         = new TemplateImport();
		$is_process = $ti->process();
		return $this->response( $is_process );
	}


	/**
	 * Check plugin is install or not
	 *
	 * @param   string $plugin_path plugin-slug.
	 *
	 * @return  bool
	 */
	private function is_plugin_installed( $plugin_path ) {
		$installed_plugins = get_plugins();
		foreach ( $installed_plugins as $plugin_file => $plugin_data ) {
			if ( $plugin_path === $plugin_file ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get template list
	 *
	 * @return array
	 */
	public static function get_template_list() {
		// $template_response       = wp_remote_get( TEMPLATE_LIST_ENDPOINT );
		$template_response = file_get_contents( TEMPLATE_LIST_ENDPOINT );
		// $templates_response_body = wp_remote_retrieve_body( $template_response );
		// $templates               = json_decode( $templates_response_body, true );
		$templates = json_decode( $template_response, true );
		try {
			// $template_response = wp_remote_post( TEMPLATE_LIST_ENDPOINT );
			// $template_response = new WP_Error( 'Template Not available' );
			// if ( is_wp_error( $template_response ) ) {
			// $error_message = $template_response->get_error_message();
			// return array();
			// echo "Something went wrong: $error_message";
			// }
			// $template_response_body = wp_remote_retrieve_body( $template_response );
			// $templates              = json_decode( $template_response_body, true );
			// return $templates['body_response'] ?? array();
			return $templates;
		} catch ( \Throwable $th ) {
			return array();
		}
	}

	/**
	 * Response method.
	 *
	 * @param   boolean $success     status of isPlugin installed or not.
	 * @param   string  $message     message after installation of plugin.
	 * @param   string  $status      status of plugin.
	 *
	 * @return  void
	 */
	public function response( $success, $message = '', $status = '' ) {
		wp_send_json(
			array(
				'message' => $message,
				'status'  => $status,
				'success' => $success,
			)
		);
	}
}
