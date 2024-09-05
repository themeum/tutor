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

use Tutor\Models\OrderModel;
use TutorPro\Subscription\Models\PlanModel;

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
	 * Send mail once new order place
	 *
	 * @since 3.0.0
	 *
	 * @param array $order_data Order data.
	 *
	 * @return void
	 */
	public function order_placed( array $order_data ) {

		$order_data        = (object) $order_data;
		$order_data->items = (object) $order_data->items;

		$student_ids    = array( $order_data->user_id );
		$admin_ids      = array();
		$instructor_ids = array();

		// Set admin user ids.
		$admin_users = get_users( array( 'role' => 'administrator' ) );

		foreach ( $admin_users as $admin_user ) {
			$admin_ids[] = $admin_user->ID;
		}

		// Set instructor ids.
		foreach ( $order_data->items as $item ) {
			$item      = (object) $item;
			$course_id = $item->item_id;
			if ( OrderModel::TYPE_SUBSCRIPTION === $order_data->order_type || OrderModel::TYPE_RENEWAL === $order_data->order_type ) {
				$course_id = apply_filters( 'tutor_subscription_course_by_plan', $course_id, $order_data );
			}
			$instructor_ids[] = get_post_field( 'post_author', $course_id );
		}

		$this->send_email_to( 'email_to_students', 'new_order', $student_ids, $order_data->id );
		$this->send_email_to( 'email_to_admin', 'new_order', $admin_ids, $order_data->id );
		$this->send_email_to( 'email_to_teachers', 'new_order', $instructor_ids, $order_data->id );
	}

	/**
	 * Send email to a specific recipient
	 *
	 * @since 3.0.0
	 *
	 * @param string $recipient_type Recipient type like
	 * email_to_students, email_to_teachers, email_to_admin.
	 * @param  string $email_type New order/ order status updated.
	 * @param  array  $recipients Recipients ids.
	 * @param  int    $order_id Order id.
	 *
	 * @return void
	 */
	private function send_email_to( $recipient_type, $email_type, $recipients, $order_id ) {
		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$option_data = $this->get_option_data( $recipient_type, $email_type );

		$order_data = ( new OrderModel() )->get_order_by_id( $order_id );
		$recipients = array_unique( $recipients );
		foreach ( $recipients as $recipient ) {
			// Ignore email when teachers himself admin.
			// because admin email has already been send.
			if ( 'email_to_teachers' === $recipient_type && user_can( $recipient, 'manage_options' ) ) {
				continue;
			}

			$user_data = get_userdata( $recipient );
			$header    = 'Content-Type: ' . $this->get_content_type() . "\r\n";
			$header    = apply_filters( 'new_order_email_header', $header );

			$replacable['{testing_email_notice}'] = '';
			$replacable['{user_name}']            = tutor_utils()->get_user_name( $user_data );
			$replacable['{site_url}']             = $site_url;
			$replacable['{site_name}']            = $site_name;

			if ( OrderModel::TYPE_SUBSCRIPTION === $order_data->order_type ) {
				$plan = ( new PlanModel() )->get_plan( $order_data->items[0]->id );

				$replacable['{course_name}'] = $plan->title;
			} else {
				$replacable['{course_name}'] = count( $order_data->items ) > 1 ? _n( 'Course', 'Courses', count( $order_data->items ) ) : $order_data->items[0]->title;
			}

			$replacable['{admin_order_url}'] = admin_url( 'admin.php?page=tutor_orders&action=edit&id=' . $order_id );

			$replacable['{site_order_url}'] = site_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'purchase_history' ) );

			$replacable['{order_id}']    = '#' . $order_data->id;
			$replacable['{order_date}']  = tutor_i18n_get_formated_date( $order_data->created_at_gmt, get_option( 'date_format' ) );
			$replacable['{order_total}'] = tutor_get_formatted_price( $order_data->total_price );

			$replacable['{dashboard_url}'] = tutor_utils()->get_tutor_dashboard_page_permalink();
			$replacable['{logo}']          = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
			$replacable['{email_heading}'] = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );

			$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );

			$replacable['{footer_text}'] = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );

			$subject = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

			ob_start();
			$this->tutor_load_email_template( 'order_new' );
			$email_tpl = apply_filters( 'tutor_email_tpl/order_new', ob_get_clean() );
			$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

			$this->send( $user_data->user_email, $subject, $message, $header );
		}
	}

	/**
	 * Setup email data
	 *
	 * @since 3.0.0
	 *
	 * @param array $email_config Default email config data.
	 *
	 * @return array.
	 */
	public function setup_email_config( $email_config ) {
		$order_email = $this->get_email_data();

		$email_config['email_to_students']['new_order']            = $order_email['email_to_students']['new_order'];
		$email_config['email_to_students']['order_status_updated'] = $order_email['email_to_students']['order_status_updated'];

		$email_config['email_to_teachers']['new_order']            = $order_email['email_to_teachers']['new_order'];
		$email_config['email_to_teachers']['order_status_updated'] = $order_email['email_to_teachers']['order_status_updated'];

		$email_config['email_to_admin']['new_order']            = $order_email['email_to_admin']['new_order'];
		$email_config['email_to_admin']['order_status_updated'] = $order_email['email_to_admin']['order_status_updated'];

		return $email_config;
	}

	/**
	 * Get email data.
	 *
	 * @return array
	 */
	public function get_email_data() {
		$email_array = array(
			'email_to_students' => array(
				'new_order'            => array(
					'label'       => __( 'New order placed', 'tutor' ),
					'default'     => 'on',
					'template'    => 'order_new',
					'tooltip'     => 'New order emails are sent to chosen recipient(s) when a new order is received.',
					'subject'     => __( 'Your Order Confirmation for {course_name}', 'tutor' ),
					'heading'     => __( 'Your order has been received!', 'tutor' ),
					'message'     => wp_json_encode(
						'
						<p>Hi {user_name},</p>
						<p>Thank you for purchasing! We’re excited to have you on board and can’t wait for you to start learning.</p>
						<p>Order Details:</p>
						<div>
							<li>Order ID : {order_id}</li>
							<li>Date: {order_date}</li>
							<li>Total: {order_total}</li>
							<a href="{site_order_url}">View Details</a>
						</div>
						<p>We will let you know once your order has been completed and is ready for access.</p>
					'
					),
					'footer_text' => __( 'Thank you for choosing {site_name}.', 'tutor' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
				'order_status_updated' => array(
					'label'       => __( 'Order status updated', 'tutor' ),
					'default'     => 'on',
					'template'    => 'order_status_updated',
					'tooltip'     => 'Order status update emails are sent to chosen recipient(s) whenever a order status updated.',
					'subject'     => __( 'Your order status has been updated!', 'tutor' ),
					'heading'     => __( 'Your order status has been updated!', 'tutor' ),
					'message'     => wp_json_encode(
						'
						<p>Hi {user_name},</p>
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
			),
			'email_to_teachers' => array(
				'new_order'            => array(
					'label'       => __( 'New order placed', 'tutor' ),
					'default'     => 'on',
					'template'    => 'order_new',
					'tooltip'     => 'New order emails are sent to chosen recipient(s) when a new order is received.',
					'subject'     => __( 'New Order for Your Course: {course_name}!', 'tutor' ),
					'heading'     => __( 'Your order has been received!', 'tutor' ),
					'message'     => wp_json_encode(
						'
						<p>Hi {user_name},</p>
						<p>We’re excited to let you know that a new order has been placed for your course, {course_name}.</p>
						<p>Order Details:</p>
						<ul>
							<li>Order ID : {order_id}</li>
							<li>Student Name: {student_name}</li>
							<li>Date: {order_date}</li>
							<li>Total: {order_total}</li>
						</ul>
						<p>Keep up the great work, and thank you for being part of our platform!</p>
					'
					),
					'footer_text' => '
						<div>
							<li>Best Regards</li>
							<li>{site_name}</li>
						</div>
					',
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
				'order_status_updated' => array(
					'label'       => __( 'Order status updated', 'tutor' ),
					'default'     => 'on',
					'template'    => 'order_status_updated',
					'tooltip'     => 'Order status update emails are sent to chosen recipient(s) whenever a order status updated.',
					'subject'     => __( 'Your order status has been updated!', 'tutor' ),
					'heading'     => __( 'Your order status has been updated!', 'tutor' ),
					'message'     => wp_json_encode(
						'
						<p>Hi {user_name},</p>
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
			),
			'email_to_admin'    => array(
				'new_order'            => array(
					'label'    => __( 'New order placed', 'tutor' ),
					'default'  => 'on',
					'template' => 'order_new',
					'tooltip'  => 'New order emails are sent to chosen recipient(s) when a new order is received.',
					'subject'  => __( 'New Order Received!', 'tutor' ),
					'heading'  => __( 'A new order has just been placed.', 'tutor' ),
					'message'  => wp_json_encode(
						'
						<p>Below are the details of your order:</p>
						<ul>
							<li>Order ID : {order_id}</li>
							<li>Date: {order_date}</li>
							<li>Total Amount: {order_total}</li>
						</ul>
						<a href="{admin_order_url}">Order Details</a>
						<p>Please review the order and ensure everything is in place for the student\'s access to the course. Thank you.</p>
					'
					),
					'footer'   => '
						<div style="list-style:none;">
							<li>Best Regards</li>
							<li>{site_name}</li>
						</div>
					',
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
				'order_status_updated' => array(
					'label'       => __( 'Order status updated', 'tutor' ),
					'default'     => 'on',
					'template'    => 'order_status_updated',
					'tooltip'     => 'Order status update emails are sent to chosen recipient(s) whenever a order status updated.',
					'subject'     => __( 'Your order status has been updated!', 'tutor' ),
					'heading'     => __( 'Your order status has been updated!', 'tutor' ),
					'message'     => wp_json_encode(
						'
						<p>Hi {user_name},</p>
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
			),
		);

		return $email_array;
	}
}
