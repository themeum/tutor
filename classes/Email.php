<?php
/**
 * Created by PhpStorm.
 * User: themeum
 * Date: 30/9/19
 * Time: 3:20 PM
 */

namespace TUTOR;

class Email {

	public function __construct() {
		// add_filter( 'tutor/options/attrs_added', array( $this, 'add_options' ), 10 ); // Priority index is important. Content Drip uses 11.

		if ( ! function_exists( 'tutor_pro' ) ) {
			add_action( 'tutor_options_before_email_notification', array( $this, 'no_pro_message' ) );
		}
	}

	public function add_options( $attr ) {
		$attr['basic'] = array(
			'label'    => __( 'Email', 'tutor' ),
			'sections' => array(
				'email' => array(
					'label'    => __( 'Email', 'tutor' ),
					'slug'     => 'email',
					'desc'     => __( 'Email Settings', 'tutor' ),
					'template' => 'basic',
					'icon'     => __( 'envelope', 'tutor' ),
					'blocks'   => array(
						array(
							'label'      => __( 'Course', 'tutor' ),
							'slug'       => 'course',
							'block_type' => 'uniform',
							'fields'     => array(
								array(
									'key'     => 'email_from_name',
									'type'    => 'text',
									'label'   => __( 'Name', 'tutor' ),
									'default' => get_option( 'blogname' ),
									'desc'    => __( 'The name under which all the emails will be sent', 'tutor' ),
								),
								array(
									'key'     => 'email_from_address',
									'type'    => 'text',
									'label'   => __( 'E-Mail Address', 'tutor' ),
									'default' => get_option( 'admin_email' ),

									'desc'    => __( 'The E-Mail address from which all emails will be sent', 'tutor' ),
								),
								array(
									'key'     => 'email_footer_text',
									'type'    => 'textarea',
									'label'   => __( 'E-Mail Footer Text', 'tutor' ),
									'default' => '',

									'desc'    => __( 'The text to appear in E-Mail template footer', 'tutor' ),
								),
								array(
									'key'          => 'mailer_native_server_cron',
									'type'         => 'group_textarea_code',
									'label'        => __( 'Mailer Native Server Cron', 'tutor' ),
									'label_title'  => __( '', 'tutor' ),
									'group_fields' => array(
										array(
											'key'         => 'mailer_native_server_cron',
											'type'        => 'toggle_switch',
											'label'       => __( 'Mailer Native Server Cron', 'tutor' ),
											'label_title' => __( '', 'tutor' ),
											'default'     => 'off',
											'desc'        => __( 'If you use OS native cron, then disable it.', 'tutor' ),
										),
										array(
											'key'         => 'mailer_native_server',
											'type'        => 'textarea_code',
											'label'       => __( 'Mailer Native Server Cron', 'tutor' ),
											'label_title' => __( '', 'tutor' ),
											'default'     => 'off',
											'desc'        => __( 'If you use OS native cron, then disable it.', 'tutor' ),
										),
									),
									'desc'         => __( 'If you use OS native cron, then disable it.', 'tutor' ),
								),
							),
						),
					),
				),

			),
		);
		$attr1['email_notification'] = array(
			'label'    => __( 'E-Mail Notification', 'tutor' ),
			'sections' => array(
				array(
					'slug'   => __( 'email_settings', 'tutor' ),
					'label'  => __( 'E-Mail Settings', 'tutor' ),
					'desc'   => __( 'Check and place necessary information here.', 'tutor' ),
					'fields' => array(
						'email_from_name'    => array(
							'type'    => 'text',
							'label'   => __( 'Name', 'tutor' ),
							'default' => get_option( 'blogname' ),
							'desc'    => __( 'The name under which all the emails will be sent', 'tutor' ),
						),
						'email_from_address' => array(
							'type'    => 'text',
							'label'   => __( 'E-Mail Address', 'tutor' ),
							'default' => get_option( 'admin_email' ),
							'desc'    => __( 'The E-Mail address from which all emails will be sent', 'tutor' ),
						),
						'email_footer_text'  => array(
							'type'    => 'textarea',
							'label'   => __( 'E-Mail Footer Text', 'tutor' ),
							'default' => '',
							'desc'    => __( 'The text to appear in E-Mail template footer', 'tutor' ),
						),
					),
				),

			),
		);

		return $attr;
	}


	public function no_pro_message() {
		tutor_alert( sprintf( __( ' %1$s Get Tutor LMS Pro %2$s to extend email functionality and send email notifications for certain events. You can easily choose the events for which you wish to send emails.', 'tutor' ), "<strong> <a href='https://www.themeum.com/product/tutor-lms/?utm_source=tutor_lms_email_settings' target='_blank'>", '</a></strong>' ) );

	}

}
