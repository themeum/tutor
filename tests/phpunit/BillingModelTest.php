<?php
/**
 * BillingModel Class Unit Test
 *
 * @package Tutor\Test
 * @since 4.2.0
 */

namespace TutorTest;

use Tutor\Models\BillingModel;

/**
 * Unit tests for BillingModel.
 *
 * Run test: vendor/bin/phpunit --filter=BillingModelTest
 */
class BillingModelTest extends \WP_UnitTestCase {
	/**
	 * Billing model instance.
	 *
	 * @var BillingModel
	 */
	protected $billing_model;

	/**
	 * Set up test environment.
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->billing_model = new BillingModel();
	}

	/**
	 * Test insert billing info.
	 *
	 * @return void
	 */
	public function test_insert_billing_info() {
		$user_id      = 1;
		$billing_info = array(
			'user_id'            => $user_id,
			'billing_first_name' => 'John',
			'billing_last_name'  => 'Doe',
			'billing_email'      => 'john.doe@example.com',
			'billing_phone'      => '1234567890',
			'billing_zip_code'   => '123456',
			'billing_address'    => '123 Main St',
			'billing_country'    => 'US',
			'billing_state'      => 'CA',
			'billing_city'       => 'Los Angeles',
		);

		$billing_id = $this->billing_model->insert( $billing_info );
		$this->assertGreaterThan( 0, $billing_id );

		$billing_info = $this->billing_model->get_info( $user_id );
		$this->assertEquals( $user_id, $billing_info->user_id );
	}

	/**
	 * Tear down test environment.

	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset( $this->billing_model );
	}
}
