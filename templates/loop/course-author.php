<?php

/**
 * Display loop thumbnail
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $post, $authordata;

$profile_url = tutor_utils()->profile_url($authordata->ID, true);
$course_categories = get_tutor_course_categories();
?>

<div class="tutor-meta tutor-mt-32">
    <div>
        <a href="<?php echo $profile_url; ?>" class="tutor-d-flex">
            <?php echo tutor_utils()->get_tutor_avatar( $post->post_author ); ?>
        </a>
    </div>

    <div>
        <?php esc_html_e('By', 'tutor') ?>
        <a href="<?php echo $profile_url; ?>"><?php esc_html_e(get_the_author()); ?></a>

        <?php if( !empty( $course_categories ) && is_array( $course_categories ) && count( $course_categories ) ) : ?>
            <?php esc_html_e('In', 'tutor'); ?>
            <?php
                $category_links = array();
                foreach ( $course_categories as $course_category ) :
                    $category_name = $course_category->name;
                    $category_link = get_term_link($course_category->term_id);
                    $category_links[] = wp_sprintf( '<a href="%1$s">%2$s</a>', esc_url( $category_link ), esc_html( $category_name ) );
                endforeach;
                echo implode(', ', $category_links);
            ?>
        <?php endif; ?>
    </div>
</div>
