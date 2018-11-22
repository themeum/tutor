<?php

/**
 * Template for displaying course tags
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

do_action('dozent_course/single/before/tags');

?>
<?php
$course_tags = get_dozent_course_tags();
if(is_array($course_tags) && count($course_tags)){ ?>
    <div class="dozent-single-course-segment">
        <div class="course-benefits-title">
            <h4 class="dozent-segment-title"><?php esc_html_e('Skills', 'dozent') ?></h4>
        </div>
        <div class="dozent-course-tags">
            <?php
                foreach ($course_tags as $course_tag){
                    $tag_link = get_term_link($course_tag->term_id);
                    echo "<a href='$tag_link'> $course_tag->name </a>";
                }
            ?>
        </div>
    </div>
<?php
}
?>
<?php do_action('dozent_course/single/after/tags'); ?>