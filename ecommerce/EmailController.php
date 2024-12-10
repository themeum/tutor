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
		add_action( 'tutor_order_placed', array( $this, 'order_placed' ) );
		add_action( 'tutor_order_payment_status_changed', array( $this, 'order_updated' ), 10, 4 );

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
		$email_data   = get_option( 'email_template_data' );
		$default_data = $this->get_email_data();

		return isset( $email_data[ $to_key ][ $trigger_key ] ) ? $email_data[ $to_key ][ $trigger_key ] : $default_data[ $to_key ][ $trigger_key ];
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

		if ( tutor()->has_pro ) {
			if ( tutor_utils()->get_option( self::TO_STUDENTS . '.new_order' ) ) {
				$this->send_email_to( self::TO_STUDENTS, 'new_order', $student_ids, $order_data->id );
			}

			if ( tutor_utils()->get_option( self::TO_ADMIN . '.new_order' ) ) {
				$this->send_email_to( self::TO_ADMIN, 'new_order', $admin_ids, $order_data->id );
			}

			if ( tutor_utils()->get_option( self::TO_TEACHERS . '.new_order' ) ) {
				$this->send_email_to( self::TO_TEACHERS, 'new_order', $instructor_ids, $order_data->id );
			}
		} else {
			$this->send_email_to( self::TO_STUDENTS, 'new_order', $student_ids, $order_data->id );
			$this->send_email_to( self::TO_ADMIN, 'new_order', $admin_ids, $order_data->id );
			$this->send_email_to( self::TO_TEACHERS, 'new_order', $instructor_ids, $order_data->id );
		}
	}

	/**
	 * Send mail once new order place
	 *
	 * @since 3.0.0
	 *
	 * @param int    $order_id Order id.
	 * @param string $prev_payment_status Order previous payment status.
	 * @param string $new_payment_status Order new status.
	 *
	 * @return void
	 */
	public function order_updated( $order_id, $prev_payment_status, $new_payment_status ) {

		$order_data = ( new OrderModel() )->get_order_by_id( $order_id );

		if ( OrderModel::PAYMENT_PARTIALLY_REFUNDED === $new_payment_status || ( OrderModel::PAYMENT_REFUNDED === $new_payment_status && OrderModel::ORDER_COMPLETED === $order_data->order_status ) ) {
			return;
		}

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
			$course_id = $item->id;
			if ( OrderModel::TYPE_SUBSCRIPTION === $order_data->order_type || OrderModel::TYPE_RENEWAL === $order_data->order_type ) {
				$course_id = apply_filters( 'tutor_subscription_course_by_plan', $course_id, $order_data );
			}
			$instructor_ids[] = get_post_field( 'post_author', $course_id );
		}

		if ( tutor_utils()->get_option( self::TO_STUDENTS . '.order_status_updated' ) ) {
			$this->send_email_to( self::TO_STUDENTS, 'order_status_updated', $student_ids, $order_data->id );
		}

		if ( tutor_utils()->get_option( self::TO_ADMIN . '.order_status_updated' ) ) {
			$this->send_email_to( self::TO_TEACHERS, 'order_status_updated', $instructor_ids, $order_data->id );
		}

		if ( tutor_utils()->get_option( self::TO_TEACHERS . '.order_status_updated' ) ) {
			$this->send_email_to( self::TO_ADMIN, 'order_status_updated', $admin_ids, $order_data->id );
		}
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

			/**
			 * Notification preference check for student.
			 *
			 * @since 3.1.0
			 */
			if ( self::TO_STUDENTS === $recipient_type ) {
				$trigger_name = $email_type;
				$user_id      = $recipient;

				$notification_enabled = apply_filters( 'tutor_is_notification_enabled_for_user', true, 'email', self::TO_STUDENTS, $trigger_name, $user_id );
				if ( ! $notification_enabled ) {
					continue;
				}
			}

			$user_data = get_userdata( $recipient );
			$header    = 'Content-Type: ' . $this->get_content_type() . "\r\n";
			$header    = apply_filters( 'new_order_email_header', $header );

			$replacable['{testing_email_notice}'] = '';
			$replacable['{user_name}']            = tutor_utils()->get_user_name( $user_data );
			$replacable['{site_url}']             = $site_url;
			$replacable['{site_name}']            = $site_name;

			if ( OrderModel::TYPE_SUBSCRIPTION === $order_data->order_type ) {
				$plan                        = ( new PlanModel() )->get_plan( $order_data->items[0]->id );
				$replacable['{course_name}'] = $plan->plan_name;
			} else {
				$replacable['{course_name}'] = count( $order_data->items ) > 1 ? _n( 'Course', 'Courses', count( $order_data->items ) ) : $order_data->items[0]->title;
			}

			$replacable['{admin_order_url}'] = admin_url( 'admin.php?page=tutor_orders&action=edit&id=' . $order_id );

			$replacable['{site_order_url}'] = 'email_to_students' === $recipient_type ? tutor_utils()->get_tutor_dashboard_page_permalink( 'purchase_history' ) : tutor_utils()->get_tutor_dashboard_page_permalink( 'analytics/statements' );

			$replacable['{order_id}']             = '#' . $order_data->id;
			$replacable['{order_date}']           = tutor_i18n_get_formated_date( $order_data->created_at_gmt, get_option( 'date_format' ) );
			$replacable['{order_total}']          = tutor_get_formatted_price( $order_data->total_price );
			$replacable['{order_status}']         = ucfirst( $order_data->order_status );
			$replacable['{order_payment_status}'] = ucfirst( $order_data->payment_status );

			$student = get_userdata( $order_data->student->id );
			if ( is_a( $student, 'WP_User' ) ) {
				$replacable['{student_name}'] = $student->display_name;
			}

			$replacable['{dashboard_url}'] = tutor_utils()->get_tutor_dashboard_page_permalink();
			$replacable['{logo}']          = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
			$replacable['{email_heading}'] = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );

			$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );

			$replacable['{footer_text}'] = $this->get_replaced_text( $option_data['footer_text'] ?? '', array_keys( $replacable ), array_values( $replacable ) );

			$subject = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

			ob_start();
			$this->tutor_load_email_template( 'order_new_' . $recipient_type );
			$email_tpl = apply_filters( 'tutor_email_tpl_' . $recipient_type, ob_get_clean() );
			$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

			$enable_queue = tutor()->has_pro && tutor_utils()->get_option( 'tutor_email_disable_wpcron' );

			$this->send( $user_data->user_email, $subject, $message, $header, array(), $enable_queue );
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

		$email_config[ self::TO_STUDENTS ]['new_order']            = $order_email[ self::TO_STUDENTS ]['new_order'];
		$email_config[ self::TO_STUDENTS ]['order_status_updated'] = $order_email['email_to_students']['order_status_updated'];

		$email_config[ self::TO_TEACHERS ]['new_order']            = $order_email['email_to_teachers']['new_order'];
		$email_config[ self::TO_TEACHERS ]['order_status_updated'] = $order_email['email_to_teachers']['order_status_updated'];

		$email_config[ self::TO_ADMIN ]['new_order']            = $order_email['email_to_admin']['new_order'];
		$email_config[ self::TO_ADMIN ]['order_status_updated'] = $order_email['email_to_admin']['order_status_updated'];

		return $email_config;
	}

	/**
	 * Get email data.
	 *
	 * @return array
	 */
	public function get_email_data() {
		$email_array = array(
			self::TO_STUDENTS => array(
				'new_order'            => array(
					'label'       => __( 'New order placed', 'tutor' ),
					'default'     => 'on',
					'template'    => 'order_new_' . self::TO_STUDENTS,
					'tooltip'     => __( 'New order emails are sent to chosen recipient(s) when a new order is received.', 'tutor' ),
					'subject'     => __( 'Your order has been received! 🎉', 'tutor' ),
					'heading'     => __( 'Your order has been received!', 'tutor' ),
					'message'     => wp_json_encode(
						sprintf(
							'
							<p>%s {user_name},</p>
							<p>%s</p>
							<div>
								<p>%s</p>
								<ul>
									<li>%s {order_id}</li>
									<li>%s {order_date}</li>
									<li>%s {order_total}</li>
								</ul>
							</div>',
							esc_html__( 'Hi', 'tutor' ),
							esc_html__( 'Thank you for your order. We\'ve received your order successfully, and it is now being processed.', 'tutor' ),
							esc_html__( 'Below are the details of your order:', 'tutor' ),
							esc_html__( 'Order ID:', 'tutor' ),
							esc_html__( 'Order Date:', 'tutor' ),
							esc_html__( 'Total Amount:', 'tutor' )
						)
					),
					'footer_text' => __( 'We will let you know once your order has been completed and is ready for access.', 'tutor' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
				'order_status_updated' => array(
					'label'       => __( 'Order status updated', 'tutor' ),
					'default'     => 'on',
					'template'    => 'order_updated_' . self::TO_STUDENTS,
					'tooltip'     => 'Order status update emails are sent to chosen recipient(s) whenever a order status updated.',
					'subject'     => __( 'Your Order Status Has Been Updated to {order_status} ', 'tutor' ),
					'heading'     => __( 'Your Order Status Has Been Updated to {order_status}', 'tutor' ),
					'message'     => wp_json_encode(
						sprintf(
							'
							<p>%s</p>
							<p>%s</p>
							<ul>
								<li>%s {order_id}</li>
								<li>%s {order_status}</li>
								<li>%s {course_name}</li>
								<li>%s {order_date}</li>
								<li>%s {order_total}</li>
							</ul>',
							esc_html__( 'Hi {user_name},', 'tutor' ),
							esc_html__( 'We\'re reaching out to let you know that your order status has been updated to {order_status}. We understand the importance of keeping you informed at every step of the way. Below is a summary of your order:', 'tutor' ),
							esc_html__( 'Order ID:', 'tutor' ),
							esc_html__( 'Order Status:', 'tutor' ),
							esc_html__( 'Course:', 'tutor' ),
							esc_html__( 'Order Date:', 'tutor' ),
							esc_html__( 'Total Amount:', 'tutor' )
						)
					),
					'footer_text' => __( 'We will let you know once your order has been completed and is ready for access.', 'tutor' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
			),
			self::TO_TEACHERS => array(
				'new_order'            => array(
					'label'       => __( 'New order placed', 'tutor' ),
					'default'     => 'on',
					'template'    => 'order_new_' . self::TO_TEACHERS,
					'tooltip'     => 'New order emails are sent to chosen recipient(s) when a new order is received.',
					'subject'     => __( 'A New Student Has Enrolled in Your Course! 🎉', 'tutor' ),
					'heading'     => __( 'A New Student Has Enrolled in Your Course!', 'tutor' ),
					'message'     => wp_json_encode(
						sprintf(
							'
							<p>%s {user_name},</p>
							<p>%s</p>
							<ul>
								<li>%s {student_name}</li>
								<li>%s {order_id}</li>
								<li>%s {order_date}</li>
								<li>%s {order_payment_status}</li>
							</ul>',
							esc_html__( 'Hi', 'tutor' ),
							esc_html__( 'We\'re excited to let you know that a new student has just enrolled in one of your courses! Here are the course details:', 'tutor' ),
							esc_html__( 'Student Name:', 'tutor' ),
							esc_html__( 'Order ID:', 'tutor' ),
							esc_html__( 'Order Date:', 'tutor' ),
							esc_html__( 'Payment Status:', 'tutor' )
						)
					),
					'footer_text' => __( 'Please review the order and ensure everything is in place for the student\'s access to the course. Thank you.', 'tutor' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
				'order_status_updated' => array(
					'label'       => __( 'Order status updated', 'tutor' ),
					'default'     => 'on',
					'template'    => 'order_updated_' . self::TO_TEACHERS,
					'tooltip'     => 'Order status update emails are sent to chosen recipient(s) whenever a order status updated.',
					'subject'     => __( 'Instructor Notice: Your Student\'s Order Status is Now {order_status}', 'tutor' ),
					'heading'     => __( 'Instructor Notice: Your Student\'s Order Status is Now {order_status}', 'tutor' ),
					'message'     => wp_json_encode(
						sprintf(
							'
							<p>%s</p>
							<p>%s</p>
							<ul>
								<li>%s {order_id}</li>
								<li>%s {order_status}</li>
								<li>%s {course_name}</li>
								<li>%s {order_total}</li>
							</ul>',
							esc_html__( 'Hi {user_name},', 'tutor' ),
							esc_html__( 'We\'d like to update you on your course enrollment status. One of your students has an order that has been updated to {order_status}. Here are the details:', 'tutor' ),
							esc_html__( 'Order ID:', 'tutor' ),
							esc_html__( 'Order Status:', 'tutor' ),
							esc_html__( 'Course Name:', 'tutor' ),
							esc_html__( 'Total Amount:', 'tutor' )
						)
					),
					'footer_text' => __( 'Please review the order and ensure everything is in place for the student\'s access to the course. Thank you.', 'tutor' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
			),
			self::TO_ADMIN    => array(
				'new_order'            => array(
					'label'       => __( 'New order placed', 'tutor' ),
					'default'     => 'on',
					'template'    => 'order_new_' . self::TO_ADMIN,
					'tooltip'     => __( 'New order emails are sent to chosen recipient(s) when a new order is received.', 'tutor' ),
					'subject'     => __( 'A New Order Has Been Placed on Your Platform!', 'tutor' ),
					'heading'     => __( 'A New Order Has Been Placed on Your Platform!', 'tutor' ),
					'message'     => wp_json_encode(
						sprintf(
							'
							<p>%s</p>
							<ul>
								<li>%s {order_id}</li>
								<li>%s {order_date}</li>
								<li>%s {order_total}</li>
							</ul>',
							esc_html__( 'Below are the order details:', 'tutor' ),
							esc_html__( 'Order ID:', 'tutor' ),
							esc_html__( 'Order Date:', 'tutor' ),
							esc_html__( 'Total Amount:', 'tutor' )
						)
					),
					'footer_text' => __( 'Please review the order and ensure everything is in place for the student\'s access to the course. Thank you.', 'tutor' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
				'order_status_updated' => array(
					'label'       => __( 'Order status updated', 'tutor' ),
					'default'     => 'on',
					'template'    => 'order_updated_' . self::TO_ADMIN,
					'tooltip'     => 'Order status update emails are sent to chosen recipient(s) whenever a order status updated.',
					'subject'     => __( 'An Order\'s Status Has Been Updated to {order_status}', 'tutor' ),
					'heading'     => __( 'An Order\'s Status Has Been Updated to {order_status}', 'tutor' ),
					'message'     => wp_json_encode(
						sprintf(
							'
							<p>%s</p>
							<p>%s</p>
							<ul>
								<li>%s: {order_id}</li>
								<li>%s: {order_date}</li>
								<li>%s: {order_status}</li>
								<li>%s: {course_name}</li>
								<li>%s: {order_total}</li>
							</ul>',
							esc_html__( 'Hi {user_name},', 'tutor' ),
							esc_html__( 'We\'re reaching out to let you know that the order status of {student_name} has been updated to {order_status}. Here is the summary of the order:', 'tutor' ),
							esc_html__( 'Order ID', 'tutor' ),
							esc_html__( 'Order Date', 'tutor' ),
							esc_html__( 'Order Status', 'tutor' ),
							esc_html__( 'Course Name', 'tutor' ),
							esc_html__( 'Total Amount', 'tutor' )
						)
					),
					'footer_text' => __( 'Please review the order and ensure everything is in place for the student\'s access to the course. Thank you.', 'tutor' ),
					// 'placeholders' => EmailPlaceholder::only( array( 'site_url', 'site_name', 'instructor_name', 'review_url', 'instructor_email', 'signup_time' ) ),
				),
			),
		);

		return $email_array;
	}
}
