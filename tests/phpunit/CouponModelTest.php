<?php
namespace TutorTest;

use Tutor\Models\CouponModel;
use TUTOR\Tutor;

class CouponModelTest extends \WP_UnitTestCase {

	private $coupon_model;

	public function set_up(): void {
		parent::set_up();

		$this->coupon_model = new CouponModel();

		$this->create_tables();
	}

	public function test_get_coupon_usage_count_before_4_2_0() {
		$user_id = $this->factory->user->create();

		$coupon = $this->get_coupon_data( $user_id );

		try {
			$this->create_coupon( $coupon );
		} catch ( \Throwable $th ) {
			$this->fail( 'Failed to create coupon: ' . $th->getMessage() );
			return;
		}

		$usage_data = array(
			'coupon_code'    => $coupon['coupon_code'],
			'user_id'        => $user_id,
			'created_at_gmt' => gmdate( 'Y-m-d H:i:s' ),
		);

		$store = $this->coupon_model->store_coupon_usage( $usage_data );

		$this->assertNotEmpty( $store );

		$actual   = (int) $this->coupon_model->get_coupon_usage_count( $coupon['coupon_code'] );
		$expected = 1;

		$this->assertSame( $expected, $actual );

		$store = $this->coupon_model->store_coupon_usage( $usage_data );
		$this->assertNotEmpty( $store );

		$actual   = (int) $this->coupon_model->get_coupon_usage_count( $coupon['coupon_code'] );
		$expected = 2;

		$this->assertSame( $expected, $actual );
	}

	public function test_get_coupon_usage_count_reservation() {
		$now_gmt     = current_time( 'mysql', true );
		$expires_gmt = gmdate( 'Y-m-d H:i:s', strtotime( '+30 minutes', strtotime( $now_gmt ) ) );

		$user_id     = $this->factory->user->create();
		$order_id    = 1;
		$coupon_data = $this->get_coupon_data( $user_id );

		$coupon_data['total_usage_limit'] = 1;
		$coupon_data['coupon_code']       = 'USAGE1';

		$this->assertNotEmpty( $this->create_coupon( $coupon_data ) );

		$usage = array(
			'coupon_code'    => $coupon_data['coupon_code'],
			'order_id'       => $order_id,
			'user_id'        => $user_id,
			'status'         => $this->coupon_model::STATUS_RESERVED,
			'expires_at_gmt' => $expires_gmt,
		);

		$usage_id = $this->coupon_model->store_coupon_usage( $usage );
		$this->assertNotEmpty( $usage_id );

		$this->assertSame( 1, (int) $this->coupon_model->get_coupon_usage_count( $coupon_data['coupon_code'] ) );

		// Another usage should return false.
		$another_user = $this->factory->user->create();
		$is_valid     = $this->coupon_model->is_coupon_valid( (object) $coupon_data, $another_user );
		$this->assertFalse( $is_valid );
	}

	/**
	 * Create coupons and coupon usage tables.
	 *
	 * @return void
	 */
	private function create_tables(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// 1. Create coupons table first as per table schema.
		$coupons_table = "CREATE TABLE {$wpdb->prefix}tutor_coupons (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			coupon_status VARCHAR(50),
			coupon_type VARCHAR(100) DEFAULT 'code',
			coupon_code VARCHAR(50) NOT NULL,
			coupon_title VARCHAR(255) NOT NULL,
			coupon_description TEXT,
			discount_type ENUM('percentage', 'flat') NOT NULL,
			discount_amount DECIMAL(13, 2) NOT NULL,
			applies_to VARCHAR(100) DEFAULT 'all_courses_and_bundles',
			total_usage_limit INT(10) UNSIGNED DEFAULT NULL,
			per_user_usage_limit TINYINT(4) UNSIGNED DEFAULT NULL,
			purchase_requirement VARCHAR(50) DEFAULT 'no_minimum',
			purchase_requirement_value DECIMAL(13, 2),
			start_date_gmt DATETIME NOT NULL,
			expire_date_gmt DATETIME DEFAULT NULL,
			created_at_gmt DATETIME NOT NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL,
			updated_at_gmt DATETIME,
			updated_by BIGINT(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY coupon_code (coupon_code),
			KEY start_date_gmt (start_date_gmt),
			KEY expire_date_gmt (expire_date_gmt)
		) $charset_collate;";

		dbDelta( $coupons_table );

		// 2. Create coupon usages table after tutor_coupons exists.
		$coupon_usage_table = Tutor::get_coupon_usage_table_schema( false );
		dbDelta( $coupon_usage_table );
	}

	/**
	 * Drop tables created for the tests.
	 *
	 * @return void
	 */
	private function drop_tables(): void {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tutor_coupon_usages" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tutor_coupons" );
	}

	private function create_coupon( array $coupon_data ) {
		return $this->coupon_model->create_coupon( $coupon_data );
	}

	private function get_coupon_data( int $user_id = 1 ) {
		return array(
			'coupon_status'              => 'active',
			'coupon_type'                => 'code',
			'coupon_code'                => 'SUMMER50',
			'coupon_title'               => 'Summer Special 50% Off',
			'coupon_description'         => 'Get a 50% discount on all courses and bundles.',
			'discount_type'              => 'percentage',
			'discount_amount'            => 50.00,
			'applies_to'                 => 'all_courses_and_bundles',
			'total_usage_limit'          => 100,
			'per_user_usage_limit'       => 1,
			'purchase_requirement'       => 'minimum_purchase',
			'purchase_requirement_value' => 20.00,
			'start_date_gmt'             => gmdate( 'Y-m-d H:i:s' ),
			'expire_date_gmt'            => gmdate( 'Y-m-d H:i:s', strtotime( '+30 days' ) ),
			'created_at_gmt'             => gmdate( 'Y-m-d H:i:s' ),
			'created_by'                 => $user_id,
			'updated_at_gmt'             => gmdate( 'Y-m-d H:i:s' ),
			'updated_by'                 => $user_id,
		);
	}

	public function tear_down(): void {
		$this->drop_tables();

		unset( $this->coupon_model );

		parent::tear_down();
	}
}
