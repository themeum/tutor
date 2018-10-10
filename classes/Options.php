<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Options {

	public $option;
	public $options_attr;

	public function __construct() {
		$this->option = (array) maybe_unserialize(get_option('tutor_option'));
		$this->options_attr = $this->options_attr();

		//Saving option
		add_action('wp_ajax_tutor_option_save', array($this, 'tutor_option_save'));
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

	public function tutor_option_save(){
		if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce( $_POST['_wpnonce'], 'tutor_option_save' ) ){
			exit();
		}

		$option = (array) isset($_POST['tutor_option']) ? $_POST['tutor_option'] : array();
		$option = apply_filters('tutor_option_input', $option);
		update_option('tutor_option', $option);

		//re-sync settings
		init::tutor_activate();

		wp_send_json_success( array('msg' => __('Option Updated', 'tutor') ) );
	}
	
	public function options_attr(){
		$pages = tutor_utils()->get_pages();

		//$course_base = tutor_utils()->course_archive_page_url();
		$lesson_url = site_url().'/course/'.'sample-course/<code>lessons</code>/sample-lesson/';

		$student_url = tutor_utils()->student_url();

		$attempts_allowed = array();
		$attempts_allowed['unlimited'] = __('Unlimited' , 'tutor');
		$attempts_allowed = array_merge($attempts_allowed, array_combine(range(1,20), range(1,20)));

		$attr = array(
			'general' => array(
				'label'     => __('General', 'tutor'),
				'sections'    => array(
					'general' => array(
						'label' => __('General', 'tutor'),
						'desc' => __('General Settings', 'tutor'),
						'fields' => array(
							'load_tutor_css' => array(
								'type'      => 'checkbox',
								'label'     => __('Load TUTOR default CSS', 'tutor'),
								'default'   => '1',
								'desc'      => __('If theme has own styling, then you can turn it off to load CSS from the plugin directory', 'tutor'),
							),
							'load_tutor_js' => array(
								'type'      => 'checkbox',
								'label'     => __('Load TUTOR default JavaScript', 'tutor'),
								'default'   => '1',
								'desc'      => __('If theme has own styling, then you can turn it off to load JavaScript from the plugin directory', 'tutor'),
							),
							'access_permission' => array(
								'type'      => 'checkbox',
								'label'     => __('Access Permission', 'tutor'),
								'desc'      => __('Students must be logged in to view course and lesson', 'tutor'),
							),
							'delete_on_uninstall' => array(
								'type'      => 'checkbox',
								'label'     => __('Delete on Uninstall', 'tutor'),
								'desc'      => __('Delete all data during uninstall', 'tutor'),
							),
						)
					)
				),
			),
			'course' => array(
				'label'     => __('Course', 'tutor'),
				'sections'    => array(
					'general' => array(
						'label' => __('General', 'tutor'),
						'desc' => __('Course Settings', 'tutor'),
						'fields' => array(
							'course_allow_upload_private_files' => array(
								'type'      => 'checkbox',
								'label'     => __('Allow Upload Private Files', 'tutor'),
								'desc'      => __('This will allow upload files to course and only enrolled students can access these files',	'tutor'),
							),
							'course_complete_terms' => array(
								'type'      => 'select',
								'label'     => __('When course will be complete', 'tutor'),
								'default'   => '0',
								'options'   => array(
									'all_lesson_complete' =>  __('When all lesson completed', 'tutor'),
									'complete_by_click' =>  __('Manually clicking the (complete course) button ', 'tutor'),
								),
								'desc'      => __('Select page to show course archieve page, none will show default course post type',	'tutor'),
							),
							'display_course_instructors' => array(
								'type'      => 'checkbox',
								'label'     => __('Display course Instructors', 'tutor'),
								'desc'      => __('Show the instructors at single course page',	'tutor'),
							),
							'display_course_head_instructors' => array(
								'type'      => 'checkbox',
								'label'     => __('Display the head instructors to course', 'tutor'),
								'desc'      => __('Show the instructors at single course page',	'tutor'),
							),
						),
					),
					'archive' => array(
						'label' => __('Archive', 'tutor'),
						'desc' => __('Course Archive Settings', 'tutor'),
						'fields' => array(
							'course_archive_page' => array(
								'type'      => 'select',
								'label'     => __('Course Archive Page', 'tutor'),
								'default'   => '0',
								'options'   => $pages,
								'desc'      => __('Select page to show course archieve page, none will show default course post type',	'tutor'),
							),
							'courses_col_per_row' => array(
								'type'      => 'slider',
								'label'     => __('Col per row', 'tutor'),
								'default'   => '4',
								'options'   => array('min'=> 1, 'max' => 6),
								'desc'      => __('Show col per row', 'tutor'),
							),
							'courses_per_page' => array(
								'type'      => 'slider',
								'label'     => __('Courses Per Page', 'tutor'),
								'default'   => '10',
								'options'   => array('min'=> 1, 'max' => 20),
								'desc'      => __('Course show per page in pagination', 'tutor'),
							),
						),
					),

					'single_course' => array(
						'label' => __('Single Course', 'tutor'),
						'desc' => __('Settings will deploy to single course page', 'tutor'),
						'fields' => array(
							'enrolled_students_number' => array(
								'type'      => 'checkbox',
								'label'     => __('Enrolled Students Number', 'tutor'),
								'desc'      => __('Display placed students number during add/edit course, uncheck to show real enrolled students total',	'tutor'),
							),

						),
					),
				),
			),
			'lesson' => array(
				'label' => __('Lessons', 'tutor'),
				'sections'    => array(
					'lesson_settings' => array(
						'label' => __('Lesson Settings', 'tutor'),
						'desc' => __('Lesson settings will be here', 'tutor'),
						'fields' => array(
							'lesson_permalink_base' => array(
								'type'      => 'text',
								'label'     => __('Lesson Permalink Base', 'tutor'),
								'default'   => 'lessons',
								'desc'      => $lesson_url,
							),

							'allow_students_comments_on_lesson' => array(
								'type'      => 'checkbox',
								'label'     => __('Allow Student Comments', 'tutor'),
								'default'   => '0',
								'desc'      => __('Allow student to place their comments on lesson page, only enrolled student can do this',	'tutor'),
							),

							'display_head_instructor_on_lesson' => array(
								'type'      => 'checkbox',
								'label'     => __('Display Head Instructor on Lesson', 'tutor'),
								'default'   => '1',
								'desc'      => __('This will allow to view head instructor on lesson page',	'tutor'),
							),

						),

					),

				),
			),

			'quiz' => array(
				'label' => __('Quiz', 'tutor'),
				'sections'    => array(
					'general' => array(
						'label' => __('Quiz', 'tutor'),
						'desc' => __('The values you set here define the default values that are used in the settings form when you create a new quiz.', 'tutor'),
						'fields' => array(
							'quiz_time_limit' => array(
								'type'      => 'group_fields',
								'label'     => __('Time Limit', 'tutor'),
								'desc'      => __('Default time limit for quizzes in seconds. 0 mean no time limit.', 'tutor'),

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
											'weeks'     =>  __('Weeks', 'tutor'),
											'days'      =>  __('Days', 'tutor'),
											'hours'     =>  __('Hours', 'tutor'),
											'minutes'   =>  __('Minutes', 'tutor'),
											'seconds'   =>  __('Seconds', 'tutor'),
										),
									),

								),
							),

							'quiz_when_time_expires' => array(
								'type'      => 'select',
								'label'      => __('When time expires', 'tutor'),
								'default'   => 'minutes',
								'select_options'   => false,
								'options'   => array(
									'autosubmit'    =>  __('Open attempts are submitted automatically', 'tutor'),
									'graceperiod'   =>  __('There is a grace period when open attempts can be submitted, but no more questions answered', 'tutor'),
									'autoabandon'   =>  __('Attempts must be submitted before time expires, or they are not counted', 'tutor'),
								),
								'desc'  => __('What should happen by default if a student does not submit the quiz before time expires.', 'tutor'),
							),

							'quiz_attempts_allowed' => array(
								'type'      => 'slider',
								'label'      => __('Attempts allowed', 'tutor'),
								'default'   => '10',
								'options'   => array('min'=> 0, 'max' => 20),
								'desc'  => __('Restriction on the number of attempts students are allowed at the quiz. 0 for no limit', 'tutor'),
							),

							'quiz_grade_method' => array(
								'type'      => 'select',
								'label'      => __('Grading method', 'tutor'),
								'default'   => 'minutes',
								'select_options'   => false,
								'options'   => array(
									'highest_grade' => __('Highest Grade', 'tutor'),
									'average_grade' => __('Average Grade', 'tutor'),
									'first_attempt' => __('First Attempt', 'tutor'),
									'last_attempt' => __('Last Attempt', 'tutor'),
								),
								'desc'  => __('When multiple attempts are allowed, which method should be used to calculate the student\'s final grade for the quiz.', 'tutor'),
							),
						)
					)
				),
			),

			'teachers' => array(
				'label'     => __('Teachers', 'tutor'),
				'sections'    => array(
					'general' => array(
						'label' => __('Student Profile settings', 'tutor'),
						'desc' => __('Enable Disable Option to on/off notification on various event', 'tutor'),
						'fields' => array(
							'teacher_can_publish_course' => array(
								'type'      => 'checkbox',
								'label'     => __('Can publish course', 'tutor'),
								'default' => '0',
								'desc'      => __('Is teacher can publish a course directly or not, if uncheck, they can still add the course and it will goes to admin for review.',	'tutor'),
							),
						),
					),
				),
			),


			'students' => array(
				'label'     => __('Students', 'tutor'),
				'sections'    => array(
					'general' => array(
						'label' => __('Student Profile settings', 'tutor'),
						'desc' => __('Enable Disable Option to on/off notification on various event', 'tutor'),
						'fields' => array(
							'student_dashboard' => array(
								'type'      => 'select',
								'label'     => __('Student Dashboard', 'tutor'),
								'default'   => '0',
								'options'   => $pages,
								'desc'      => __('This page will show students related stuff, like my courses, order, etc', 'tutor'),
							),

							'student_public_url_enable' => array(
								'type'      => 'checkbox',
								'label'     => __('Enable student pubic URL', 'tutor'),
								'default' => '0',
								'desc'      => __('Any students public profile can be accessible via URL.',	'tutor')."<br />".$student_url,
							),

							'students_own_review_show_at_profile' => array(
								'type'      => 'checkbox',
								'label'     => __('Own review show at profile', 'tutor'),
								'default' => '0',
								'desc'      => __('Show review at students public profile placed by them.',	'tutor')."<br />".$student_url,
							),
							'show_courses_completed_by_student' => array(
								'type'      => 'checkbox',
								'label'     => __('Show Completed Course', 'tutor'),
								'default' => '0',
								'desc'      => __('Completed courses will be show at student profile',	'tutor')."<br />".$student_url,
							),

						),
					),
				),
			),

			'email_notification' => array(
				'label'     => __('E-Mail Notification', 'tutor'),
				'sections'    => array(
					'general' => array(
						'label' => __('Enable/Disable', 'tutor'),
						'desc' => __('Enable Disable Option to on/off notification on various event', 'tutor'),
						'fields' => array(
							'email_to_students' => array(
								'type'      => 'checkbox',
								'label'     => __('E-Mail to Students', 'tutor'),
								'options'   => array(
									'quiz_is_graded' => __('Quiz Graded', 'tutor'),
									'completed_course' => __('Completed a course', 'tutor'),
								),
								'desc'      => __('Select notification that will be sent to students',	'tutor'),
							),
							'email_to_teachers' => array(
								'type'      => 'checkbox',
								'label'     => __('E-Mail to Teachers', 'tutor'),
								'options'   => array(
									'a_student_started_course'              => __('A learner starts their course ', 'tutor'),
									'a_student_completed_course'            => __('A Student Completed Course', 'tutor'),
									'a_student_completed_lesson'            => __('A Student Completed Lesson', 'tutor'),
									'a_student_submitted_exam_for_grading'  => __('A Student Submitted Exam for Grading', 'tutor'),
									'a_student_sent_msg_to_teacher'         => __('A Student Sent Message to teacher', 'tutor'),
								),
								'desc'      => __('Select the notifications that will be sent to teachers.',	'tutor'),
							),

						),
					),
					'email_settings' => array(
						'label' => __('E-Mail Settings', 'tutor'),
						'desc' => __('Check and place necessary information here.', 'tutor'),
						'fields' => array(
							'email_from_name' => array(
								'type'      => 'text',
								'label'     => __('E-Mail From Name', 'tutor'),
								'default'   => get_option('blogname'),
								'desc'      => __('The name from which all emails will be sent',	'tutor'),
							),
							'email_from_address' => array(
								'type'      => 'text',
								'label'     => __('From E-Mail Address', 'tutor'),
								'default'   => get_option('admin_email'),
								'desc'      => __('The E-Mail address from which all emails will be sent', 'tutor'),
							),
							'email_footer_text' => array(
								'type'      => 'textarea',
								'label'     => __('E-Mail Footer Text', 'tutor'),
								'default'   => '',
								'desc'      => __('The text to appear in E-Mail template footer', 'tutor'),
							),
						),
					),

				),
			),
		);




		if (tutor_utils()->has_wc()) {
			$attr['woocommerce'] = array(
				'label' => __( 'WooCommerce', 'tutor' ),
			);
		}

		return apply_filters('tutor/options/attr', $attr);
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
		include tutor()->path.'views/options/option_field.php';
		return ob_get_clean();
	}

	public function field_type($field = array()){
		ob_start();
		include tutor()->path."views/options/field-types/{$field['type']}.php";
		return ob_get_clean();
	}

	public function generate(){
		ob_start();
		include tutor()->path.'views/options/options_generator.php';
		return ob_get_clean();
	}



}