<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<h2><?php _e('Purchase History', 'tutor'); ?></h2>

<?php
$orders = tutor_utils()->get_orders_by_user_id();

if (tutor_utils()->count($orders)){
	?>
    <div class="responsive-table-wrap">
        <table>
            <tr>
                <th><?php _e('ID', 'tutor'); ?></th>
                <th><?php _e('Courses', 'tutor'); ?></th>
                <th><?php _e('Amount', 'tutor'); ?></th>
                <th><?php _e('Status', 'tutor'); ?></th>
                <th><?php _e('Date', 'tutor'); ?></th>
            </tr>
            <?php
            foreach ($orders as $order){
                $wc_order = wc_get_order($order->ID);
                ?>
                <tr>
                    <td>#<?php echo $order->ID; ?></td>
                    <td>
                        <?php
                        $courses = tutor_utils()->get_course_enrolled_ids_by_order_id($order->ID);
                        if (tutor_utils()->count($courses)){
                            foreach ($courses as $course){
                                echo '<p>'.get_the_title($course['course_id']).'</p>';
                            }
                        }
                        ?>
                    </td>
                    <td><?php echo tutor_utils()->tutor_price($wc_order->get_total()); ?></td>
                    <td><?php echo tutor_utils()->order_status_context($order->post_status); ?></td>

                    <td>
                        <?php echo date_i18n(get_option('date_format'), strtotime($order->post_date)) ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>

	<?php
}else{
	echo _e('No purchase history available', 'tutor');
}

?>
