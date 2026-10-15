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

use Tutor\GDPR\DB\UserConsents as Table;
use TUTOR\Input;
use Tutor\Models\BaseModel;

defined( 'ABSPATH' ) || exit;

/**
 * User content model class.
 *
 * @since 4.0.0
 */
class UserConsents extends BaseModel {

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
		'consent_title',
		'label_snapshot',
		'label_snapshot_plain_text',
		'links_snapshot',
		'consent_method',
		'version',
		'ip_address',
		'user_agent',
		'source',
		'created_at_gmt',
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
