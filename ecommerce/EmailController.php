<?php
/**
 * Manage Email
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EmailController class
 *
 * @since 3.0.0
 */
class EmailController {
	const INACTIVE_REMINDED_META = 'tutor_inactive_reminded';
	const TO_STUDENTS            = 'email_to_students';
	const TO_TEACHERS            = 'email_to_teachers';
	const TO_ADMIN               = 'email_to_admin';
	const ORDER_EMAILS           = 'order_emails';

	/**
	 * Queue table
	 *
	 * @var string
	 */
	private $queue_table;

	/**
	 * Constructor.
	 *
	 * Initializes the Orders class, sets the page title, and optionally registers
	 * hooks for handling AJAX requests related to order data, bulk actions, order status updates,
	 * and order deletions.
	 *
	 * @param bool $register_hooks Whether to register hooks for handling requests. Default is true.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		global $wpdb;
		$this->queue_table = $wpdb->tutor_email_queue;

		if ( ! $register_hooks ) {
			return;
		}

		add_action( 'wp_ajax_tutor_send_mail_test', array( $this, 'send_test_mail' ) );

		add_filter( 'tutor_pro/email/list', array( $this, 'setup_email_config' ) );
	}

	// @TODO: Will be removed later.
	public function send_test_mail() {
		$this->order_placed( get_current_user_id() );
	}

	/**
	 * Send E-Mail Notification for Tutor Event.
	 *
	 * @param string $to to address.
	 * @param string $subject email subject.
	 * @param string $message message.
	 * @param mixed  $headers headers.
	 * @param array  $attachments attachments.
	 * @param bool   $force_enqueue force enqueue.
	 * @param int    $batch batch number, default false.
	 *
	 * @return void
	 */
	public function send( $to, $subject, $message, $headers, $attachments = array(), $force_enqueue = false, $batch = false ) {
		$message = apply_filters( 'tutor_mail_content', $message );
		$this->enqueue_email( $to, $subject, $message, $headers, $attachments, $force_enqueue, $batch );
	}

	/**
	 * Email enqueue.
	 *
	 * @param string  $to to.
	 * @param string  $subject subject.
	 * @param string  $message message.
	 * @param mixed   $headers headers.
	 * @param array   $attachments attachments.
	 * @param boolean $force_enqueue force enqueue.
	 * @param int     $batch batch number. default false.
	 *
	 * @return void
	 */
	private function enqueue_email( $to, $subject, $message, $headers, $attachments = array(), $force_enqueue = false, $batch = false ) {
		global $wpdb;

		if ( ! $batch ) {
			$batch = time();
		}

		$data = array(
			'mail_to' => $to,
			'subject' => $subject,
			'message' => $message,
			'headers' => serialize( $headers ),
			'batch'   => $batch,
		);

		if ( is_string( $to ) && ! $force_enqueue ) {
			// Send email instantly in case single recipient.
			$this->send_mail( array( $data ) );
			return;
		}

		! is_array( $to ) ? $to = array( $to ) : 0;

		foreach ( $to as $email ) {
			$insert_data = array_merge( $data, array( 'mail_to' => $email ) );
			$wpdb->insert( $this->queue_table, $insert_data );
		}
	}

