<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

// Get the user ID and active tab
$current_user_id = get_current_user_id();
!isset($active_tab) ? $active_tab = 'my-courses' : 0;

// Map required course status according to page
$status_map = array(
    'my-courses' => 'publish',
    'my-courses/draft-courses' => 'draft',
    'my-courses/pending-courses' => 'pending'
);

// Set currently required course status fo rcurrent tab
$status = isset( $status_map[$active_tab] ) ? $status_map[$active_tab] : 'publish';

// Get counts for course tabs
$count_map = array(
    'publish' => tutor_utils()->get_courses_by_instructor($current_user_id, 'publish', 0, 0, true),
    'pending' => tutor_utils()->get_courses_by_instructor($current_user_id, 'pending', 0, 0, true),
    'draft' => tutor_utils()->get_courses_by_instructor($current_user_id, 'draft', 0, 0, true),
);

$course_archive_arg = isset($GLOBALS['tutor_course_archive_arg']) ? $GLOBALS['tutor_course_archive_arg']['column_per_row'] : null;
$courseCols         = $course_archive_arg === null ? tutor_utils()->get_option('courses_col_per_row', 4) : $course_archive_arg;
$per_page           = tutor_utils()->get_option( 'courses_per_page', 10 );
$paged              = (isset($_GET['current_page']) && is_numeric($_GET['current_page']) && $_GET['current_page'] >= 1) ? $_GET['current_page'] : 1;
$offset             = $per_page * ($paged-1);

$results            = tutor_utils()->get_courses_by_instructor($current_user_id, $status, $offset, $per_page);
?>

<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16">
    <?php esc_html_e('My Courses', 'tutor'); ?>
</div>

