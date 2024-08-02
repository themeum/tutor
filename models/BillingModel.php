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
	 * Insert billing info
	 *
	 * @param array $data Bulling info data.
	 *
	 * @return int The ID of the inserted row on success, or 0 on failure.
	 */
	public function insert( $data ) {
		global $wpdb;

		return QueryHelper::insert(
			"{$wpdb->prefix}tutor_customers",
			array(
				'user_id'          => $data['user_id'],
				'billing_name'     => $data['first_name'] . ' ' . $data['last_name'],
				'billing_email'    => $data['email'],
				'billing_phone'    => $data['phone'],
				'billing_zip_code' => $data['zip_code'],
				'billing_address'  => $data['address'],
				'billing_country'  => $data['country'],
				'billing_state'    => $data['state'],
				'billing_city'     => $data['city'],
			),
		);
	}

	/**
	 * Update billing info
	 *
	 * @param array $data Bulling info data.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function update( $data ) {
		global $wpdb;

		return QueryHelper::update(
			"{$wpdb->prefix}tutor_customers",
			array(
				'user_id'          => $data['user_id'],
				'billing_name'     => $data['first_name'] . ' ' . $data['last_name'],
				'billing_email'    => $data['email'],
				'billing_phone'    => $data['phone'],
				'billing_zip_code' => $data['zip_code'],
				'billing_address'  => $data['address'],
				'billing_country'  => $data['country'],
				'billing_state'    => $data['state'],
				'billing_city'     => $data['city'],
			),
			array(
				'user_id' => $data['user_id'],
			),
		);
	}

	/**
	 * Get billing info
	 *
	 * @param int $user_id User ID.
	 *
	 * @return object|false The billing info as an object if found, or false if not found.
	 */
	public function get_info( $user_id ) {
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
