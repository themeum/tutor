<?php
namespace Dozent;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Options {

	public $option;
	public $options_attr;

	public function __construct() {
		$this->option = (array) maybe_unserialize(get_option('dozent_option'));
		$this->options_attr = $this->options_attr();

		//Saving option
		add_action('wp_ajax_dozent_option_save', array($this, 'dozent_option_save'));
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
			return apply_filters($key, $option[$key]);
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
			return apply_filters($key, $new_option);
		}

		return $default;
	}

	public function dozent_option_save(){
		if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce( $_POST['_wpnonce'], 'dozent_option_save' ) ){
			exit();
		}

		$option = (array) isset($_POST['dozent_option']) ? $_POST['dozent_option'] : array();
		$option = apply_filters('dozent_option_input', $option);
		update_option('dozent_option', $option);

		//re-sync settings
		init::dozent_activate();

		wp_send_json_success( array('msg' => __('Option Updated', 'dozent') ) );
	}
	
	public function options_attr(){
		$pages = dozent_utils()->get_pages();

		//$course_base = dozent_utils()->course_archive_page_url();
		$lesson_url = site_url().'/course/'.'sample-course/<code>lessons</code>/sample-lesson/';

		$student_url = dozent_utils()->profile_url();

		$attempts_allowed = array();
		$attempts_allowed['unlimited'] = __('Unlimited' , 'dozent');
		$attempts_allowed = array_merge($attempts_allowed, array_combine(range(1,20), range(1,20)));

		$attr = array(
			'general' => array(
				'label'     => __('General', 'dozent'),
				'sections'    => array(
					'general' => array(
						'label' => __('General', 'dozent'),
						'desc' => __('General Settings', 'dozent'),
						'fields' => array(
							'enable_public_profile' => array(
								'type'      => 'checkbox',
								'label'     => __('Enable Public Profile', 'dozent'),
								'default' => '0',
								'desc'      => __('Enable this to make a profile publicly visible',	'dozent')."<br />"
								               .$student_url,
							),
							'load_dozent_css' => array(
								'type'      => 'checkbox',
								'label'     => __('Load Dozent default CSS', 'dozent'),
								'default'   => '1',
								'desc'      => __('If your theme has its own styling, then you can turn it off to load CSS from the plugin directory', 'dozent'),
							),
							'load_dozent_js' => array(
								'type'      => 'checkbox',
								'label'     => __('Load Dozent default JavaScript', 'dozent'),
								'default'   => '1',
								'desc'      => __('If you have put required script in your theme javascript file, then you can turn it off to load JavaScript from the plugin directory', 'dozent'),
							),
							'student_must_login_to_view_course' => array(
								'type'      => 'checkbox',
								'label'     => __('Course Permission', 'dozent'),
								'desc'      => __('Students must be logged in to view course', 'dozent'),
							),
							'delete_on_uninstall' => array(
								'type'      => 'checkbox',
								'label'     => __('Erase upon uninstallation', 'dozent'),
								'desc'      => __('Delete all data during uninstall', 'dozent'),
							),
						)
					)
				),
			),
			'course' => array(
				'label'     => __('Course', 'dozent'),
				'sections'    => array(
					'general' => array(
						'label' => __('General', 'dozent'),
						'desc' => __('Course Settings', 'dozent'),
						'fields' => array(
							'course_allow_upload_private_files' => array(
								'type'          => 'checkbox',
								'label'         => __('Private file uploading', 'dozent'),
								'label_title'   => __('Allow uploading private files', 'dozent'),
								'desc'          => __('This will allow uploading files to courses and only enrolled students can access these files',	'dozent'),
							),
							/*
							'course_complete_terms' => array(
								'type'      => 'select',
								'label'     => __('When course will be complete', 'dozent'),
								'default'   => '0',
								'options'   => array(
									'all_lesson_complete' =>  __('When all lesson completed', 'dozent'),
									'complete_by_click' =>  __('Manually clicking the (complete course) button ', 'dozent'),
								),
								'desc'      => __('Select page to show course archieve page, none will show default course post type',	'dozent'),
							),*/

							'display_course_teachers' => array(
								'type'      => 'checkbox',
								'label'     => __('Display teachers profile', 'dozent'),
								'label_title'   => __('Show the teacher profile on course single page.', 'dozent'),
							),
							'enable_q_and_a_on_course' => array(
								'type'      => 'checkbox',
								'label'     => __('Enable Q &amp; A on course', 'dozent'),
								'default'   => '0',
								'desc'      => __('Allow student to place their questions and answers on the course page, only enrolled student can do this',	'dozent'),
							),
						),
					),
					'archive' => array(
						'label' => __('Archive', 'dozent'),
						'desc' => __('Course Archive Settings', 'dozent'),
						'fields' => array(
							'course_archive_page' => array(
								'type'      => 'select',
								'label'     => __('Course Archive Page', 'dozent'),
								'default'   => '0',
								'options'   => $pages,
								'desc'      => __('Choose the page from the dropdown list where you want to show all of the courses',	'dozent'),
							),
							'courses_col_per_row' => array(
								'type'      => 'slider',
								'label'     => __('Column per row', 'dozent'),
								'default'   => '4',
								'options'   => array('min'=> 1, 'max' => 6),
								'desc'      => __('Define how many column you want to show on the course single page', 'dozent'),
							),
							'courses_per_page' => array(
								'type'      => 'slider',
								'label'     => __('Courses Per Page', 'dozent'),
								'default'   => '10',
								'options'   => array('min'=> 1, 'max' => 20),
								'desc'      => __('Define how many courses you want to show per page', 'dozent'),
							),
						),
					),
				),
			),
			'lesson' => array(
				'label' => __('Lessons', 'dozent'),
				'sections'    => array(
					'lesson_settings' => array(
						'label' => __('Lesson Settings', 'dozent'),
						'desc' => __('Lesson settings will be here', 'dozent'),
						'fields' => array(
							'lesson_permalink_base' => array(
								'type'      => 'text',
								'label'     => __('Lesson Permalink Base', 'dozent'),
								'default'   => 'lessons',
								'desc'      => $lesson_url,
							),

						),

					),

				),
			),
			'quiz' => array(
				'label' => __('Quiz', 'dozent'),
				'sections'    => array(
					'general' => array(
						'label' => __('Quiz', 'dozent'),
						'desc' => __('The values you set here define the default values that are used in the settings form when you create a new quiz.', 'dozent'),
						'fields' => array(
							'quiz_time_limit' => array(
								'type'      => 'group_fields',
								'label'     => __('Time Limit', 'dozent'),
								'desc'      => __('Default time limit for quizzes. 0 means no time limit.', 'dozent'),
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
											'weeks'     =>  __('Weeks', 'dozent'),
											'days'      =>  __('Days', 'dozent'),
											'hours'     =>  __('Hours', 'dozent'),
											'minutes'   =>  __('Minutes', 'dozent'),
											'seconds'   =>  __('Seconds', 'dozent'),
										),
									),
								),
							),

							'quiz_when_time_expires' => array(
								'type'      => 'select',
								'label'      => __('When time expires', 'dozent'),
								'default'   => 'minutes',
								'select_options'   => false,
								'options'   => array(
									'autosubmit'    =>  __('Current attempts are submitted automatically', 'dozent'),
									'graceperiod'   =>  __('There is a grace period when current attempts can be submitted, but no more questions answered', 'dozent'),
									'autoabandon'   =>  __('Attempts must be submitted before time expires, otherwise they will not be counted', 'dozent'),
								),
								'desc'  => __('What should happen by default if a student does not submit the quiz before time expires.', 'dozent'),
							),

							'quiz_attempts_allowed' => array(
								'type'      => 'slider',
								'label'      => __('Attempts allowed', 'dozent'),
								'default'   => '10',
								'options'   => array('min'=> 0, 'max' => 20),
								'desc'  => __('Restriction on the number of attempts students are allowed to take for a quiz. 0 for no limit', 'dozent'),
							),

							'quiz_grade_method' => array(
								'type'      => 'select',
								'label'      => __('Grading method', 'dozent'),
								'default'   => 'minutes',
								'select_options'   => false,
								'options'   => array(
									'highest_grade' => __('Highest Grade', 'dozent'),
									'average_grade' => __('Average Grade', 'dozent'),
									'first_attempt' => __('First Attempt', 'dozent'),
									'last_attempt' => __('Last Attempt', 'dozent'),
								),
								'desc'  => __('When multiple attempts are allowed, which method should be used to calculate a student\'s final grade for the quiz.', 'dozent'),
							),
						)
					)
				),
			),
			'teachers' => array(
				'label'     => __('Teachers', 'dozent'),
				'sections'    => array(
					'general' => array(
						'label' => __('Teacher Profile Settings', 'dozent'),
						'desc' => __('Enable Disable Option to on/off notification on various event', 'dozent'),
						'fields' => array(
							'teacher_register_page' => array(
								'type'      => 'select',
								'label'     => __('Teacher Register Page', 'dozent'),
								'default'   => '0',
								'options'   => $pages,
								'desc'      => __('This will be teacher register page', 'dozent'),
							),
							'teacher_can_publish_course' => array(
								'type'      => 'checkbox',
								'label'     => __('Can publish course', 'dozent'),
								'default' => '0',
								'desc'      => __('Define if a teacher can publish his courses directly or not, if unchecked, they can still add courses, but it will go to admin for review',	'dozent'),
							),
						),
					),
				),
			),

			'students' => array(
				'label'     => __('Students', 'dozent'),
				'sections'    => array(
					'general' => array(
						'label' => __('Student Profile settings', 'dozent'),
						'desc' => __('Enable Disable Option to on/off notification on various event', 'dozent'),
						'fields' => array(
							'student_register_page' => array(
								'type'      => 'select',
								'label'     => __('Student Register Page', 'dozent'),
								'default'   => '0',
								'options'   => $pages,
								'desc'      => __('Choose the page for student registration page', 'dozent'),
							),
							'student_dashboard' => array(
								'type'      => 'select',
								'label'     => __('Student Dashboard', 'dozent'),
								'default'   => '0',
								'options'   => $pages,
								'desc'      => __('This page will show students related stuff, like my courses, order, etc', 'dozent'),
							),

							'students_own_review_show_at_profile' => array(
								'type'      => 'checkbox',
								'label'     => __('Show reviews on profile', 'dozent'),
								'label_title'     => __('Enable students review on their profile', 'dozent'),
								'default' => '0',
								'desc'      => __('Enabling this will allow the reviews written by each individual students on their profile',	'dozent')."<br />" .$student_url,
							),
							'show_courses_completed_by_student' => array(
								'type'      => 'checkbox',
								'label'     => __('Show Completed Course', 'dozent'),
								'default' => '0',
								'desc'      => __('Completed courses will be show on student profile',	'dozent')."<br />".$student_url,
							),

						),
					),
				),
			),

			'email_notification' => array(
				'label'     => __('E-Mail Notification', 'dozent'),
				'sections'    => array(
					'general' => array(
						'label' => __('Enable/Disable', 'dozent'),
						'desc' => __('Enable Disable Option to on/off notification on various event', 'dozent'),
						'fields' => array(
							'email_to_students' => array(
								'type'      => 'checkbox',
								'label'     => __('E-Mail to Students', 'dozent'),
								'options'   => array(
									'quiz_completed' => __('Quiz Completed', 'dozent'),
									'completed_course' => __('Completed a course', 'dozent'),
								),
								'desc'      => __('Select when to sent notification to the students',	'dozent'),
							),
							'email_to_teachers' => array(
								'type'      => 'checkbox',
								'label'     => __('E-Mail to Teachers', 'dozent'),
								'options'   => array(
									'a_student_enrolled_in_course'              => __('A Student enrolled in course ', 'dozent'),
									'a_student_completed_course'            => __('A Student Completed Course', 'dozent'),
									'a_student_completed_lesson'            => __('A Student Completed Lesson', 'dozent'),
									'a_student_placed_question'             => __('A Student placed question to course', 'dozent'),
								),
								'desc'      => __('Select when to sent notification to the teachers',	'dozent'),
							),
						),
					),
					'email_settings' => array(
						'label' => __('E-Mail Settings', 'dozent'),
						'desc' => __('Check and place necessary information here.', 'dozent'),
						'fields' => array(
							'email_from_name' => array(
								'type'      => 'text',
								'label'     => __('E-Mail From Name', 'dozent'),
								'default'   => get_option('blogname'),
								'desc'      => __('The name under which all the emails will be sent',	'dozent'),
							),
							'email_from_address' => array(
								'type'      => 'text',
								'label'     => __('From E-Mail Address', 'dozent'),
								'default'   => get_option('admin_email'),
								'desc'      => __('The E-Mail address from which all emails will be sent', 'dozent'),
							),
							'email_footer_text' => array(
								'type'      => 'textarea',
								'label'     => __('E-Mail Footer Text', 'dozent'),
								'default'   => '',
								'desc'      => __('The text to appear in E-Mail template footer', 'dozent'),
							),
						),
					),

				),
			),
		);

		return apply_filters('dozent/options/attr', $attr);
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
		include dozent()->path.'views/options/option_field.php';
		return ob_get_clean();
	}

	public function field_type($field = array()){
		ob_start();
		include dozent()->path."views/options/field-types/{$field['type']}.php";
		return ob_get_clean();
	}

	public function generate(){
		ob_start();
		include dozent()->path.'views/options/options_generator.php';
		return ob_get_clean();
	}



}