<?php
/**
 * User content model.
 *
 * @package Tutor\GDPR\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\GDPR\Models;

use Tutor\GDPR\DB\UserContents as Table;
use Tutor\Models\BaseModel;

defined( 'ABSPATH' ) || exit;

/**
 * User content model class.
 *
 * @since 4.0.0
 */
class UserContents extends BaseModel {

	/**
	 * Table name without prefix.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $table_name = 'tutor_user_contents';

	/**
	 * Fillable fields for create/update.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $fillable = array(
		'user_id',
		'user_email',
		'compliance_key',
		'label_snapshot',
		'policy_url',
		'version',
		'accepted',
		'ip_address',
		'user_agent',
		'source',
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
