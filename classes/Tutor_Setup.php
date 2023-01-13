<?php
/**
 * Tutor setup class
 *
 * @package Tutor\Setup
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage setup functionalities
 *
 * @since 1.0.0
 */
class Tutor_Setup {

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus' ) );
		add_action( 'admin_init', array( $this, 'setup_wizard' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_setup_action', array( $this, 'tutor_setup_action' ) );
		add_filter( 'tutor_wizard_attributes', array( $this, 'tutor_setup_attributes_callback' ) );
	}

	/**
	 * Tutor setup attr callback
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $attr attr.
	 *
	 * @return array
	 */
	public function tutor_setup_attributes_callback( $attr ) {
		$options   = (array) maybe_unserialize( get_option( 'tutor_option' ) );
		$final_arr = array();
		$data_arr  = $this->tutor_setup_attributes();
		foreach ( $data_arr as $key => $section ) {
			foreach ( $section['attr'] as $k => $val ) {
				$final_arr[ $k ] = isset( $options[ $k ] ) ? $options[ $k ] : '';
			}
		}
		return $final_arr;
	}

	/**
	 * Setup action
	 *
	 * @since 1.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_setup_action() {
		tutor_utils()->checking_nonce();

		$options = (array) maybe_unserialize( get_option( 'tutor_option' ) );
		$action  = Input::post( 'action', '' );
		if ( 'setup_action' !== $action || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// General Settings.
		$change_data = apply_filters( 'tutor_wizard_attributes', array() );
		foreach ( $change_data as $key => $value ) {
			$post_key = Input::post( $key, '' );
			if ( Input::has( $key ) ) {
				if ( $post_key != $change_data[ $key ] ) {
					if ( '' === $post_key ) {
						unset( $options[ $key ] );
					} else {
						$options[ $key ] = $post_key;
					}
				}
				$options_preset[ $key ] = $post_key;
			} else {
				unset( $options[ $key ] );
			}
		}

		update_option( 'tutor_default_option', $options_preset );
		update_option( 'tutor_option', $options );

		// Payment Settings.
		$payments      = (array) maybe_unserialize( get_option( 'tutor_withdraw_options' ) );
		$payments_data = array( 'bank_transfer_withdraw', 'echeck_withdraw', 'paypal_withdraw' );
		foreach ( $payments_data as $key ) {
			$post_key = Input::post( $key, '' );
			if ( Input::has( $key ) ) {
				$payments[ $key ]['enabled'] = 1;
			} else {
				if ( 'bank_transfer_withdraw' === $key ) {
					unset( $payments[ $key ]['enabled'] );
				} else {
					unset( $payments[ $key ] );
				}
			}
		}
		update_option( 'tutor_withdraw_options', $payments );

		// Add wizard flag.
		wp_send_json_success( array( 'status' => 'success' ) );
	}

	/**
	 * Add dashboard page without title
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'tutor-setup', '' );
	}

	/**
	 * Setup wizard
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function setup_wizard() {
		$setup_page = Input::get( 'page', '' );
		if ( 'tutor-setup' === $setup_page ) {
			$this->tutor_setup_wizard_header();
			$this->tutor_setup_wizard_boarding();
			$this->tutor_setup_wizard_type();
			$this->tutor_setup_wizard_settings();
			$this->tutor_setup_wizard_footer();
			exit;
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function tutor_setup_generator() {
		$i         = 1;
		$html      = '';
		$options   = (array) maybe_unserialize( get_option( 'tutor_option' ) );
		$payments  = (array) maybe_unserialize( get_option( 'tutor_withdraw_options' ) );
		$field_arr = $this->tutor_setup_attributes();

		$down_desc_fields  = array( 'rows', 'slider', 'text', 'radio', 'dropdown', 'range', 'payments' );
		$full_width_fields = array( 'rows', 'slider', 'radio', 'range', 'payments', 'dropdown' );

		foreach ( $field_arr as $key_parent => $field_parent ) {
			$html             .= '<li class="' . ( $i == 1 ? 'active' : '' ) . '">';
				$html         .= '<div class="tutor-setup-content-heading heading">';
					$html     .= '<div class="setup-section-title tutor-fs-6 tutor-fw-medium tutor-color-black">' . $field_parent['lable'] . '</div>';
					$html     .= '<div class="step-info">';
						$html .= '<span class="tutor-fs-7 tutor-color-muted">' . __( 'Step', 'tutor' ) . ':</span> <strong class="tutor-color-black">' . $i . '/' . count( $field_arr ) . ' </strong>';
					$html     .= '</div>';
					$html     .= '<div class="tutor-reset-section tutor-text-btn-small tutor-color-secondary tutor-d-flex tutor-align-center">' . __( 'Reset Default', 'tutor' ) . '</div>';
				$html         .= '</div>';
				$html         .= '<div class="tutor-setup-content-heading body">';

			foreach ( $field_parent['attr'] as $key => $field ) {
				if ( ! isset( $field['lable'] ) ) {
					continue; }

				// Generate data attributes if necessary
				$data_attr = '';
				if ( isset( $field['data'] ) && is_array( $field['data'] ) ) {
					foreach ( $field['data'] as $data_key => $data_value ) {
						$data_attr .= ' data-' . $data_key . '="' . $data_value . '" ';
					}
				}

						$html .= '<div class="tutor-setting' . ( in_array( $field['type'], $full_width_fields ) ? ' course-setting-wrapper' : '' ) . ' ' . ( isset( $field['class'] ) ? $field['class'] : '' ) . '">';
						$html .= isset( $field['lable'] ) ? '<div class="tutor-fs-6  tutor-color-black ______">' . $field['lable'] : '';

						$html .= isset( $field['tooltip'] ) ? '<span class="tooltip-wrap tooltip-icon"><span class="tooltip-txt tooltip-right">' . $field['tooltip'] . '</span></span>' : '';
						$html .= isset( $field['lable'] ) ? '</div>' : '';

				if ( ! in_array( $field['type'], $down_desc_fields ) ) {
					$html .= isset( $field['desc'] ) ? '<div class="content tutor-fs-7 tutor-color-secondary">' . $field['desc'] . '</div>' : '';
				}

						$html .= '<div class="settings">';

				switch ( $field['type'] ) {

					case 'switch':
						$html         .= '<label for="' . $key . '" class="switch-label input-switch-label">';
						$html         .= '<span class="label-off">' . __( 'OFF', 'tutor' ) . '</span>';
						$html         .= '<div class="switchbox-wrapper">';
								$html .= '<input ' . $data_attr . ' id="' . $key . '" class="input-switchbox" type="checkbox" name="' . $key . '" value="on" ' . ( isset( $options[ $key ] ) && $options[ $key ] ? 'checked' : '' ) . '/>';
								$html .= '<span class="switchbox-icon"></span>';
						$html         .= '</div>';
						$html         .= '<span class="label-on">' . __( 'ON', 'tutor' ) . '</span>';
						$html         .= '</label>';
						break;

					case 'text':
						$html .= '<input type="text" name="' . $key . '" class="lesson-permalink" value="' . ( isset( $options[ $key ] ) ? $options[ $key ] : '' ) . '" />';
						break;

					case 'rows':
						$html                 .= '<div class="content">';
							$html             .= '<div class="course-per-row">';
								$html         .= '<div class="wrapper">';
									$html     .= '<label for="' . $key . '1">';
										$html .= '<input type="radio" value="1" name="' . $key . '" class="course" id="' . $key . '1" ' . ( isset( $options[ $key ] ) && $options[ $key ] == 1 ? 'checked' : '' ) . '>';
										$html .= '<span class="span"><span>1</span></span>';
									$html     .= '</label>';
								$html         .= '</div>';
								$html         .= '<div class="wrapper">';
									$html     .= '<label for="' . $key . '2">';
										$html .= '<input type="radio" value="2" name="' . $key . '" class="course" id="' . $key . '2" ' . ( isset( $options[ $key ] ) && $options[ $key ] == 2 ? 'checked' : '' ) . '>';
										$html .= '<span class="span"><span>2</span><span>2</span></span>';
									$html     .= '</label>';
								$html         .= '</div>';
								$html         .= '<div class="wrapper">';
									$html     .= '<label for="' . $key . '3">';
										$html .= '<input type="radio" value="3" name="' . $key . '" class="course" id="' . $key . '3" ' . ( isset( $options[ $key ] ) && $options[ $key ] == 3 ? 'checked' : '' ) . '>';
										$html .= '<span class="span"><span>3</span><span>3</span><span>3</span></span>';
									$html     .= '</label>';
								$html         .= '</div>';
								$html         .= '<div class="wrapper">';
									$html     .= '<label for="' . $key . '4">';
										$html .= '<input type="radio" value="4" name="' . $key . '" class="course" id="' . $key . '4" ' . ( isset( $options[ $key ] ) && $options[ $key ] == 4 ? 'checked' : '' ) . '>';
										$html .= '<span class="span"><span>4</span><span>4</span><span>4</span><span>4</span></span>';
									$html     .= '</label>';
								$html         .= '</div>';
							$html             .= '</div>';
						$html                 .= '</div>';
						break;

					case 'radio':
						if ( isset( $field['options'] ) ) {
							foreach ( $field['options'] as $k => $val ) {
								$html .= '<label for="' . $key . $k . '" class="time-expires"><input type="radio" id="' . $key . $k . '" name="' . $key . '" value="' . $k . '" ' . ( isset( $options[ $key ] ) && $options[ $key ] == $k ? 'checked' : '' ) . ' /> ' . '<span class="radio-icon"></span>';
								$html .= $val . '</label>';
							}
						}
						break;

					case 'slider':
						$available_times = array(
							'seconds' => __( 'seconds', 'tutor' ),
							'minutes' => __( 'minutes', 'tutor' ),
							'hours'   => __( 'hours', 'tutor' ),
							'days'    => __( 'days', 'tutor' ),
							'weeks'   => __( 'weeks', 'tutor' ),
						);
						$html           .= '<div class="limit-slider">';
						if ( isset( $field['time'] ) ) {
								$html .= '<input type="range" name="' . $key . '[value]" min="' . ( isset( $field['min'] ) ? $field['min'] : 0 ) . '" max="' . ( isset( $field['max'] ) ? $field['max'] : 60 ) . '" step="1" value="' . ( isset( $options[ $key ]['value'] ) ? $options[ $key ]['value'] : '' ) . '"  class="range-input"/>';
								$html .= '<input type="hidden" name="' . $key . '[time]" value="' . ( isset( $options[ $key ]['time'] ) ? $options[ $key ]['time'] : __( 'minutes', 'tutor' ) ) . '"  class="range-input"/>';
								$html .= '<span class=""><span class="range-value">' . ( isset( $options[ $key ]['value'] ) ? $options[ $key ]['value'] : '' ) . '</span>';
								$html .= isset( $options[ $key ]['time'] ) ? $available_times[ $options[ $key ]['time'] ] : '';
								$html .= '</span>';
						} else {
									$html .= '<input type="range" name="' . $key . '" min="' . ( isset( $field['min'] ) ? $field['min'] : '' ) . '" max="' . ( isset( $field['max'] ) ? $field['max'] : 30 ) . '" step="1" value="' . ( isset( $options[ $key ] ) ? $options[ $key ] : '' ) . '"  class="range-input"/>';
									$html .= ' <strong class="range-value">' . ( isset( $options[ $key ] ) ? $options[ $key ] : '' ) . '</strong>';
						}
										$html .= '</div>';
						break;

					case 'dropdown':
						$html             .= '<div class="grade-calculation"><div class="select-box"><div class="options-container">';
							$selected_data = '';
						if ( isset( $field['options'] ) ) {
							foreach ( $field['options'] as $val ) {
								$html .= '<div class="option">';
								$html .= '<input type="radio" class="radio" id="' . $val['value'] . '" name="' . $key . '" value="' . $val['value'] . '" ' . ( isset( $options[ $key ] ) && $options[ $key ] == $val['value'] ? 'checked' : '' ) . ' />';
								$html .= '<label for="' . $val['value'] . '">';
								$html .= '<h3>' . $val['title'] . '</h3>';
								$html .= '<h5>' . $val['desc'] . '</h5>';
								$html .= '</label>';
								$html .= '</div>';

								if ( isset( $options[ $key ] ) && $options[ $key ] == $val['value'] ) {
											$selected_data .= '<div class="selected">';
											$selected_data .= '<h3>' . $val['title'] . '</h3>';
											$selected_data .= '<h5>' . $val['desc'] . '</h5>';
											$selected_data .= '</div>';
								}
							}
						}
							$html .= '</div>';
							$html .= $selected_data ? $selected_data : '<div class="selected"><h3>' . $field['options'][0]['title'] . '</h3><h5>' . $field['options'][0]['desc'] . '</h5></div>';
							$html .= '</div></div>';
						break;

					case 'payments':
						$html                          .= '<div class="checkbox-wrapper column-3">';
							$available_withdraw_methods = get_tutor_all_withdrawal_methods();
						if ( ! empty( $available_withdraw_methods ) ) {
							foreach ( $available_withdraw_methods as $key => $value ) {
								$html .= '<div class="payment-setting">';
								$html .= '<label for="' . $key . '" class="label">';
								$html .= '<div>';
								$html .= '<input type="checkbox" name="' . $key . '" id="' . $key . '" class="checkbox payment" ' . ( isset( $payments[ $key ]['enabled'] ) && $payments[ $key ]['enabled'] ? 'checked' : '' ) . ' />';
								$html .= '<span class="check-icon round"></span>';
								$html .= '</div>';
								$html .= '<div>';
								$html .= '<img src="' . $value['image'] . '" alt="' . $value['method_name'] . '">';
								$html .= '<h4>' . $value['method_name'] . '</h4>';
								$html .= '</div>';
								$html .= '</label>';
								$html .= '</div>';
							}
						}
							$html .= '</div>';
						break;

					case 'range':
						$earning_instructor = isset( $options['earning_instructor_commission'] ) ? $options['earning_instructor_commission'] : 80;
						$earning_admin      = isset( $options['earning_admin_commission'] ) ? $options['earning_admin_commission'] : 20;
						$html              .= '<div class="limit-slider column-1">';
							$html          .= '<div class="limit-slider-has-parent">';
								$html      .= '<input type="range" min="0" max="100" step="1" value="' . $earning_instructor . '" class="range-input double-range-slider" name=""/>';
							$html          .= '</div>';
							$html          .= '<div class="commision-data">';
								$html      .= '<div class="data">';
									$html  .= '<h4 class="range-value-1">' . $earning_instructor . '%</h4>';
									$html  .= '<h5>' . __( 'Instructor', 'tutor' ) . '</h5>';
									$html  .= '<input type="hidden" min="0" max="100" step="1" value="' . $earning_instructor . '" class="range-value-data-1 range-input" name="earning_instructor_commission"/>';
								$html      .= '</div>';
								$html      .= '<div class="data">';
									$html  .= '<h4 class="range-value-2">' . $earning_admin . '%</h4>';
									$html  .= '<h5>' . __( 'Admin / Owner', 'tutor' ) . '</h5>';
									$html  .= '<input type="hidden" min="0" max="100" step="1" value="' . $earning_admin . '" class="range-value-data-2 range-input" name="earning_admin_commission"/>';
								$html      .= '</div>';
							$html          .= '</div>';
						$html              .= '</div> ';
						break;

					case 'checkbox':
						$html .= '<div class="checkbox-wrapper column-2">';
						if ( isset( $field['options'] ) ) {
							foreach ( $field['options'] as $k => $val ) {
								$html             .= '<div class="email-notification">';
									$html         .= '<label for="' . $key . $k . '" class="label">';
										$html     .= '<div>';
											$html .= '<input type="checkbox" value="' . $k . '" ' . ( isset( $options[ $key ] ) && $options[ $key ] == $k ? 'checked' : '' ) . ' name="' . $key . '" id="' . $key . $k . '" class="checkbox" />';
											$html .= '<span class="check-icon square"></span>';
										$html     .= '</div>';
										$html     .= '<div>';
											$html .= '<h4>' . $val . '</h4>';
										$html     .= '</div>';
									$html         .= '</label>';
								$html             .= '</div>';
							}
						}
						$html .= '</div>';
						break;

					case 'attempt':
						$html .= '<div class="tutor-setting course-setting-wrapper">';

							$html .= '<input type="hidden" name="quiz_attempts_allowed" value="' . ( isset( $options[ $key ] ) ? $options[ $key ] : 'off' ) . '">';

							$html                     .= '<div class="content">';
								$html                 .= '<div class="course-per-page attempts-allowed">';
									$html             .= '<div class="wrapper">';
										$html         .= '<label for="attempts-allowed-1">';
											$html     .= '<input type="radio" value="single" name="attempts-allowed" class="course-p" id="attempts-allowed-1" ' . ( isset( $options[ $key ] ) && $options[ $key ] ? 'checked' : '' ) . '>';
											$html     .= '<span class="radio-icon"></span>';
											$html     .= '<span class="label-text label-text-2">';
												$html .= '<input type="number" value="' . $options[ $key ] . '" name="attempts-allowed-number" class="attempts tutor-form-number-verify" id="attempts-allowed-1" min="' . ( isset( $field['min'] ) ? $field['min'] : 0 ) . '" max="' . ( isset( $field['max'] ) ? $field['max'] : 30 ) . '">';
											$html     .= '</span>';
										$html         .= '</label>';
									$html             .= '</div>';
									$html             .= '<div class="wrapper tutor-unlimited-value">';
										$html         .= '<label for="attempts-allowed-2">';
											$html     .= '<input type="radio" name="attempts-allowed" value="unlimited" class="course-p" id="attempts-allowed-2" ' . ( ( ! isset( $options[ $key ] ) ) || $options[ $key ] == 0 ? 'checked' : '' ) . '>';
											$html     .= '<span class="radio-icon"></span>';
											$html     .= '<span class="label-text">' . __( 'Unlimited', 'tutor' ) . '</span>';
										$html         .= '</label>';
									$html             .= '</div>';
								$html                 .= '</div>';
							$html                     .= '</div>';
						$html                         .= '</div>';
						break;

					default:
						// code...
						break;
				}

				if ( in_array( $field['type'], $down_desc_fields ) ) {
					$html .= isset( $field['desc'] ) ? '<div class="content">' . $field['desc'] . '</div>' : '';
				}
								$html .= '</div>';
								$html .= '</div>';

			}
				$html .= '</div>';
			if ( 'payments' !== $field['type'] ) {
				$html .= $this->tutor_setup_wizard_action();
			} else {
				$html .= $this->tutor_setup_wizard_action_final();
			}
				$html .= '</li>';
				$i++;
		}

		echo tutor_kses_html( $html );
	}

	/**
	 * Setup attrs
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function tutor_setup_attributes() {
		$general_fields = array(

			'general'    => array(
				'lable' => __( 'General Settings', 'tutor' ),
				'attr'  => array(
					'enable_course_marketplace'     => array(
						'type' => 'marketplace',
					),
					'public_profile_layout'         => array(
						'type'  => 'switch',
						'data'  => array(
							'off' => 'private',
							'on'  => 'pp-rectangle',
						),
						'lable' => __( 'Instructor Profile', 'tutor' ),
						'desc'  => __( 'Allow users to have a instructor profile to showcase awards and completed courses.', 'tutor' ),
					),
					'student_public_profile_layout' => array(
						'type'  => 'switch',
						'data'  => array(
							'off' => 'private',
							'on'  => 'pp-rectangle',
						),
						'lable' => __( 'Student Profile', 'tutor' ),
						'desc'  => __( 'Allow users to have a student profile to showcase awards and completed courses.', 'tutor' ),
					),
					'lesson_permalink_base'         => array(
						'type'  => 'text',
						'max'   => 50,
						'lable' => __( 'Lesson permalink', 'tutor' ),
						'desc'  => sprintf( __( 'Example:  %s', 'tutor' ), get_home_url() . '/' . tutor()->course_post_type . '/sample-course/<strong>' . ( tutor_utils()->get_option( 'lesson_permalink_base', 'lessons' ) ) . '</strong>/sample-lesson/' ),
					),
				),
			),

			'course'     => array(
				'lable' => __( 'Course Settings', 'tutor' ),
				'attr'  => array(
					'display_course_instructors' => array(
						'type'  => 'switch',
						'lable' => __( 'Show Instructor Bio', 'tutor' ),
						'desc'  => __( 'Let the students know the instructor(s). Display their credentials, professional experience, and more.', 'tutor' ),
					),
					'enable_q_and_a_on_course'   => array(
						'type'  => 'switch',
						'lable' => __( 'Question and Anwser', 'tutor' ),
						'desc'  => __( 'Allows a Q&A forum on each course.', 'tutor' ),
					),
					'courses_col_per_row'        => array(
						'type'    => 'rows',
						'lable'   => __( 'Courses Per Row', 'tutor' ),
						'tooltip' => __( 'How many courses per row on the archive pages.', 'tutor' ),
					),
					'courses_per_page'           => array(
						'type'    => 'slider',
						'lable'   => __( 'Courses Per Page', 'tutor' ),
						'tooltip' => __( 'How many courses per page on the archive pages.', 'tutor' ),
					),
				),
			),

			'instructor' => array(
				'lable' => __( 'Instructor Settings', 'tutor' ),
				'attr'  => array(
					'enable_become_instructor_btn'  => array(
						'type'  => 'switch',
						'lable' => __( 'New Signup', 'tutor' ),
						'desc'  => __( 'Choose between open and closed instructor signup. If you’re creating a course marketplace, instructor signup should be open.', 'tutor' ),
					),
					'instructor_can_publish_course' => array(
						'type'  => 'switch',
						'lable' => __( 'Earning', 'tutor' ),
						'desc'  => __( 'Enable earning for instructors?', 'tutor' ),
					),
				),
			),

			'payment'    => array(
				'lable' => __( 'Payment Settings ', 'tutor' ),
				'attr'  => array(
					'enable_guest_course_cart'      => array(
						'type'  => 'switch',
						'lable' => __( 'Guest Checkout', 'tutor' ),
						'desc'  => __( 'Allow users to buy and consume content without logging in.', 'tutor' ),
					),
					'commission_split'              => array(
						'type'    => 'range',
						'lable'   => __( 'Commission Rate', 'tutor' ),
						'tooltip' => __( 'Control revenue sharing between admin and instructor.', 'tutor' ),
					),
					'earning_instructor_commission' => array(
						'type' => 'commission',
					),
					'earning_admin_commission'      => array(
						'type' => 'commission',
					),
					'withdraw_split'                => array(
						'type'  => 'payments',
						'lable' => __( 'Payment Withdrawal Method', 'tutor' ),
						'desc'  => __( 'Choose your preferred withdrawal method from the options.', 'tutor' ),
					),

				),
			),

		);

		return $general_fields;
	}

	/**
	 * Wizard settings
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_setup_wizard_settings() {
		$options = (array) maybe_unserialize( get_option( 'tutor_option' ) );
		?>
			<div class="tutor-wizard-container">
				<div class="tutor-wrapper-boarding tutor-setup-wizard-settings">
					<div class="tutor-setup-wrapper">
						<ul class="tutor-setup-title">
							<li data-url="general" class="general active current">
								<?php esc_html_e( 'General', 'tutor' ); ?>
							</li>
							<li data-url="course" class="course">
								<?php esc_html_e( 'Course', 'tutor' ); ?>
							</li>
							<li data-url="instructor" class="instructor">
								<?php esc_html_e( 'Instructor', 'tutor' ); ?>
							</li>
							<li data-url="payment" class="payment">
								<?php esc_html_e( 'Payment', 'tutor' ); ?>
							</li>
							<li data-url="finish" style="display:none" class="finish">
								<?php esc_html_e( 'Finish', 'tutor' ); ?>
							</li>
						</ul>


						<form id="tutor-setup-form" method="post">
						<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
							<input type="hidden" name="action" value="setup_action">

						<?php $course_marketplace = tutor_utils()->get_option( 'enable_course_marketplace' ); ?>
							<input type="hidden" name="enable_course_marketplace" class="enable_course_marketplace_data" value="<?php echo ( $course_marketplace ? 'on' : 'off' ); ?>">

							<ul class="tutor-setup-content">
							<?php $this->tutor_setup_generator(); ?>
								<li>
									<div class="tutor-setup-content-heading greetings">
										<div class="header">
											<img src="<?php echo esc_url( tutor()->url ) . 'assets/images/greeting-img.png'; ?>" alt="greeting">
										</div>
										<div class="content">
											<h2>
												<?php esc_html_e( 'Congratulations, you’re all set!', 'tutor' ); ?>
											</h2>
											<p>
												<?php esc_html_x( 'Tutor LMS is up and running on your website! If you really want to become a Tutor LMS genius, read our ', 'tutor setup content', 'tutor' ); ?>
												<a target="_blank" href="https://docs.themeum.com/tutor-lms/">
													<?php esc_html_x( 'documentation', 'tutor setup content', 'tutor' ); ?>
												</a>
												<?php esc_html_x( 'that covers everything!', 'tutor setup content', 'tutor' ); ?>
											</p>
											<p>
												<?php esc_html_x( 'If you need further assistance, please don’t hesitate to contact us via our ', 'tutor-setup-assistance', 'tutor' ); ?>
												<a target="_blank" href="https://www.themeum.com/contact-us/">
													<?php esc_html_x( 'contact form.', 'tutor-setup-assistance', 'tutor' ); ?>
												</a>
											</p>
										</div>
										<div class="tutor-setup-content-footer footer">
										<?php
											$welcome_url = admin_url( 'admin.php?page=tutor&welcome=1' );
											$addons_url  = admin_url( 'admin.php?page=tutor-addons' );
											$course_url  = admin_url( 'admin.php?page=tutor' );
										?>
											<a class="tutor-btn tutor-btn-primary" href="<?php echo esc_url( ! self::is_welcome_page_visited() ? $welcome_url : $course_url ); ?>">
												<?php esc_html_e( 'Create a New Course', 'tutor' ); ?>
											</a>
											<a class="tutor-btn tutor-btn-outline-primary" href="<?php echo esc_url( ! self::is_welcome_page_visited() ? $welcome_url : $addons_url ); ?>">
												<?php esc_html_e( 'Explore Addons', 'tutor' ); ?>
											</a>
										</div>
									</div>
								</li>
							</ul>
						</form>
					</div>
				</div>
			</div>
			<?php
	}

	/**
	 * Setup wizard action
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function tutor_setup_wizard_action() {
		$html              = '<div class="tutor-setup-content-footer footer">';
			$html         .= '<div class="tutor-setup-btn-wrapper">';
				$html     .= '<button class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-setup-previous">';
					$html .= '<span>&#8592;</span>&nbsp;<span>' . __( 'Previous', 'tutor' ) . '</span>';
				$html     .= '</button>';
			$html         .= '</div>';
			$html         .= '<div class="tutor-setup-btn-wrapper">';
				$html     .= '<button class="tutor-setup-skip tutor-btn tutor-btn-ghost">' . __( 'Skip this step', 'tutor' ) . '</button>';
			$html         .= '</div>';
			$html         .= '<div class="tutor-setup-btn-wrapper">';
				$html     .= '<button class="tutor-btn tutor-btn-primary tutor-btn-md tutor-setup-next">';
					$html .= '<span>' . __( 'Next', 'tutor' ) . '</span>&nbsp;<span>&#8594;</span>';
				$html     .= '</button>';
			$html         .= '</div>';
		$html             .= '</div>';

		return $html;
	}

	/**
	 * Setup wizard action final
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function tutor_setup_wizard_action_final() {
		$welcome_url = admin_url( 'admin.php?page=tutor&welcome=1' );

		$html              = '<div class="tutor-setup-content-footer footer">';
			$html         .= '<div class="tutor-setup-btn-wrapper">';
				$html     .= '<button class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-setup-previous">';
					$html .= '<span>&#8592;</span>&nbsp;<span>' . __( 'Previous', 'tutor' ) . '</span>';
				$html     .= '</button>';
			$html         .= '</div>';
			$html         .= '<div class="tutor-setup-btn-wrapper">';
				$html     .= '<a href="' . esc_url( $welcome_url ) . '" class="tutor-btn tutor-btn-ghost">' . __( 'Skip this step', 'tutor' ) . '</a>';
			$html         .= '</div>';
			$html         .= '<div class="tutor-setup-btn-wrapper">';
				$html     .= '<a href="' . esc_url( $welcome_url ) . '" class="tutor-btn tutor-btn-primary tutor-btn-md">' . __( 'Finish Setup', 'tutor' ) . '</a>';
			$html         .= '</div>';
		$html             .= '</div>';

		return $html;
	}

	/**
	 * Setup wizard boarding
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_setup_wizard_boarding() {
		global $current_user;
		?>
			<div class="tutor-wizard-container">
				<div class="tutor-wrapper-boarding tutor-setup-wizard-boarding active">
					<div class="wizard-boarding-header">
						<div>
							<img src="<?php echo esc_url( tutor()->url ) . 'assets/images/tutor-logo.svg'; ?>" />
						</div>
						<div>
							<div class="wizard-boarding-header-sub tutor-fs-5 tutor-color-black">
							<?php
								$greeting  = _x( 'Hello ', 'tutor-wizard-greeting', 'tutor' );
								$greeting .= tutor_utils()->display_name( $current_user->ID );
								$greeting .= _x( ' welcome to Tutor LMS!', 'tutor-wizard-greeting', 'tutor' );
								echo esc_html( $greeting );
							?>
							</div>
							<div class="wizard-boarding-header-main tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mt-10">
							<?php esc_html_e( 'Thank You for Choosing Us', 'tutor' ); ?>
							</div>
						</div>
					</div>
					<div class="wizard-boarding-body tutor-mt-60">
						<ul class="slider tutor-boarding">
							<li>
								<div class="slide-thumb">
									<img src="<?php echo esc_url( tutor()->url ) . 'assets/images/scalable_lms_solution.jpg'; ?>" alt="<?php esc_html_e( 'A Powerful, Smart, and Scalable LMS Solution', 'tutor' ); ?>"/>
								</div>
								<div class="slide-title tutor-fs-5 tutor-fw-medium  tutor-color-black"><?php esc_html_e( 'A Powerful, Smart, and Scalable LMS Solution', 'tutor' ); ?></div>
								<div class="slide-subtitle tutor-fs-6 tutor-color-secondary tutor-mt-16">
								<?php esc_html_e( 'From individual instructors to vast eLearning platforms, Tutor LMS grows with you to create your ideal vision of an LMS website.', 'tutor' ); ?>
								</div>
							</li>
							<li>
								<div class="slide-thumb">
									<img src="<?php echo esc_url( tutor()->url ) . 'assets/images/extensive_course_builder.jpg'; ?>" alt="<?php esc_html_e( 'Extensive Course Builder', 'tutor' ); ?>"/>
								</div>
								<div class="slide-title tutor-fs-5 tutor-fw-medium  tutor-color-black"><?php esc_html_e( 'Extensive Course Builder', 'tutor' ); ?></div>
								<div class="slide-subtitle tutor-fs-6 tutor-color-secondary tutor-mt-16">
								<?php esc_html_e( 'Tutor LMS comes with a state-of-the-art frontend course builder. Construct rich and resourceful courses with ease.', 'tutor' ); ?>
								</div>
							</li>
							<li>
								<div class="slide-thumb">
									<img src="<?php echo esc_url( tutor()->url ) . 'assets/images/advanced_quiz_creator.jpg'; ?>" alt="<?php esc_html_e( 'Advanced Quiz Creator', 'tutor' ); ?>"/>
								</div>
								<div class="slide-title tutor-fs-5 tutor-fw-medium  tutor-color-black"><?php esc_html_e( 'Advanced Quiz Creator', 'tutor' ); ?></div>
								<div class="slide-subtitle tutor-fs-6 tutor-color-secondary tutor-mt-16">
								<?php esc_html_e( 'Build interactive quizzes with the vast selection of question types and verify the learning of your students.', 'tutor' ); ?>
								</div>
							</li>
							<li>
								<div class="slide-thumb">
									<img src="<?php echo esc_url( tutor()->url ) . 'assets/images/freedom_with_ecommerce.jpg'; ?>" alt="<?php esc_html_e( 'Freedom With eCommerce', 'tutor' ); ?>"/>
								</div>
								<div class="slide-title tutor-fs-5 tutor-fw-medium  tutor-color-black"><?php esc_html_e( 'Freedom With eCommerce', 'tutor' ); ?></div>
								<div class="slide-subtitle tutor-fs-6 tutor-color-secondary tutor-mt-16">
								<?php esc_html_e( 'Select an eCommerce plugin and sell courses any way you like and use any payment gateway you want!', 'tutor' ); ?>
								</div>
							</li>
							<li>
								<div class="slide-thumb">
									<img src="<?php echo esc_url( tutor()->url ) . 'assets/images/reports_and_analytics.jpg'; ?>" alt="<?php esc_html_e( 'Reports and Analytics', 'tutor' ); ?>"/>
								</div>
								<div class="slide-title tutor-fs-5 tutor-fw-medium  tutor-color-black"><?php esc_html_e( 'Reports and Analytics', 'tutor' ); ?></div>
								<div class="slide-subtitle tutor-fs-6 tutor-color-secondary tutor-mt-16">
								<?php esc_html_e( 'Track what type of courses sell the most! Gain insights on user purchases, manage reviews and track quiz attempts.', 'tutor' ); ?>
								</div>
							</li>
						</ul>
					</div>
					<div class="wizard-boarding-footer">
						<div class="">
							<button class="tutor-btn tutor-btn-primary tutor-btn-md tutor-boarding-next">
							<?php esc_html_e( 'Let’s Start', 'tutor' ); ?>
							</button>
						</div>
						<div>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=tutor' ) ); ?>" class="tutor-text-btn-medium">
							<?php esc_html_e( 'I already know, skip it!', 'tutor' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
			<?php
	}

	/**
	 * Setup wizard type
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_setup_wizard_type() {
		$course_marketplace = tutor_utils()->get_option( 'enable_course_marketplace' );
		$course_marketplace = 1 === $course_marketplace ? 'on' : 'off';
		?>
			<div class="tutor-wizard-container">
				<div class="tutor-wrapper-type tutor-setup-wizard-type">
					<div class="wizard-type-header">
						<div class="logo"><img src="<?php echo esc_url( tutor()->url . 'assets/images/tutor-logo.svg' ); ?>" /></div>
						<div class="title"><?php esc_html_e( 'Let’s get the platform up and running', 'tutor' ); ?></div>
						<div class="subtitle"><?php esc_html_e( 'Pick a category for your LMS platform. You can always update this later.', 'tutor' ); ?></div>
					</div>
					<div class="wizard-type-body">
						<div class="wizard-type-item">
							<input id="enable_course_marketplace-0" type="radio" name="enable_course_marketplace" value="off" 
							<?php
							if ( ! $course_marketplace ) {
								echo 'checked'; }
							?>
							/>
							<span class="icon"></span>
							<label for="enable_course_marketplace-0">
								<img src="<?php echo esc_url( tutor()->url . 'assets/images/single-marketplace.svg' ); ?>" />
								<div class="title"><?php esc_html_e( 'Individual', 'tutor' ); ?></div>
								<div class="subtitle"><?php esc_html_e( 'Share solo journey as an educator and spread knowledge', 'tutor' ); ?></div>
								<div class="action">
									<button class="tutor-btn tutor-btn-primary tutor-btn-md tutor-type-next">
									<?php esc_html_e( 'Next', 'tutor' ); ?>
									</button>
								</div>
							</label>
						</div>

						<div class="wizard-type-item">
							<input id="enable_course_marketplace-1" type="radio" name="enable_course_marketplace" value="on" 
							<?php
							if ( $course_marketplace ) {
								echo 'checked'; }
							?>
							/>
							<span class="icon"></span>
							<label for="enable_course_marketplace-1">
								<img src="<?php echo esc_url( tutor()->url . 'assets/images/multiple-marketplace.svg' ); ?>" />
								<div class="title"><?php esc_html_e( 'Marketplace', 'tutor' ); ?></div>
								<div class="subtitle"><?php esc_html_e( 'Create an eLearning platform to let anyone earn by teaching online', 'tutor' ); ?></div>
								<div class="action">
									<button class="tutor-btn tutor-btn-primary tutor-btn-md tutor-type-next">
									<?php esc_html_e( 'Next', 'tutor' ); ?>
									</button>
								</div>
							</label>
						</div>
					</div>

					<div class="wizard-type-footer">
						<div class="tutor-fs-7">
							<span><?php esc_html_e( 'Not sure?', 'tutor' ); ?></span>&nbsp;
							<a href="#" class="tutor-type-skip" class="">
							<?php esc_html_e( 'Let’s go to the next step.', 'tutor' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
			<?php
	}

	/**
	 * Setup wizard header
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_setup_wizard_header() {
		set_current_screen();
		?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
			<head>
				<meta name="viewport" content="width=device-width" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title><?php esc_html_e( 'Tutor &rsaquo; Setup Wizard', 'tutor' ); ?></title>
			<?php
			try {
				do_action( 'admin_enqueue_scripts' );
			} catch ( \Throwable $th ) { //phpcs:ignore
			}
			?>
			<?php wp_print_scripts( 'tutor-plyr' ); ?>
			<?php wp_print_scripts( 'tutor-slick' ); ?>
			<?php wp_print_scripts( 'tutor-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
			</head>
			<body class="tutor-setup wp-core-ui">
			<?php
	}

	/**
	 * Setup wizard footer
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_setup_wizard_footer() {
		?>
				</body>
			</html>
			<?php
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$page = Input::get( 'page', '' );
		if ( 'tutor-setup' === $page ) {
			wp_enqueue_style( 'tutor-setup', tutor()->url . 'assets/css/tutor-setup.min.css', array(), TUTOR_VERSION );
			wp_enqueue_style( 'tutor-slick', tutor()->url . 'assets/packages/slick/slick.css', array(), TUTOR_VERSION );
			wp_enqueue_style( 'tutor-slick-theme', tutor()->url . 'assets/packages/slick/slick-theme.css', array(), TUTOR_VERSION );
			wp_register_script( 'tutor-slick', tutor()->url . 'assets/packages/slick/slick.min.js', array( 'jquery' ), TUTOR_VERSION, true );
			wp_register_script( 'tutor-setup-v2', tutor()->url . 'assets/js/tutor.min.js', array( 'jquery', 'wp-i18n' ), TUTOR_VERSION, true );
			wp_register_script( 'tutor-setup', tutor()->url . 'assets/js/tutor-setup.min.js', array( 'jquery', 'tutor-slick', 'wp-i18n' ), TUTOR_VERSION, true );
			wp_localize_script( 'tutor-setup', '_tutorobject', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		}
	}


	/**
	 * Check if welcome page already visited
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_welcome_page_visited(): bool {
		return false;
	}

	/**
	 * Mark as welcome page visited
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function mark_as_visited() {
		update_option( 'tutor_welcome_page_visited', true );
	}
}

