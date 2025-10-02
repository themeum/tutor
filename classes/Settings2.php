<?php
/**
 * Settings 2 (React) page handler
 *
 * @package Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings 2 page handler
 *
 * @since 3.0.0
 */
class Settings2 {

	/**
	 * Register hooks
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 20 );
		add_action( 'wp_ajax_tutor_get_settings_fields', array( $this, 'get_settings_fields' ) );
		add_action( 'wp_ajax_tutor_get_settings_values', array( $this, 'get_settings_values' ) );
		add_action( 'wp_ajax_tutor_search_settings', array( $this, 'search_settings' ) );
	}

	/**
	 * Register admin menu for Settings 2
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'tutor',
			__( 'Settings 2 (React)', 'tutor' ),
			__( 'Settings 2', 'tutor' ),
			'manage_tutor',
			'tutor_settings_2',
			array( $this, 'load_settings_page' )
		);
	}

	/**
	 * Load the React settings page
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function load_settings_page() {
		// Load the React settings template
		$template_path = tutor()->path . '/views/options/settings-react.php';
		include $template_path;
	}

	/**
	 * Get settings fields structure
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function get_settings_fields() {
		tutor_utils()->checking_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$options_v2 = new Options_V2( false );
		$fields = $options_v2->get_setting_fields();

		// Extract only the option_fields part which contains the sections
		$sections = isset( $fields['option_fields'] ) ? $fields['option_fields'] : array();

		// Normalize the sections structure for React
		$normalized_sections = $this->normalize_sections_structure( $sections );

		// Debug: Log the structure for troubleshooting
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Tutor Settings API - Original sections count: ' . count( $sections ) );
			error_log( 'Tutor Settings API - Normalized sections count: ' . count( $normalized_sections ) );
		}

		wp_send_json_success( $normalized_sections );
	}

	/**
	 * Normalize sections structure to ensure blocks are always arrays
	 *
	 * @since 3.0.0
	 *
	 * @param array $sections The sections array from get_setting_fields
	 *
	 * @return array Normalized sections with blocks as arrays
	 */
	private function normalize_sections_structure( $sections ) {
		$normalized = array();

		foreach ( $sections as $section_key => $section ) {
			if ( ! is_array( $section ) ) {
				continue;
			}

			$normalized_section = $section;

			// Ensure blocks is always an array
			if ( isset( $section['blocks'] ) ) {
				if ( is_array( $section['blocks'] ) ) {
					// If blocks is an associative array (object), convert to indexed array
					$normalized_section['blocks'] = array_values( $section['blocks'] );
				} else {
					$normalized_section['blocks'] = array();
				}
			}

			// Handle submenu sections if they exist
			if ( isset( $section['submenu'] ) && is_array( $section['submenu'] ) ) {
				$normalized_submenu = array();
				foreach ( $section['submenu'] as $submenu_key => $submenu_section ) {
					if ( is_array( $submenu_section ) ) {
						$normalized_submenu_section = $submenu_section;
						
						// Ensure submenu blocks are also arrays
						if ( isset( $submenu_section['blocks'] ) && is_array( $submenu_section['blocks'] ) ) {
							$normalized_submenu_section['blocks'] = array_values( $submenu_section['blocks'] );
						}
						
						$normalized_submenu[] = $normalized_submenu_section;
					}
				}
				$normalized_section['submenu'] = $normalized_submenu;
			}

			$normalized[ $section_key ] = $normalized_section;
		}

		return $normalized;
	}

	/**
	 * Get current settings values
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function get_settings_values() {
		tutor_utils()->checking_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$tutor_option = get_option( 'tutor_option', array() );
		
		wp_send_json_success( $tutor_option );
	}

	/**
	 * Search settings fields (normalized version)
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function search_settings() {
		tutor_utils()->checking_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$query = sanitize_text_field( $_POST['query'] ?? '' );
		
		if ( empty( $query ) ) {
			wp_send_json_success( array( 'fields' => array() ) );
		}

		$options_v2 = new Options_V2( false );
		$fields = $options_v2->get_setting_fields();
		$sections = isset( $fields['option_fields'] ) ? $fields['option_fields'] : array();
		$normalized_sections = $this->normalize_sections_structure( $sections );

		$search_results = array();

		foreach ( $normalized_sections as $section ) {
			if ( ! is_array( $section ) || empty( $section ) ) {
				continue;
			}

			// Search in main section blocks
			$blocks = is_array( $section['blocks'] ) ? $section['blocks'] : array();
			foreach ( $blocks as $block ) {
				if ( isset( $block['fields'] ) && is_array( $block['fields'] ) ) {
					foreach ( $block['fields'] as $field ) {
						if ( $this->field_matches_query( $field, $query ) ) {
							$search_results[] = $this->prepare_search_item( $section, $block, $field );
						}
					}
				}
			}

			// Search in submenu sections
			if ( isset( $section['submenu'] ) && is_array( $section['submenu'] ) ) {
				foreach ( $section['submenu'] as $submenu_section ) {
					$submenu_blocks = is_array( $submenu_section['blocks'] ) ? $submenu_section['blocks'] : array();
					foreach ( $submenu_blocks as $block ) {
						if ( isset( $block['fields'] ) && is_array( $block['fields'] ) ) {
							foreach ( $block['fields'] as $field ) {
								if ( $this->field_matches_query( $field, $query ) ) {
									$search_results[] = $this->prepare_search_item( $submenu_section, $block, $field );
								}
							}
						}
					}
				}
			}
		}

		wp_send_json_success( array( 'fields' => $search_results ) );
	}

	/**
	 * Check if a field matches the search query
	 *
	 * @since 3.0.0
	 *
	 * @param array $field The field to check
	 * @param string $query The search query
	 *
	 * @return bool True if field matches query
	 */
	private function field_matches_query( $field, $query ) {
		$query = strtolower( $query );
		
		// Search in field label
		if ( isset( $field['label'] ) && stripos( $field['label'], $query ) !== false ) {
			return true;
		}

		// Search in field description
		if ( isset( $field['desc'] ) && is_string( $field['desc'] ) && stripos( $field['desc'], $query ) !== false ) {
			return true;
		}

		// Search in field key
		if ( isset( $field['key'] ) && stripos( $field['key'], $query ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Prepare search item (same as Options_V2 but with normalized structure)
	 *
	 * @since 3.0.0
	 *
	 * @param array $section section item
	 * @param array $block block item
	 * @param array $field field item
	 *
	 * @return array prepared searchable field item
	 */
	private function prepare_search_item( $section, $block, $field ) {
		$field['section_label'] = isset( $section['label'] ) ? $section['label'] : '';
		$field['section_slug']  = isset( $section['slug'] ) ? $section['slug'] : '';
		$field['block_label']   = isset( $block['label'] ) ? $block['label'] : '';

		return $field;
	}
}