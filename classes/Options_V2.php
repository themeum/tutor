<?php

namespace Tutor;

if (!defined('ABSPATH'))
	exit;

class Options_V2 {

	public $option;
	public $options_attr;

	public function __construct() {
		$this->option = (array) maybe_unserialize(get_option('tutor_option'));
		$this->options_attr = $this->options_attr();

		//Saving option
		add_action('wp_ajax_tutor_option_save', array($this, 'tutor_option_save'));
	}

	private function get($key = null, $default = false) {
		$option = $this->option;
		if (empty($option) || !is_array($option)) {
			return $default;
		}
		if (!$key) {
			return $option;
		}
		if (array_key_exists($key, $option)) {
			return apply_filters($key, $option[$key]);
		}
		//Access array value via dot notation, such as option->get('value.subvalue')
		if (strpos($key, '.')) {
			$option_key_array = explode('.', $key);
			$new_option = $option;
			foreach ($option_key_array as $dotKey) {
				if (isset($new_option[$dotKey])) {
					$new_option = $new_option[$dotKey];
				} else {
					return $default;
				}
			}
			return apply_filters($key, $new_option);
		}

		return $default;
	}

	public function tutor_option_save() {
		tutils()->checking_nonce();

		!current_user_can('manage_options') ? wp_send_json_error() : 0;

		do_action('tutor_option_save_before');

		$option = (array)tutils()->array_get('tutor_option', $_POST, array());
		$option = apply_filters('tutor_option_input', $option);
		update_option('tutor_option', $option);

		do_action('tutor_option_save_after');
		//re-sync settings
		//init::tutor_activate();

		wp_send_json_success(array('msg' => __('Option Updated', 'tutor')));
	}

