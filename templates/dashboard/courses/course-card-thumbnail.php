<?php
/**
 * Course Card Thumbnail Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Enrolled_Courses
 * @author Themeum
 *
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

$thumbnail_img = isset( $thumbnail_img ) ? $thumbnail_img : get_tutor_course_thumbnail_src();
$post_id       = isset( $post_id ) ? $post_id : get_the_ID();
?>

<div class="tutor-progress-card-thumbnail">
	<?php do_action( 'tutor_courses_card_before_thumbnail', $post_id ); ?>
	<?php if ( ! empty( $thumbnail_img ) ) : ?>
		<img src="<?php echo esc_url( $thumbnail_img ); ?>" alt="<?php the_title_attribute( array( 'post' => $post_id ) ); ?>" loading="lazy" />
	<?php endif; ?>
</div>
