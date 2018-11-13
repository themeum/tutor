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

$user_name = get_query_var('tutor_student_username');
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
                                <a href="<?php echo tutor_utils()->student_url($user_id); ?>"> <?php echo esc_html($user_data->display_name); ?> </a>

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
                            <ul>
                                <li>
                                    <h4>440</h4>
                                    <span>Course Complete</span>
                                </li>
                                <li>
                                    <h4>440</h4>
                                    <span>Course Complete</span>
                                </li>
                                <li>
                                    <h4>440</h4>
                                    <span>Course Complete</span>
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
                        <ul class="tutor-dashboard-permalinks">
                            <li><a href="#">Bio</a></li>
                            <li><a href="#">Enrolled Course</a></li>
                            <li><a href="#">Review</a></li>
                        </ul>
                    </div>
                    <div class="tutor-col-9">
						<?php
						$profile_bio = get_user_meta($user_id, '_tutor_profile_bio', true);
						if ($profile_bio){
							?>

                            <h3><?php _e('About Me:', 'tutor'); ?></h3>
							<?php echo wpautop($profile_bio) ?>
						<?php } ?>
                    </div> <!-- .tutor-col-8 -->



                </div>

            </div> <!-- .tutor-row -->
        </div> <!-- .tutor-container -->
    </div>

<?php do_action('tutor_student/after/wrap'); ?>

<?php
get_footer();
