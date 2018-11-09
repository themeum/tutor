<?php
/**
 * Template for displaying lead info
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */


if ( ! defined( 'ABSPATH' ) )
    exit;

?>

<div class="tutor-single-course-segment tutor-single-course-lead-info">
    <div class="tutor-leadinfo-top-meta">
        <!-- @TODO Best Selling Badge -->
        <span class="selling-badge">Bestseller</span>
        <span class="tutor-single-course-rating">
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

    <h1 class="tutor-course-header-h1"><?php the_title(); ?></h1>

    <div class="tutor-single-course-meta tutor-meta-top">
        <ul>
            <li class="tutor-single-course-author-meta">
                <div class="tutor-single-course-avatar">
                    <?php echo tutor_utils()->get_tutor_avatar(get_tutor_course_author()); ?>
                </div>
                <div class="tutor-single-course-author-name">
                    <h6><?php _e('by', 'tutor'); ?></h6>
                    <?php echo get_tutor_course_author(); ?>
                </div>
                <div class="tutor-single-course-in">
                    <h6><?php _e('in'); ?></h6>
                    Design
                </div>
            </li>
            <li class="tutor-course-level">
                <h6><?php _e('Course level:', 'tutor'); ?></h6>
                <!-- @TODO: Need Course Level -->
                Intermediate
            </li>
        </ul>

    </div>

    <div class="tutor-single-course-meta tutor-lead-meta">
        <!--
            @TODO: Need Category Funciton
            @TODO: Total Course Duration
        -->
        <ul>
            <li>
                <h6><?php esc_html_e('Categories', 'tutor') ?></h6>
                <a href="#">Ios</a>
                <a href="#">Apps</a>
            </li>
            <li>
                <h6><?php esc_html_e('Total Hour', 'tutor') ?></h6>
                <span><?php esc_html_e('34h:06m:50s', 'tutor') ?></span>
            </li>
            <li>
                <h6><?php esc_html_e('Total Enrolled', 'tutor') ?></h6>
                <span><?php echo tutor_utils()->get_total_students(); ?></span>
            </li>
            <li>
                <h6><?php esc_html_e('Last Update', 'tutor') ?></h6>
                <?php echo esc_html(get_the_modified_date()); ?>
            </li>
        </ul>
    </div>

    <?php
    $excerpt = tutor_get_the_excerpt();

    if (! empty($excerpt)){
        ?>
            <div class="tutor-course-summery">
                <h3  class="tutor-segment-title"><?php esc_html_e('About Course', 'tutor') ?></h3>
                <?php echo $excerpt; ?>
            </div>
        <?php
    }
    ?>


</div>