<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$shortcode_arg = isset($GLOBALS['tutor_shortcode_arg']) ? $GLOBALS['tutor_shortcode_arg']['column_per_row'] : null;
$courseCols = $shortcode_arg===null ? tutor_utils()->get_option( 'courses_col_per_row', 4 ) : $shortcode_arg;
!isset($active_tab) ? $active_tab='my-courses' : 0;
?>

<h3><?php esc_html_e('My Courses', 'tutor'); ?></h3>

<div class="tutor-dashboard-content-inner my-courses">
    <?php
        $user = wp_get_current_user();
        $publish_courses_count = count(tutor_utils()->get_courses_by_instructor($user->ID));
        $pending_courses_count = count(tutor_utils()->get_pending_courses_by_instructor($user->ID));
    ?>

    <!-- Navigation Tab -->
    <div class="tutor-dashboard-inline-links">
        <ul>
            <li class="<?php echo $active_tab=='my-courses' ? 'active' : ''; ?>">
                <a href="<?php echo esc_url(tutor_utils()->get_tutor_dashboard_page_permalink('my-courses')); ?>"> 
                    <?php esc_html_e('Publish', 'tutor'); ?> <?php echo "(".$publish_courses_count.")"; ?>
                </a> 
            </li>
            <li class="<?php echo $active_tab=='my-courses/pending-courses' ? 'active' : ''; ?>">
                <a href="<?php echo esc_url(tutor_utils()->get_tutor_dashboard_page_permalink('my-courses/pending-courses')); ?>"> 
                    <?php esc_html_e('Pending', 'tutor'); ?> <?php echo "(".$pending_courses_count.")"; ?>
                </a> 
            </li>
        </ul>
    </div>

    <!-- Course list -->
    <div class="tutor-course-listing-grid tutor-course-listing-grid-3">
	    <?php
            $status = $active_tab=='my-courses' ? array('publish') : array('pending', 'draft');
	        $my_courses = tutor_utils()->get_courses_by_instructor(null, $status);

	        if (is_array($my_courses) && count($my_courses)):
                global $post;

                foreach ($my_courses as $post):
                    setup_postdata($post);

                    $avg_rating = tutor_utils()->get_course_rating()->rating_avg;
                    $tutor_course_img = get_tutor_course_thumbnail_src();
                    $id_string_delete = 'tutor_my_courses_delete_' . $post->ID;
                    $row_id = 'tutor-dashboard-my-course-' . $post->ID;
                    ?>
                    
                    <div id="<?php echo $row_id; ?>" class="tutor-course-listing-item tutor-course-listing-item-sm tutor-mycourse-<?php the_ID(); ?>">
                        <div class="tutor-course-listing-item-head tutor-bs-d-flex">
                            <img src="<?php echo esc_url($tutor_course_img); ?>" alt="Course Thumbnail">
                        </div>
                        <div class="tutor-course-listing-item-body tutor-px-20 tutor-py-18">
                            <div class="list-item-rating tutor-bs-d-flex tutor-mb-10">
                                <span class="date text-h6 color-text-primary">
                                    <?php echo esc_html( get_the_date() ); ?> <?php echo esc_html( get_the_time() ); ?>
                                </span>
                            </div>
                            <div class="list-item-title text-medium-h6 color-text-primary">
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

                        <!-- Card footer -->
                        <div class="tutor-course-listing-item-footer has-border tutor-py-15 tutor-px-20">
                            <div class="tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-between">
                                <div class="list-item-price tutor-bs-d-flex tutor-bs-align-items-center">
                                    <span class="price text-h6">
                                        <?php esc_html_e('Price:', 'tutor') ?> 
                                    </span>
                                    <span class="price text-h6 color-text-primary">
                                        <?php echo tutor_utils()->tutor_price(tutor_utils()->get_course_price()); ?>
                                    </span>
                                </div>
                                <div class="list-item-button">
                                    <a href="<?php echo tutor_utils()->course_edit_link($post->ID); ?>" class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-sm">
                                        <i class="btn-icon tutor-icon-pencil"></i>
                                    </a>
                                    <i data-tutor-modal-target="<?php echo $id_string_delete; ?>" class="tutor-dashboard-element-delete-btn tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-sm">
                                        <i class="btn-icon tutor-icon-garbage"></i>
                                    </i>
                                </div>
                            </div>
                        </div>

                        <!-- Delete prompt modal -->
                        <div id="<?php echo $id_string_delete; ?>" class="tutor-modal">
                            <span class="tutor-modal-overlay"></span>
                            <button data-tutor-modal-close class="tutor-modal-close">
                                <span class="las la-times"></span>
                            </button>
                            <div class="tutor-modal-root">
                                <div class="tutor-modal-inner">
                                    <div class="tutor-modal-body tutor-text-center">
                                        <div class="tutor-modal-icon">
                                            <img src="<?php echo tutor()->url; ?>assets/images/icon-trash.svg" />
                                        </div>
                                        <div class="tutor-modal-text-wrap">
                                            <h3 class="tutor-modal-title">
                                                <?php esc_html_e('Delete This Course?', 'tutor'); ?>
                                            </h3>
                                            <p>
                                                <?php esc_html_e('Are you sure you want to delete this course permanently from the site? Please confirm your choice.', 'tutor'); ?>
                                            </p>
                                        </div>
                                        <div class="tutor-modal-btns tutor-btn-group">
                                            <button data-tutor-modal-close class="tutor-btn tutor-is-outline tutor-is-default">
                                                <?php esc_html_e('Cancel', 'tutor'); ?>
                                            </button>
                                            <button class="tutor-btn tutor-list-ajax-action" data-request_data='{"course_id":<?php echo $post->ID;?>,"action":"tutor_delete_dashboard_course"}' data-delete_element_id="<?php echo $row_id; ?>">
                                                <?php esc_html_e('Yes, Delete This', 'tutor'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
		        <?php endforeach; wp_reset_postdata(); ?>
            <?php else: ?>
                <div>
                    <h2><?php esc_html_e("Not Found" , 'tutor'); ?></h2>
                    <p><?php esc_html_e("Sorry, you don't have any courses." , 'tutor'); ?></p>
                </div>
            <?php 
            endif; 
        ?>
    </div>
</div>