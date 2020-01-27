<?php
defined( 'ABSPATH' ) || exit;

if ( !class_exists('Tutor_Setup') ) {
    class Tutor_Setup {


        public function __construct() {
            //if ( get_option( 'tutor_enable_setup_wizard', '' ) ) {
                add_action( 'admin_menu', array( $this, 'admin_menus' ) );
                add_action( 'admin_init', array( $this, 'setup_wizard' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
                add_action( 'wp_ajax_setup_action', array( $this, 'setup_action' ) );
            // }
        }

        public function tutor_modal_create_or_update_lesson(){
            // $lesson_id = (int) sanitize_text_field(tutor_utils()->avalue_dot('lesson_id', $_POST));
            // $_lesson_thumbnail_id = (int) sanitize_text_field(tutor_utils()->avalue_dot('_lesson_thumbnail_id', $_POST));
            
            
    
            wp_send_json_success(array('course_contents' => 'wow'));
        }


        public function admin_menus() {
            add_dashboard_page( '', '', 'manage_options', 'tutor-setup', '' );
        }


        public function setup_wizard() {
            if( isset($_GET['page']) ) {
                if( $_GET['page'] == 'tutor-setup' ) {
                    ob_start();
                    $this->tutor_setup_wizard_header();
                    // $this->tutor_setup_wizard_video();
                    // $this->tutor_setup_wizard_type();
                    // $this->tutor_setup_wizard_boarding();
                    $this->tutor_setup_wizard_settings();
                    $this->tutor_setup_wizard_footer();
                    exit;
                }
            }
        }

        public function tutor_setup_generator($field_arr) {
            $html = '';
            $options = (array) maybe_unserialize(get_option('tutor_option'));
            foreach ($field_arr as $key => $field) {
                $html .= '<div class="tutor-setting">';
                    switch ($field['type']) {
                        case 'switch':
                            $html .= isset( $field['lable'] ) ? '<div class="title">'.$field['lable'].'</div>' : '';
                            $html .= isset( $field['desc'] ) ? '<div class="content">'.$field['desc'].'</div>' : '';
                            $html .= '<div class="settings">';

                                $html .= '<label for="'.$key.'" class="switch-label input-switch-label">';
                                    $html .= '<span class="label-text">OFF</span>';
                                    $html .= '<div class="switchbox-wrapper">';
                                            $html .= '<input id="'.$key.'" class="input-switchbox" type="checkbox" name="'.$key.'" value="1" '.(isset($options[$key]) && $options[$key] ? 'checked' : '').'/>';
                                            $html .= '<span class="switchbox-icon"></span>';
                                    $html .= '</div>';
                                    $html .= '<span class="label-text">ON</span>';
                                $html .= '</label>';
                                //$html .= '<input type="checkbox" name="'.$key.'" value="1" '.(isset($options[$key]) && $options[$key] ? 'checked' : '').'/>';
                            $html .= '</div>';
                            break;

                        case 'text':
                            $html .= isset( $field['lable'] ) ? '<div class="title">'.$field['lable'].'</div>' : '';
                            $html .= isset( $field['desc'] ) ? '<div class="content">'.$field['desc'].'</div>' : '';
                            $html .= '<div class="settings">';
                                $html .= '<input type="text" name="'.$key.'" value="'.(isset($options[$key]) ? $options[$key] : '').'" />';
                            $html .= '</div>';
                            break;
                        
                        default:
                            # code...
                            break;
                    }
                    
                $html .= '</div>';
            }
            echo $html;
        }
        
        public function tutor_setup_wizard_settings() {

            $options = (array) maybe_unserialize(get_option('tutor_option'));

            $general_fields = array(
                'enable_public_profile' => array(
                    'type' => 'switch',
                    'lable' => __('Public Profile', 'tutor'),
                    'desc' => __('Tutor LMS comes with a revolutionary drag & drop system to create resourceful courses.', 'tutor'),
                    'default' => 1
                ),
                'enable_spotlight_mode' => array(
                    'type' => 'switch',
                    'lable' => __('Spotlight Mode', 'tutor'),
                    'desc' => __('Are you an individual and want to spread knowledge online? Tutor is for you! Live Demo.', 'tutor'),
                    'default' => ''
                ),
                'disable_default_player_youtube' => array(
                    'type' => 'switch',
                    'lable' => __('YouTube Player', 'tutor'),
                    'desc' => __('Are you an individual and want to spread knowledge online? Tutor is for you! Live Demo.', 'tutor'),
                    'default' => ''
                ),
                'disable_default_player_vimeo' => array(
                    'type' => 'switch',
                    'lable' => __('Vimeo Player', 'tutor'),
                    'desc' => __('Are you an individual and want to spread knowledge online? Tutor is for you!', 'tutor'),
                    'default' => ''
                ),
                'lesson_permalink_base' => array(
                    'type' => 'text',
                    'lable' => __('Lesson permalink', 'tutor'),
                    'desc' => 'http://tutor.test/course/sample-course/lesson/sample-lesson/',
                    'default' => ''
                ),
            );


            $course_fields = array(
                'display_course_instructors' => array(
                    'type' => 'switch',
                    'lable' => __('Show Instructor Bio', 'tutor'),
                    'desc' => __('Tutor LMS comes with a revolutionary drag & drop system to create resourceful courses. ', 'tutor'),
                    'default' => ''
                ),
                'enable_q_and_a_on_course' => array(
                    'type' => 'switch',
                    'lable' => __('Question and Anwser', 'tutor'),
                    'desc' => __('Are you an individual and want to spread knowledge online? Tutor is for you! Live Demo', 'tutor'),
                    'default' => ''
                ),
                'courses_col_per_row' => array(
                    'type' => 'switch',
                    'lable' => __('Courses Per Row', 'tutor'),
                    'default' => ''
                ),
                'courses_per_page' => array(
                    'type' => 'switch',
                    'lable' => __('Courses Per Page', 'tutor'),
                    'default' => ''
                ),
            );

            ?>
            <div class="tutor-wrapper-boarding active">
                <div><?php _e('Hello, Welcome Tutor LMS.', 'tutor'); ?></div>
                <div class="tutor-setup-wrapper">
                    <ul class="tutor-setup-title">
                        <li class="active"><?php _e('General', 'tutor'); ?></li>
                        <li><?php _e('Course', 'tutor'); ?></li>
                        <li><?php _e('Finish', 'tutor'); ?></li>
                    </ul>
                    <ul class="tutor-setup-content">

                        <form id="tutor-setup-form" method="post">
                            <input type="hidden" name="action" value="setup_action">
                            <li class="active">
                                <div class="tutor-setup-content-heading heading">
                                    <div><?php _e('General Settings', 'tutor'); ?></div>
                                    <div><?php _e('1 / 8 Step Completed', 'tutor'); ?></div>
                                    <div><?php _e('Reset Default', 'tutor'); ?></div>
                                </div>
                                <div class="tutor-setup-content-heading body">
                                    <?php $this->tutor_setup_generator($general_fields); ?>
                                </div>
                                <?php $this->tutor_setup_wizard_action(); ?>
                            </li>

                            <li>
                                <div class="tutor-setup-content-heading heading">
                                    <div><?php _e('General Settings', 'tutor'); ?></div>
                                    <div><?php _e('2 / 8 Step Completed', 'tutor'); ?></div>
                                    <div><?php _e('Reset Default', 'tutor'); ?></div>
                                </div>
                                <div class="tutor-setup-content-heading body">
                                    <?php $this->tutor_setup_generator($course_fields); ?>
                                </div>
                                <?php $this->tutor_setup_wizard_action(); ?>
                            </li>

                            <li>
                                <div class="tutor-setup-content-heading">
                                    <h2><?php _e('Thank You!', 'tutor'); ?></h2>
                                    <p><?php _e('Tutor LMS comes with a revolutionary drag & drop system to create resourceful courses. ', 'tutor'); ?></p>
                                    <button class="tutor-redirect" data-url="<?php echo admin_url('admin.php?page=tutor_settings'); ?>"><?php _e('Finish', 'tutor'); ?></button>
                                </div>
                            </li>

                        </form>

                    </ul>
                    
                </div>
            <?php
        }


        public function tutor_setup_wizard_action() {
            ?>
            <div class="tutor-setup-content-footer footer">
                <div class="tutor-setup-btn-wrapper">
                    <button class="tutor-setup-previous"><?php _e('Previous', 'tutor'); ?></button>
                </div>
                <div class="tutor-setup-btn-wrapper">
                    <button class="tutor-setup-skip"><?php _e('Skip This Step', 'tutor'); ?></button>
                </div>
                <div class="tutor-setup-btn-wrapper">
                    <button class="tutor-setup-next"><?php _e('Next', 'tutor'); ?></button>
                </div>
                
                
            </div>
            <?php
        }

        public function tutor_setup_wizard_boarding() {
            ?>
            <div class="tutor-wrapper-boarding active">
                <div><?php _e('Hello, Welcome Tutor LMS.', 'tutor'); ?></div>
                <div>
                    <ul class="slider tutor-boarding">
                        <li>
                            <img src="<?php echo tutor()->url.'assets/images/setup-individual.jpg'; ?>" />
                            <div><?php _e('Install Theme 1', 'tutor'); ?></div>
                            <div><?php _e('Pick a project category to connect with a specific community. You can always update this later.', 'tutor'); ?></div>
                        </li>
                        <li>
                            <img src="<?php echo tutor()->url.'assets/images/setup-marketplace.jpg'; ?>" />
                            <div><?php _e('Install Theme 2', 'tutor'); ?></div>
                            <div><?php _e('Pick a project category to connect with a specific community. You can always update this later.', 'tutor'); ?></div>
                        </li>
                        <li>
                            <img src="<?php echo tutor()->url.'assets/images/setup-individual.jpg'; ?>" />
                            <div><?php _e('Install Theme 3', 'tutor'); ?></div>
                            <div><?php _e('Pick a project category to connect with a specific community. You can always update this later.', 'tutor'); ?></div>
                        </li>
                        <li>
                            <img src="<?php echo tutor()->url.'assets/images/setup-individual.jpg'; ?>" />
                            <div><?php _e('Install Theme 4', 'tutor'); ?></div>
                            <div><?php _e('Pick a project category to connect with a specific community. You can always update this later.', 'tutor'); ?></div>
                        </li>
                        <li>
                            <img src="<?php echo tutor()->url.'assets/images/setup-individual.jpg'; ?>" />
                            <div><?php _e('Install Theme 5', 'tutor'); ?></div>
                            <div><?php _e('Pick a project category to connect with a specific community. You can always update this later.', 'tutor'); ?></div>
                        </li>
                    </ul>
                </div>
                <div>
                    <a class="tutor-boarding-next" href="#"><?php _e('I alredy know, Skip it!', 'tutor'); ?></a>
                    <div class="tutor-boarding-skip"><?php _e('Contact with Live support', 'tutor'); ?></div>
                </div>
            </div>
            <?php
        }

        public function tutor_setup_wizard_type() {
            $course_marketplace = tutor_utils()->get_option('enable_course_marketplace');
            ?>
            <div class="tutor-wrapper-type active">
                <div>
                    <div><?php _e('First, let’s get you set up.', 'tutor'); ?></div>
                    <div><?php _e('Pick a project category to connect with a specific community. You can always update this later.', 'tutor'); ?></div>
                </div>
                <div>
                    <div>
                        <input id="enable_course_marketplace-0" type="radio" name="enable_course_marketplace" value="0" <?php if(!$course_marketplace){ echo 'checked'; } ?> />
                        <label for="enable_course_marketplace-0">
                            <img src="<?php echo tutor()->url.'assets/images/setup-individual.jpg'; ?>" />
                            <div><?php _e( 'Individual', 'tutor' ); ?></div>
                            <div><?php _e( 'Destination that helps everyone gain the skills.', 'tutor' ); ?></div>
                        </label>
                    </div>
                    <div>
                        <input id="enable_course_marketplace-1" type="radio" name="enable_course_marketplace" value="1" <?php if($course_marketplace){ echo 'checked'; } ?>/>
                        <label for="enable_course_marketplace-1">
                            <img src="<?php echo tutor()->url.'assets/images/setup-marketplace.jpg'; ?>" />
                            <div><?php _e( 'Marketplace', 'tutor' ); ?></div>
                            <div><?php _e( 'Destination that helps everyone gain the skills.', 'tutor' ); ?></div>
                        </label>
                    </div>
                </div>
                <div>
                    <button class="tutor-type-next"><?php _e('Let’s Start', 'tutor'); ?></button>
                    <a href="#" class="tutor-type-skip" class=""><?php _e('I am confused, Skip this step', 'tutor'); ?></a>
                </div>
            </div>
            <?php
        }


        public function tutor_setup_wizard_video() {
            ?>
            <div class="tutor-wrapper-video active">

                <video poster="/path/to/poster.jpg" id="player" playsinline controls>
                    <source src="http://techslides.com/demos/sample-videos/small.mp4" type="video/mp4" />
                    <source src="http://techslides.com/demos/sample-videos/small.webm" type="video/webm" />
                </video>

            </div>
            <?php
        }


        public function tutor_setup_wizard_header() {
            set_current_screen();
            ?>
            <!DOCTYPE html>
            <html <?php language_attributes(); ?>>
            <head>
                <meta name="viewport" content="width=device-width" />
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title><?php esc_html_e( 'Tutor &rsaquo; Setup Wizard', 'tutor' ); ?></title>
                <?php do_action( 'admin_enqueue_scripts' ); ?>
                <?php wp_print_scripts( 'tutor-plyr' ); ?>
                <?php wp_print_scripts( 'tutor-slick' ); ?>
                <?php wp_print_scripts( 'tutor-setup' ); ?>
                <?php do_action( 'admin_print_styles' ); ?>
                <?php do_action( 'admin_head' ); ?>
            </head>
            <body class="tutor-setup wp-core-ui">
            <h1>Tutor Check</h1>
            <?php
        }


        public function tutor_setup_wizard_footer() {
                ?>
                </body>
            </html>
            <?php
        }


        public function enqueue_scripts() {
            //Plyr
		    wp_enqueue_style( 'tutor-plyr', tutor()->url . 'assets/packages/plyr/plyr.css', array(), tutor()->version );
		    wp_enqueue_script( 'tutor-plyr', tutor()->url . 'assets/packages/plyr/plyr.min.js', array( 'jquery' ), tutor()->version, true );

            wp_enqueue_style( 'tutor-setup', tutor()->url . 'assets/css/tutor-setup.css', array(), tutor()->version );

            wp_enqueue_style( 'tutor-slick', tutor()->url . 'assets/packages/slick/slick.css', array(), tutor()->version );
            wp_enqueue_style( 'tutor-slick-theme', tutor()->url . 'assets/packages/slick/slick-theme.css', array(), tutor()->version );
            wp_register_script( 'tutor-slick', tutor()->url . 'assets/packages/slick/slick.min.js', array( 'jquery' ), tutor()->version, true );

            wp_register_script( 'tutor-setup', tutor()->url . 'assets/js/tutor-setup.js', array( 'jquery', 'tutor-plyr', 'tutor-slick' ), tutor()->version, true );

            wp_localize_script('tutor-setup', '_tutorobject', array('ajaxurl' => admin_url('admin-ajax.php')));
        }


    }
    new Tutor_Setup();
}
