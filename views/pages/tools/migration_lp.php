
<div class="tools-migration-ld-page">

    <?php
    global $wpdb;

    $courses_count = (int) $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'lp_course';");
    $orders_count = (int) $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'lp_order';");

    ?>

    <button id="migrate_lp_courses_btn" class="tutor-button tutor-button-primary"><?php echo sprintf(__('Migrate %s courses', 'tutor'), $courses_count); ?></button>

    <button id="migrate_lp_orders_btn" class="tutor-button button-success"><?php echo sprintf(__('Migrate %s orders', 'tutor'), $orders_count); ?></button>

    <p>----</p>

    <?php
    tutor_maintenance_mode(false);

    $course_id = 1567;

    ?>

</div>