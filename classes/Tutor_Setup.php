<?php
defined( 'ABSPATH' ) || exit;

if ( !class_exists('Tutor_Setup') ) {
    class Tutor_Setup {


        public function __construct() {
            //if ( get_option( 'tutor_enable_setup_wizard', '' ) ) {
                add_action( 'admin_menu', array( $this, 'admin_menus' ) );
                add_action( 'admin_init', array( $this, 'setup_wizard' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            // }
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
            foreach ($field_arr as $key => $field) {
                $html .= '<div class="tutor-setting">';
                    switch ($field['type']) {
                        case 'switch':
                            $html .= isset( $field['lable'] ) ? '<div class="title">'.$field['lable'].'</div>' : '';
                            $html .= isset( $field['desc'] ) ? '<div class="content">'.$field['desc'].'</div>' : '';
                            $html .= '<div class="settings">';
                                $html .= '<input type="checkbox" name="'.$key.'" value="1" />';
                            $html .= '</div>';
                            break;

                        case 'text':
                            $html .= isset( $field['lable'] ) ? '<div class="title">'.$field['lable'].'</div>' : '';
                            $html .= isset( $field['desc'] ) ? '<div class="content">'.$field['desc'].'</div>' : '';
                            $html .= '<div class="settings">';
                                $html .= '<input type="text" name="'.$key.'" value="" />';
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
            $field_arr = array(
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
            ?>
            <div class="tutor-wrapper-boarding active">
                <div><?php _e('Hello, Welcome Tutor LMS.', 'tutor'); ?></div>
                <div>
                    <ul class="tutor-setup-title">
                        <li><?php _e('General', 'tutor'); ?></li>
                        <li><?php _e('Course', 'tutor'); ?></li>
                        <li><?php _e('Finish', 'tutor'); ?></li>
                    </ul>
                    <ul class="tutor-setup-content">
                        <li>
                            <div class="tutor-setup-content-heading">
                                <div><?php _e('General Settings', 'tutor'); ?></div>
                                <div><?php _e('1 / 8 Step Completed', 'tutor'); ?></div>
                                <div><?php _e('Reset Default', 'tutor'); ?></div>
                            </div>
                            <div class="tutor-setup-content-heading">
                                <?php $this->tutor_setup_generator($field_arr); ?>
                            </div>
                        </li>
                        <li></li>
                        <li></li>
                    </ul>
        
        <!--
                General
                General Settigns 
                    Public Profile
                    Tutor LMS comes with a revolutionary drag & drop system to create resourceful courses. 

                    Spotlight Mode
                    Are you an individual and want to spread knowledge online? Tutor is for you! Live Demo

                    YouTube Player
                    Tutor LMS comes with a revolutionary drag & drop system to create resourceful courses. 

                    Vimeo Player
                    Are you an individual and want to spread knowledge online? Tutor is for you!

                    Lesson permalink
                    http://tutor.test/course/sample-course/lesson/sample-lesson/


                Course
                Course Settigns 
                    Show Instructor Bio
                    Tutor LMS comes with a revolutionary drag & drop system to create resourceful courses. 

                    Question and Anwser
                    Are you an individual and want to spread knowledge online? Tutor is for you! Live Demo

                    Courses Per Row

                    Courses Per Page



                Quiz
                Quiz Settings 
                    Time Limit
                    When Time Expires
                    Attempts allowed
                    Final Grade Calculation

                Instructor
                Profile
                Payment
                Email Notification
                Finish
        -->
                    
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
            <div class="tutor-wrapper-type">
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
        }


    }
    new Tutor_Setup();
}
