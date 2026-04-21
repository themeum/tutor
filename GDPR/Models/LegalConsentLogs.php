<?php
/**
 * Compliance logs model.
 *
 * @package Tutor\GDPR\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\GDPR\Models;

use Tutor\GDPR\DB\Logs as Table;
use Tutor\Models\BaseModel;

defined( 'ABSPATH' ) || exit;

/**
 * Compliance logs model class.
 *
 * @since 4.0.0
 */
class LegalConsentLogs extends BaseModel {

	/**
	 * Table name without prefix.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $table_name = 'tutor_legal_consent_logs';

	/**
	 * Fillable fields for create/update.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $fillable = array(
		'id',
		'compliance_id',
		'action',
		'old_data',
		'new_data',
		'created_at_utc',
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
