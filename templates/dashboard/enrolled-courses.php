<?php
    /**
     * @package TutorLMS/Templates
     * @version 1.4.3
     */

    $page_tabs = array(
        'enrolled-courses' => __('All Courses', 'tutor'),
        'enrolled-courses/active-courses' => __('Active Courses', 'tutor'),
        'enrolled-courses/completed-courses' => __('Completed Courses', 'tutor')
    );

    // Default tab set
    (!isset($active_tab, $page_tabs[$active_tab])) ? $active_tab='enrolled-courses' : 0;

    // Get course list
    $courses_list_array = array(
        'enrolled-courses' => tutor_utils()->get_enrolled_courses_by_user( get_current_user_id(), array( 'private', 'publish' ) ),
        'enrolled-courses/active-courses' =>  tutor_utils()->get_active_courses_by_user(),
        'enrolled-courses/completed-courses' => tutor_utils()->get_courses_by_user(),
    );

    // Prepare course list based on page tab
    $courses_list = $courses_list_array[$active_tab];
?>

<h3><?php esc_html_e($page_tabs[$active_tab]); ?></h3>
<div class="tutor-dashboard-content-inner enrolled-courses">
    <div class="tutor-dashboard-inline-links">
        <ul>
            <?php 
                foreach($page_tabs as $slug => $tab) {
                    ?>
                    <li class="<?php echo $slug==$active_tab ? 'active' : '' ?>">
                        <a href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( $slug ) ); ?>"> 
                            <?php 
                                echo esc_html($tab); 

                                $course_count = ($courses_list_array[$slug] && $courses_list_array[$slug]->have_posts()) ? count($courses_list_array[$slug]->posts) : 0;
                                if($course_count) {
                                    echo esc_html('&nbsp;('.$course_count.')');
                                }
                            ?>
                        </a> 
                    </li>
                    <?php
                }
            ?>
        </ul>
    </div>

    <div class="tutor-course-listing-grid tutor-course-listing-grid-3">
        <?php
            // Loop through all the courses
            if ( $courses_list && $courses_list->have_posts() ) {
                while ( $courses_list->have_posts() ) {

                    $courses_list->the_post();
                    $avg_rating = tutor_utils()->get_course_rating()->rating_avg;
                    $tutor_course_img = get_tutor_course_thumbnail_src();
                    
                    /**
                     * wp 5.7.1 showing plain permalink for private post
                     * since tutor do not work with plain permalink
                     * url is set to post_type/slug (courses/course-slug)
                     * @since 1.8.10
                    */
                        
                    $post = $courses_list->post;
                    $custom_url = home_url( $post->post_type.'/'.$post->post_name );
                    
                    /**
                     * @hook tutor_course/archive/before_loop_course
                     * @type action
                     * Usage Idea, you may keep a loop within a wrap, such as bootstrap col
                     */
                    do_action( 'tutor_course/archive/before_loop_course' );
        
                    tutor_load_template( 'loop.course' );
        
                    /**
                     * @hook tutor_course/archive/after_loop_course
                     * @type action
                     * Usage Idea, If you start any div before course loop, you can end it here, such as </div>
                     */
                    do_action( 'tutor_course/archive/after_loop_course' );
                }
                
                wp_reset_postdata();

            } else {
                ?>
                    <div class='tutor-mycourse-wrap'>
                        <div class='tutor-mycourse-content'>
                            <?php esc_html_e( 'You haven\'t purchased any course', 'tutor' ); ?>
                        </div>
                    </div>
                <?php
            }
	    ?>
    </div>
</div>
