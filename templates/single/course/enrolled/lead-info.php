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

global $wp_query;
global $post, $authordata;

$profile_url = tutor_utils()->profile_url($authordata->ID);
?>
<div class="tutor-single-course-segment tutor-single-course-lead-info">
    <div class="tutor-leadinfo-top-meta">
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

	<?php do_action('tutor_course/single/title/after'); ?>
	<?php do_action('tutor_course/single/lead_meta/before'); ?>

    <div class="tutor-single-course-meta tutor-meta-top">
        <ul>
            <li class="tutor-single-course-author-meta">
                <div class="tutor-single-course-avatar">
                    <a href="<?php echo $profile_url; ?>"> <?php echo tutor_utils()->get_tutor_avatar($post->post_author); ?></a>
                </div>
                <div class="tutor-single-course-author-name">
                    <strong><?php _e('by', 'tutor'); ?></strong>
                    <a href="<?php echo tutor_utils()->profile_url($authordata->ID); ?>"><?php echo get_the_author(); ?></a>
                </div>
            </li>
            <li class="tutor-course-level">
                <strong><?php _e('Course level:', 'tutor'); ?></strong>
				<?php echo get_tutor_course_level(); ?>
            </li>
        </ul>
    </div>


    <div class="tutor-single-course-meta tutor-lead-meta">
        <ul>
			<?php
			$course_categories = get_tutor_course_categories();
			if(is_array($course_categories) && count($course_categories)){
				?>
                <li>
                    <strong><?php esc_html_e('Categories', 'tutor') ?></strong>
                    <?php
                    foreach ($course_categories as $course_category){
                        $category_name = $course_category->name;
                        $category_link = get_term_link($course_category->term_id);
                        echo "<a href='$category_link'>$category_name</a>";
                    }
                    ?>
                </li>
			<?php } ?>

			<?php
			$course_duration = get_tutor_course_duration_context();
			if(!empty($course_duration)){
				?>
                <li>
                    <strong><?php esc_html_e('Total Hour', 'tutor') ?></strong>
                    <span><?php echo $course_duration; ?></span>
                </li>
			<?php } ?>
            <li>
                <strong><?php esc_html_e('Total Enrolled', 'tutor') ?></strong>
                <span>
                    <?php
                    $get_total_student = tutor_utils()->get_total_students();
                    $total_students = $get_total_student ? $get_total_student : 0;
                    echo $total_students;
                    ?>
                </span>
            </li>
            <li>
                <strong><?php esc_html_e('Last Update', 'tutor') ?></strong>
				<?php echo esc_html(get_the_modified_date()); ?>
            </li>
        </ul>
    </div>

    <div class="tutor-course-enrolled-info">
		<?php $count_completed_lesson = tutor_course_completing_progress_bar(); ?>

        <div class="tutor-lead-info-btn-group">
			<?php
			if ( $wp_query->query['post_type'] !== 'lesson') {
				$lesson_url = tutor_utils()->get_course_first_lesson();
				if ( $lesson_url ) {
					?>
                    <a href="<?php echo $lesson_url; ?>" class="tutor-button"><?php _e( 'Continue to lesson', 'tutor' ); ?></a>
                <?php }
            }
            ?>
            <?php tutor_course_mark_complete_html(); ?>
        </div>
    </div>

	<?php do_action('tutor_course/single/lead_meta/after'); ?>
	<?php do_action('tutor_course/single/excerpt/before'); ?>

    <?php
	$excerpt = tutor_get_the_excerpt();
	if (! empty($excerpt)){
		?>
        <div class="tutor-course-summery">
            <h4  class="tutor-segment-title"><?php esc_html_e('About Course', 'tutor') ?></h4>
			<?php echo $excerpt; ?>
        </div>
		<?php
	}
	?>

	<?php do_action('tutor_course/single/excerpt/after'); ?>

</div>