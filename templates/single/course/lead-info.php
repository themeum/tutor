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

if ( ! defined( 'ABSPATH' ) )
	exit;

global $post, $authordata;
$profile_url = tutor_utils()->profile_url($authordata->ID);
?>

<header class="tutor-course-details-header tutor-mb-42">
	<?php
        $disable = get_tutor_option('disable_course_review');
        if ( ! $disable){
            ?>
            <div class="tutor-course-details-ratings">
                <div class="tutor-ratings">
                    <?php
                    $course_rating = tutor_utils()->get_course_rating();
                    tutor_utils()->star_rating_generator($course_rating->rating_avg);
                    ?>
                    <span class="tutor-single-rating-count">
                        <?php
                            echo $course_rating->rating_avg;
                            echo '<i>('.$course_rating->rating_count.')</i>';
                        ?>
                    </span>
                </span>
            </div>
            <?php 
        } 
    ?>

    <div class="tutor-course-details-title text-bold-h4 color-text-primary tutor-mt-10">
        <?php do_action('tutor_course/single/title/before'); ?>
        <h1 class="tutor-course-header-h1">
            <?php the_title(); ?>
        </h1>
    </div>
    <div class="tutor-bs-d-sm-flex tutor-bs-align-items-center tutor-bs-justify-content-between tutor-mt-28">
        <div class="tutor-course-details-category text-medium-body color-text-primary">
            <span class="text-regular-body color-text-hints">
                <?php _e('Categories', 'tutor'); ?>:
            </span>
            <span>
                <?php 
                    $course_categories = get_tutor_course_categories();
                    $cats_array = [];
                    if(is_array($course_categories) && count($course_categories)){
                        foreach ($course_categories as $course_category){
                            $category_name = $course_category->name;
                            $category_link = get_term_link($course_category->term_id);
                            $cats_array[] = "<a href='$category_link'>$category_name</a>";
                        }

                        echo implode(', ', $cats_array);
                    } 
                ?>
            </span>
        </div>
        <div class="tutor-course-details-action-btns tutor-mt-10 tutor-mt-sm-0">
            <a href="#" class="action-btn text-regular-body color-text-primary">
                <span class="ttr-fav-line-filled"></span>Wishlist
            </a>
            <a href="#" class="action-btn text-regular-body color-text-primary">
                <span class="ttr-share-filled"></span>Share
            </a>
        </div>
    </div>
</header>