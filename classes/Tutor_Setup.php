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
                foreach ($section['attr'] as $k => $val) {
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

            // General Settings
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


            // Payment Settings
            $payments = (array) maybe_unserialize(get_option('tutor_withdraw_options'));
            $payments_data = array( 'bank_transfer_withdraw', 'echeck_withdraw', 'paypal_withdraw' );
            foreach ($payments_data as $key) {
                if(isset($_POST[$key])){
                    $payments[$key]['enabled'] = 1;
                } else {
                    if($key == 'bank_transfer_withdraw') {
                        unset($payments[$key]['enabled']);
                    }else{
                        unset($payments[$key]);
                    }
                }
            }
            update_option('tutor_withdraw_options', $payments);


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
                    $this->tutor_setup_wizard_boarding();
                    $this->tutor_setup_wizard_type();
                    $this->tutor_setup_wizard_settings();
                    $this->tutor_setup_wizard_footer();
                    exit;
                }
            }
        }

        public function tutor_setup_generator() {
            
            $i = 1;
            $html = '';
            $options = (array) maybe_unserialize(get_option('tutor_option'));
            $field_arr = $this->tutor_setup_attributes();

            $down_desc_fields = array('rows', 'slider', 'text', 'radio', 'dropdown', 'range', 'payments');
            $full_width_fields = array('rows', 'slider', 'radio', 'range', 'payments');

            foreach ($field_arr as $key_parent => $field_parent) {

                $html .= '<li class="'.($i==1 ? "active" : "").'">';
                    $html .= '<div class="tutor-setup-content-heading heading">';
                        $html .= '<div>'.$field_parent['lable'].'</div>';
                        $html .= '<div><strong>'.$i.'</strong> / '.count($field_arr).' '.__('Step Completed', 'tutor').'</div>';
                        $html .= '<div class="tutor-reset-section">'.__('Reset Default', 'tutor').'</div>';
                    $html .= '</div>';
                    $html .= '<div class="tutor-setup-content-heading body">';

                        foreach ($field_parent['attr'] as $key => $field) {

                            $html .= '<div class="tutor-setting'.(in_array( $field['type'], $full_width_fields ) ? " course-setting-wrapper" : "").'">';
                                $html .= isset( $field['lable'] ) ? '<div class="title">'.$field['lable'] : '';
                                $html .= isset( $field['tooltip'] ) ? '<span id="tooltip-btn" class="tooltip-btn" data-tooltip="'.$field['tooltip'].'"><span></span></span>' : '';
                                $html .= isset( $field['lable'] ) ? '</div>' : '';

                                if(!in_array($field['type'], $down_desc_fields)) {
                                    $html .= isset( $field['desc'] ) ? '<div class="content">'.$field['desc'].'</div>' : '';
                                }

                                $html .= '<div class="settings">';

                                    switch ($field['type']) {

                                        case 'switch':
                                            $html .= '<label for="'.$key.'" class="switch-label input-switch-label">';
                                                $html .= '<span class="label-off">OFF</span>';
                                                $html .= '<div class="switchbox-wrapper">';
                                                        $html .= '<input id="'.$key.'" class="input-switchbox" type="checkbox" name="'.$key.'" value="1" '.(isset($options[$key]) && $options[$key] ? 'checked' : '').'/>';
                                                        $html .= '<span class="switchbox-icon"></span>';
                                                $html .= '</div>';
                                                $html .= '<span class="label-on">ON</span>';
                                            $html .= '</label>';
                                        break;
                    
                                        case 'text':
                                            $html .= '<input type="text" name="'.$key.'" class="lesson-permalink" value="'.(isset($options[$key]) ? $options[$key] : '').'" />';    
                                        break;
                    
                                        case 'rows':
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
                                        break;
                                        
                                        case 'radio':
                                            if ( isset($field['options']) ) {
                                                foreach ($field['options'] as $k => $val) {
                                                    $html .= '<label for="'.$key.$k.'" class="time-expires"><input type="radio" id="'.$key.$k.'" name="'.$key.'" value="'.$k.'" '.( isset($options[$key]) && $options[$key] == $k ? 'checked' : '' ).' /> '.'<span class="radio-icon"></span>';
                                                    $html .= $val.'</label>';
                                                }
                                            }
                                        break;
                                        
                                        case 'slider':
                                            $html .= '<div class="limit-slider">';
                                                if (isset($field['time'])) {
                                                    $html .= '<input type="range" name="'.$key.'[value]" min="'.(isset($field['min']) ? $field['min'] : 0).'" max="'.(isset($field['max']) ? $field['max'] : 60).'" step="1" value="'.(isset($options[$key]['value']) ? $options[$key]['value'] : '').'"  class="range-input"/>';
                                                    $html .= '<input type="hidden" name="'.$key.'[time]" value="'.(isset($options[$key]['time']) ? $options[$key]['time'] : 'minutes').'"  class="range-input"/>';
                                                    $html .= '<span class=""><span class="range-value">'.(isset($options[$key]['value']) ? $options[$key]['value'] : '').'</span>';
                                                    $html .= isset($options[$key]['time']) ? $options[$key]['time'] : '';
                                                    $html .= '</span>';
                                                } else {
                                                    $html .= '<input type="range" name="'.$key.'" min="'.(isset($field['min']) ? $field['min'] : "").'" max="'.(isset($field['max']) ? $field['max'] : "" ).'" step="1" value="'.(isset($options[$key]) ? $options[$key] : '').'"  class="range-input"/>';
                                                    $html .= ' <strong class="range-value">'.(isset($options[$key]) ? $options[$key] : '').'</strong>';
                                                }
                                            $html .= '</div>';
                                        break;

                                        case 'dropdown':
                                            $html .= '<div class="grade-calculation"><div class="select-box"><div class="options-container">';
                                                if (isset($field['options'])) {
                                                    foreach ($field['options'] as $val) {
                                                        $html .= '<div class="option '.((isset($options[$key]) && $val['value'] == $options[$key]) ? 'selected' : '').'">';
                                                            $html .= '<input type="radio" class="radio" id="'.$val['value'].'" name="'.$key.'"/>';
                                                            $html .= '<label for="'.$val['value'].'">';
                                                                $html .= '<h3>'.$val['title'].'</h3>';
                                                                $html .= '<h5>'.$val['desc'].'</h5>';
                                                            $html .= '</label>';
                                                        $html .= '</div>';
                                                    }
                                                }
                                            $html .= '</div>';
                                            $html .= '<div class="selected">';
                                                $html .= '<h3>'.$val['title'].'</h3>';
                                                $html .= '<h5>'.$val['desc'].'</h5>';    
                                            $html .= '</div></div></div>';
                                        break;

                                        case 'payments':
                                            $html .= '<div class="checkbox-wrapper column-3">';
                                                $html .= '<div class="payment-setting">';
                                                    $html .= '<label for="payment-1" class="label">';
                                                        $html .= '<div>';
                                                            $html .= '<input type="checkbox" checked name="bank_transfer_withdraw" id="payment-1" class="checkbox payment">';
                                                            $html .= '<span class="check-icon round"></span>';
                                                        $html .= '</div>';
                                                        $html .= '<div>';
                                                            $html .= '<img src="'.tutor()->url . 'assets/images/payment-icon-1-min.png" alt="Payment Check">';
                                                            $html .= '<h4>'.__('Bank','tutor').'</h4>';
                                                        $html .= '</div>';
                                                    $html .= '</label>';
                                                $html .= '</div>';
                                                $html .= '<div class="payment-setting">';
                                                    $html .= '<label for="payment-2" class="label">';
                                                        $html .= '<div>';
                                                            $html .= '<input type="checkbox" name="echeck_withdraw" id="payment-2" class="checkbox payment">';
                                                            $html .= '<span class="check-icon round"></span>';
                                                        $html .= '</div>';
                                                        $html .= '<div>';
                                                            $html .= '<img src="'.tutor()->url . 'assets/images/payment-icon-2-min.png" alt="Payment echeck">';
                                                            $html .= '<h4>'.__('Check','tutor').'</h4>';
                                                        $html .= '</div>';
                                                    $html .= '</label>';
                                                $html .= '</div>';
                                                $html .= '<div class="payment-setting">';
                                                    $html .= '<label for="payment-3" class="label">';
                                                        $html .= '<div>';
                                                            $html .= '<input type="checkbox" name="paypal_withdraw" id="payment-3" class="checkbox payment">';
                                                            $html .= '<span class="check-icon round"></span>';
                                                        $html .= '</div>';
                                                        $html .= '<div>';
                                                            $html .= '<img src="'.tutor()->url . 'assets/images/payment-icon-3-min.png" alt="Payment Paypal">';
                                                            $html .= '<h4>'.__('Paypal','tutor').'</h4>';
                                                        $html .= '</div>';
                                                    $html .= '</label>';
                                                $html .= '</div>';
                                            $html .= '</div>';
                                        break;

                                        case 'range':
                                            $html .= '<div class="limit-slider column-1">';
                                                $html .= '<div>';
                                                    $html .= '<input type="range" min="0" max="100" step="1" value="'.$options["earning_instructor_commission"].'" class="range-input double-range-slider" name=""/>';
                                                $html .= '</div>';
                                                $html .= '<div class="commision-data">';
                                                    $html .= '<div class="data">';
                                                        $html .= '<h4 class="range-value-1">'.$options["earning_instructor_commission"].'%</h4>';
                                                        $html .= '<h5>'.__('Instructor', 'tutor').'</h5>';
                                                        $html .= '<input type="hidden" min="0" max="100" step="1" value="'.$options["earning_instructor_commission"].'" class="range-value-data-1 range-input" name="earning_instructor_commission"/>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="data">';
                                                        $html .= '<h4 class="range-value-2">'.$options["earning_admin_commission"].'%</h4>';
                                                        $html .= '<h5>'.__('Admin', 'tutor').'</h5>';
                                                        $html .= '<input type="hidden" min="0" max="100" step="1" value="'.$options["earning_admin_commission"].'" class="range-value-data-2 range-input" name="earning_admin_commission"/>';
                                                    $html .= '</div>';
                                                $html .= '</div>';
                                            $html .= '</div> ';
                                        break;

                                        case 'checkbox':
                                            $html .= '<div class="checkbox-wrapper column-2">';
                                            if (isset($field['options'])) {
                                                foreach ($field['options'] as $k => $val) {
                                                    $html .= '<div class="email-notification">';
                                                        $html .= '<label for="'.$key.$k.'" class="label">';
                                                            $html .= '<div>';
                                                                $html .= '<input type="checkbox" value="'.$k.'" '.( isset($options[$key]) && $options[$key] == $k ? 'checked' : '' ).' name="'.$key.'" id="'.$key.$k.'" class="checkbox" />';
                                                                $html .= '<span class="check-icon square"></span>';
                                                            $html .= '</div>';
                                                            $html .= '<div>';
                                                                $html .= '<h4>'.$val.'</h4>';
                                                            $html .= '</div>';
                                                        $html .= '</label>';
                                                    $html .= '</div>';
                                                }
                                            }
                                            $html .= '</div>';
                                        break;
                                        
                                        default:
                                            # code...
                                            break;
                                    }

                                if (in_array($field['type'], $down_desc_fields)) {
                                    $html .= isset($field['desc']) ? '<div class="content">'.$field['desc'].'</div>' : '';
                                }
                            $html .= '</div>';
                        $html .= '</div>';

                        }
                    $html .= '</div>';
                    $html .= $this->tutor_setup_wizard_action();
                $html .= '</li>';
                $i++;
            }

            echo $html;
        }


        public function tutor_setup_attributes() {
            $general_fields = array(

                'general' => array(
                    'lable' => __('General Settings', 'tutor'),
                    'attr' => array(
                        'enable_public_profile' => array(
                            'type' => 'switch',
                            'lable' => __('Public Profile', 'tutor'),
                            'desc' => __('Allow users to have a public profile to showcase awards and completed courses.', 'tutor'),
                        ),
                        'enable_spotlight_mode' => array(
                            'type' => 'switch',
                            'lable' => __('Spotlight Mode', 'tutor'),
                            'desc' => __('Create a focused learning environment. Block out all the distractions around your course content.', 'tutor'),
                        ),
                        'disable_default_player_youtube' => array(
                            'type' => 'switch',
                            'lable' => __('YouTube Player', 'tutor'),
                            'desc' => __('Toggle OFF to use the default YouTube player.', 'tutor'),
                        ),
                        'disable_default_player_vimeo' => array(
                            'type' => 'switch',
                            'lable' => __('Vimeo Player', 'tutor'),
                            'desc' => __('Toggle OFF to use the default Vimeo player.', 'tutor'),
                        ),
                        'lesson_permalink_base' => array(
                            'type' => 'text',
                            'lable' => __('Lesson Permalink', 'tutor'),
                            'desc' => 'Pick the URL prefix you want for your lessons.',
                        )
                    )
                ),


                'course' => array(
                    'lable' => __('General Settings', 'tutor'),
                    'attr' => array(
                        'display_course_instructors' => array(
                            'type' => 'switch',
                            'lable' => __('Show Instructor Bio', 'tutor'),
                            'desc' => __('Let the students know the instructor(s). Display their credentials, professional experience, and more.', 'tutor'),
                        ),
                        'enable_q_and_a_on_course' => array(
                            'type' => 'switch',
                            'lable' => __('Question and Anwser', 'tutor'),
                            'desc' => __('Allows a Q&A forum on each course.', 'tutor'),
                        ),
                        'courses_col_per_row' => array(
                            'type' => 'rows',
                            'lable' => __('Courses Per Row', 'tutor'),
                            'tooltip' => __('How many courses per row on the archive pages.', 'tutor')
                        ),
                        'courses_per_page' => array(
                            'type' => 'slider',
                            'lable' => __('Courses Per Page', 'tutor'),
                            'tooltip' => __('How many courses per page on the archive pages.', 'tutor'),
                        )
                    )
                ),


                'quiz' => array(
                    'lable' => __('Quiz Settings', 'tutor'),
                    'attr' => array(
                        'quiz_time_limit' => array(
                            'type' => 'slider',
                            'time' => true,
                            'lable' => __('Time Limit', 'tutor'),
                            'tooltip' => __('How much time to complete a quiz?', 'tutor'),
                        ),
                        'quiz_when_time_expires' => array(
                            'type' => 'radio',
                            'lable' => __('When Time Expires', 'tutor'),
                            'options' => array(
                                'autosubmit' => __('The current quiz answers are submitted automatically.', 'tutor'),
                                'graceperiod' => __('The current quiz answers are submitted by students.', 'tutor'),
                                'autoabandon' => __('Attempts must be submitted before time expires, otherwise they will not be counted', 'tutor'),
                            ),
                            'tooltip' => __('What message to display when the quiz time expires?', 'tutor'),
                        ),
                        'quiz_attempts_allowed' => array(
                            'type' => 'slider',
                            'lable' => __('Attempts Allowed', 'tutor'),
                            'tooltip' => __('How many attempts does a student get to pass a quiz?', 'tutor'),
                        ),
                        'quiz_grade_method' => array(
                            'type' => 'dropdown',
                            'lable' => __('Final Grade Calculation', 'tutor'),
                            'options' => array(
                                array(
                                    'title' => __('Highest Grade', 'tutor'),
                                    'desc' => __('Pick the student’s best grade', 'tutor'),
                                    'value' => 'highest_grade',
                                ),
                                array(
                                    'title' => __('Average Grade', 'tutor'),
                                    'desc' => __('Use the average score', 'tutor'),
                                    'value' => 'average_grade',
                                ),
                                array(
                                    'title' => __('First Attempt', 'tutor'),
                                    'desc' => __('Pick the first attempt', 'tutor'),
                                    'value' => 'first_attempt',
                                ),
                                array(
                                    'title' => __('Last Attempt', 'tutor'),
                                    'desc' => __('Pick the most recent attempt', 'tutor'),
                                    'value' => 'last_attempt',
                                ),          
                            ),
                            'tooltip' => __('When you allow multiple quiz attempts, which grade do you want to count?', 'tutor'),
                        )
                    )
                ),


                'instructor' => array(
                    'lable' => __('Instructor Settings', 'tutor'),
                    'attr' => array(
                        'enable_become_instructor_btn' => array(
                            'type' => 'switch',
                            'lable' => __('New Signup', 'tutor'),
                            'desc' => __('Choose between open and closed instructor signup. If you’re creating a course marketplace, instructor signup should be open.', 'tutor'),
                        ),
                        'instructor_can_publish_course' => array(
                            'type' => 'switch',
                            'lable' => __('Earning', 'tutor'),
                            'desc' => __('Enable earning for instructors?', 'tutor'),
                        ),
                    )
                ),


                'profile' => array(
                    'lable' => __('Profile Settings', 'tutor'),
                    'attr' => array(
                        'students_own_review_show_at_profile' => array(
                            'type' => 'switch',
                            'lable' => __('Show Reviews on Profile', 'tutor'),
                            'desc' => __('Choose whether you want to show students’ ratings and reviews.', 'tutor'),
                        ),
                        'show_courses_completed_by_student' => array(
                            'type' => 'switch',
                            'lable' => __('Show Completed Courses', 'tutor'),
                            'desc' => __('Choose whether you want to display a list of a student’s completed courses.', 'tutor'),
                        )
                    )
                ),


                'payment' => array(
                    'lable' => __('Payment Settings ', 'tutor'),
                    'attr' => array(
                        'enable_guest_course_cart' => array(
                            'type' => 'switch',
                            'lable' => __('Guest Checkout', 'tutor'),
                            'desc' => __('Allow users to buy and consume content without logging in.', 'tutor')
                        ),
                        'commission_split' => array(
                            'type' => 'range',
                            'lable' => __('Commission Rate', 'tutor'),
                            'tooltip' => __('Control revenue sharing between admin and instructor.', 'tutor')
                        ),
                        'earning_instructor_commission' => array(
                            'type' => 'commission',
                        ),
                        'earning_admin_commission' => array(
                            'type' => 'commission',
                        ),
                        'withdraw_split' => array(
                            'type' => 'payments',
                            'lable' => __('Payment Withdrawal Method', 'tutor'),
                            'desc' => __('Choose your preferred withdrawal method from the options.', 'tutor')
                        ),
          
                        
                    )
                ),


            );

            return $general_fields;
        }
        
        public function tutor_setup_wizard_settings() {

            $options = (array) maybe_unserialize(get_option('tutor_option'));

            ?>
            <div class="tutor-wizard-container">
                <div class="tutor-wrapper-boarding tutor-setup-wizard-settings">
                    <div class="tutor-setup-wrapper">
                        <ul class="tutor-setup-title">
                            <li class="active"><?php _e('General', 'tutor'); ?></li>
                            <li><?php _e('Course', 'tutor'); ?></li>
                            <li><?php _e('Quiz', 'tutor'); ?></li>
                            <li><?php _e('Instructor', 'tutor'); ?></li>
                            <li><?php _e('Profile', 'tutor'); ?></li>
                            <li><?php _e('Payment', 'tutor'); ?></li>
                            <li><?php _e('Finish', 'tutor'); ?></li>
                        </ul>


                        <form id="tutor-setup-form" method="post">
                            <input type="hidden" name="action" value="setup_action">
                            <input type="hidden" name="enable_course_marketplace" class="enable_course_marketplace_data" value="">
                            <ul class="tutor-setup-content">
                                <?php $this->tutor_setup_generator(); ?>
                                <li>
                                    <div class="tutor-setup-content-heading greetings">
                                        <div class="header">
                                            <img src="<?php echo tutor()->url . 'assets/images/greeting-img.jpg'; ?>" alt="greeting">
                                        </div>
                                        <div class="content">
                                            <h2><?php _e('Congratulations, you’re all set!', 'tutor'); ?></h2>
                                            <p><?php _e( 'Tutor LMS is up and running on your website! If you really want to become a Tutor LMS genius, read our <a href="https://www.themeum.com/docs/tutor-introduction/">documentation</a> that covers everything!', 'tutor' ); ?></p>
                                            <p><?php _e( 'If you need further assistance, please don’t hesitate to contact us via our <a href="https://www.themeum.com/contact-us/">contact form.</a>', 'tutor' ); ?></p>
                                        </div>
                                        <div class="tutor-setup-content-footer footer">
                                            <button class="tutor-redirect primary-btn" data-url="<?php echo admin_url('edit.php?post_type=courses'); ?>"><?php _e('Finish', 'tutor'); ?></button>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </form>


                    </div>
                </div>
            </div>
            <?php
        }


        public function tutor_setup_wizard_action() {
            
            $html = '<div class="tutor-setup-content-footer footer">';
                $html .= '<div class="tutor-setup-btn-wrapper">';
                    $html .= '<button class="tutor-setup-previous previous animated-btn">';
                        $html .= '<svg xmlns="http://www.w3.org/2000/svg" id="prev-arrow-1" width="17" height="12">';
                            $html .= '<path fill="#fff" stroke="" d="M11.492.65a.603.603 0 0 0-.86 0 .607.607 0 0 0 0 .85l4.361 4.362H.603A.6.6 0 0 0 0 6.465c0 .335.267.61.602.61h14.391l-4.36 4.353a.617.617 0 0 0 0 .86c.24.241.627.241.86 0l5.393-5.393a.592.592 0 0 0 0-.852L11.492.65z"/>';
                        $html .= '</svg>';
                        $html .= '<span>'.__('Previous', 'tutor').'</span>';
                        $html .= '<svg xmlns="http://www.w3.org/2000/svg" id="prev-arrow-2" width="17" height="12">';
                            $html .= '<path fill="#fff" stroke="" d="M11.492.65a.603.603 0 0 0-.86 0 .607.607 0 0 0 0 .85l4.361 4.362H.603A.6.6 0 0 0 0 6.465c0 .335.267.61.602.61h14.391l-4.36 4.353a.617.617 0 0 0 0 .86c.24.241.627.241.86 0l5.393-5.393a.592.592 0 0 0 0-.852L11.492.65z"/>';
                        $html .= '</svg>';
                    $html .= '</button>';
                $html .= '</div>';
                $html .= '<div class="tutor-setup-btn-wrapper">';
                    $html .= '<button class="tutor-setup-skip">'.__('Skip This Step', 'tutor').'</button>';
                $html .= '</div>';
                $html .= '<div class="tutor-setup-btn-wrapper">';
                    $html .= '<button class="tutor-setup-next next animated-btn">';
                        $html .= '<svg xmlns="http://www.w3.org/2000/svg" id="next-arrow-1" width="17" height="12">';
                            $html .= '<path fill="#fff" stroke="" d="M11.492.65a.603.603 0 0 0-.86 0 .607.607 0 0 0 0 .85l4.361 4.362H.603A.6.6 0 0 0 0 6.465c0 .335.267.61.602.61h14.391l-4.36 4.353a.617.617 0 0 0 0 .86c.24.241.627.241.86 0l5.393-5.393a.592.592 0 0 0 0-.852L11.492.65z"/>';
                        $html .= '</svg>';
                        $html .= '<span>'.__('Next', 'tutor').'</span>';
                        $html .= '<svg xmlns="http://www.w3.org/2000/svg" id="next-arrow-2" width="17" height="12">';
                            $html .= '<path fill="#fff" stroke="" d="M11.492.65a.603.603 0 0 0-.86 0 .607.607 0 0 0 0 .85l4.361 4.362H.603A.6.6 0 0 0 0 6.465c0 .335.267.61.602.61h14.391l-4.36 4.353a.617.617 0 0 0 0 .86c.24.241.627.241.86 0l5.393-5.393a.592.592 0 0 0 0-.852L11.492.65z"/>';
                        $html .= '</svg>';
                    $html .= '</button>';
                $html .= '</div>';
            $html .= '</div>';

            return $html;
        }

        public function tutor_setup_wizard_boarding() {
            global $current_user;
            ?>
            <div class="tutor-wizard-container">
                <div class="tutor-wrapper-boarding tutor-setup-wizard-boarding active">
                    <div class="wizard-boarding-header">
                        <div><img src="<?php echo tutor()->url.'assets/images/tutor-logo.svg'; ?>" /></div>
                        <div><?php printf(__('Hello %s, welcome to Tutor LMS! Thank you for choosing us.', 'tutor'), $current_user->user_login); ?></div>
                    </div>
                    <div class="wizard-boarding-body">
                        <ul class="slider tutor-boarding">
                            <li>
                                <!-- <img src="<?php echo tutor()->url.'assets/images/setup-individual.jpg'; ?>" /> -->
                                <div class="slide-thumb"><img src="https://picsum.photos/540/350" alt="<?php _e('A Powerful, Smart, and Scalable LMS Solution', 'tutor') ?>"/></div>
                                <div class="slide-title"><?php _e('A Powerful, Smart, and Scalable LMS Solution', 'tutor'); ?></div>
                                <div class="slide-subtitle"><?php _e('From individual instructors to vast eLearning platforms, Tutor LMS grows with you to create your ideal vision of an LMS website.', 'tutor'); ?></div>
                            </li>
                            <li>
                                <div class="slide-thumb"><img src="https://picsum.photos/540/350" alt="<?php _e('Extensive Course Builder', 'tutor') ?>"/></div>
                                <div class="slide-title"><?php _e('Extensive Course Builder', 'tutor'); ?></div>
                                <div class="slide-subtitle"><?php _e('Tutor LMS comes with a state-of-the-art frontend course builder. Construct rich and resourceful courses with ease.', 'tutor'); ?></div>
                            </li>
                            <li>
                                <div class="slide-thumb"><img src="https://picsum.photos/540/350" alt="<?php _e('Advanced Quiz Creator', 'tutor'); ?>"/></div>
                                <div class="slide-title"><?php _e('Advanced Quiz Creator', 'tutor'); ?></div>
                                <div class="slide-subtitle"><?php _e('Build interactive quizzes with the vast selection of question types and verify the learning of your students.', 'tutor'); ?></div>
                            </li>
                            <li>
                                <div class="slide-thumb"><img src="https://picsum.photos/540/350" alt="<?php _e('Freedom With eCommerce', 'tutor'); ?>"/></div>
                                <div class="slide-title"><?php _e('Freedom With eCommerce', 'tutor'); ?></div>
                                <div class="slide-subtitle"><?php _e('Select an eCommerce plugin and sell courses any way you like and use any payment gateway you want!', 'tutor'); ?></div>
                            </li>
                            <li>
                                <div class="slide-thumb"><img src="https://picsum.photos/540/350" alt="<?php _e('Reports and Analytics', 'tutor'); ?>"/></div>
                                <div class="slide-title"><?php _e('Reports and Analytics', 'tutor'); ?></div>
                                <div class="slide-subtitle"><?php _e('Track what type of courses sell the most! Gain insights on user purchases, manage reviews and track quiz attempts.', 'tutor'); ?></div>
                            </li>
                        </ul>
                    </div>
                    <div class="wizard-boarding-footer">
                        <div>
                            <button class="tutor-boarding-next next animated-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" id="next-arrow-1" width="17" height="12">
                                    <path fill="#fff" stroke="" d="M11.492.65a.603.603 0 0 0-.86 0 .607.607 0 0 0 0 .85l4.361 4.362H.603A.6.6 0 0 0 0 6.465c0 .335.267.61.602.61h14.391l-4.36 4.353a.617.617 0 0 0 0 .86c.24.241.627.241.86 0l5.393-5.393a.592.592 0 0 0 0-.852L11.492.65z"/>
                                </svg>
                                <span><?php _e('Next', 'tutor'); ?></span>
                                <svg xmlns="http://www.w3.org/2000/svg" id="next-arrow-2" width="17" height="12">
                                    <path fill="#fff" stroke="" d="M11.492.65a.603.603 0 0 0-.86 0 .607.607 0 0 0 0 .85l4.361 4.362H.603A.6.6 0 0 0 0 6.465c0 .335.267.61.602.61h14.391l-4.36 4.353a.617.617 0 0 0 0 .86c.24.241.627.241.86 0l5.393-5.393a.592.592 0 0 0 0-.852L11.492.65z"/>
                                </svg>
                            </button>
                        </div>
                        <div><a class="tutor-boarding-skip" href="#"><?php _e('I already know, Skip it!', 'tutor'); ?></a></div>
                        <div><?php _e('Contact with Live support', 'tutor'); ?></div>
                    </div>
                </div>
            </div>
            <?php
        }

        public function tutor_setup_wizard_type() {
            $course_marketplace = tutor_utils()->get_option('enable_course_marketplace');
            ?>
            <div class="tutor-wizard-container">
                <div class="tutor-wrapper-type tutor-setup-wizard-type">
                    <div class="wizard-type-header">
                        <div class="logo"><img src="<?php echo tutor()->url.'assets/images/tutor-logo.svg'; ?>" /></div>
                        <div class="title"><?php _e('Let’s get the platform up and running', 'tutor'); ?></div>
                        <div class="subtitle"><?php _e('Pick a category for your LMS platform. You can always update this later.', 'tutor'); ?></div>
                    </div>
                    <div class="wizard-type-body">
                        <div class="wizard-type-item">
                            <input id="enable_course_marketplace-0" type="radio" name="enable_course_marketplace_setup" value="0" <?php if(!$course_marketplace){ echo 'checked'; } ?> />
                            <span class="icon"></span>
                            <label for="enable_course_marketplace-0">
                                <img src="<?php echo tutor()->url.'assets/images/single-marketplace.svg'; ?>" />
                                <div class="title"><?php _e( 'Individual', 'tutor' ); ?></div>
                                <div class="subtitle"><?php _e( 'I want to start my solo journey as an educator and spread my knowledge.', 'tutor' ); ?></div>
                            </label>
                        </div>

                        <div class="wizard-type-item">
                            <input id="enable_course_marketplace-1" type="radio" name="enable_course_marketplace_setup" value="1" <?php if($course_marketplace){ echo 'checked'; } ?>/>
                            <span class="icon"></span>
                            <label for="enable_course_marketplace-1">
                                <img src="<?php echo tutor()->url.'assets/images/multiple-marketplace.svg'; ?>" />
                                <div class="title"><?php _e( 'Marketplace', 'tutor' ); ?></div>
                                <div class="subtitle"><?php _e( 'I want to create an eLearning platform to let anyone earn by teaching online.', 'tutor' ); ?></div>
                            </label>
                        </div>
                    </div>

                    <div class="wizard-type-footer">
                        <div>
                            <button class="tutor-type-next primary-btn "><?php _e('Let’s Start', 'tutor'); ?></button>
                        </div>
                        <div>
                            <a href="#" class="tutor-type-skip" class=""><?php _e('Not sure. Let’s go to the next step.', 'tutor'); ?></a>
                        </div>
                    </div>
                </div>
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
            <?php
        }


        public function tutor_setup_wizard_footer() {
                ?>
                </body>
            </html>
            <?php
        }


        public function enqueue_scripts() {
            wp_enqueue_style( 'tutor-setup', tutor()->url . 'assets/css/tutor-setup.css', array(), tutor()->version );
            wp_enqueue_style( 'tutor-slick', tutor()->url . 'assets/packages/slick/slick.css', array(), tutor()->version );
            wp_enqueue_style( 'tutor-slick-theme', tutor()->url . 'assets/packages/slick/slick-theme.css', array(), tutor()->version );
            wp_register_script( 'tutor-slick', tutor()->url . 'assets/packages/slick/slick.min.js', array( 'jquery' ), tutor()->version, true );
            wp_register_script( 'tutor-setup', tutor()->url . 'assets/js/tutor-setup.js', array( 'jquery', 'tutor-slick' ), tutor()->version, true );
            wp_localize_script('tutor-setup', '_tutorobject', array('ajaxurl' => admin_url('admin-ajax.php')));
        }


    }
    new Tutor_Setup();
}
