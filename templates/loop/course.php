<?php

/**
 * A single course loop
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */


do_action('dozent_course/loop/before_content');

do_action('dozent_course/loop/badge');


do_action('dozent_course/loop/before_header');
do_action('dozent_course/loop/header');
do_action('dozent_course/loop/after_header');


do_action('dozent_course/loop/start_content_wrap');

do_action('dozent_course/loop/before_rating');
do_action('dozent_course/loop/rating');
do_action('dozent_course/loop/after_rating');

do_action('dozent_course/loop/before_title');
do_action('dozent_course/loop/title');
do_action('dozent_course/loop/after_title');


do_action('dozent_course/loop/before_meta');
do_action('dozent_course/loop/meta');
do_action('dozent_course/loop/after_meta');


do_action('dozent_course/loop/before_excerpt');
do_action('dozent_course/loop/excerpt');
do_action('dozent_course/loop/after_excerpt');

do_action('dozent_course/loop/end_content_wrap');

do_action('dozent_course/loop/before_footer');
do_action('dozent_course/loop/footer');
do_action('dozent_course/loop/after_footer');

do_action('dozent_course/loop/after_content');

?>