<?php
/**
 * Template for displaying Assignments
 *
 * @since v.1.7.9
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.7.9
 */

global $wpdb;

$per_page = 20;
$current_page = max( 1, tutor_utils()->avalue_dot('current_page', $_GET) );
$offset = ($current_page-1)*$per_page;

$current_user = get_current_user_id();
$assignments = tutor_utils()->get_assignments_by_instructor(null,  compact('per_page', 'offset'));

if($assignments->count){ ?>
    <div class="tutor-dashboard-info-table-wrap">
        <div class="heading">
            <h3><?php _e('Announcements', 'tutor'); ?></h3>
        </div>
        <table class="tutor-dashboard-info-table tutor-dashboard-assignment-table">
            <thead>
            <tr>
                <th><?php _e('Date', 'tutor') ?></th>
                <th style="text-align:left"><?php _e('Announcements', 'tutor') ?></th>
               
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($assignments->results as $item){
                $max_mark = tutor_utils()->get_assignment_option($item->ID, 'total_mark');
                $course_id = tutor_utils()->get_course_id_by_assignment($item->ID);
                $course_url = tutor_utils()->get_tutor_dashboard_page_permalink('assignments/course');
                $submitted_url = tutor_utils()->get_tutor_dashboard_page_permalink('assignments/submitted');
                $comment_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(comment_ID) FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND comment_post_ID = %d", $item->ID));
                // @TODO: assign post_meta is empty if user don't click on update button (http://prntscr.com/oax4t8) but post status is publish
                ?>
                <tr>
                    <td>11/11/21</td>
                    <td style="text-align:left">
                        <h5><?php echo $item->post_title ?></h5>
                        <h5><a href='<?php echo esc_url($course_url.'?course_id='.$course_id) ?>'><?php echo __('Course: ', 'tutor'). get_the_title($course_id); ?> </a></h5>
                    </td>
                    
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>

	<div class="tutor-pagination">
		<?php

		echo paginate_links( array(
			'format' => '?current_page=%#%',
			'current' => $current_page,
			'total' => ceil($assignments->count/$per_page)
		) );
		?>
	</div>

<?php } else{
	echo '<p>'.__('No announcements posted yet', 'tutor' ).'</p>';
}