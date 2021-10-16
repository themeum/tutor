<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Since 1.7.9
 * configure query with get params
 */
$per_page           = 10;
$paged              = (isset($_GET['paged']) && is_numeric($_GET['paged']) && $_GET['paged']>=1) ? $_GET['paged'] : 1;

$order_filter       = (isset($_GET['order']) && strtolower($_GET['order'])=='asc') ? 'ASC' : 'DESC';
$search_filter      = sanitize_text_field( tutor_utils()->array_get('search', $_GET, '') );
//announcement's parent
$course_id          = sanitize_text_field( tutor_utils()->array_get('course-id', $_GET, '') );
$date_filter        = sanitize_text_field( tutor_utils()->array_get('date', $_GET, '') );

$year               = date('Y', strtotime($date_filter));
$month              = date('m', strtotime($date_filter));
$day                = date('d', strtotime($date_filter));

$args = array(
    'post_type'         => 'tutor_announcements',
    'post_status'       => 'publish',
    's'                 => $search_filter,
    'post_parent'       => $course_id,
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
?>
<div class="wrap">
    <div class="tutor-admin-search-box-container">

        <div>
            <div class="menu-label"><?php _e('Search', 'tutor'); ?></div>
            <div>
                <input type="text" class="tutor-report-search tutor-announcement-search-field" value="<?php echo $search_filter; ?>" autocomplete="off" placeholder="<?php _e('Search Announcements', 'tutor'); ?>" />
                <button class="tutor-report-search-btn tutor-announcement-search-sorting"><i class="tutor-icon-magnifying-glass-1"></i></button>
            </div>
        </div>

        <div>
            <div class="menu-label"><?php _e('Courses', 'tutor'); ?></div>
            <div>
                <?php
                //get courses
                $courses = (current_user_can('administrator')) ? tutils()->get_courses() : tutils()->get_courses_by_instructor();
                ?>

                <select class="tutor-report-category tutor-announcement-course-sorting">
                
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
        </div>

        <div>
            <div class="menu-label"><?php _e('Sort By', 'tutor'); ?></div>
            <div>
                <select class="tutor-report-sort tutor-announcement-order-sorting">
                    <option <?php selected($order_filter, 'ASC'); ?>>ASC</option>
                    <option <?php selected($order_filter, 'DESC'); ?>>DESC</option>
                </select>
            </div>
        </div>

        <div>
            <div class="menu-label"><?php _e('Date', 'tutor'); ?></div>
            <div class="date-range-input">
                <input type="text" class="tutor_date_picker tutor-announcement-date-sorting" id="tutor-announcement-datepicker" placeholder="<?php _e( get_option( 'date_format' ), 'tutor' );?>" value="<?php echo '' !== $date_filter ? tutor_get_formated_date( get_option( 'date_format' ), $date_filter ) : ''; ?>" autocomplete="off" />
                <i class="tutor-icon-calendar"></i>
            </div>
        </div>
    </div>

    <div class="tutor-list-wrap tutor-report-course-list">
        <div class="tutor-list-header tutor-announcements-header">
            <div class="heading"><?php _e('Announcements', 'tutor'); ?></div>
            <button type="button" class="tutor-btn tutor-announcement-add-new" data-tutor-modal-target="tutor_announcement_new">
                <?php _e('Add new', 'tutor'); ?>
            </button>
        </div>
    </div>
    <br/>
    <?php 
        $announcements = $the_query->have_posts() ? $the_query->posts : array();

        tutor_load_template_from_custom_path(tutor()->path . '/views/fragments/announcement-list.php', array(
            'announcements' => is_array( $announcements ) ? $announcements : array()
        ));
    ?>

    <!--pagination-->
    <div class="tutor-announcement-pagination">
        <?php
        $big = 999999999; // need an unlikely integer

        echo paginate_links(array(
            'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format'    => '?current_page=%#%',
            'current'   => $paged,
            'total'     => $the_query->max_num_pages
        ));
        ?>
    </div>
    <!--pagination end-->
</div>