	/**
	 * Sent email.
	 *
	 * @param array $mails list of mail address.
	 *
	 * @return void
	 */
	public function send_mail( $mails ) {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		foreach ( $mails as $mail ) {
			$mail['headers'] = unserialize( $mail['headers'] );
			wp_mail( $mail['mail_to'], $mail['subject'], $mail['message'], $mail['headers'] );
		}

		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	/**
	 * Load email template.
	 *
	 * @param string  $template template.
	 * @param boolean $pro is pro.
	 * @param array   $extra extra data.
	 *
	 * @return void
	 */
	public function tutor_load_email_template( $template, $extra = array() ) {
		extract( $extra ); //phpcs:ignore
		include tutor_get_template( 'email.' . $template, );
	}

	/**
	 * Get the from name for outgoing emails from tutor
	 *
	 * @return string
	 */
	public function get_from_name() {
		$email_from_name = tutor_utils()->get_option( 'email_from_name' );
		$from_name       = apply_filters( 'tutor_email_from_name', $email_from_name );
		return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
	}

	/**
	 * Get the from name for outgoing emails from tutor
	 *
	 * @return string
	 */
	public function get_from_address() {
		$email_from_address = tutor_utils()->get_option( 'email_from_address' );
		$from_address       = apply_filters( 'tutor_email_from_address', $email_from_address );
		return sanitize_email( $from_address );
	}

	/**
	 * Get content type
	 *
	 * @return string
	 */
	public function get_content_type() {
		return apply_filters( 'tutor_email_content_type', 'text/html' );
	}

	/**
	 * Get message.
	 *
	 * @param string $message message.
	 * @param array  $search search.
	 * @param array  $replace replace.
	 *
	 * @return string
	 */
	public function get_message( $message = '', $search = array(), $replace = array() ) {
		$email_footer_text = tutor_utils()->get_option( 'email_footer_text' );

		$placeholders = array(
			'{site_name}'    => get_bloginfo( 'name' ),
			'{site_url}'     => site_url(),
			'{current_year}' => gmdate( 'Y' ),
		);

		$email_footer_text = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_footer_text );
		$message           = str_replace( $search, $replace, $message );
		if ( $email_footer_text ) {
			$message .= '<div class="tutor-email-footer-content">' . wp_unslash( json_decode( $email_footer_text ) ) . '</div>';
		}
		return $message;
	}

	/**
	 * Ready the e-mail message
	 * Used unslash and trim (") from front and end of the message
	 *
	 * @param string $message message.
	 * @return string
	 */
	public function prepare_message( $message ) {
		return wp_unslash( json_decode( $message ) );
	}

	/**
	 * Function to replace and return
	 *
	 * @since 2.6.1
	 *
	 * Conditionally replacing message to avoid deprecation error what
	 * was added on the PHP 8 version
	 *
	 * @param  mixed $message message.
	 * @param  mixed $search search.
	 * @param  mixed $replace replace.
	 *
	 * @return string
	 */
	public function get_replaced_text( $message = '', $search = array(), $replace = array() ) {
		return $message ? str_replace( $search, $replace, $message ) : $message;
	}

	/**
	 * Get trigger saved data with fallback default data support.
	 *
	 * @since 2.5.0
	 *
	 * @param string $to_key to key like email_to_students, email_to_teachers, email_to_admin.
	 * @param string $trigger_key trigger name.
	 *
	 * @return array
	 */
	public function get_option_data( $to_key, $trigger_key ) {
		return $this->get_email_data()[ $to_key ][ $trigger_key ];
	}