	public function options_attr() {
		$pages = tutor_utils()->get_pages();

		//$course_base = tutor_utils()->course_archive_page_url();
		$lesson_url = site_url() . '/course/' . 'sample-course/<code>lessons</code>/sample-lesson/';
		$student_url = tutor_utils()->profile_url();
		$attempts_allowed = array();
		$attempts_allowed['unlimited'] = __('Unlimited', 'tutor');
		$attempts_allowed = array_merge($attempts_allowed, array_combine(range(1, 20), range(1, 20)));

		$video_sources = array(
			'html5' => __('HTML 5 (mp4)', 'tutor'),
			'external_url' => __('External URL', 'tutor'),
			'youtube' => __('Youtube', 'tutor'),
			'vimeo' => __('Vimeo', 'tutor'),
			'embedded' => __('Embedded', 'tutor')
		);

		$course_filters = array(
			'search' => __('Keyword Search', 'tutor'),
			'category' => __('Category', 'tutor'),
			'tag' => __('Tag', 'tutor'),
			'difficulty_level' => __('Difficulty Level', 'tutor'),
			'price_type' => __('Price Type', 'tutor')
		);


		$attr = array(
			'basic' => array(
				'label'     => __('Basic', 'tutor'),
				'sections'    => array(
					array(
						'label' => __('General', 'tutor'),
						'slug' => 'general',
						'desc' => __('General Settings', 'tutor'),
						'icon' => __('earth', 'tutor'),
						'blocks' => array(
							array(
								'label' => false,
								'block_type' => 'uniform',
								'fields' => array(
									'tutor_dashboard_page_id' => array(
										'type'          => 'select',
										'label'         => __('Dashboard Page', 'tutor'),
										'default'       => '0',
										'options'       => $pages,
										'desc'          => __('This page will be used for student and instructor dashboard', 'tutor'),
									),
								)
							),
							array(
								'label' => __('Course', 'tutor'),
								'slug' => 'course',
								'block_type' => 'uniform',
								'fields' => array(
									'student_must_login_to_view_course' => array(
										'type'      => 'checkbox',
										'label'     => __('Course Visibility', 'tutor'),
										'label_title' => __('Logged in only', 'tutor'),
										'desc'      => __('Students must be logged in to view course', 'tutor'),
									),
									'course_archive_page' => array(
										'type'      => 'select',
										'label'     => __('Course Archive Page', 'tutor'),
										'default'   => '0',
										'options'   => $pages,
										'desc'      => __('This page will be used to list all the published courses.',	'tutor'),
									),
									'course_content_access_for_ia' => array(
										'type'      => 'checkbox',
										'label'     => __('Enable / Disable', 'tutor'),
										'label_title'   => __('Course Content Access', 'tutor'),
										'desc' => __('Allow instructors and admins to view the course content without enrolling', 'tutor'),
									),
									'course_completion_process' => array(
										'type'          => 'radio',
										'label'         => __('Course Completion Process', 'tutor'),
										'default'       => 'flexible',
										'select_options'   => false,
										'options'   => array(
											'flexible'  =>  __('Flexible', 'tutor'),
											'strict'    =>  __('Strict Mode', 'tutor'),
										),
										'desc'          => __('Students can complete courses anytime in the Flexible mode. In the Strict mode, students have to complete, pass all the lessons and quizzes (if any) to mark a course as complete.', 'tutor'),
									),
								),
							),
							array(
								'label' => __('Video', 'tutor'),
								'slug' => 'video',
								'block_type' => 'uniform',
								'fields' => array(
									'supported_video_sources' => array(
										'type'      => 'checkbox',
										'label'     => __('Preferred Video Source', 'tutor'),
										'options'	=> $video_sources,
										'desc'      => __('Choose video sources you\'d like to support. Unchecking all will not disable video feature.', 'tutor'),
									),
									'default_video_source' => array(
										'type'      => 'select',
										'label'     => __('Default Video Source', 'tutor'),
										'default'   => '',
										'options'   => $video_sources,
										'desc'      => __('Choose video source to be selected by default.',	'tutor'),
									),
								),
							),
							array(
								'label' => __('Others', 'tutor'),
								'slug' => 'others',
								'block_type' => 'isolate',
								'fields' => array(
									'lesson_permalink_base' => array(
										'type'      => 'text',
										'label'     => __('Lesson Permalink Base', 'tutor'),
										'default'   => 'lessons',
										'desc'      => $lesson_url,
									),
									'student_register_page' => array(
										'type'          => 'select',
										'label'         => __('Student Registration Page', 'tutor'),
										'default'       => '0',
										'options'       => $pages,
										'desc'          => __('Choose the page for student registration page', 'tutor'),
									),
								),
							),
							array(
								'label' => __('Instructor', 'tutor'),
								'slug' => 'instructor',
								'block_type' => 'uniform',
								'fields' => array(
									'instructor_register_page' => array(
										'type'      => 'select',
										'label'     => __('Instructor Registration Page', 'tutor'),
										'default'   => '0',
										'options'   => $pages,
										'desc'      => __('This page will be used to sign up new instructors.', 'tutor'),
									),
									'instructor_can_publish_course' => array(
										'type'      => 'checkbox',
										'label'     => __('Allow Instructors Publishing Courses', 'tutor'),
										'label_title' => __('Enable', 'tutor'),
										'default' => '0',
										'desc'      => __('Enable instructors to publish the course directly. If disabled, admins will be able to review course content before publishing.',	'tutor'),
									),
									'enable_become_instructor_btn' => array(
										'type'      => 'checkbox',
										'label'     => __('Become Instructor Button', 'tutor'),
										'label_title' => __('Enable', 'tutor'),
										'default' => '0',
										'desc'      => __('Uncheck this option to hide the button from student dashboard.',	'tutor'),
									),
								),
							),
						),
					),
					array(
						'label' => __('Course', 'tutor'),
						'slug' => 'course',
						'desc' => __('Course Settings', 'tutor'),
						'icon' => __('book-open', 'tutor'),
						'blocks' => array(
							array(
								'label' => __('Lesson', 'tutor'),
								'slug' => 'lesson',
								'block_type' => 'uniform',
								'fields' => array(
									'autoload_next_course_content' => array(
										'type'      => 'checkbox',
										'label'     => __('Enable / Disable', 'tutor'),
										'label_title'   => __('Automatically load next course content.', 'tutor'),
										'desc' => __('Enabling this feature will be load next course content automatically after finishing current video.', 'tutor'),
									),
								),
							),
							array(
								'label' => __('Quiz', 'tutor'),
								'slug' => 'quiz',
								'block_type' => 'uniform',
								'fields' => array(
									'quiz_time_limit' => array(
										'type'      => 'group_fields',
										'label'     => __('Time Limit', 'tutor'),
										'desc'      => __('0 means unlimited time.', 'tutor'),
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
										'type'      => 'radio',
										'label'      => __('When time expires', 'tutor'),
										'default'   => 'minutes',
										'select_options'   => false,
										'options'   => array(
											'autosubmit'    =>  __('The current quiz answers are submitted automatically.', 'tutor'),
											'graceperiod'   =>  __('The current quiz answers are submitted by students.', 'tutor'),
											'autoabandon'   =>  __('Attempts must be submitted before time expires, otherwise they will not be counted', 'tutor'),
										),
										'desc'  => __('Choose which action to follow when the quiz time expires.', 'tutor'),
									),
									'quiz_attempts_allowed' => array(
										'type'      => 'number',
										'label'      => __('Attempts allowed', 'tutor'),
										'default'   => '10',
										'desc'  => __('The highest number of attempts students are allowed to take for a quiz. 0 means unlimited attempts.', 'tutor'),
									),
									'quiz_grade_method' => array(
										'type'      => 'select',
										'label'      => __('Final grade calculation', 'tutor'),
										'default'   => 'minutes',
										'select_options'   => false,
										'options'   => array(
											'highest_grade' => __('Highest Grade', 'tutor'),
											'average_grade' => __('Average Grade', 'tutor'),
											'first_attempt' => __('First Attempt', 'tutor'),
											'last_attempt' => __('Last Attempt', 'tutor'),
										),
										'desc'  => __('When multiple attempts are allowed, which method should be used to calculate a student\'s final grade for the quiz.', 'tutor'),
									),
								),
							),
						)
					),
					array(
						'label' => __('Monitization', 'tutor'),
						'slug' => 'monitization',
						'desc' => __('Monitization Settings', 'tutor'),
						'icon' => __('discount-filled', 'tutor'),
						'blocks' => array(
							array(
								'label' => false,
								'block_type' => 'uniform',
								'fields' => array(
									'autoload_next_course_content' => array(
										'type'      => 'checkbox',
										'label'     => __('Enable / Disable', 'tutor'),
										'label_title'   => __('Automatically load next course content.', 'tutor'),
										'desc' => __('Enabling this feature will be load next course content automatically after finishing current video.', 'tutor'),
									),
								),
							),
							array(
								'label' => false,
								'slug' => 'options',
								'block_type' => 'uniform',
								'fields' => array(
									'statement_show_per_page' => array(
										'type'      => 'number',
										'label'      => __('Show Statement Per Page', 'tutor'),
										'default'   => '20',
										'desc'  => __('Define the number of statements to show.', 'tutor'),
									),
								),
							),
						)
					),
					'design' => array(
						'label' => __('Design', 'tutor'),
						'slug' => 'design',
						'desc' => __('Design Settings', 'tutor'),
						'icon' => __('design', 'tutor'),
						'blocks' => array()
					),
					'advanced' => array(
						'label' => __('Advanced', 'tutor'),
						'slug' => 'advanced',
						'desc' => __('Advanced Settings', 'tutor'),
						'icon' => __('filter', 'tutor'),
						'blocks' => array()
					),
					'email' => array(
						'label' => __('Email', 'tutor'),
						'slug' => 'email',
						'desc' => __('Email Settings', 'tutor'),
						'icon' => __('envelope', 'tutor'),
						'blocks' => array()
					),
					'certificate' => array(
						'label' => __('Certificate', 'tutor'),
						'slug' => 'certificate',
						'desc' => __('Certificate Settings', 'tutor'),
						'icon' => __('certificate', 'tutor'),
						'blocks' => array()
					),
					'gradebook' => array(
						'label' => __('Gradebook', 'tutor'),
						'slug' => 'gradebook',
						'desc' => __('Gradebook Settings', 'tutor'),
						'icon' => __('gradebook', 'tutor'),
						'blocks' => array()
					),
				),
			),
			'addons' => array(
				'label'     => __('Addons', 'tutor'),
				'sections'    => array(
					array(
						'label' => __('Zoom', 'tutor'),
						'slug' => 'zoom',
						'desc' => __('Zoom Settings', 'tutor'),
						'icon' => __('zoom', 'tutor'),
						'blocks' => array(
							'block' => array(),
						)
					),
					array(
						'label' => __('Google Classroom', 'tutor'),
						'slug' => 'google_classroom',
						'desc' => __('Google Classroom Settings', 'tutor'),
						'icon' => __('classroom', 'tutor'),
						'blocks' => array(
							'block' => array(),
						)
					),
					array(
						'label' => __('Buddypress', 'tutor'),
						'slug' => 'buddypress',
						'desc' => __('Buddypress Settings', 'tutor'),
						'icon' => __('buddypress', 'tutor'),
						'blocks' => array(
							'block' => array(),
						)
					),
					array(
						'label' => __('Paid Memberships Pro', 'tutor'),
						'slug' => 'paid_memberships_pro',
						'desc' => __('Paid Memberships Pro Settings', 'tutor'),
						'icon' => __('paid-membersip-pro', 'tutor'),
						'blocks' => array(
							'block' => array(),
						)
					),
				),
			),
			'tools' => array(
				'label'     => __('Tools', 'tutor'),
				'sections'    => array(
					array(
						'label' => __('Status', 'tutor'),
						'slug' => 'status',
						'desc' => __('Status Settings', 'tutor'),
						'icon' => __('chart', 'tutor'),
						'blocks' => array(
							'block' => array(),
						)
					),
					array(
						'label' => __('Import/Export', 'tutor'),
						'slug' => 'import_export',
						'desc' => __('Import/Export Settings', 'tutor'),
						'icon' => __('import-export', 'tutor'),
						'blocks' => array(
							'block' => array(),
						)
					),
					array(
						'label' => __('Tutor Pages', 'tutor'),
						'slug' => 'tutor_pages',
						'desc' => __('Tutor Pages Settings', 'tutor'),
						'icon' => __('buddypress', 'tutor'),
						'blocks' => array(
							'block' => array(),
						)
					),
					array(
						'label' => __('Setup Wizard', 'tutor'),
						'slug' => 'setup_wizard',
						'desc' => __('Setup Wizard Settings', 'tutor'),
						'icon' => __('paid-membersip-pro', 'tutor'),
						'blocks' => array(
							'block' => array(),
						)
					),
				),
			),

		);

		return json_decode(json_encode($attr), FALSE);
	}

	/**
	 * @param array $field
	 *
	 * @return string
	 *
	 * Generate Option Field
	 */
	public function generate_field($field = array()) {
		ob_start();
		include tutor()->path . 'views/options/option_field.php';
		return ob_get_clean();
	}

	public function field_type($field = array()) {
		ob_start();
		include tutor()->path . "views/options/field-types/{$field['type']}.php";
		return ob_get_clean();
	}

	public function generate() {
		ob_start();
		include tutor()->path . 'views/options/options_generator.php';
		return ob_get_clean();
	}
}
