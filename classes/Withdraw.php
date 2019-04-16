<?php
/**
 * Withdraw class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.0.0
 */

namespace TUTOR;


if ( ! defined( 'ABSPATH' ) )
	exit;

class Withdraw {

	public $available_withdraw_methods;
	public $get_options;
	protected $withdraw_methods;

	public function __construct() {

		$this->get_options = $this->get_options();
		$this->withdraw_methods = $this->withdraw_methods();
		$this->available_withdraw_methods = $this->available_withdraw_methods();

		add_action('tutor_options_tutor_withdraw_withdraw_methods_before', array($this, 'withdraw_admin_options'));
		add_action('tutor_option_save_after', array($this, 'withdraw_option_save'));
	}



	public function withdraw_methods(){

		$methods = array(
			'bank_transfer_withdraw' => array(
				'method_name'  => __('Bank Transfer', 'tutor'),
				'desc'  => __('Get your payment directly into your bank account', 'tutor'),

				'admin_form_fields'           => array(
					'instruction' => array(
						'type'      => 'textarea',
						'label'     => __('Instruction', 'tutor'),
						'desc'     => __('Write instruction for the instructor to fill bank information', 'tutor'),
					),
				),

				'form_fields'           => array(
					'account_name' => array(
						'type'      => 'text',
						'label'     => __('Account Name', 'tutor'),
					),

					'account_number' => array(
						'type'      => 'number',
						'label'      => __('Account Number', 'tutor'),
					),

					'bank_name' => array(
						'type'      => 'text',
						'label'     => __('Bank Name', 'tutor'),
					),
					'iban' => array(
						'type'      => 'text',
						'label'     => __('IBAN', 'tutor'),
					),
					'swift' => array(
						'type'      => 'text',
						'label'     => __('BIC / SWIFT', 'tutor'),
					),

				),
			),

			'echeck_withdraw' => array(
				'method_name'  => __('ECHECK', 'tutor'),

				'form_fields'           => array(
					'bank_name' => array(
						'type'      => 'textarea',
						'label'     => __('Your Physical Address', 'tutor'),
						'desc'      => __('We will send you an ECHECK to this address directly.', 'tutor'),
					),

				),
			),

			'paypal_withdraw' => array(
				'method_name'  => __('PayPal Payment', 'tutor'),

				'form_fields'           => array(
					'bank_name' => array(
						'type'      => 'email',
						'label'     => __('PayPal E-Mail Address', 'tutor'),
						'desc'      => __('Write your paypal email address to get payout directly to your paypal account', 'tutor'),
					),

				),
			),

		);

		$withdraw_methods = apply_filters('tutor_withdraw_methods', $methods);

		return $withdraw_methods;
	}

	/**
	 * @return mixed|array
	 *
	 * Return only enabled methods
	 */
	public function available_withdraw_methods(){
		$withdraw_options = $this->get_options();
		$methods = $this->withdraw_methods();

		foreach ($methods as $method_id => $method){
			$is_enable = (bool) tutor_utils()->avalue_dot($method_id.".enabled", $withdraw_options);

			if ( ! $is_enable){
				unset($methods[$method_id]);
			}
		}

		return $methods;
	}

	public function get_options(){
		return (array) maybe_unserialize(get_option('tutor_withdraw_options'));
	}

	public function withdraw_admin_options(){
		include tutor()->path.'views/options/withdraw/withdraw_admin_options_generator.php';
	}


	public function withdraw_option_save(){
		
		do_action('tutor_withdraw_options_save_before');

		$option = (array) isset($_POST['tutor_withdraw_options']) ? $_POST['tutor_withdraw_options'] : array();
		$option = apply_filters('tutor_withdraw_options_input', $option);
		update_option('tutor_withdraw_options', $option);

		do_action('tutor_withdraw_options_save_after');

		
	}



}