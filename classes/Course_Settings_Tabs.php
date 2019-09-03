<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Course_Settings_Tabs{

	private $args = array();
	private $settings_meta = null;

	public function __construct() {
		$this->args = $this->get_default_args();

		add_action( 'edit_form_after_editor', array($this, 'display') );
		add_action('tutor_save_course', array($this, 'save_course'), 10, 2);
	}

	private function get_default_args(){
		$args = array(
			'general' => array(
				'label' => __('General', 'tutor'),
				'desc' => __('General Settings', 'tutor'),
				'callback'  => '',
				'fields'    => array(
					'maximum_students' => array(
						'type'      => 'number',
						'label'     => __('Maximum Students', 'tutor'),
						'label_title' => __('Enable', 'tutor'),
						'default' => '0',
						'desc'      => __('Number of maximum students can enroll in this course, set zero for no limits', 'tutor'),
					),
				),

			),

			'contentdrip' => array(
				'label' => __('Content Drip', 'tutor'),
				'desc' => __('Tutor Content Drip allow you to schedule publish topics / lesson', 'tutor'),
				'callback'  => '',
				'fields'    => array(
					'enable_content_drip' => array(
						'type'      => 'checkbox',
						'label'     => __('Enable', 'tutor'),
						'label_title' => __('Enable', 'tutor'),
						'default' => '0',
						'desc'      => __('Enable / Disable content drip', 'tutor'),
					),
				),

			),

		);

		return apply_filters('tutor_course_settings_tabs', $args);
	}

	public function display( $post){
		$settings_meta = get_post_meta(get_the_ID(), '_tutor_course_settings', true);
		$this->settings_meta = (array) maybe_unserialize($settings_meta);

		$course_type = tutor()->course_post_type;
		if (tutils()->count($this->args) && $post->post_type === $course_type) {
			include tutor()->path . "views/metabox/course/settings-tabs.php";
		}
	}

	public function generate_field($fields = array()){
		if (tutils()->count($fields)){
			foreach ($fields as $field_key => $field){
				?>
				<div class="tutor-option-field-row">
					<?php
					if (isset($field['label'])){
						?>
						<div class="tutor-option-field-label">
							<label for=""><?php echo $field['label']; ?></label>
						</div>
						<?php
					}
					?>
					<div class="tutor-option-field">
						<?php
						$field['field_key'] = $field_key;

						$this->field_type($field);

						if (isset($field['desc'])){
							echo "<p class='desc'>{$field['desc']}</p>";
						}
						?>
					</div>
				</div>
				<?php
			}
		}
	}

	public function field_type($field = array()){
		include tutor()->path."views/metabox/course/field-types/{$field['type']}.php";
	}

	private function get($key = null, $default = false){
		return tutils()->array_get($key, $this->settings_meta, $default);
	}

	/**
	 * @param $post_ID
	 * @param $post
	 *
	 * Fire when course saving...
	 */
	public function save_course($post_ID, $post){
		$_tutor_course_settings = tutils()->array_get('_tutor_course_settings', $_POST);
		if (tutils()->count($_tutor_course_settings)){
			update_post_meta($post_ID, '_tutor_course_settings', $_tutor_course_settings);
		}
	}

}