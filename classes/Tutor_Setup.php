<?php
defined( 'ABSPATH' ) || exit;

if ( !class_exists('Tutor_Setup') ) {
    class Tutor_Setup {


        public function __construct() {
            //if ( get_option( 'tutor_enable_setup_wizard', '' ) ) {
                add_action( 'admin_menu', array( $this, 'admin_menus' ) );
                add_action( 'admin_init', array( $this, 'setup_wizard' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
                add_action( 'wp_ajax_setup_action', array( $this, 'tutor_setup_action' ) );
            // }
            
            add_filter('tutor_wizard_attributes', array( $this, 'tutor_setup_attributes_callback' ));
        }

        function tutor_setup_attributes_callback($attr) {
            $options = (array) maybe_unserialize(get_option('tutor_option'));
            $final_arr = array();
            $data_arr = $this->tutor_setup_attributes();
            foreach ($data_arr as $key => $section) {
                foreach ($section as $k => $val) {
                    $final_arr[$k] = $options[$k];
                }
            }
            return $final_arr;
        }
        

        public function tutor_setup_action(){
            $options = (array) maybe_unserialize(get_option('tutor_option'));
            if(!isset($_POST['action']) && $_POST['action'] != 'setup_action') {
                return;
            }

            $change_data = apply_filters('tutor_wizard_attributes');
            foreach ($change_data as $key => $value) {
                if ( isset($_POST[$key]) ) {
                    if ($_POST[$key] != $change_data[$key]) {
                        if ($_POST[$key] == '') {
                            unset($options[$key]);
                        } else {
                            $options[$key] = $_POST[$key];
                        }
                    }
                } else {
                    unset($options[$key]);
                }
            }

            update_option('tutor_option', $options);

            wp_send_json_success(array('status' => 'success'));
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
                switch ($field['type']) {
                    case 'switch':
                        $html .= '<div class="tutor-setting">';
                            $html .= isset( $field['lable'] ) ? '<div class="title">'.$field['lable'].'</div>' : '';
                            $html .= isset( $field['desc'] ) ? '<div class="content">'.$field['desc'].'</div>' : '';
                            $html .= '<div class="settings">';

                                $html .= '<label for="'.$key.'" class="switch-label input-switch-label">';
                                    $html .= '<span class="label-off">OFF</span>';
                                    $html .= '<div class="switchbox-wrapper">';
                                            $html .= '<input id="'.$key.'" class="input-switchbox" type="checkbox" name="'.$key.'" value="1" '.(isset($options[$key]) && $options[$key] ? 'checked' : '').'/>';
                                            $html .= '<span class="switchbox-icon"></span>';
                                    $html .= '</div>';
                                    $html .= '<span class="label-on">ON</span>';
                                $html .= '</label>';
                                //$html .= '<input type="checkbox" name="'.$key.'" value="1" '.(isset($options[$key]) && $options[$key] ? 'checked' : '').'/>';
                            $html .= '</div>';
                        $html .= '</div>';
                        break;

                    case 'text':
                        $html .= '<div class="tutor-setting">';
                            $html .= isset( $field['lable'] ) ? '<div class="title">'.$field['lable'].'</div>' : '';
                                $html .= '<div class="content">';
                                    $html .= '<input type="text" name="'.$key.'" class="lesson-permalink" value="'.(isset($options[$key]) ? $options[$key] : '').'" />';    
                                    $html .= isset( $field['desc'] ) ? '<div>'.$field['desc'].'</div>' : '';
                                $html .= '</div>';
                            $html .= '<div class="settings"></div>';
                        $html .= '</div>';
                        break;

                    case 'rows':
                        $html .= '<div class="tutor-setting course-setting-wrapper wrapper-row">';
                            $html .= isset( $field['lable'] ) ? '<div class="title">'.$field['lable'].'</div>' : '';
                            $html .= '<div class="content">';
                                $html .= '<div class="course-per-row">';
                                    $html .= '<div class="wrapper">';
                                        $html .= '<label for="'.$key.'1">';
                                            $html .= '<input type="radio" value="1" name="'.$key.'" class="course" id="'.$key.'1" '.( isset($options[$key]) && $options[$key] == 1 ? 'checked' : '' ).'>';
                                            $html .= '<span class="span"><span>1</span></span>';
                                        $html .= '</label>';
                                    $html .= '</div>';
                                    $html .= '<div class="wrapper">';
                                        $html .= '<label for="'.$key.'2">';
                                            $html .= '<input type="radio" value="2" name="'.$key.'" class="course" id="'.$key.'2" '.( isset($options[$key]) && $options[$key] == 2 ? 'checked' : '' ).'>';
                                            $html .= '<span class="span"><span>2</span><span>2</span></span>';
                                        $html .= '</label>';
                                    $html .= '</div>';
                                    $html .= '<div class="wrapper">';
                                        $html .= '<label for="'.$key.'3">';
                                            $html .= '<input type="radio" value="3" name="'.$key.'" class="course" id="'.$key.'3" '.( isset($options[$key]) && $options[$key] == 3 ? 'checked' : '' ).'>';
                                            $html .= '<span class="span"><span>3</span><span>3</span><span>3</span></span>';
                                        $html .= '</label>';
                                    $html .= '</div>';
                                    $html .= '<div class="wrapper">';
                                        $html .= '<label for="'.$key.'4">';
                                            $html .= '<input type="radio" value="4" name="'.$key.'" class="course" id="'.$key.'4" '.( isset($options[$key]) && $options[$key] == 4 ? 'checked' : '' ).'>';
                                            $html .= '<span class="span"><span>4</span><span>4</span><span>4</span><span>4</span></span>';
                                        $html .= '</label>';
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="settings"></div>';
                        $html .= '</div>';
                        break;
                    
                    case 'radio':
                        $html .= '<div class="tutor-setting">';
                            $html .= isset( $field['lable'] ) ? '<div class="title">'.$field['lable'].'</div>' : '';
                            $html .= isset( $field['desc'] ) ? '<div class="content">'.$field['desc'].'</div>' : '';
                            $html .= '<div class="settings">';
                                if ( isset($field['options']) ) {
                                    foreach ($field['options'] as $k => $val) {
                                        $html .= '<input type="radio" name="'.$key.'" value="'.$k.'" '.( isset($options[$key]) && $options[$key] == $k ? 'checked' : '' ).' />';  
                                        $html .= $val.'<br>';
                                    }
                                }
                            $html .= '</div>';
                        $html .= '</div>';
                        break;

                    
                    default:
                        # code...
                        break;
                }
            }
            echo $html;
        }


        public function tutor_setup_attributes() {
            $general_fields = array(
                'general' => array(
                    'enable_public_profile' => array(
                        'type' => 'switch',
                        'lable' => __('Public Profile', 'tutor'),
                        'desc' => __('Tutor LMS comes with a revolutionary drag & drop system to create resourceful courses.', 'tutor'),
                    ),
                    'enable_spotlight_mode' => array(
                        'type' => 'switch',
                        'lable' => __('Spotlight Mode', 'tutor'),
                        'desc' => __('Are you an individual and want to spread knowledge online? Tutor is for you! Live Demo.', 'tutor'),
                    ),
                    'disable_default_player_youtube' => array(
                        'type' => 'switch',
                        'lable' => __('YouTube Player', 'tutor'),
                        'desc' => __('Are you an individual and want to spread knowledge online? Tutor is for you! Live Demo.', 'tutor'),
                    ),
                    'disable_default_player_vimeo' => array(
                        'type' => 'switch',
                        'lable' => __('Vimeo Player', 'tutor'),
                        'desc' => __('Are you an individual and want to spread knowledge online? Tutor is for you!', 'tutor'),
                    ),
                    'lesson_permalink_base' => array(
                        'type' => 'text',
                        'lable' => __('Lesson permalink', 'tutor'),
                        'desc' => 'http://tutor.test/course/sample-course/lesson/sample-lesson/',
                    ),
                ),
                'course' => array(
                    'display_course_instructors' => array(
                        'type' => 'switch',
                        'lable' => __('Show Instructor Bio', 'tutor'),
                        'desc' => __('Tutor LMS comes with a revolutionary drag & drop system to create resourceful courses. ', 'tutor'),
                    ),
                    'enable_q_and_a_on_course' => array(
                        'type' => 'switch',
                        'lable' => __('Question and Anwser', 'tutor'),
                        'desc' => __('Are you an individual and want to spread knowledge online? Tutor is for you! Live Demo', 'tutor'),
                    ),
                    'courses_col_per_row' => array(
                        'type' => 'rows',
                        'lable' => __('Courses Per Row', 'tutor'),
                    ),
                    'courses_per_page' => array(
                        'type' => 'switch',
                        'desc' => __('Are you an individual and want to spread knowledge online? Tutor is for you!', 'tutor'),
                        'lable' => __('Courses Per Page', 'tutor'),
                    ),
                ),

                // 'course' => array(
                //     'display_course_instructors' => array(
                //         'type' => 'switch',
                //         'lable' => __('Show Instructor Bio', 'tutor'),
                //         'desc' => __('Tutor LMS comes with a revolutionary drag & drop system to create resourceful courses. ', 'tutor'),
                //     ),
                // )
                // Quiz Settings 
                // Time Limit
                // When Time Expires
                // Attempts allowed
                // Final Grade Calculation

            );

            return $general_fields;
        }
        
        public function tutor_setup_wizard_settings() {

            $options = (array) maybe_unserialize(get_option('tutor_option'));

            ?>
            <div class="tutor-wrapper-boarding active">
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
                                    <div><?php _e('<strong>1</strong> / 8 Step Completed', 'tutor'); ?></div>
                                    <div><?php _e('Reset Default', 'tutor'); ?></div>
                                </div>
                                <div class="tutor-setup-content-heading body">
                                    <?php $this->tutor_setup_generator( $this->tutor_setup_attributes()['general'] ); ?>
                                </div>
                                <?php $this->tutor_setup_wizard_action(); ?>
                            </li>
                            <li>
                                <div class="tutor-setup-content-heading heading">
                                    <div><?php _e('General Settings', 'tutor'); ?></div>
                                    <div><?php _e('<strong>2</strong> / 8 Step Completed', 'tutor'); ?></div>
                                    <div><?php _e('Reset Default', 'tutor'); ?></div>
                                </div>
                                <div class="tutor-setup-content-heading body">
                                    <?php $this->tutor_setup_generator( $this->tutor_setup_attributes()['course'] ); ?>
                                </div>
                                <?php $this->tutor_setup_wizard_action(); ?>
                            </li>
                            <li>
                                <div class="tutor-setup-content-heading greetings ">
                                    <div class="header">
                                        <img src="http://saief.local/WP-TutorLMS/wp-content/uploads/2020/01/greeting-img.jpg" alt="">
                                    </div>
                                    <div class="content">
                                        <h2><?php _e('Thank You!', 'tutor'); ?></h2>
                                        <p><?php _e('Tutor LMS comes with a revolutionary drag & drop system to create resourceful courses. ', 'tutor'); ?></p>

                                    </div>
                                    <div class="tutor-setup-content-footer footer">
                                        <button class="tutor-redirect primary-btn" data-url="<?php echo admin_url('admin.php?page=tutor_settings'); ?>"><?php _e('Finish', 'tutor'); ?></button>

                                    </div>
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
                    <button class="tutor-setup-previous previous animated-btn">
                        <!-- <?php _e('Previous', 'tutor'); ?> -->
						<svg xmlns="http://www.w3.org/2000/svg" id="prev-arrow-1" width="17" height="12">
                            <path fill="#fff" stroke="" d="M11.492.65a.603.603 0 0 0-.86 0 .607.607 0 0 0 0 .85l4.361 4.362H.603A.6.6 0 0 0 0 6.465c0 .335.267.61.602.61h14.391l-4.36 4.353a.617.617 0 0 0 0 .86c.24.241.627.241.86 0l5.393-5.393a.592.592 0 0 0 0-.852L11.492.65z"/>
                        </svg>
                        <span><?php _e('Previous', 'tutor'); ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" id="prev-arrow-2" width="17" height="12">
                            <path fill="#fff" stroke="" d="M11.492.65a.603.603 0 0 0-.86 0 .607.607 0 0 0 0 .85l4.361 4.362H.603A.6.6 0 0 0 0 6.465c0 .335.267.61.602.61h14.391l-4.36 4.353a.617.617 0 0 0 0 .86c.24.241.627.241.86 0l5.393-5.393a.592.592 0 0 0 0-.852L11.492.65z"/>
                        </svg>
                    </button>
                </div>
                <div class="tutor-setup-btn-wrapper">
                    <button class="tutor-setup-skip"><?php _e('Skip This Step', 'tutor'); ?></button>
                </div>
                <div class="tutor-setup-btn-wrapper">
                    <button class="tutor-setup-next next animated-btn">
                        <!-- <?php _e('Next', 'tutor'); ?> -->
                            <svg xmlns="http://www.w3.org/2000/svg" id="next-arrow-1" width="17" height="12">
                                <path fill="#fff" stroke="" d="M11.492.65a.603.603 0 0 0-.86 0 .607.607 0 0 0 0 .85l4.361 4.362H.603A.6.6 0 0 0 0 6.465c0 .335.267.61.602.61h14.391l-4.36 4.353a.617.617 0 0 0 0 .86c.24.241.627.241.86 0l5.393-5.393a.592.592 0 0 0 0-.852L11.492.65z"/>
                            </svg>
                            <span><?php _e('Next', 'tutor'); ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" id="next-arrow-2" width="17" height="12">
                                <path fill="#fff" stroke="" d="M11.492.65a.603.603 0 0 0-.86 0 .607.607 0 0 0 0 .85l4.361 4.362H.603A.6.6 0 0 0 0 6.465c0 .335.267.61.602.61h14.391l-4.36 4.353a.617.617 0 0 0 0 .86c.24.241.627.241.86 0l5.393-5.393a.592.592 0 0 0 0-.852L11.492.65z"/>
                            </svg>
                        </button>
                    </button>
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
