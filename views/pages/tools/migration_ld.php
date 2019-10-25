
<div class="tools-migration-ld-page">


    <?php
    global $wpdb;

    $learndash_courses = (int) $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'sfwd-courses' ;");
    if ( ! $learndash_courses){
	    tutor_alert(__('No courses found to be migrate', 'tutor') );
    }else{
	    tutor_alert(sprintf(__('Found %s courses, migrated %s courses', 'tutor'), $learndash_courses, 0), 'success' );

    }




    ?>





</div>