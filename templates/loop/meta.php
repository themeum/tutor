<?php
global $post, $authordata;

$profile_url = dozent_utils()->profile_url($authordata->ID);
?>
<div class="dozent-loop-author">
<div class="dozent-single-course-avatar">
    <a href="<?php echo $profile_url; ?>"> <?php echo dozent_utils()->get_dozent_avatar($post->post_author); ?></a>
</div>

<div class="dozent-single-course-author-name">
	<strong><?php _e('by', 'dozent'); ?></strong>
    <a href="<?php echo $profile_url; ?>"><?php echo get_the_author(); ?></a>
</div>

<div class="dozent-course-lising-category">
	<?php
	$course_categories = get_dozent_course_categories();
	if(!empty($course_categories) && is_array($course_categories ) && count($course_categories)){
		?>
		<strong><?php esc_html_e('In', 'dozent') ?></strong>
		<?php
        foreach ($course_categories as $course_category){
            $category_name = $course_category->name;
            $category_link = get_term_link($course_category->term_id);
            echo "<a href='$category_link'>$category_name</a>";
        }
	}
	?>
</div>
</div>