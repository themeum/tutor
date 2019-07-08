<?php
global $wpdb;
$course_id = $_GET['course_id'];

// @TODO: function should be moved to utils
function get_assignment_by_course_id($course_id = 0){
    global $wpdb;
    $course_id = tutor_utils()->get_post_id($course_id);
    $topics = $wpdb->get_col("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'topics' AND post_parent = {$course_id}");
    $topics = join("','",$topics);
    $assignments = $wpdb->get_col("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = '{$wpdb->prefix}assignments' AND post_parent IN ('$topics')");
    return $assignments;
}


?>

<h3><?php echo get_the_title($course_id) ?></h3>


<table>
    <thead>
    <tr>
        <th>Assignment Name</th>
        <th>Total Mark</th>
        <th>Total Submit</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $assignments = get_assignment_by_course_id($course_id);
    foreach ($assignments as $item){
        $max_mark = tutor_utils()->get_assignment_option($item, 'total_mark');
        $comment_count = $wpdb->get_var("SELECT COUNT(comment_ID) FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND comment_post_ID = {$item}");
        ?>
        <tr>
            <td><?php echo get_the_title($item); ?></td>
            <td><?php echo $max_mark; ?></td>
            <td><?php echo $comment_count ?></td>
        </tr>
        <?php
    }

    ?>
    </tbody>
</table>