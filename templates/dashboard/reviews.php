<?php
/**
 * Reviews received
 *
 * @since v.1.2.13
 *
 * @author Themeum
 * @url https://themeum.com
 * @package Tutor
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if(!tutor_utils()->is_instructor()) {
    include __DIR__ . '/reviews/given-reviews.php'; 
    return;
}

//Pagination Variable
$per_page = tutor_utils()->get_option('pagination_per_page', 20);
$current_page = max( 1, tutor_utils()->avalue_dot('current_page', $_GET) );
$offset = ($current_page-1)*$per_page;

$reviews = tutor_utils()->get_reviews_by_instructor(get_current_user_id(), $offset, $per_page);
$given_count = tutor_utils()->get_reviews_by_user(0, 0, 0, true)->count;
?>

    <div class="tutor-dashboard-content-inner">
		<?php
		if (current_user_can(tutor()->instructor_role)){
			?>
            <div class="tutor-dashboard-inline-links">
                <ul>
                    <li class="active"><a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('reviews'); ?>"> <?php _e('Received', 'tutor'); ?> (<?php echo $reviews->count; ?>)</a> </li>
                    <?php if($given_count): ?>
                        <li> <a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('reviews/given-reviews'); ?>"> <?php _e('Given', 'tutor'); ?> (<?php echo $given_count; ?>)</a> </li>
                    <?php endif; ?>
                </ul>
            </div>
		<?php } ?>

        <?php
			if ($reviews->count){
				?>
                <table class="tutor-ui-table tutor-ui-table-responsive table-reviews">
                    <thead>
                        <tr>
                            <th>
                                <span class="text-regular-small color-text-subsued">
                                    <?php _e('Student', 'tutor'); ?>
                                </span>
                            </th>
                            <th>
                                <span class="text-regular-small color-text-subsued">
                                    <?php _e('Date', 'tutor'); ?>
                                </span>
                            </th>
                            <th>
                                <span class="text-regular-small color-text-subsued">
                                    <?php _e('Feedback', 'tutor'); ?>
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        foreach ($reviews->results as $review){
                            $user_data = get_userdata( $review->user_id );
                            $profile_url = tutor_utils()->profile_url($review->user_id);
                            $avatar_url = get_avatar_url($review->user_id);
                            $student_name = $user_data->display_name;
                            ?>
                            <tr>
                                <td data-th="<?php _e('Student', 'tutor'); ?>" class="column-fullwidth">
                                    <div class="td-avatar">
                                        <img src="<?php echo $avatar_url; ?>" alt="student avatar"/>
                                        <span class="text-medium-body color-text-primary">
                                            <?php echo $student_name; ?>
                                        </span>
                                    </div>
                                </td>
                                <td data-th="<?php _e('Date', 'tutor'); ?>">
                                    <span class="text-medium-caption color-text-primary">
                                        <?php echo  date( get_option( 'date_format'), strtotime($review->comment_date) ); ?>
                                    </span>
                                </td>
                                <td data-th="<?php _e('Feedback', 'tutor'); ?>">
                                    <div class="td-feedback">
                                        <div class="td-tutor-rating text-regular-body color-text-subsued">
                                            <?php tutor_utils()->star_rating_generator($review->rating); ?>
                                        </div>
                                        <p class="review-text color-text-subsued tutor-mb-0">
                                            <?php echo htmlspecialchars($review->comment_content); ?>
                                        </p>
                                        <p class="course-name text-medium-small color-text-title tutor-mb-0">
                                            <strong><?php _e('Course', 'tutor'); ?>:</strong>&nbsp;
                                            <a href="<?php echo get_the_permalink($review->comment_post_ID); ?>">
                                                <?php echo get_the_title($review->comment_post_ID); ?>
                                            </a>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
					?>
                    </tbody>
                </table>
			<?php }else{
				?>
                <div class="tutor-dashboard-content-inner">
                    <p><?php _e("Sorry, but you are looking for something that isn't here." , 'tutor'); ?></p>
                </div>
				<?php
			} 
        ?>
    </div>

<?php
if ($reviews->count){
	?>
    <div class="tutor-pagination">
		<?php
		echo paginate_links( array(
			'format' => '?current_page=%#%',
			'current' => $current_page,
			'total' => ceil($reviews->count/$per_page)
		) );
		?>
    </div>
	<?php
}
