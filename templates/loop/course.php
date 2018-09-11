<?php

/**
 * A single course loop
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */


do_action('lms_course/loop/before_content');


do_action('lms_course/loop/before_thumbnail');
do_action('lms_course/loop/thumbnail');
do_action('lms_course/loop/after_thumbnail');


do_action('lms_course/loop/before_title');
do_action('lms_course/loop/title');
do_action('lms_course/loop/after_title');


do_action('lms_course/loop/before_excerpt');
do_action('lms_course/loop/excerpt');
do_action('lms_course/loop/after_excerpt');


do_action('lms_course/loop/before_rating');
do_action('lms_course/loop/rating');
do_action('lms_course/loop/after_rating');



do_action('lms_course/loop/after_content');

?>
