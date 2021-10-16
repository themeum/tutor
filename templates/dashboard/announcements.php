<?php
if (!defined('ABSPATH'))
    exit;
/**
 * Template for displaying Announcements
 *
 * @since v.1.7.9
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.7.9
 */
$per_page           = 10;
$paged              = max(1, tutor_utils()->avalue_dot('current_page', $_GET));

$order_filter       = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
$search_filter      = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
//announcement's parent
$course_id          = isset($_GET['course-id']) ? sanitize_text_field($_GET['course-id']) : '';
$date_filter        = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';

$year               = date('Y', strtotime($date_filter));
$month              = date('m', strtotime($date_filter));
$day                = date('d', strtotime($date_filter));

$args = array(
    'post_type'         => 'tutor_announcements',
    'post_status'       => 'publish',
    's'                 => sanitize_text_field($search_filter),
    'post_parent'       => sanitize_text_field($course_id),
    'posts_per_page'    => sanitize_text_field($per_page),
    'paged'             => sanitize_text_field($paged),
    'orderBy'           => 'ID',
    'order'             => sanitize_text_field($order_filter),

);
if (!empty($date_filter)) {
    $args['date_query'] = array(
        array(
            'year'      => $year,
            'month'     => $month,
            'day'       => $day
        )
    );
}
if (!current_user_can('administrator')) {
    $args['author'] = get_current_user_id();
}
$the_query = new WP_Query($args);

//get courses
$courses = (current_user_can('administrator')) ? tutils()->get_courses() : tutils()->get_courses_by_instructor();
$image_base = tutor()->url . '/assets/images/';


?>

<div class="tutor-dashboard-content-inner">
    <h4><?php echo __('Announcement', 'tutor'); ?></h4>
    <!--notice-->
    <div class="tutor-component-three-col-action new-announcement-wrap">
        <div class="tutor-announcement-big-icon">
            <i class="tutor-icon-speaker"></i>
        </div>
        <div>
            <small><?php _e('Create Announcement', 'tutor'); ?></small>
            <p>
                <strong>
                    <?php _e('Notify all students of your course', 'tutor'); ?>
                </strong>
            </p>
        </div>
        <div class="new-announcement-button">
            <button type="button" class="tutor-btn" data-tutor-modal-target="tutor_announcement_new">
                <?php _e('Add New Announcement', 'tutor'); ?>
            </button>
        </div>
    </div>
    <!--notice end-->
</div>


<!--Filter-->
<div class="tutor-row tutor-mb-30">
    <div class="tutor-col-12 tutor-col-lg-6">
        <label class="tutor-d-block">
            <?php _e('Courses', 'tutor'); ?>
        </label>
        <select class="tutor-form-select tutor-announcement-course-sorting">
           
            <option value=""><?php _e('All', 'tutor'); ?></option>
        
            <?php if ($courses) : ?>
                <?php foreach ($courses as $course) : ?>
                    <option value="<?php echo esc_attr($course->ID) ?>" <?php selected($course_id, $course->ID, 'selected') ?>>
                        <?php echo $course->post_title; ?>
                    </option>
                <?php endforeach; ?>
            <?php else : ?>
                <option value=""><?php _e('No course found', 'tutor'); ?></option>
            <?php endif; ?>
        </select>
    </div>

    <div class="tutor-col-6 tutor-col-lg-3">
        <label class="tutor-d-block"><?php _e('Sort By', 'tutor'); ?></label>
        <select class="tutor-form-select tutor-announcement-order-sorting">
            <option <?php selected($order_filter, 'ASC'); ?>><?php _e('ASC', 'tutor'); ?></option>
            <option <?php selected($order_filter, 'DESC'); ?>><?php _e('DESC', 'tutor'); ?></option>
        </select>
    </div>

    <div class="tutor-col-6 tutor-col-lg-3 tutor-announcement-datepicker">
        <label class="tutor-d-block"><?php _e('Date', 'tutor'); ?></label>
        <input type="text" class="tutor-form-control tutor_date_picker tutor-announcement-date-sorting" id="tutor-announcement-datepicker" value="<?php echo $date_filter !== '' ? tutor_get_formated_date( get_option( 'date_format' ), $date_filter ) : ''; ?>" placeholder="<?php echo get_option( 'date_format' ); ?>" autocomplete="off" />
        <i class="tutor-icon-calendar"></i>
    </div>
</div>
<!--Filter end-->

<?php 
    $announcements = $the_query->have_posts() ? $the_query->posts : array();

    tutor_load_template_from_custom_path(tutor()->path . '/views/fragments/announcement-list.php', array(
        'announcements' => is_array( $announcements ) ? $announcements : array()
    ));
?>

<!--pagination-->
<div class="tutor-pagination">
    <?php
        $big = 999999999; // need an unlikely integer
        
        echo paginate_links( array(
           
            'format'    => '?current_page=%#%',
            'current'   => $paged,
            'total'     => $the_query->max_num_pages
        ) );
      
    ?>
</div>
<!--pagination end-->

<?php
include 'announcements/details.php';
?>