	/**
	 * Order placed email
	 *
	 * @param int $user_id user id.
	 *
	 * @return void.
	 */
	public function order_placed( $user_id ) {

		$user = get_userdata( $user_id );
		if ( false === $user ) {
			return;
		}

		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$option_data = $this->get_option_data( self::ORDER_EMAILS, 'new_order' );
		$header      = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header      = apply_filters( 'new_order_email_header', $header );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $user );
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{dashboard_url}']        = tutor_utils()->get_tutor_dashboard_page_permalink();
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'order_new' );
		$email_tpl = apply_filters( 'tutor_email_tpl/order_new', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $user->user_email, $subject, $message, $header );

	}

	/**
	 * Setup email data
	 *
	 * @param int $email_config email config data.
	 *
	 * @return array.
	 */
	public function setup_email_config( $email_config ) {
		$email_data = $this->get_email_data();
		$email_data = array_merge( $email_config, $email_data );

		return $email_data;
	}
	/**
	 * Get email data.
	 *
	 * @return array
	 */
	public function get_email_data() {
		$email_array = array(
			'order_emails' => array(
				'new_order'        => array(
					'label'       => __( 'New order', 'tutor' ),
					'default'     => 'on',
					'template'    => 'order_new',
					'tooltip'     => 'New order emails are sent to chosen recipient(s) when a new order is received.',
					'subject'     => __( 'Your order has been received!', 'tutor' ),
					'heading'     => __( 'Your order has been received!', 'tutor' ),
					'message'     => wp_json_encode(
						'
                        <p>Hi {student_name},</p>
                        <p>Thank you for your order. We’ve received your order successfully, and it is now being processed.</p>
                        <p>Below are the details of your order:</p>
                        <ul>
                            <li>Order ID : {order_id}</li>
                            <li>Date: {order_date}</li>
                            <li>Total: {order_total}</li>
                        </ul>
                        <p>We will let you know once your order has been completed and is ready for access.</p>
                    '
					),
					'footer_text' => __( 'Thank you for choosing {site_name}.', 'tutor' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
				'cancelled_order'  => array(
					'label'       => __( 'Cancelled order', 'tutor-pro' ),
					'default'     => 'on',
					'template'    => 'order_cancelled',
					'tooltip'     => 'Cancelled order emails are sent to chosen recipient(s) when orders have been marked cancelled.',
					'subject'     => __( 'New order placed at {site_url}', 'tutor-pro' ),
					'heading'     => __( 'Order placed successfully', 'tutor-pro' ),
					'message'     => wp_json_encode( 'A new instructor has signed up on <strong>{site_url}</strong>.' ),
					'footer_text' => __( 'You may reply to this email to communicate with the instructor.', 'tutor-pro' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
				'failed_order'     => array(
					'label'       => __( 'Failed order', 'tutor-pro' ),
					'default'     => 'on',
					'template'    => 'order_failed',
					'tooltip'     => 'Enable this option to use numerical points instead of letter grades.',
					'subject'     => __( 'New order placed at {site_url}', 'tutor-pro' ),
					'heading'     => __( 'Order placed successfully', 'tutor-pro' ),
					'message'     => wp_json_encode( 'A new instructor has signed up on <strong>{site_url}</strong>.' ),
					'footer_text' => __( 'You may reply to this email to communicate with the instructor.', 'tutor-pro' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
				'on_hold_order'    => array(
					'label'       => __( 'Order on-hold', 'tutor-pro' ),
					'default'     => 'on',
					'template'    => 'order_on_hold',
					'tooltip'     => 'Enable this option to use numerical points instead of letter grades.',
					'subject'     => __( 'New order placed at {site_url}', 'tutor-pro' ),
					'heading'     => __( 'Order placed successfully', 'tutor-pro' ),
					'message'     => wp_json_encode( 'A new instructor has signed up on <strong>{site_url}</strong>.' ),
					'footer_text' => __( 'You may reply to this email to communicate with the instructor.', 'tutor-pro' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
				'processing_order' => array(
					'label'       => __( 'Processing order', 'tutor-pro' ),
					'default'     => 'on',
					'template'    => 'order_processing',
					'tooltip'     => 'Enable this option to use numerical points instead of letter grades.',
					'subject'     => __( 'New order placed at {site_url}', 'tutor-pro' ),
					'heading'     => __( 'Order placed successfully', 'tutor-pro' ),
					'message'     => wp_json_encode( 'A new instructor has signed up on <strong>{site_url}</strong>.' ),
					'footer_text' => __( 'You may reply to this email to communicate with the instructor.', 'tutor-pro' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
				'completed_order'  => array(
					'label'       => __( 'Completed order', 'tutor-pro' ),
					'default'     => 'on',
					'template'    => 'order_completed',
					'tooltip'     => 'Enable this option to use numerical points instead of letter grades.',
					'subject'     => __( 'New order placed at {site_url}', 'tutor-pro' ),
					'heading'     => __( 'Order placed successfully', 'tutor-pro' ),
					'message'     => wp_json_encode( 'A new instructor has signed up on <strong>{site_url}</strong>.' ),
					'footer_text' => __( 'You may reply to this email to communicate with the instructor.', 'tutor-pro' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
				'refunded_order'   => array(
					'label'       => __( 'Refunded order', 'tutor-pro' ),
					'default'     => 'on',
					'template'    => 'order_refunded',
					'tooltip'     => 'Enable this option to use numerical points instead of letter grades.',
					'subject'     => __( 'New order placed at {site_url}', 'tutor-pro' ),
					'heading'     => __( 'Order placed successfully', 'tutor-pro' ),
					'message'     => wp_json_encode( 'A new instructor has signed up on <strong>{site_url}</strong>.' ),
					'footer_text' => __( 'You may reply to this email to communicate with the instructor.', 'tutor-pro' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
			),
		);

		return $email_array;
	}
}
