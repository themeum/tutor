<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
    exit;

class Course_Settings_Tabs{

    public $course_post_type = '';
    public $args = array();
    public $settings_meta = null;
    private $is_frontend = null;

    public function __construct() {
        $this->course_post_type = tutor()->course_post_type;

        add_action( 'add_meta_boxes', array($this, 'register_meta_box') );

        add_action( 'tutor/frontend_course_edit/after/description', array($this, 'display_frontend'), 10, 0 );

        add_action('tutor_save_course', array($this, 'save_course'), 10, 2);
        add_action('tutor_save_course_settings', array($this, 'save_course'), 10, 2);
    }

    public function register_meta_box() {
		\tutor_meta_box_wrapper( 'course-settings', __( 'Course Settings', 'tutor' ), array( $this, 'display' ), $this->course_post_type, 'advanced', 'high', 'tutor-admin-post-meta' );
    }

    public function get_default_args(){
        $args = array(
            'general' => array(
                'label' => __('General', 'tutor'),
                'desc' => __('General Settings', 'tutor'),
                'icon_class'  => ' tutor-icon-gear',
                'callback'  => '',
                'fields'    => array(
                    '_tutor_course_settings[maximum_students]' => array(
                        'type'      => 'number',
                        'label'     => __('Maximum Students', 'tutor'),
                        'label_title' => __('Enable', 'tutor'),
                        'value'     => (int) tutor_utils()->get_course_settings(get_the_ID(), 'maximum_students', 0),
                        'desc'      => __('Number of students that can enrol in this course. Set 0 for no limits.', 'tutor'),
                    )
                ),
            ),
        );

        $filtered = apply_filters('tutor_course_settings_tabs', $args);

        $filtered['general']['fields']['_tutor_is_public_course'] = array(
            'type'        => 'toggle_switch',
            'label' => __('Public Course', 'tutor'),
            'options'	  => array(
                array(
                    'checked' => get_post_meta(get_the_ID(), '_tutor_is_public_course', true)=='yes',
                    'value' => 'yes',
                    'hint' => __('Make This Course Public. No enrolment required.', 'tutor')
                )
            )
        );

        $q_and_a_global = get_option( 'tutor_option' )['enable_q_and_a_on_course'];
        $_tutor_enable_qa = get_post_meta(get_the_ID(), '_tutor_enable_qa', true);
        if($_tutor_enable_qa){
            $qa_enabled = 'yes'===$_tutor_enable_qa ?  true : false;
        }elseif(isset($q_and_a_global) && !empty($q_and_a_global)){
            $qa_enabled = ( 'on' === $q_and_a_global || true === $q_and_a_global )? true : false ;
        }
        $qa_enabled = true === $qa_enabled ? $qa_enabled : false;

        $filtered['general']['fields']['_tutor_enable_qa'] = array(
            'type'        => 'toggle_switch',
            'label'       => __('Q&A', 'tutor'),
            'options'	  => array(
                array(
                    'checked' => $qa_enabled,
                    'value' => 'yes',
                    'hint' => __('Enable Q&A section for your course', 'tutor')
                )
            )
        );

        return $filtered;
    }

    public function display(){
        global $post;
        $this->args = $this->get_default_args();

        $settings_meta = get_post_meta(get_the_ID(), '_tutor_course_settings', true);
        $this->settings_meta = (array) maybe_unserialize($settings_meta);

        if (tutor_utils()->count($this->args) && $post->post_type === $this->course_post_type) {
            include tutor()->path . "views/metabox/settings-tabs.php";
        }
    }

    public function display_frontend() {
        ?>
        <div class="tutor-mb-32">
            <label class="tutor-form-label tutor-fs-6"><?php _e('Course Settings', 'tutor'); ?></label>
            <div class="tutor-input-group tutor-mb-16">
                <?php $this->display(); ?>
            </div>
        </div>
        <?php
    }

    public function get($key = null, $default = false){
        return tutor_utils()->array_get($key, $this->settings_meta, $default);
    }

    /**
     * @param $post_ID
     * @param $post
     *
     * Fire when course saving...
     */
    public function save_course($post_ID, $post){
        $_tutor_course_settings = tutor_utils()->array_get('_tutor_course_settings', $_POST);

        if (tutor_utils()->count($_tutor_course_settings)){

            $existing = get_post_meta( $post_ID, '_tutor_course_settings', true );
            !is_array( $existing ) ? $existing = array() : 0;

            $meta = array_merge($existing, $_tutor_course_settings);

            update_post_meta($post_ID, '_tutor_course_settings', $meta);
        }
    }
}
