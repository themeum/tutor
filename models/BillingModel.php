<?php
/**
 * Billing Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Models;

use Tutor\Helpers\QueryHelper;

/**
 * Billing model class for performing billing functionalities
 */
class BillingModel {

	/**
	 * Save billing info
	 *
	 * @param int   $user_id User ID.
	 * @param array $data Bulling info data.
	 *
	 * @return array
	 */
	public function save_billing_info( $user_id, $data ) {
		global $wpdb;

		$billing_info = QueryHelper::insert(
			"{$wpdb->prefix}tutor_customers",
			array(
				'user_id'          => $user_id,
				'billing_name'     => $data['first_name'] . $data['last_name'],
				'billing_email'    => $data['email'],
				'billing_phone'    => $data['phone'],
				'billing_zip_code' => $data['zip_code'],
				'billing_address'  => $data['address'],
				'billing_country'  => $data['country'],
				'billing_state'    => $data['state'],
				'billing_city'     => $data['city'],
			),
		);
		return $billing_info;
	}

	/**
	 * Update billing info
	 *
	 * @param int   $user_id User ID.
	 * @param array $data Bulling info data.
	 *
	 * @return array
	 */
	public function update_billing_info( $user_id, $data ) {
		global $wpdb;

		$billing_info = QueryHelper::update(
			"{$wpdb->prefix}tutor_customers",
			array(
				'user_id'          => $user_id,
				'billing_name'     => $data['first_name'] . $data['last_name'],
				'billing_email'    => $data['email'],
				'billing_phone'    => $data['phone'],
				'billing_zip_code' => $data['zip_code'],
				'billing_address'  => $data['address'],
				'billing_country'  => $data['country'],
				'billing_state'    => $data['state'],
				'billing_city'     => $data['city'],
			),
			array(
				'user_id' => $user_id,
			),
		);
		return $billing_info;
	}

	/**
	 * Get billing info
	 *
	 * @param int $user_id User ID.
	 *
	 * @return array
	 */
	public function get_billing_info( $user_id ) {
		global $wpdb;

		$billing_info = QueryHelper::get_row(
			"{$wpdb->prefix}tutor_customers",
			array(
				'user_id' => $user_id,
			),
			'id'
		);
		return $billing_info;
	}
}
