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

global $post, $authordata;
$profile_url = dozent_utils()->profile_url($authordata->ID);
?>

<div class="dozent-single-course-segment dozent-single-course-lead-info">
    <div class="dozent-leadinfo-top-meta">
        <span class="dozent-single-course-rating">
            <?php
            $course_rating = dozent_utils()->get_course_rating();
            dozent_utils()->star_rating_generator($course_rating->rating_avg);
            ?>
            <span class="dozent-single-rating-count">
                <?php
                echo $course_rating->rating_avg;
                echo '<i>('.$course_rating->rating_count.')</i>';
                ?>
            </span>
        </span>
    </div>

    <h1 class="dozent-course-header-h1"><?php the_title(); ?></h1>

    <div class="dozent-single-course-meta dozent-meta-top">
        <ul>
            <li class="dozent-single-course-author-meta">
                <div class="dozent-single-course-avatar">
                    <a href="<?php echo $profile_url; ?>"> <?php echo dozent_utils()->get_dozent_avatar($post->post_author); ?></a>
                </div>
                <div class="dozent-single-course-author-name">
                    <strong><?php _e('by', 'dozent'); ?></strong>
                    <a href="<?php echo dozent_utils()->profile_url($authordata->ID); ?>"><?php echo get_the_author(); ?></a>
                </div>
            </li>
            <li class="dozent-course-level">
                <strong><?php _e('Course level:', 'dozent'); ?></strong>
				<?php echo get_dozent_course_level(); ?>
            </li>
        </ul>
    </div>

    <div class="dozent-single-course-meta dozent-lead-meta">
        <ul>
			<?php
			$course_categories = get_dozent_course_categories();
			if(is_array($course_categories) && count($course_categories)){
				?>
                <li>
                    <strong><?php esc_html_e('Categories', 'dozent') ?></strong>
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
			$course_duration = get_dozent_course_duration_context();
			if(!empty($course_duration)){
				?>
                <li>
                    <strong><?php esc_html_e('Total Hour', 'dozent') ?></strong>
                    <span><?php echo $course_duration; ?></span>
                </li>
			<?php } ?>
            <li>
                <strong><?php esc_html_e('Total Enrolled', 'dozent') ?></strong>
                <span>
                    <?php
                    $get_total_student = dozent_utils()->get_total_students();
                    $total_students = $get_total_student ? $get_total_student : 0;
                    echo $total_students;
                    ?>
                </span>
            </li>
            <li>
                <strong><?php esc_html_e('Last Update', 'dozent') ?></strong>
				<?php echo esc_html(get_the_modified_date()); ?>
            </li>
        </ul>
    </div>

	<?php
	$excerpt = dozent_get_the_excerpt();

	if (! empty($excerpt)){
		?>
        <div class="dozent-course-summery">
            <h4  class="dozent-segment-title"><?php esc_html_e('About Course', 'dozent') ?></h4>
			<?php echo $excerpt; ?>
        </div>
		<?php
	}
	?>
</div>