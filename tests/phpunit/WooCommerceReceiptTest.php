<?php
/**
 * WooCommerce Receipt Tests
 *
 * @package TutorTest
 */

namespace TutorTest;

use TUTOR\Course;
use TUTOR\Tutor;
use TUTOR\WooCommerce;
use WP_UnitTestCase;

/**
 * Class WooCommerceReceiptTest
 */
class WooCommerceReceiptTest extends WP_UnitTestCase {

	/**
	 * Admin user ID.
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Customer user ID.
	 *
	 * @var int
	 */
	protected $customer_id;

	/**
	 * Other student user ID.
	 *
	 * @var int
	 */
	protected $other_user_id;

	/**
	 * WooCommerce integration instance.
	 *
	 * @var WooCommerce
	 */
	protected $woocommerce;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		Tutor::manage_tutor_roles_and_permissions();

		$this->admin_id      = $this->factory()->user->create( array( 'role' => 'administrator' ) );
		$this->customer_id   = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->other_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->woocommerce   = new WooCommerce();
	}

	/**
	 * Clean up test state.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		wp_set_current_user( 0 );
		$_GET = array();
		parent::tearDown();
	}

	/**
	 * Test receipt URL generation contains query param without nonce or auto-print params.
	 *
	 * @return void
	 */
	public function test_get_receipt_url_contains_expected_query_params() {
		$url   = WooCommerce::get_receipt_url( 190 );
		$parts = wp_parse_url( $url );
		parse_str( $parts['query'] ?? '', $query );

		$this->assertSame( '190', $query[ WooCommerce::RECEIPT_QUERY_PARAM ] ?? '' );
		$this->assertArrayNotHasKey( tutor()->nonce, $query );
		$this->assertArrayNotHasKey( 'print', $query );
	}

	/**
	 * Test receipt page, dashboard, and legacy script filter detection.
	 *
	 * @return void
	 */
	public function test_receipt_page_and_asset_filter_detection() {
		$_GET = array();
		$this->assertFalse( WooCommerce::is_receipt_page() );
		$this->assertFalse( $this->woocommerce->filter_is_dashboard_page_for_receipt( false ) );
		$this->assertTrue( $this->woocommerce->filter_should_load_legacy_scripts_for_receipt( true ) );
		$this->assertSame( 'page.php', $this->woocommerce->load_wc_receipt_template( 'page.php' ) );

		$_GET[ WooCommerce::RECEIPT_QUERY_PARAM ] = '190';
		$this->assertTrue( WooCommerce::is_receipt_page() );
		$this->assertTrue( $this->woocommerce->filter_is_dashboard_page_for_receipt( false ) );
		$this->assertFalse( $this->woocommerce->filter_should_load_legacy_scripts_for_receipt( true ) );
	}

	/**
	 * Test unauthenticated and invalid order requests return expected HTTP status codes.
	 *
	 * @return void
	 */
	public function test_validate_receipt_access_guards_unauthenticated_and_missing_orders() {
		wp_set_current_user( 0 );
		$guest_result = WooCommerce::validate_receipt_access( 190 );
		$this->assertInstanceOf( \WP_Error::class, $guest_result );
		$this->assertSame( 'tutor_wc_receipt_unauthorized', $guest_result->get_error_code() );
		$this->assertSame( array( 'status' => 401 ), $guest_result->get_error_data() );

		wp_set_current_user( $this->customer_id );
		$invalid_id_result = WooCommerce::validate_receipt_access( 0 );
		$this->assertInstanceOf( \WP_Error::class, $invalid_id_result );
		$this->assertSame( 'tutor_wc_receipt_not_found', $invalid_id_result->get_error_code() );
		$this->assertSame( array( 'status' => 404 ), $invalid_id_result->get_error_data() );
	}

	/**
	 * Test full WooCommerce order receipt data, ownership/capability checks, and status gating.
	 *
	 * @return void
	 */
	public function test_woocommerce_order_receipt_data_and_access_control() {
		if ( ! function_exists( 'wc_create_order' ) ) {
			$this->markTestSkipped( 'WooCommerce is not active in this PHPUnit environment.' );
		}

		update_option( 'blogname', 'Academia Demo' );
		update_option( 'woocommerce_store_address', 'Calle Gran Via 12' );
		update_option( 'woocommerce_store_city', 'Madrid' );
		update_option( 'woocommerce_store_postcode', '28013' );
		update_option( 'woocommerce_default_country', 'ES:M' );

		$order = wc_create_order(
			array(
				'customer_id' => $this->customer_id,
				'status'      => 'completed',
			)
		);
		$order->set_currency( 'EUR' );
		$order->set_billing_first_name( 'Ana' );
		$order->set_billing_last_name( 'Garcia' );
		$order->set_billing_company( 'Formacion SL' );
		$order->set_billing_address_1( 'Calle Alcala 50' );
		$order->set_billing_city( 'Madrid' );
		$order->set_billing_postcode( '28014' );
		$order->set_billing_country( 'ES' );
		$order->set_billing_email( 'ana@example.com' );
		$order->set_billing_phone( '+34 600 000 000' );
		$order->set_payment_method_title( 'Stripe' );

		$item = new \WC_Order_Item_Product();
		$item->set_name( 'Curso WordPress' );
		$item->set_quantity( 1 );
		$item->set_subtotal( '99.00' );
		$item->set_total( '99.00' );
		$order->add_item( $item );
		$order->calculate_totals();
		$order->update_meta_data( Course::IS_TUTOR_ORDER_FOR_COURSE_META, 'yes' );
		$order->save();

		$order_id = $order->get_id();

		wp_set_current_user( $this->other_user_id );
		$denied = WooCommerce::validate_receipt_access( $order_id );
		$this->assertInstanceOf( \WP_Error::class, $denied );
		$this->assertSame( 'tutor_wc_receipt_forbidden', $denied->get_error_code() );
		$this->assertSame( array( 'status' => 403 ), $denied->get_error_data() );

		wp_set_current_user( $this->customer_id );
		$this->assertInstanceOf( \WC_Order::class, WooCommerce::validate_receipt_access( $order_id ) );

		wp_set_current_user( $this->admin_id );
		$this->assertInstanceOf( \WC_Order::class, WooCommerce::validate_receipt_access( $order_id ) );

		$receipt = WooCommerce::build_receipt_data( $order );
		$this->assertSame( 'Academia Demo', $receipt['company']['name'] );
		$this->assertStringContainsString( 'Calle Gran Via 12', $receipt['company']['formatted_address'] );
		$this->assertSame( 'Ana Garcia', $receipt['customer']['name'] );
		$this->assertSame( 'Formacion SL', $receipt['customer']['company'] );
		$this->assertSame( 'ana@example.com', $receipt['customer']['email'] );
		$this->assertSame( 'EUR', $receipt['order']['currency'] );
		$this->assertSame( 'Stripe', $receipt['order']['payment_method_title'] );
		$this->assertArrayHasKey( 'date_completed', $receipt['order'] );
		$this->assertArrayHasKey( 'coupon_codes', $receipt['order'] );
		$this->assertArrayHasKey( 'net_total', $receipt['order'] );
		$this->assertCount( 1, $receipt['order']['items'] );
		$this->assertSame( 'Curso WordPress', $receipt['order']['items'][0]['name'] );
		$this->assertArrayHasKey( 'course_id', $receipt['order']['items'][0] );
		$this->assertArrayHasKey( 'is_course', $receipt['order']['items'][0] );
		$this->assertNotEmpty( $receipt['order']['items'][0]['image'] );

		$order->set_status( 'processing' );
		$order->save();
		$this->assertFalse( WooCommerce::is_receipt_available( $order ) );

		add_filter(
			'tutor_wc_receipt_allowed_statuses',
			static fn() => array( 'completed', 'processing' )
		);
		$this->assertTrue( WooCommerce::is_receipt_available( $order ) );
		remove_all_filters( 'tutor_wc_receipt_allowed_statuses' );
	}
}
