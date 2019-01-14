<?php

global $wpdb;

$reviewsCount = (int) $wpdb->get_var("SELECT COUNT(comment_ID) from {$wpdb->comments} WHERE comment_type = 'tutor_course_rating' ;");

$per_page = 50;
$total_items = $reviewsCount;
$current_page = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
$start =  max( 0,($current_page-1)*$per_page );

$course_query  = '';
if ( ! empty($_GET['course_id'])){
	$course_id = sanitize_text_field($_GET['course_id']);
	$course_query = "AND {$wpdb->comments}.comment_post_ID =".$course_id;
}
$user_query  = '';
if ( ! empty($_GET['user_id'])){
	$user_id = sanitize_text_field($_GET['user_id']);
	$user_query = "AND {$wpdb->comments}.user_id =".$user_id;
}

$reviews = $wpdb->get_results("select {$wpdb->comments}.comment_ID, 
			{$wpdb->comments}.comment_post_ID, 
			{$wpdb->comments}.comment_author, 
			{$wpdb->comments}.comment_author_email, 
			{$wpdb->comments}.comment_date, 
			{$wpdb->comments}.comment_content, 
			{$wpdb->comments}.user_id, 
			{$wpdb->commentmeta}.meta_value as rating,
			{$wpdb->users}.display_name 
			
			from {$wpdb->comments}
			INNER JOIN {$wpdb->commentmeta} 
			ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id {$course_query} {$user_query}
			INNER  JOIN {$wpdb->users}
			ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
			AND meta_key = 'tutor_rating' ORDER BY comment_ID DESC LIMIT {$start},{$per_page} ;");
?>

<div class="tutor-bg-white box-padding">

    <h3><?php _e('Reviews', 'tutor-report'); ?></h3>

    <p><?php echo sprintf(__('Total reviews %d', 'tutor-report'), $reviewsCount) ?></p>

    <table class="widefat tutor-report-table">
        <tr>
            <th><?php _e('User', 'tutor-report'); ?> </th>
            <th><?php _e('Course', 'tutor-report'); ?> </th>
            <th><?php _e('Rating', 'tutor-report'); ?> </th>
            <th><?php _e('Reviews', 'tutor-report'); ?> </th>
            <th><?php _e('Time', 'tutor-report'); ?> </th>
            <th>#</th>
        </tr>
		<?php
		if (is_array($reviews) && count($reviews)){
			foreach ($reviews as $review){
				?>
                <tr>
                    <td><a href="<?php echo add_query_arg(array('user_id' => $review->user_id)); ?>"><?php echo $review->display_name;
							?></a> </td>
                    <td><a href="<?php echo add_query_arg(array('course_id' => $review->comment_post_ID)); ?>"><?php echo get_the_title
							($review->comment_post_ID); ?></a> </td>
                    <td><?php tutor_utils()->star_rating_generator($review->rating, true); ?></td>
                    <td><?php echo wpautop($review->comment_content); ?></td>
                    <td><?php echo human_time_diff(strtotime($review->comment_date)).' '.__('ago', 'tutor-report'); ?></td>
                    <td>
                        <button type="button" class="button tutor-delete-link tutor-rating-delete-link" data-rating-id="<?php echo $review->comment_ID; ?>">
                            <i class="tutor-icon-trash"></i> <?php _e('Delete'); ?>
                        </button>
                    </td>
                </tr>
				<?php
			}
		}
		?>
    </table>

    <div class="tutor-pagination" >
		<?php
		echo paginate_links( array(
			'base' => str_replace( $current_page, '%#%', "admin.php?page=tutor_report&sub_page=reviews&paged=%#%" ),
			'current' => max( 1, $current_page ),
			'total' => ceil($total_items/$per_page)
		) );
		?>
    </div>
</div>