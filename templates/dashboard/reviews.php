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
        <h3><?php _e('Reviews', 'tutor'); ?></h3>
		<?php if (current_user_can(tutor()->instructor_role)): ?>
            <div class="tutor-dashboard-inline-links">
                <ul>
                    <li class="active">
                        <a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('reviews'); ?>"> 
                            <?php _e('Received', 'tutor'); ?> (<?php echo $reviews->count; ?>)
                        </a> 
                    </li>
                    <?php if($given_count): ?>
                        <li> 
                            <a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('reviews/given-reviews'); ?>"> 
                                <?php _e('Given', 'tutor'); ?> (<?php echo $given_count; ?>)
                            </a> 
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
		<?php endif; ?>

        <?php if ($reviews->count): ?>
            <table class="tutor-ui-table tutor-ui-table-responsive table-reviews">
                <thead>
                    <tr>
                        <th>
                            <span class="text-regular-small tutor-color-text-subsued">
                                <?php esc_html_e('Student', 'tutor'); ?>
                            </span>
                        </th>
                        <th>
                            <span class="text-regular-small tutor-color-text-subsued">
                                <?php esc_html_e('Date', 'tutor'); ?>
                            </span>
                        </th>
                        <th>
                            <span class="text-regular-small tutor-color-text-subsued">
                                <?php esc_html_e('Feedback', 'tutor'); ?>
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
                            <td data-th="<?php esc_html_e('Student', 'tutor'); ?>" class="column-fullwidth">
                                <div class="td-avatar">
                                    <img src="<?php echo esc_url($avatar_url); ?>" alt="student avatar"/>
                                    <span class="tutor-text-medium-body  tutor-color-text-primary">
                                        <?php esc_html_e($student_name); ?>
                                    </span>
                                </div>
                            </td>
                            <td data-th="<?php esc_html_e('Date', 'tutor'); ?>">
                                <span class="text-medium-caption tutor-color-text-primary">
                                    <?php echo tutor_get_formated_date(null, $review->comment_date); ?>
                                </span>
                            </td>
                            <td data-th="<?php esc_html_e('Feedback', 'tutor'); ?>">
                                <div class="td-feedback">
                                    <div class="td-tutor-rating tutor-text-regular-body tutor-color-text-subsued">
                                        <?php tutor_utils()->star_rating_generator_v2($review->rating, null, true); ?>
                                    </div>
                                    <p class="review-text tutor-color-text-subsued tutor-mb-0">
                                        <?php echo htmlspecialchars(stripslashes( $review->comment_content )); ?>
                                    </p>
                                    <p class="course-name tutor-text-medium-small tutor-color-text-title tutor-mb-0">
                                        <strong><?php esc_html_e('Course', 'tutor'); ?>:</strong>&nbsp;
                                        <span data-href="<?php echo esc_url(get_the_permalink($review->comment_post_ID)); ?>">
                                            <?php esc_html_e(get_the_title($review->comment_post_ID)); ?>
                                        </span>
                                    </p>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="tutor-dashboard-content-inner">
                <?php tutor_utils()->tutor_empty_state(); ?>
            </div>
        <?php endif; ?>
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
