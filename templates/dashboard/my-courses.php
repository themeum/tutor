<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$shortcode_arg = isset($GLOBALS['tutor_shortcode_arg']) ? $GLOBALS['tutor_shortcode_arg']['column_per_row'] : null;
$courseCols = $shortcode_arg===null ? tutor_utils()->get_option( 'courses_col_per_row', 4 ) : $shortcode_arg;

?>

<h3><?php _e('My Courses', 'tutor'); ?></h3>

<div class="tutor-dashboard-content-inner my-courses">
    <?php
        $user       = wp_get_current_user();
        $publish_courses_count = count(tutor_utils()->get_courses_by_instructor($user->ID));
        $pending_courses_count = count(tutor_utils()->get_pending_courses_by_instructor($user->ID));
    ?>
    <div class="tutor-dashboard-inline-links">
        <ul>
            <li class="active">
                <a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('my-courses'); ?>"> 
                    <?php _e('Publish', 'tutor'); ?> <?php echo "(".$publish_courses_count.")"; ?>
                </a> 
            </li>
            <li>
                <a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('my-courses/pending-courses'); ?>"> 
                    <?php _e('Pending', 'tutor'); ?> <?php echo "(".$pending_courses_count.")"; ?>
                </a> 
            </li>
        </ul>
    </div>
    <div class="tutor-course-listing tutor-course-listing-grid-<?php echo $courseCols; ?>">
	<?php
	$my_courses = tutor_utils()->get_courses_by_instructor(null, array('publish'));

	if (is_array($my_courses) && count($my_courses)):
		global $post;
		foreach ($my_courses as $post):
			setup_postdata($post);

			$avg_rating = tutor_utils()->get_course_rating()->rating_avg;
            $tutor_course_img = get_tutor_course_thumbnail_src();
			?>

            <div id="tutor-dashboard-course-<?php the_ID(); ?>" class="tutor-course-listing-item tutor-mycourse-<?php the_ID(); ?>">
                <div class="tutor-course-listing-item-head tutor-bs-d-flex">
                    <img src="<?php echo esc_url($tutor_course_img); ?>" alt="Course Thumbnail">
                </div>
                <div class="tutor-course-listing-item-body tutor-px-20 tutor-py-18">
                    <div class="list-item-rating tutor-bs-d-flex tutor-mb-10">
                        <span class="price text-h6 color-text-primary">
                            <?php echo esc_html( get_the_date() ); ?> <?php echo esc_html( get_the_time() ); ?>
                        </span>
                    </div>
                    <div class="list-item-title text-medium-h5 color-text-primary">
                        <a href="<?php echo get_the_permalink(); ?>"><?php the_title(); ?></a>
                    </div>
                    <div class="list-item-meta text-medium-caption color-text-primary tutor-bs-d-flex tutor-mt-10">
                        <?php
                        $course_duration = get_tutor_course_duration_context();
                        $course_students = tutor_utils()->count_enrolled_users_by_course();
                        $disable_total_enrolled = (int) tutor_utils()->get_option( 'disable_course_total_enrolled' );
                        ?>
                        <?php
                        if(!empty($course_duration)) { ?>
                            <div class="tutor-bs-d-flex tutor-bs-align-items-center">
                            <span class="meta-icon ttr-clock-filled color-text-hints"></span><span><?php echo $course_duration; ?></span>
                            </div>
                        <?php } ?>
                        <?php if ( ! $disable_total_enrolled ) : ?>
                            <div class="tutor-bs-d-flex tutor-bs-align-items-center">
                            <span class="meta-icon ttr-user-filled color-text-hints"></span><span><?php echo $course_students; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="tutor-course-listing-item-footer has-border tutor-py-15 tutor-px-20">
                    <div class="tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-between">
                        <div class="list-item-price tutor-bs-d-flex tutor-bs-align-items-center">
                            <span class="price text-h6">
                                <?php _e('Price:', 'tutor') ?> 
                            </span>
                            <span class="price text-h6 color-text-primary">
                                <?php echo tutor_utils()->tutor_price(tutor_utils()->get_course_price()); ?>
                            </span>
                        </div>
                        <div class="list-item-button">
                            <a href="<?php echo tutor_utils()->course_edit_link($post->ID); ?>" class="tutor-mycourse-edit tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-sm">
                                <i class="btn-icon tutor-icon-pencil"></i>
                            </a>
                            <a href="#tutor-course-delete" class="tutor-dashboard-element-delete-btn tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-sm" data-id="<?php echo $post->ID; ?>">
                                <i class="btn-icon tutor-icon-garbage"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
		<?php
		endforeach;
	else : ?>
        <div>
            <h2><?php _e("Not Found" , 'tutor'); ?></h2>
            <p><?php _e("Sorry, but you are looking for something that isn't here." , 'tutor'); ?></p>
        </div>
	<?php endif; ?>


    <div class="tutor-frontend-modal" data-popup-rel="#tutor-course-delete" style="display: none">
        <div class="tutor-frontend-modal-overlay"></div>
        <div class="tutor-frontend-modal-content">
            <button class="tm-close tutor-icon-line-cross"></button>

            <div class="tutor-modal-body tutor-course-delete-popup">
                <img src="<?php echo tutor()->url . 'assets/images/delete-icon.png' ?>" alt="">
                <h3><?php _e('Delete This Course?', 'tutor'); ?></h3>
                <p><?php _e("You are going to delete this course, it can't be undone", 'tutor'); ?></p>
                <div class="tutor-modal-button-group">
                    <form action="" id="tutor-dashboard-delete-element-form">
                        <input type="hidden" name="action" value="tutor_delete_dashboard_course">
                        <input type="hidden" name="course_id" id="tutor-dashboard-delete-element-id" value="">
                        <button type="button" class="tutor-modal-btn-cancel"><?php _e('Cancel', 'tutor') ?></button>
                        <button type="submit" class="tutor-danger tutor-modal-element-delete-btn"><?php _e('Yes, Delete Course', 'tutor') ?></button>
                    </form>
                </div>
            </div>
            
        </div> <!-- tutor-frontend-modal-content -->
    </div> <!-- tutor-frontend-modal -->

    </div>
</div>
