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

$user_name = sanitize_text_field(get_query_var('dozent_student_username'));
$sub_page = sanitize_text_field(get_query_var('profile_sub_page'));
$get_user = dozent_utils()->get_user_by_login($user_name);
$user_id = $get_user->ID;
?>

<?php do_action('dozent_student/before/wrap'); ?>

    <div <?php dozent_post_class('dozent-full-width-student-profile'); ?>>

        <div class="dozent-student-dashboard-leadinfo">
            <div class="dozent-container">
				<?php
				$user_data = get_userdata($user_id);
				$roles = wp_roles();
				?>

                <div class="dozent-row">
                    <div class="dozent-col-auto">
                        <div class="dozent-dashboard-avater">
							<?php
							echo dozent_utils()->get_dozent_avatar($user_id, 'thumbnail'); ?>
                        </div>
                    </div>
                    <div class="dozent-col">
                        <div class="dozent-dashboard-student-info">
                            <h3 class="dozent-student-name">
                                <a href="<?php echo dozent_utils()->profile_url($user_id); ?>"> <?php echo esc_html($user_data->display_name); ?> </a>

                            </h3>
                            <ul class="dozent-dashboard-user-role">
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
							$designation = get_user_meta($user_id, '_dozent_profile_job_title', true);
							if ($designation){
								echo '<h4 class="dozent-designation">'.$designation.'</h4>';
							}
							?>
                        </div>
                        <div class="dozent-dashboard-student-meta">
                            <?php
                            $completed_course = dozent_utils()->get_completed_courses_ids_by_user($user_id);
                            $count_reviews = dozent_utils()->count_reviews_wrote_by_user($user_id);
                            ?>
                            <ul>
                                <li>
                                    <h4><?php echo count($completed_course); ?></h4>
                                    <span><?php _e('Course completed', 'dozent'); ?></span>
                                </li>
                                <li>
                                    <h4><?php echo $count_reviews; ?></h4>
                                    <span><?php _e('Reviews Wrote', 'dozent'); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="dozent-seperator"></div>
            </div>
        </div>

        <div <?php dozent_post_class('dozent-dashboard-student'); ?>>

            <div class="dozent-container">
                <div class="dozent-row">
                    <div class="dozent-col-3">
                        <?php
                        $permalinks = dozent_utils()->user_profile_permalinks();
                        $student_profile_url = dozent_utils()->profile_url($user_id);
                        ?>
                        <ul class="dozent-dashboard-permalinks">
                            <li><a href="<?php echo dozent_utils()->profile_url($user_id); ?>"><?php _e('Bio', 'dozent'); ?></a></li>
                            <?php
                            if (is_array($permalinks) && count($permalinks)){
                                foreach ($permalinks as $permalink_key => $permalink){
                                    echo '<li><a href="'.trailingslashit($student_profile_url).$permalink_key.'"> '.$permalink.' </a> </li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="dozent-col-9">

                        <?php
                        if ($sub_page){
                            dozent_load_template('profile.'.$sub_page);
                        }else{
	                        dozent_load_template('profile.bio');
                        }

                        ?>

                    </div> <!-- .dozent-col-8 -->
                </div>

            </div> <!-- .dozent-row -->
        </div> <!-- .dozent-container -->
    </div>

<?php do_action('dozent_student/after/wrap'); ?>

<?php
get_footer();
