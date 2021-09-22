<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
    exit;

class Course_Settings_Tabs{

    public $course_post_type = '';
    public $args = array();
    public $settings_meta = null;

    public function __construct() {
        $this->course_post_type = tutor()->course_post_type;

        add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
            
        add_action( 'tutor/frontend_course_edit/after/description', array($this, 'display_frontend'), 10, 0 );

        add_action('tutor_save_course', array($this, 'save_course'), 10, 2);
    }

    public function register_meta_box(){
        add_meta_box( 'course-settings', __( 'Course Settings', 'tutor' ), array($this, 'display'), $this->course_post_type, 'advanced', 'high' );
    }

    public function get_default_args(){
        $args = array(
            'general' => array(
                'label' => __('General', 'tutor'),
                'desc' => __('General Settings', 'tutor'),
                'icon_class'  => ' tutor-icon-settings-1',
                'callback'  => '',
                'fields'    => array(
                    'maximum_students' => array(
                        'type'      => 'number',
                        'label'     => __('Maximum Students', 'tutor'),
                        'label_title' => __('Enable', 'tutor'),
                        'default' => '0',
                        'desc'      => __('Number of students that can enrol in this course. Set 0 for no limits.', 'tutor'),
                    ),
                ),
            ),
        );

        return apply_filters('tutor_course_settings_tabs', $args);
    }

    public function display(){
        global $post;
        $this->args = $this->get_default_args();

        $settings_meta = get_post_meta(get_the_ID(), '_tutor_course_settings', true);
        $this->settings_meta = (array) maybe_unserialize($settings_meta);

        if (tutils()->count($this->args) && $post->post_type === $this->course_post_type) {
            include tutor()->path . "views/metabox/course/settings-tabs.php";
        }
    }

    public function display_frontend() {
        ?>
        <div class="tutor-mb-30">
            <label class="tutor-form-label"><?php _e('Course Settings', 'tutor'); ?></label>
            <div class="tutor-input-group tutor-mb-15">
                <?php $this->display(); ?>
            </div>
        </div>
        <?php
    }

    public function generate_field($fields = array()){
        if (tutils()->count($fields)){
            foreach ($fields as $field_key => $field){
                $type = tutils()->array_get('type', $field);
                ?>
                <div class="row">
                    <?php
                    if (!empty($field['label'])){
                        ?>
                        <div class="col-4">
                            <label for=""><?php echo $field['label']; ?></label>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="col-8">
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

    public function get($key = null, $default = false){
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