<div class="tutor-dashboard-content-inner my-courses">
    <div class="tutor-mb-32">
        <ul class="tutor-nav">
            <li class="tutor-nav-item">
                <a class="tutor-nav-link<?php echo $active_tab == 'my-courses' ? ' is-active' : ''; ?>" href="<?php echo esc_url(tutor_utils()->get_tutor_dashboard_page_permalink('my-courses')); ?>">
                    <?php esc_html_e('Publish', 'tutor'); ?> <?php echo "(" . $count_map['publish'] . ")"; ?>
                </a>
            </li>
            <li class="tutor-nav-item">
                <a class="tutor-nav-link<?php echo $active_tab == 'my-courses/pending-courses' ? ' is-active' : ''; ?>" href="<?php echo esc_url(tutor_utils()->get_tutor_dashboard_page_permalink('my-courses/pending-courses')); ?>">
                    <?php esc_html_e('Pending', 'tutor'); ?> <?php echo "(" . $count_map['pending'] . ")"; ?>
                </a>
            </li>
            <li class="tutor-nav-item">
                <a class="tutor-nav-link<?php echo $active_tab == 'my-courses/draft-courses' ? ' is-active' : ''; ?>" href="<?php echo esc_url(tutor_utils()->get_tutor_dashboard_page_permalink('my-courses/draft-courses')); ?>">
                    <?php esc_html_e('Draft', 'tutor'); ?> <?php echo "(" . $count_map['draft'] . ")"; ?>
                </a>
            </li>
        </ul>
    </div>

    <!-- Course list -->
    <?php
    $placeholder_img = tutor()->url . 'assets/images/placeholder.png';

    if (!is_array($results) || (!count($results) && $paged==1)) {
        tutor_utils()->tutor_empty_state(tutor_utils()->not_found_text());
    } else {
        ?>
        <div class="tutor-course-listing-grid tutor-course-listing-grid-3">
            <?php
            global $post;
            foreach ($results as $post) :
                setup_postdata($post);

                $avg_rating = tutor_utils()->get_course_rating()->rating_avg;
                $tutor_course_img = get_tutor_course_thumbnail_src();
                $id_string_delete = 'tutor_my_courses_delete_' . $post->ID;
                $row_id = 'tutor-dashboard-my-course-' . $post->ID;
                ?>

                <div id="<?php echo $row_id; ?>" class="tutor-course-listing-item tutor-course-listing-item-sm tutor-mycourses-card tutor-mycourse-<?php the_ID(); ?>">
                    <div class="tutor-course-listing-item-head tutor-d-flex">
                        <div class="tutor-course-listing-thumbnail tutor-ratio tutor-ratio-16x9">
                            <img src="<?php echo empty(esc_url($tutor_course_img)) ? $placeholder_img : esc_url($tutor_course_img) ?>" alt="<?php the_title(); ?>" loading="lazy">
                        </div>
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
                                    <span class="meta-icon tutor-icon-clock-line tutor-color-muted tutor-mr-8"></span>
                                    <span class="tutor-fs-7 tutor-fw-medium tutor-color-black"><?php echo $course_duration; ?></span>
                                </div>
                            <?php } ?>
                            <?php if (!empty($course_students)) : ?>
                                <div class="tutor-d-flex tutor-align-items-center">
                                    <span class="meta-icon tutor-icon-user-line tutor-color-muted tutor-mr-8"></span>
                                    <span><?php echo $course_students; ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card footer -->
                    <div class="tutor-course-listing-item-footer has-border tutor-py-8 tutor-pl-20 tutor-pr-8">
                        <div class="tutor-d-flex tutor-align-items-center tutor-justify-between">
                            <div class="tutor-d-flex tutor-align-items-center">
                                <span class="tutor-fs-7 tutor-fw-medium tutor-color-muted tutor-mr-4">
                                    <?php esc_html_e('Price:', 'tutor') ?>
                                </span>
                                <span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
                                    <?php
                                        $price = tutor_utils()->get_course_price();
                                        if ( null === $price ) {
                                            esc_html_e( 'Free', 'tutor' );
                                        } else {
                                            echo tutor_utils()->get_course_price();
                                        }
                                    ?>
                                </span>
                            </div>
                            <div class="tutor-iconic-btn-group">
                                <a href="<?php echo tutor_utils()->course_edit_link($post->ID); ?>" class="tutor-iconic-btn">
                                    <i class="tutor-icon-edit" area-hidden="true"></i>
                                </a>
                                <a href="#" data-tutor-modal-target="<?php echo $id_string_delete; ?>" class="tutor-dashboard-element-delete-btn tutor-iconic-btn">
                                    <i class="tutor-icon-trash-can-line" area-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Delete prompt modal -->
                    <div id="<?php echo $id_string_delete; ?>" class="tutor-modal">
                        <div class="tutor-modal-overlay"></div>
                        <div class="tutor-modal-window">
                            <div class="tutor-modal-content tutor-modal-content-white">
                                <button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
                                    <span class="tutor-icon-times" area-hidden="true"></span>
                                </button>

                                <div class="tutor-modal-body tutor-text-center">
                                    <div class="tutor-mt-48">
                                        <img class="tutor-d-inline-block" src="<?php echo tutor()->url; ?>assets/images/icon-trash.svg" />
                                    </div>

                                    <div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12"><?php esc_html_e('Delete This Course?', 'tutor'); ?></div>
                                    <div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e('Are you sure you want to delete this course permanently from the site? Please confirm your choice.', 'tutor'); ?></div>
                                    
                                    <div class="tutor-d-flex tutor-justify-center tutor-my-48">
                                        <button data-tutor-modal-close class="tutor-btn tutor-btn-outline-primary">
                                            <?php esc_html_e('Cancel', 'tutor'); ?>
                                        </button>
                                        <button class="tutor-btn tutor-btn-primary tutor-list-ajax-action tutor-ml-20" data-request_data='{"course_id":<?php echo $post->ID; ?>,"action":"tutor_delete_dashboard_course"}' data-delete_element_id="<?php echo $row_id; ?>">
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
                if ($count_map[$status] > $per_page) {

                    $pagination_data = array(
                        'total_items' => $count_map[$status],
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
    } 
    ?>
</div>
