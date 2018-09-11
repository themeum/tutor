<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Options {

	public $option;
	public $options_attr;

	public function __construct() {
		$this->option = (array) maybe_unserialize(get_option('lms_option'));
		$this->options_attr = $this->options_attr();

		//Saving option
		add_action('wp_ajax_lms_option_save', array($this, 'lms_option_save'));
	}

	private function get($key = null, $default = false){
		$option = $this->option;
		if (empty($option) || ! is_array($option)){
			return $default;
		}
		if ( ! $key){
			return $option;
		}
		if (array_key_exists($key, $option)){
			return $option[$key];
		}

		//Access array value via dot notation, such as option->get('value.subvalue')
		if (strpos($key, '.')){
			$option_key_array = explode('.', $key);

			$new_option = $option;
			foreach ($option_key_array as $dotKey){
				if (isset($new_option[$dotKey])){
					$new_option = $new_option[$dotKey];
				}else{
					return $default;
				}
			}

			return $new_option;
		}

		return $default;
	}

	public function lms_option_save(){
		if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce( $_POST['_wpnonce'], 'lms_option_save' ) ){
			exit();
		}

		$option = (array) isset($_POST['lms_option']) ? $_POST['lms_option'] : array();
		$option = apply_filters('lms_option_input', $option);
		update_option('lms_option', $option);
		wp_send_json_success( array('msg' => __('Option Updated', 'lms') ) );
	}
	
	public function options_attr(){
		$pages = lms_utils()->get_pages();

		//$course_base = lms_utils()->course_archive_page_url();
		$lesson_url = site_url().'/course/'.'sample-course/<code>lessons</code>/sample-lesson/';

		$student_url = lms_utils()->student_url();

		$attempts_allowed = array();
		$attempts_allowed['unlimited'] = __('Unlimited' , 'lms');
		$attempts_allowed = array_merge($attempts_allowed, array_combine(range(1,20), range(1,20)));

		$attr = array(
			'general' => array(
				'label'     => __('General', 'lms'),
				'sections'    => array(
					'general' => array(
						'label' => __('General', 'lms'),
						'desc' => __('General Settings', 'lms'),
						'fields' => array(
							'load_lms_css' => array(
								'type'      => 'checkbox',
								'label'     => __('Load LMS default CSS', 'lms'),
								'default'   => '1',
								'desc'      => __('If theme has own styling, then you can turn it off to load CSS from the plugin directory', 'lms'),
							),
							'load_lms_js' => array(
								'type'      => 'checkbox',
								'label'     => __('Load LMS default JavaScript', 'lms'),
								'default'   => '1',
								'desc'      => __('If theme has own styling, then you can turn it off to load JavaScript from the plugin directory', 'lms'),
							),
							'access_permission' => array(
								'type'      => 'checkbox',
								'label'     => __('Access Permission', 'lms'),
								'desc'      => __('Students must be logged in to view course and lesson', 'lms'),
							),
							'delete_on_uninstall' => array(
								'type'      => 'checkbox',
								'label'     => __('Delete on Uninstall', 'lms'),
								'desc'      => __('Delete all data during uninstall', 'lms'),
							),
						)
					)
				),
			),
			'course' => array(
				'label'     => __('Course', 'lms'),
				'sections'    => array(
					'general' => array(
						'label' => __('General', 'lms'),
						'desc' => __('Course Settings', 'lms'),
						'fields' => array(
							'course_allow_upload_private_files' => array(
								'type'      => 'checkbox',
								'label'     => __('Allow Upload Private Files', 'lms'),
								'desc'      => __('This will allow upload files to course and only enrolled students can access these files',	'lms'),
							),
							'course_complete_terms' => array(
								'type'      => 'select',
								'label'     => __('When course will be complete', 'lms'),
								'default'   => '0',
								'options'   => array(
									'all_lesson_complete' =>  __('When all lesson completed', 'lms'),
									'complete_by_click' =>  __('Manually clicking the (complete course) button ', 'lms'),
								),
								'desc'      => __('Select page to show course archieve page, none will show default course post type',	'lms'),
							),
							'display_course_instructors' => array(
								'type'      => 'checkbox',
								'label'     => __('Display course Instructors', 'lms'),
								'desc'      => __('Show the instructors at single course page',	'lms'),
							),
							'display_course_head_instructors' => array(
								'type'      => 'checkbox',
								'label'     => __('Display the head instructors to course', 'lms'),
								'desc'      => __('Show the instructors at single course page',	'lms'),
							),
						),
					),
					'archive' => array(
						'label' => __('Archive', 'lms'),
						'desc' => __('Course Archive Settings', 'lms'),
						'fields' => array(
							'course_archive_page' => array(
								'type'      => 'select',
								'label'     => __('Course Archive Page', 'lms'),
								'default'   => '0',
								'options'   => $pages,
								'desc'      => __('Select page to show course archieve page, none will show default course post type',	'lms'),
							),
							'my_course_page' => array(
								'type'      => 'select',
								'label'     => __('My course page', 'lms'),
								'default'   => '0',
								'options'   => $pages,
								'desc'      => __('This page will show students enrolled course', 'lms'),
							),
							'courses_col_per_row' => array(
								'type'      => 'slider',
								'label'     => __('Col per row', 'lms'),
								'default'   => '4',
								'options'   => array('min'=> 1, 'max' => 6),
								'desc'      => __('Show col per row', 'lms'),
							),
							'courses_per_page' => array(
								'type'      => 'slider',
								'label'     => __('Courses Per Page', 'lms'),
								'default'   => '10',
								'options'   => array('min'=> 1, 'max' => 20),
								'desc'      => __('Course show per page in pagination', 'lms'),
							),
						),
					),

					'single_course' => array(
						'label' => __('Single Course', 'lms'),
						'desc' => __('Settings will deploy to single course page', 'lms'),
						'fields' => array(
							'enrolled_students_number' => array(
								'type'      => 'checkbox',
								'label'     => __('Enrolled Students Number', 'lms'),
								'desc'      => __('Display placed students number during add/edit course, uncheck to show real enrolled students total',	'lms'),
							),

						),
					),
				),
			),
			'lesson' => array(
				'label' => __('Lessons', 'lms'),
				'sections'    => array(
					'lesson_settings' => array(
						'label' => __('Lesson Settings', 'lms'),
						'desc' => __('Lesson settings will be here', 'lms'),
						'fields' => array(
							'lesson_permalink_base' => array(
								'type'      => 'text',
								'label'     => __('Lesson Permalink Base', 'lms'),
								'default'   => 'lessons',
								'desc'      => $lesson_url,
							),

							'allow_students_comments_on_lesson' => array(
								'type'      => 'checkbox',
								'label'     => __('Allow Student Comments', 'lms'),
								'default'   => '0',
								'desc'      => __('Allow student to place their comments on lesson page, only enrolled student can do this',	'lms'),
							),

							'display_head_instructor_on_lesson' => array(
								'type'      => 'checkbox',
								'label'     => __('Display Head Instructor on Lesson', 'lms'),
								'default'   => '1',
								'desc'      => __('This will allow to view head instructor on lesson page',	'lms'),
							),

						),

					),

				),
			),

			'quiz' => array(
				'label' => __('Quiz', 'lms'),
				'sections'    => array(
					'general' => array(
						'label' => __('Quiz', 'lms'),
						'desc' => __('The values you set here define the default values that are used in the settings form when you create a new quiz.', 'lms'),
						'fields' => array(
							'quiz_time_limit' => array(
								'type'      => 'group_fields',
								'label'     => __('Time Limit', 'lms'),
								'desc'      => __('Default time limit for quizzes in seconds. 0 mean no time limit.', 'lms'),

								'group_fields'  => array(
									'value' => array(
										'type'      => 'text',
										'default'   => '0',
									),

									'time' => array(
										'type'      => 'select',
										'default'   => 'minutes',
										'select_options'   => false,
										'options'   => array(
											'weeks'     =>  __('Weeks', 'lms'),
											'days'      =>  __('Days', 'lms'),
											'hours'     =>  __('Hours', 'lms'),
											'minutes'   =>  __('Minutes', 'lms'),
											'seconds'   =>  __('Seconds', 'lms'),
										),
									),

								),
							),

							'quiz_when_time_expires' => array(
								'type'      => 'select',
								'label'      => __('When time expires', 'lms'),
								'default'   => 'minutes',
								'select_options'   => false,
								'options'   => array(
									'autosubmit'    =>  __('Open attempts are submitted automatically', 'lms'),
									'graceperiod'   =>  __('There is a grace period when open attempts can be submitted, but no more questions answered', 'lms'),
									'autoabandon'   =>  __('Attempts must be submitted before time expires, or they are not counted', 'lms'),
								),
								'desc'  => __('What should happen by default if a student does not submit the quiz before time expires.', 'lms'),
							),

							'quiz_attempts_allowed' => array(
								'type'      => 'slider',
								'label'      => __('Attempts allowed', 'lms'),
								'default'   => '10',
								'options'   => array('min'=> 0, 'max' => 20),
								'desc'  => __('Restriction on the number of attempts students are allowed at the quiz. 0 for no limit', 'lms'),
							),

							'quiz_grade_method' => array(
								'type'      => 'select',
								'label'      => __('Grading method', 'lms'),
								'default'   => 'minutes',
								'select_options'   => false,
								'options'   => array(
									'highest_grade' => __('Highest Grade', 'lms'),
									'average_grade' => __('Average Grade', 'lms'),
									'first_attempt' => __('First Attempt', 'lms'),
									'last_attempt' => __('Last Attempt', 'lms'),
								),
								'desc'  => __('When multiple attempts are allowed, which method should be used to calculate the student\'s final grade for the quiz.', 'lms'),
							),
						)
					)
				),
			),

			'students' => array(
				'label'     => __('Students', 'lms'),

				'sections'    => array(
					'general' => array(
						'label' => __('Student Profile settings', 'lms'),
						'desc' => __('Enable Disable Option to on/off notification on various event', 'lms'),
						'fields' => array(
							'student_public_url_enable' => array(
								'type'      => 'checkbox',
								'label'     => __('Enable student pubic URL', 'lms'),
								'default' => '0',
								'desc'      => __('Any students public profile can be accessible via URL.',	'lms')."<br />".$student_url,
							),

							'students_own_review_show_at_profile' => array(
								'type'      => 'checkbox',
								'label'     => __('Own review show at profile', 'lms'),
								'default' => '0',
								'desc'      => __('Show review at students public profile placed by them.',	'lms')."<br />".$student_url,
							),
							'show_courses_completed_by_student' => array(
								'type'      => 'checkbox',
								'label'     => __('Show Completed Course', 'lms'),
								'default' => '0',
								'desc'      => __('Completed courses will be show at student profile',	'lms')."<br />".$student_url,
							),

						),
					),

				),


			),
			'video_player' => array(
				'label'     => __('Video Player', 'lms'),
			),

			'email_notification' => array(
				'label'     => __('E-Mail Notification', 'lms'),
				'sections'    => array(
					'general' => array(
						'label' => __('Enable/Disable', 'lms'),
						'desc' => __('Enable Disable Option to on/off notification on various event', 'lms'),
						'fields' => array(
							'email_to_students' => array(
								'type'      => 'checkbox',
								'label'     => __('E-Mail to Students', 'lms'),
								'options'   => array(
									'quiz_is_graded' => __('Quiz Graded', 'lms'),
									'completed_course' => __('Completed a course', 'lms'),
								),
								'desc'      => __('Select notification that will be sent to students',	'lms'),
							),
							'email_to_teachers' => array(
								'type'      => 'checkbox',
								'label'     => __('E-Mail to Teachers', 'lms'),
								'options'   => array(
									'a_student_started_course'              => __('A learner starts their course ', 'lms'),
									'a_student_completed_course'            => __('A Student Completed Course', 'lms'),
									'a_student_completed_lesson'            => __('A Student Completed Lesson', 'lms'),
									'a_student_submitted_exam_for_grading'  => __('A Student Submitted Exam for Grading', 'lms'),
									'a_student_sent_msg_to_teacher'         => __('A Student Sent Message to teacher', 'lms'),
								),
								'desc'      => __('Select the notifications that will be sent to teachers.',	'lms'),
							),

						),
					),
					'email_settings' => array(
						'label' => __('E-Mail Settings', 'lms'),
						'desc' => __('Check and place necessary information here.', 'lms'),
						'fields' => array(
							'email_from_name' => array(
								'type'      => 'text',
								'label'     => __('E-Mail From Name', 'lms'),
								'default'   => get_option('blogname'),
								'desc'      => __('The name from which all emails will be sent',	'lms'),
							),
							'email_from_address' => array(
								'type'      => 'text',
								'label'     => __('From E-Mail Address', 'lms'),
								'default'   => get_option('admin_email'),
								'desc'      => __('The E-Mail address from which all emails will be sent', 'lms'),
							),
							'email_footer_text' => array(
								'type'      => 'textarea',
								'label'     => __('E-Mail Footer Text', 'lms'),
								'default'   => '',
								'desc'      => __('The text to appear in E-Mail template footer', 'lms'),
							),
						),
					),

				),
			),
		);




		if (lms_utils()->has_wc()) {
			$attr['woocommerce'] = array(
				'label' => __( 'WooCommerce', 'lms' ),
			);
		}

		return apply_filters('lms/options/attr', $attr);
	}


	/**
	 * @param array $field
	 *
	 * @return string
	 *
	 * Generate Option Field
	 */
	public function generate_field($field = array()){
		ob_start();
		include lms()->path.'views/options/option_field.php';
		return ob_get_clean();
	}

	public function field_type($field = array()){
		ob_start();
		include lms()->path."views/options/field-types/{$field['type']}.php";
		return ob_get_clean();
	}

	public function generate(){
		ob_start();
		include lms()->path.'views/options/options_generator.php';
		return ob_get_clean();
	}



}