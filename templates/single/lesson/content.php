<?php
/**
 * Display the content
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;


do_action('tutor_lesson/single/before/content');
?>
<?php tutor_lesson_video(); ?>
<?php the_content(); ?>
<?php get_tutor_posts_attachments(); ?>
<?php tutor_lesson_mark_complete_html(); ?>

<?php
do_action('tutor_lesson/single/after/content'); ?>