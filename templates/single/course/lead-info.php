<?php

/**
 * Template for displaying lead info
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if (!defined('ABSPATH'))
    exit;

global $post, $authordata;
$profile_url = tutor_utils()->profile_url( $authordata->ID, true );
?>

<header class="tutor-course-details-header tutor-mb-44">
    <?php
    $disable = !get_tutor_option('enable_course_review');
    if (!$disable) {
    ?>
        <div class="tutor-course-details-ratings">
            <?php
            $course_rating = tutor_utils()->get_course_rating();
            tutor_utils()->star_rating_generator_v2($course_rating->rating_avg, $course_rating->rating_count, true);
            ?>
        </div>
    <?php
    }
    ?>

    <h1 class="tutor-course-details-title tutor-fs-4 tutor-fw-bold tutor-color-black tutor-mt-12 tutor-mb-0">
        <?php do_action('tutor_course/single/title/before'); ?>
        <span><?php the_title(); ?></span>
    </h1>
    
    <div class="tutor-d-sm-flex tutor-align-items-center tutor-justify-content-between tutor-mt-16">
        <div class="tutor-course-details-category tutor-fs-6 tutor-fw-medium tutor-color-black tutor-d-flex tutor-align-items-end">
            <!-- <?php if (tutor_utils()->get_option('enable_course_author')) : ?>
                <div class="tutor-course-author tutor-mr-16">
                    <img src="<?php echo get_avatar_url(get_the_author_meta('ID')); ?>" />
                    <span><?php _e('by', 'tutor'); ?></span>
                    <strong><?php echo get_the_author_meta('display_name'); ?></strong>
                </div>
            <?php endif; ?> -->
            <div>
                <span class="tutor-fs-6 tutor-color-muted">
                    <?php _e('Categories', 'tutor'); ?>:
                </span>
                <span>
                    <?php
                    $course_categories = get_tutor_course_categories();
                    $cats_array = [];
                    if (is_array($course_categories) && count($course_categories)) {
                        foreach ($course_categories as $course_category) {
                            $category_name = $course_category->name;
                            $category_link = get_term_link($course_category->term_id);
                            $cats_array[] = "<a href='$category_link'>$category_name</a>";
                        }

                        echo implode(', ', $cats_array);
                    } else {
                        _e('Uncategorized', 'tutor');
                    }
                    ?>
                </span>
            </div>
        </div>
        <div class="tutor-course-details-action-btns tutor-mt-12 tutor-mt-sm-0">
            <a href="#" class="tutor-btn-ghost tutor-btn-ghost-fd action-btn tutor-fs-6 tutor-color-black tutor-course-wishlist-btn" data-course-id="<?php echo get_the_ID(); ?>">
                <i class="tutor-icon-fav-line-filled"></i> <?php _e('Wishlist', 'tutor'); ?>
            </a>

            <?php
            if (tutor_utils()->get_option('enable_course_share', false, true, true)) {
                tutor_load_template_from_custom_path(tutor()->path . '/views/course-share.php', array(), false);
            }
            ?>
        </div>
    </div>
</header>
