<?php
/**
 * Withdraw class
 *
 * @package Tutor\Withdraw
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

use Exception;
use Tutor\Models\WithdrawModel;
use Tutor\Traits\JsonResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Withdraw class
 *
 * @since 1.0.0
 */
class Withdraw {
	use JsonResponse;

	/**
	 * Withdraw method
	 *
	 * @since 1.0.0
	 *
	 * @var mixed
	 */
	public $withdraw_methods;

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_tutor_save_withdraw_account', array( $this, 'tutor_save_withdraw_account' ) );
		add_action( 'wp_ajax_tutor_make_an_withdraw', array( $this, 'tutor_make_an_withdraw' ) );
		add_filter( 'tutor_withdrawal_methods_all', array( $this, 'withdraw_methods_all' ) );
		add_filter( 'tutor_withdrawal_methods_available', array( $this, 'withdraw_methods_available' ) );
	}

	/**
	 * Available withdraw methods
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function withdraw_methods_all() {

		$this->migrate_withdrawal_method_data();

		$methods = array(
			'bank_transfer_withdraw' => array(
				'method_name' => __( 'Bank Transfer', 'tutor' ),
				'image'       => tutor()->url . 'assets/images/payment-bank.png',
				'desc'        => __( 'Get your payment directly into your bank account', 'tutor' ),

				'form_fields' => array(
					'account_name'   => array(
						'type'  => 'text',
						'label' => __( 'Account Name', 'tutor' ),
					),
					'account_number' => array(
						'type'  => 'text',
						'label' => __( 'Account Number', 'tutor' ),
					),
					'bank_name'      => array(
						'type'  => 'text',
						'label' => __( 'Bank Name', 'tutor' ),
					),
					'iban'           => array(
						'type'  => 'text',
						'label' => __( 'IBAN', 'tutor' ),
					),
					'swift'          => array(
						'type'  => 'text',
						'label' => __( 'BIC / SWIFT', 'tutor' ),
					),

				),
			),

			'echeck_withdraw'        => array(
				'method_name' => __( 'E-Check', 'tutor' ),
				'image'       => tutor()->url . 'assets/images/payment-echeck.png',
				'form_fields' => array(
					'physical_address' => array(
						'type'  => 'text',
						'label' => __( 'Your Physical Address', 'tutor' ),
						'desc'  => __( 'We will send you an E-Check to this address directly.', 'tutor' ),
					),
				),
			),

			'paypal_withdraw'        => array(
				'method_name' => __( 'PayPal', 'tutor' ),
				'image'       => tutor()->url . 'assets/images/payment-paypal.png',
				'form_fields' => array(
					'paypal_email' => array(
						'type'  => 'email',
						'label' => __( 'PayPal E-Mail Address', 'tutor' ),
						'desc'  => __( 'We will use this email address to send the money to your Paypal account', 'tutor' ),
					),

				),
			),
		);

		$saved_options              = (array) get_option( 'tutor_option', array() );
		$withdrawal_payment_methods = $saved_options['tutor_withdrawal_methods'] ?? array();
		foreach ( $methods as $key => $method ) {
			$methods[ $key ]['enabled'] = in_array( $key, $withdrawal_payment_methods, true );
		}

		return apply_filters( 'tutor_withdraw_methods', $methods );
	}

	/**
	 * Withdraw method's tab
	 *
	 * @return void
	 */
	private function migrate_withdrawal_method_data() {
		$old_data = get_option( 'tutor_withdraw_options', null );

		if ( ! $old_data ) {
			// Return if already migrated.
			return;
		}

		$withdraw_options  = (array) maybe_unserialize( $old_data );
		$new_methods_array = array();

		foreach ( $withdraw_options as $key => $option ) {
			if ( is_array( $option ) ) {

				// Set enable state.
				if ( isset( $option['enabled'] ) ) {
					$option['enabled'] ? $new_methods_array[] = $key : 0;
				}

				// Set instruction.
				if ( isset( $option['instruction'] ) ) {
					tutor_utils()->update_option( 'tutor_' . $key . '_instruction', $option['instruction'] );
				}
			}
		}

		// Update new.
		tutor_utils()->update_option( 'tutor_withdrawal_methods', $new_methods_array );

		// Delete old.
		delete_option( 'tutor_withdraw_options' );
	}

	/**
	 * Return only enabled methods
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|array
	 */
	public function withdraw_methods_available() {
		$methods          = $this->withdraw_methods_all();
		$withdraw_options = tutor_utils()->get_option( 'tutor_withdrawal_methods', array() );

		foreach ( $methods as $method_id => $method ) {
			if ( ! in_array( $method_id, $withdraw_options ) ) {
				// Remove the unavailable methods from array.
				unset( $methods[ $method_id ] );
			}
		}

		return $methods;
	}

	/**
	 * Check whether a string looks like a file path or a JSON string.
	 *
	 * Rejected patterns:
	 *  - Escape HTML
	 *  - Strings that start with / or ./ or ../ (absolute / relative paths).
	 *  - Path-traversal sequences anywhere in the value.
	 *  - Values that end with a file extension (e.g. .php, .js, .sh).
	 *  - PHP / script open tags embedded in the value.
	 *  - JSON objects or arrays (starts with { or [).
	 *
	 * @since 4.1.1
	 *
	 * @param string $value Sanitized field value.
	 * @return bool True when the value must be rejected.
	 */
	private function is_dangerous_value( string $value ): bool {

		$decoded = html_entity_decode( $value );

		// Check for PHP / script tags on the DECODED string.
		if ( preg_match( '/<\?(?:php)?|<script/i', $decoded ) ) {
			return true;
		}

		// Strip any remaining HTML / tag-like content after the tag checks.
		$clean   = wp_kses( $decoded, array() );
		$trimmed = trim( $clean );

		// Treat a value that is entirely HTML tags (nothing left after stripping)
		// as dangerous — it means the input was purely markup with no real content.
		if ( empty( $trimmed ) ) {
			return true;
		}

		// Reject JSON objects or arrays.
		if ( str_starts_with( $trimmed, '{' ) || str_starts_with( $trimmed, '[' ) ) {
			return true;
		}

		// Reject file-path patterns (absolute, relative, Windows-style).
		if ( preg_match( '#(^[/\\\\]|\.{1,2}[/\\\\]|[/\\\\]\.\.)#', $trimmed ) ) {
			return true;
		}

		// Reject values that end with a known dangerous file extension.
		if ( preg_match( '/\.(php\d*|phtml|js|sh|py|rb|pl|cgi|asp|aspx|exe|bat|cmd)$/i', $trimmed ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Validate a single field value against its declared type.
	 *
	 * Supported types: text, email, number, textarea.
	 * Unknown types fall back to the same rules as "text".
	 *
	 * @since 4.0.10
	 *
	 * @param string $type  Field type declared in withdraw_methods_all().
	 * @param string $value Sanitized field value.
	 * @return bool True when the value passes validation.
	 */
	private function is_valid_field_value( string $type, string $value ): bool {
		// Empty values are handled by the required-field check; skip type validation.
		if ( '' === $value ) {
			return true;
		}

		switch ( $type ) {
			case 'email':
				return (bool) is_email( $value );

			case 'number':
				return is_numeric( $value );

			case 'text':
			case 'textarea':
			default:
				// Must not exceed a reasonable length and must not be purely whitespace.
				return strlen( $value ) <= 500 && '' !== trim( $value );
		}
	}

	/**
	 * Save Withdraw Method Data
	 *
	 * Hardening checklist (4.0.10):
	 *  1. Method key must exist in withdraw_methods_all() (full registry).
	 *  2. Method key must also be currently available (enabled by the admin).
	 *  3. Submitted field keys must match the fields declared in withdraw_methods_all().
	 *     Any unrecognised field is rejected with an error message.
	 *  4. Values containing file paths, JSON strings, or PHP/script tags are rejected.
	 *  5. Each value is validated against the field's declared type.
	 *
	 * @since 1.2.0
	 * @since 4.0.8  Capability check, field whitelist, no esc_sql().
	 * @since 4.0.10 Reject file paths / JSON; unknown-field check; per-type validation.
	 *
	 * @return void Sends a JSON response and exits.
	 */
	public function tutor_save_withdraw_account() {
		// Verify nonce.
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();

		// Withdraw account settings are for instructors only.
		if ( ! tutor_utils()->is_instructor( $user_id ) ) {
			wp_send_json_error( array( 'msg' => tutor_utils()->error_message() ) );
		}

		//phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce already verified
		$method      = sanitize_key( tutor_utils()->avalue_dot( 'tutor_selected_withdraw_method', $_POST ) );
		$all_methods = $this->withdraw_methods_all();

		if ( ! $method || ! isset( $all_methods[ $method ] ) ) {
			wp_send_json_error(
				array( 'msg' => __( 'Invalid withdrawal method.', 'tutor' ) )
			);
		}

		$available_methods = $this->withdraw_methods_available();
		if ( ! isset( $available_methods[ $method ] ) ) {
			wp_send_json_error(
				array( 'msg' => __( 'This withdrawal method is not currently available.', 'tutor' ) )
			);
		}

		$form_fields = $all_methods[ $method ]['form_fields'] ?? array();
		if ( ! is_array( $form_fields ) || empty( $form_fields ) ) {
			wp_send_json_error(
				array( 'msg' => __( 'No form fields defined for this withdrawal method.', 'tutor' ) )
			);
		}

		$method_data = tutor_utils()->avalue_dot( 'withdraw_method_field.' . $method, $_POST );
		if ( ! is_array( $method_data ) || ! tutor_utils()->count( $method_data ) ) {
			wp_send_json_error(
				array( 'msg' => __( 'No withdrawal data submitted.', 'tutor' ) )
			);
		}

		$saved_data                         = array();
		$saved_data['withdraw_method_key']  = $method;
		$saved_data['withdraw_method_name'] = $all_methods[ $method ]['method_name'] ?? '';
		$errors                             = array();

		foreach ( $method_data as $submitted_key => $raw_value ) {
			$submitted_key = sanitize_key( $submitted_key );

			if ( ! array_key_exists( $submitted_key, $form_fields ) ) {
				$errors[] = sprintf(
					/* translators: %s: submitted field key */
					__( 'Unknown field submitted: "%s".', 'tutor' ),
					$submitted_key
				);
				continue;
			}

			if ( is_array( $raw_value ) ) {
				$errors[] = sprintf(
					/* translators: %s: field label */
					__( 'Field "%s" must not be an array.', 'tutor' ),
					$form_fields[ $submitted_key ]['label'] ?? $submitted_key
				);
				continue;
			}

			$field      = $form_fields[ $submitted_key ];
			$field_type = $field['type'] ?? 'text';
			$label      = $field['label'] ?? $submitted_key;

			$value = ( 'email' === $field_type )
				? sanitize_email( wp_unslash( $raw_value ) )
				: sanitize_text_field( wp_unslash( $raw_value ) );

			if ( $this->is_dangerous_value( $value ) ) {
				$errors[] = sprintf(
					/* translators: %s: field label */
					__( 'Field "%s" contains an invalid value.', 'tutor' ),
					$label
				);
				continue;
			}

			if ( ! $this->is_valid_field_value( $field_type, $value ) ) {
				$errors[] = sprintf(
					/* translators: 1: field label, 2: expected field type */
					__( 'Field "%1$s" has an invalid value for type "%2$s".', 'tutor' ),
					$label,
					$field_type
				);
				continue;
			}

			$saved_data[ $submitted_key ] = array(
				'value' => $value,
				'label' => $label,
			);
		}

		// Return all validation errors at once.
		if ( ! empty( $errors ) ) {
			wp_send_json_error( array( 'message' => implode( ' ', $errors ) ) );
		}

		// $saved_data always starts with 2 internal keys (method_key, method_name).
		if ( count( $saved_data ) <= 2 ) {
			wp_send_json_error(
				array( 'msg' => __( 'Please fill in the required withdrawal fields.', 'tutor' ) )
			);
		}

		update_user_meta( $user_id, '_tutor_withdraw_method_data', $saved_data );
		update_user_meta( $user_id, '_tutor_withdraw_selected_method', $method );
		update_user_meta( $user_id, '_tutor_withdraw_method_data_' . $method, $saved_data );

		$msg = apply_filters( 'tutor_withdraw_method_set_success_msg', __( 'Withdrawal information saved!', 'tutor' ) );
		wp_send_json_success( array( 'msg' => $msg ) );
	}

	/**
	 * Handle withdraw request form submit.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 *
	 * @throws Exception If any validation fails.
	 */
	public function tutor_make_an_withdraw() {
		global $wpdb;

		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();
		if ( ! tutor_utils()->is_instructor( $user_id ) ) {
			wp_send_json_error( array( 'msg' => tutor_utils()->error_message() ) );
		}

		$lock_name = 'tutor_withdraw_lock_' . $user_id;
		$locked    = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 10)', $lock_name ) );

		if ( 1 !== (int) $locked ) {
			wp_send_json_error(
				array(
					'msg' => __( 'Another withdrawal request is in progress. Please try again.', 'tutor' ),
				)
			);
		}

		try {
			$withdraw_amount = (float) Input::post( 'amount' );
			$earning_summary = WithdrawModel::get_withdraw_summary( $user_id );
			$min_withdraw    = (float) tutor_utils()->get_option( 'min_withdraw_amount' );

			if ( ( $earning_summary->total_pending + $withdraw_amount ) > $earning_summary->available_for_withdraw ) {
				throw new Exception(
					wp_sprintf(
					/* translators: 1: total pending withdraw request 2: available for withdraw */
						__( "You have total %1\$s pending withdraw request. You can't make more than %2\$s withdraw request at a time", 'tutor' ),
						$earning_summary->total_pending,
						$earning_summary->available_for_withdraw
					)
				);
			}

			$saved_withdraw_account        = WithdrawModel::get_user_withdraw_method();
			$formatted_min_withdraw_amount = tutor_utils()->tutor_price( $min_withdraw );

			if ( ! tutor_utils()->count( $saved_withdraw_account ) ) {
				$no_withdraw_method = apply_filters( 'tutor_no_withdraw_method_msg', __( 'Please save withdraw method ', 'tutor' ) );
				throw new Exception( $no_withdraw_method );
			}

			if ( ( ! is_numeric( $withdraw_amount ) && ! is_float( $withdraw_amount ) ) || $withdraw_amount < $min_withdraw ) {
				/* translators: 1: strong tag start 2: min withdrawal amount 3: strong tag end */
				$required_min_withdraw = apply_filters( 'tutor_required_min_amount_msg', sprintf( __( 'Minimum withdrawal amount is %1$s %2$s %3$s ', 'tutor' ), '<strong>', $formatted_min_withdraw_amount, '</strong>' ) );
				throw new Exception( $required_min_withdraw );
			}

			if ( $earning_summary->available_for_withdraw < $withdraw_amount ) {
				$insufficient_balence = apply_filters( 'tutor_withdraw_insufficient_balance_msg', __( 'Insufficient balance.', 'tutor' ) );
				throw new Exception( $insufficient_balence );
			}

			$date = gmdate( 'Y-m-d H:i:s', tutor_time() );

			$withdraw_data = apply_filters(
				'tutor_pre_withdraw_data',
				array(
					'user_id'     => $user_id,
					'amount'      => $withdraw_amount,
					'method_data' => maybe_serialize( $saved_withdraw_account ),
					'status'      => 'pending',
					'created_at'  => $date,
				)
			);

			do_action( 'tutor_insert_withdraw_before', $withdraw_data );

			$inserted = $wpdb->insert( $wpdb->prefix . 'tutor_withdraws', $withdraw_data );
			if ( false === $inserted ) {
				throw new Exception( __( 'Unable to process withdrawal request. Please try again.', 'tutor' ) );
			}

			$withdraw_id = $wpdb->insert_id;

			do_action( 'tutor_insert_withdraw_after', $withdraw_id, $withdraw_data );

			/**
			* Getting earning and balance data again
			*/
			$earning               = WithdrawModel::get_withdraw_summary( $user_id );
			$new_available_balance = tutor_utils()->tutor_price( $earning->available_for_withdraw );

			do_action( 'tutor_withdraw_after' );

			$response = array(
				'msg'               => apply_filters( 'tutor_withdraw_successful_msg', __( 'Withdrawal Request Sent!', 'tutor' ) ),
				'available_balance' => $new_available_balance,
			);

		} catch ( Exception $e ) {

			$response = array(
				'error' => true,
				'msg'   => $e->getMessage(),
			);

		} finally {

			$wpdb->query(
				$wpdb->prepare(
					'SELECT RELEASE_LOCK(%s)',
					$lock_name
				)
			);
		}

		if ( ! empty( $response['error'] ) ) {
			wp_send_json_error(
				array(
					'msg' => $response['msg'],
				)
			);
		}

		wp_send_json_success( $response );
	}
}
