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
		'compliance_key',
		'title',
		'label_text',
		'policy_url',
		'version',
		'is_required',
		'is_active',
		'placements',
		'settings',
		'created_at_utc',
		'updated_at_utc',
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
}
