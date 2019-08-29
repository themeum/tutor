<?php
/**
 * Reviews received
 *
 * @since v.1.2.13
 *
 * @author Themeum
 * @url https://themeum.com
 * @package Tutor
 */



//Pagination Variable
$per_page = 1;
$current_page = max( 1, tutor_utils()->avalue_dot('current_page', $_GET) );
$offset = ($current_page-1)*$per_page;

$reviews = tutor_utils()->get_reviews_by_instructor(get_current_user_id(), $offset, $per_page);
?>

<div class="tutor-dashboard-content-inner">
    <div class="tutor-dashboard-inline-links">
        <ul>
            <li> <a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('reviews'); ?>"> <?php _e('Given', 'tutor'); ?></a> </li>
            <li class="active"><a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('reviews/received-reviews'); ?>"> <?php _e('Received', 'tutor');
            ?></a> </li>
        </ul>
    </div>
    <div class="tutor-dashboard-reviews-wrap">

		<?php
		if ( ! is_array($reviews->results) || ! count($reviews->results)){ ?>
            <div class="tutor-dashboard-content-inner">
                <p><?php _e("Sorry, but you are looking for something that isn't here." , 'tutor'); ?></p>
            </div>
			<?php
		}
		?>

        <div class="tutor-dashboard-reviews">
			<?php
			foreach ($reviews->results as $review){
				$profile_url = tutor_utils()->profile_url($review->user_id);
				?>
                <div class="tutor-dashboard-single-review tutor-review-<?php echo $review->comment_ID; ?>">
                    <div class="tutor-dashboard-review-header">

                        <div class="tutor-dashboard-review-heading">
                            <div class="tutor-dashboard-review-title">
								<?php _e('Course: ', 'tutor'); ?>
                                <a href="<?php echo get_the_permalink($review->comment_post_ID); ?>"><?php echo get_the_title($review->comment_post_ID); ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="individual-dashboard-review-body">
                        <div class="individual-star-rating-wrap">
							<?php tutor_utils()->star_rating_generator($review->rating); ?>
                            <p class="review-meta"><?php  echo sprintf(__('%s ago', 'tutor'), human_time_diff(strtotime($review->comment_date)));  ?></p>
                        </div>

						<?php echo wpautop(stripslashes($review->comment_content)); ?>
                    </div>
                </div>
				<?php
			}
			?>
        </div>

    </div>
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
