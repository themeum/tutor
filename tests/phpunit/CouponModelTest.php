<?php
namespace TutorTest;

use Tutor\Database\Tables\CouponUsagesTable;
use Tutor\Core\DB;
use Tutor\Models\CouponModel;

class CouponModelTest extends \WP_UnitTestCase {

	private $coupon_model;

	public function set_up(): void {
		parent::set_up();

		$this->coupon_model = new CouponModel();

		$this->drop_tables();
		$this->create_tables();
	}

	public function test_get_coupon_usage_count_before_4_2_0() {
		$user_id = $this->factory()->user->create();

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

		$user_id     = $this->factory()->user->create();
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
		$another_user = $this->factory()->user->create();
		$is_valid     = $this->coupon_model->is_coupon_valid( (object) $coupon_data, $another_user );
		$this->assertFalse( $is_valid );
	}

	/**
	 * Active reserved holds must count toward the per-user usage limit.
	 *
	 * @return void
	 */
	public function test_get_user_usage_count_includes_active_reservation() {
		$now_gmt     = current_time( 'mysql', true );
		$expires_gmt = gmdate( 'Y-m-d H:i:s', strtotime( '+30 minutes', strtotime( $now_gmt ) ) );

		$user_id     = $this->factory()->user->create();
		$coupon_data = $this->get_coupon_data( $user_id );

		$coupon_data['total_usage_limit']    = 0;
		$coupon_data['per_user_usage_limit'] = 1;
		$coupon_data['coupon_code']          = 'PERUSER1';

		$this->assertNotEmpty( $this->create_coupon( $coupon_data ) );

		$usage = array(
			'coupon_code'    => $coupon_data['coupon_code'],
			'order_id'       => 10,
			'user_id'        => $user_id,
			'status'         => $this->coupon_model::STATUS_RESERVED,
			'expires_at_gmt' => $expires_gmt,
			'created_at_gmt' => $now_gmt,
		);

		$this->assertNotEmpty( $this->coupon_model->store_coupon_usage( $usage ) );
		$this->assertSame( 1, (int) $this->coupon_model->get_user_usage_count( $coupon_data['coupon_code'], $user_id ) );

		// Second order for the same user should be blocked by per-user limit.
		$this->assertFalse( $this->coupon_model->is_coupon_valid( (object) $coupon_data, $user_id ) );
	}

	/**
	 * Expired reserved holds must not count toward the per-user usage limit.
	 *
	 * @return void
	 */
	public function test_get_user_usage_count_ignores_expired_reservation() {
		$now_gmt     = current_time( 'mysql', true );
		$expires_gmt = gmdate( 'Y-m-d H:i:s', strtotime( '-1 minute', strtotime( $now_gmt ) ) );

		$user_id     = $this->factory()->user->create();
		$coupon_data = $this->get_coupon_data( $user_id );

		$coupon_data['total_usage_limit']    = 0;
		$coupon_data['per_user_usage_limit'] = 1;
		$coupon_data['coupon_code']          = 'PERUSEREXP';

		$this->assertNotEmpty( $this->create_coupon( $coupon_data ) );

		$usage = array(
			'coupon_code'    => $coupon_data['coupon_code'],
			'order_id'       => 11,
			'user_id'        => $user_id,
			'status'         => $this->coupon_model::STATUS_RESERVED,
			'expires_at_gmt' => $expires_gmt,
			'created_at_gmt' => $now_gmt,
		);

		$this->assertNotEmpty( $this->coupon_model->store_coupon_usage( $usage ) );
		$this->assertSame( 0, (int) $this->coupon_model->get_user_usage_count( $coupon_data['coupon_code'], $user_id ) );
		$this->assertTrue( $this->coupon_model->is_coupon_valid( (object) $coupon_data, $user_id ) );
	}

	/**
	 * Re-reserving an existing usage row must refresh expires_at_gmt.
	 *
	 * @return void
	 */
	public function test_reserve_coupon_usage_refreshes_expiry() {
		$now_gmt                             = current_time( 'mysql', true );
		$expired_gmt                         = gmdate( 'Y-m-d H:i:s', strtotime( '-10 minutes', strtotime( $now_gmt ) ) );
		$user_id                             = $this->factory()->user->create();
		$order_id                            = 12;
		$coupon_data                         = $this->get_coupon_data( $user_id );
		$coupon_data['coupon_code']          = 'RENEWTTL';
		$coupon_data['total_usage_limit']    = 1;
		$coupon_data['per_user_usage_limit'] = 1;

		$this->assertNotEmpty( $this->create_coupon( $coupon_data ) );

		$usage = array(
			'coupon_code'    => $coupon_data['coupon_code'],
			'order_id'       => $order_id,
			'user_id'        => $user_id,
			'status'         => $this->coupon_model::STATUS_RESERVED,
			'expires_at_gmt' => $expired_gmt,
			'created_at_gmt' => $now_gmt,
		);
		$this->assertNotEmpty( $this->coupon_model->store_coupon_usage( $usage ) );

		$this->assertTrue(
			$this->coupon_model->reserve_coupon_usage( $order_id, $coupon_data['coupon_code'], $user_id, 30 )
		);

		$row = DB::table( 'tutor_coupon_usages' )
			->where( 'coupon_code', $coupon_data['coupon_code'] )
			->where( 'order_id', $order_id )
			->first();

		$this->assertSame( $this->coupon_model::STATUS_RESERVED, $row->status );
		$this->assertNotEmpty( $row->expires_at_gmt );
		$this->assertGreaterThan( $now_gmt, $row->expires_at_gmt );
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
		$coupon_usage_table = CouponUsagesTable::get_schema( false );
		dbDelta( $coupon_usage_table );
	}

	/**
	 * Drop tables created for the tests.
	 *
	 * @return void
	 */
	private function drop_tables(): void {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Test teardown must drop schema tables.
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=0' );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tutor_coupon_usages" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tutor_coupon_applications" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tutor_coupons" );
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=1' );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
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
