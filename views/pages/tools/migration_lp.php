
<div class="tools-migration-ld-page">

    <?php
    global $wpdb;

    $courses_count = (int) $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'lp_course';");
    $orders_count = (int) $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'lp_order';");

    $reviews_count = (int) $wpdb->get_var("SELECT COUNT(comments.comment_ID) FROM {$wpdb->comments} comments INNER JOIN {$wpdb->commentmeta} cm ON cm.comment_id = comments.comment_ID AND cm.meta_key = '_lpr_rating' WHERE comments.comment_type = 'review';");
    ?>

    <button id="migrate_lp_courses_btn" class="tutor-button tutor-button-primary">
        <?php echo sprintf(__('Migrate %s courses', 'tutor'), $courses_count); ?>
    </button>

    <form method="post">
        <input type="hidden" name="tutor_action" value="migrate_lp_orders">
        <button type="submit" id="migrate_lp_orders_btn" class="tutor-button button-success">
            <?php echo sprintf(__('Migrate %s orders', 'tutor'), $orders_count); ?>
        </button>
    </form>

    <form method="post">
        <input type="hidden" name="tutor_action" value="migrate_lp_reviews">
        <button type="submit" id="migrate_lp_orders_btn" class="tutor-button button-success">
            <?php echo sprintf(__('Migrate %s Reviews', 'tutor'), $reviews_count); ?>
        </button>
    </form>

    <div id="course_migration_progress" style="margin-top: 50px;"></div>

</div>