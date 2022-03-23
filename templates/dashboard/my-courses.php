<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$user = wp_get_current_user();
$shortcode_arg = isset($GLOBALS['tutor_shortcode_arg']) ? $GLOBALS['tutor_shortcode_arg']['column_per_row'] : null;
$courseCols = $shortcode_arg === null ? tutor_utils()->get_option('courses_col_per_row', 4) : $shortcode_arg;
!isset($active_tab) ? $active_tab = 'my-courses' : 0;
$per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$paged    = (isset($_GET['current_page']) && is_numeric($_GET['current_page']) && $_GET['current_page'] >= 1) ? $_GET['current_page'] : 1;
$offset     = $per_page * ($paged-1);
$status = $active_tab == 'my-courses' ? array('publish') : array('pending');
$courses_per_page = tutor_utils()->get_courses_by_instructor($user->ID, $status, $offset, $per_page);
$my_courses = tutor_utils()->get_courses_by_instructor(null, $status);
?>

<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16"><?php esc_html_e('My Courses', 'tutor'); ?></div>

<div class="tutor-dashboard-content-inner my-courses">
    <?php
    $user = wp_get_current_user();
    $publish_courses_count = count(tutor_utils()->get_courses_by_instructor($user->ID));
    $pending_courses_count = count(tutor_utils()->get_pending_courses_by_instructor($user->ID));
    ?>

    <!-- Navigation Tab -->
    <div class="tutor-dashboard-inline-links">
        <ul>
            <li class="<?php echo $active_tab == 'my-courses' ? 'active' : ''; ?>">
                <a href="<?php echo esc_url(tutor_utils()->get_tutor_dashboard_page_permalink('my-courses')); ?>">
                    <?php esc_html_e('Publish', 'tutor'); ?> <?php echo "(" . $publish_courses_count . ")"; ?>
                </a>
            </li>
            <li class="<?php echo $active_tab == 'my-courses/pending-courses' ? 'active' : ''; ?>">
                <a href="<?php echo esc_url(tutor_utils()->get_tutor_dashboard_page_permalink('my-courses/pending-courses')); ?>">
                    <?php esc_html_e('Pending', 'tutor'); ?> <?php echo "(" . $pending_courses_count . ")"; ?>
                </a>
            </li>
        </ul>
    </div>

    <!-- Course list -->
    <?php
    $placeholder_img = tutor()->url . 'assets/images/placeholder.png';

    if (is_array($courses_per_page) && count($courses_per_page)) {
        global $post;
    ?>
        <div class="tutor-course-listing-grid tutor-course-listing-grid-3">
            <?php
            foreach ($courses_per_page as $post) :
                setup_postdata($post);

                $avg_rating = tutor_utils()->get_course_rating()->rating_avg;
                $tutor_course_img = get_tutor_course_thumbnail_src();
                $id_string_delete = 'tutor_my_courses_delete_' . $post->ID;
                $row_id = 'tutor-dashboard-my-course-' . $post->ID;
            ?>

                <div id="<?php echo $row_id; ?>" class="tutor-course-listing-item tutor-course-listing-item-sm tutor-mycourses-card tutor-mycourse-<?php the_ID(); ?>">
                    <div class="tutor-course-listing-item-head tutor-d-flex">
                        <!-- <img src="<?php //echo esc_url($tutor_course_img); ?>" alt="Course Thumbnail"> -->
                        <div class="tutor-course-listing-thumbnail" style="background-image:url(<?php echo empty(esc_url($tutor_course_img)) ? $placeholder_img : esc_url($tutor_course_img) ?>)"></div>
                    </div>
                    <div class="tutor-course-listing-item-body tutor-px-20 tutor-py-20">
                        <div class="tutor-d-flex tutor-mb-7">
                            <span class="tutor-fs-6 tutor-color-black-60">
                                <?php echo esc_html(get_the_date()); ?> <?php echo esc_html(get_the_time()); ?>
                            </span>
                        </div>
                        <div class="list-item-title tutor-fs-6 tutor-fw-bold tutor-color-black tutor-mb-16">
                            <a href="<?php echo get_the_permalink(); ?>"><?php the_title(); ?></a>
                        </div>
                        <div class="list-item-meta tutor-fs-7 tutor-fw-medium tutor-color-black tutor-d-flex tutor-mt-12">
                            <?php
                            $course_duration = get_tutor_course_duration_context($post->ID, true);
                            $course_students = tutor_utils()->count_enrolled_users_by_course();
                            ?>
                            <?php
                            if (!empty($course_duration)) { ?>
                                <div class="tutor-d-flex tutor-align-items-center">
                                    <span class="meta-icon tutor-icon-clock-filled tutor-color-muted tutor-icon-20 tutor-mr-4"></span>
                                    <span class="tutor-fs-7 tutor-fw-medium tutor-color-black"><?php echo $course_duration; ?></span>
                                </div>
                            <?php } ?>
                            <?php if (!empty($course_students)) : ?>
                                <div class="tutor-d-flex tutor-align-items-center">
                                    <span class="meta-icon tutor-icon-user-filled tutor-color-muted"></span>
                                    <span><?php echo $course_students; ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card footer -->
                    <div class="tutor-course-listing-item-footer has-border tutor-py-8 tutor-pl-20 tutor-pr-8">
                        <div class="tutor-d-flex tutor-align-items-center tutor-justify-content-between">
                            <div class="tutor-d-flex tutor-align-items-center">
                                <span class="tutor-fs-7 tutor-fw-medium tutor-color-muted tutor-mr-4">
                                    <?php esc_html_e('Price:', 'tutor') ?>
                                </span>
                                <span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
                                    <?php echo tutor_utils()->tutor_price(tutor_utils()->get_course_price()); ?>
                                </span>
                            </div>
                            <div class="tutor-course-listing-item-btns">
                                <a href="<?php echo tutor_utils()->course_edit_link($post->ID); ?>" class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-sm">
                                    <i class="tutor-icon-edit-filled tutor-icon-26 tutor-color-muted"></i>
                                </a>
                                <a href="#" data-tutor-modal-target="<?php echo $id_string_delete; ?>" class="tutor-dashboard-element-delete-btn tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-sm">
                                    <i class="tutor-icon-delete-stroke-filled tutor-icon-24 tutor-color-muted"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Delete prompt modal -->
                    <div id="<?php echo $id_string_delete; ?>" class="tutor-modal">
                        <span class="tutor-modal-overlay"></span>
                        <button data-tutor-modal-close class="tutor-modal-close">
                            <span class="tutor-icon-line-cross-line"></span>
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
                                        <button class="tutor-btn tutor-list-ajax-action" data-request_data='{"course_id":<?php echo $post->ID; ?>,"action":"tutor_delete_dashboard_course"}' data-delete_element_id="<?php echo $row_id; ?>">
                                            <?php esc_html_e('Yes, Delete This', 'tutor'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach;
            wp_reset_postdata(); ?>
        </div>
        <div class="tutor-mt-20">
            <?php
            if (($status[0] === 'publish' && $publish_courses_count > $per_page) || ($status[0] === 'pending' && $pending_courses_count > $per_page)) {
                $pagination_data = array(
                    'total_items' => $status[0] === 'publish' ? $publish_courses_count : $pending_courses_count,
                    'per_page'    => $per_page,
                    'paged'       => $paged,
                );
                tutor_load_template_from_custom_path(
                    tutor()->path . 'templates/dashboard/elements/pagination.php',
                    $pagination_data
                );
            }
            ?>

        </div>
    <?php
    } else {
        tutor_utils()->tutor_empty_state(tutor_utils()->not_found_text());
    }
    ?>
</div>
