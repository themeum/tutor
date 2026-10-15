<?php
/**
 * Legal consent model.
 *
 * @package Tutor\GDPR\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\GDPR\Models;

use Tutor\GDPR\DB\LegalConsents as Table;
use Tutor\Models\BaseModel;

defined( 'ABSPATH' ) || exit;

/**
 * Legal consent model class.
 *
 * @since 4.0.0
 */
class LegalConsents extends BaseModel {

	/**
	 * Table name without prefix.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $table_name = 'tutor_legal_consents';

	/**
	 * Fillable fields for create/update.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $fillable = array(
		'consent_title',
		'display_on',
		'consent_message',
		'consent_map',
		'version',
		'consent_method',
		'is_active',
		'settings',
		'created_at_gmt',
		'updated_at_gmt',
	);

	/**
	 * Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->table_name = Table::get_table_name();
		parent::__construct();
	}

	/**
	 * Get fillable fields
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public function get_fillable_fields() {
		return $this->fillable;
	}

	/**
	 * Retrieve legal consent entries filtered by display key.
	 *
	 * @since 4.0.0
	 *
	 * @param string $display_key The display key to filter consents (e.g. 'login', 'signup', etc.).
	 *
	 * @return array An array of legal consent objects or an empty array if none found.
	 */
	public function get_consents_by_display_key( string $display_key ): array {
		global $wpdb;

		$res = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					*
				FROM {$this->table_name}
				WHERE FIND_IN_SET( %s, display_on )
				",
				$display_key
			)
		);

		return is_array( $res ) ? $res : array();
	}
}
