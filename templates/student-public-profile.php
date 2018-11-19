<?php
/**
 * Template for displaying student Public Profile
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

get_header();

$user_name = sanitize_text_field(get_query_var('tutor_student_username'));
$sub_page = sanitize_text_field(get_query_var('profile_sub_page'));
$get_user = tutor_utils()->get_user_by_login($user_name);
$user_id = $get_user->ID;
?>

<?php do_action('tutor_student/before/wrap'); ?>

    <div <?php tutor_post_class('tutor-full-width-student-profile'); ?>>

        <div class="tutor-student-dashboard-leadinfo">
            <div class="tutor-container">
				<?php
				$user_data = get_userdata($user_id);
				$roles = wp_roles();
				?>

                <div class="tutor-row">
                    <div class="tutor-col-auto">
                        <div class="tutor-dashboard-avater">
							<?php
							echo tutor_utils()->get_tutor_avatar($user_id, 'thumbnail'); ?>
                        </div>
                    </div>
                    <div class="tutor-col">
                        <div class="tutor-dashboard-student-info">
                            <h3 class="tutor-student-name">
                                <a href="<?php echo tutor_utils()->profile_url($user_id); ?>"> <?php echo esc_html($user_data->display_name); ?> </a>

                            </h3>
                            <ul class="tutor-dashboard-user-role">
								<?php
								if (is_array($user_data->roles) && count($user_data->roles)){
									foreach ($user_data->roles as $role){
										if ( ! empty($roles->roles[$role]['name'])){
											echo "<li>{$roles->roles[$role]['name']}</li>";
										}
									}
								}
								?>
                            </ul>
							<?php
							$designation = get_user_meta($user_id, '_tutor_profile_job_title', true);
							if ($designation){
								echo '<h4 class="tutor-designation">'.$designation.'</h4>';
							}
							?>
                        </div>
                        <div class="tutor-dashboard-student-meta">
                            <?php
                            $completed_course = tutor_utils()->get_completed_courses_ids_by_user($user_id);
                            $count_reviews = tutor_utils()->count_reviews_wrote_by_user($user_id);
                            ?>
                            <ul>
                                <li>
                                    <h4><?php echo count($completed_course); ?></h4>
                                    <span><?php _e('Course completed', 'tutor'); ?></span>
                                </li>
                                <li>
                                    <h4><?php echo $count_reviews; ?></h4>
                                    <span><?php _e('Reviews Wrote', 'tutor'); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="tutor-seperator"></div>
            </div>
        </div>

        <div <?php tutor_post_class('tutor-dashboard-student'); ?>>

            <div class="tutor-container">
                <div class="tutor-row">
                    <div class="tutor-col-3">
                        <?php
                        $permalinks = tutor_utils()->user_profile_permalinks();
                        $student_profile_url = tutor_utils()->profile_url($user_id);
                        ?>
                        <ul class="tutor-dashboard-permalinks">
                            <li><a href="<?php echo tutor_utils()->profile_url($user_id); ?>"><?php _e('Bio', 'tutor'); ?></a></li>
                            <?php
                            if (is_array($permalinks) && count($permalinks)){
                                foreach ($permalinks as $permalink_key => $permalink){
                                    echo '<li><a href="'.trailingslashit($student_profile_url).$permalink_key.'"> '.$permalink.' </a> </li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="tutor-col-9">

                        <?php
                        if ($sub_page){
                            tutor_load_template('profile.'.$sub_page);
                        }else{
	                        tutor_load_template('profile.bio');
                        }

                        ?>

                    </div> <!-- .tutor-col-8 -->
                </div>

            </div> <!-- .tutor-row -->
        </div> <!-- .tutor-container -->
    </div>

<?php do_action('tutor_student/after/wrap'); ?>

<?php
get_footer();
