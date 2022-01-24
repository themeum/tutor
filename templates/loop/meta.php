<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $post, $authordata;

$profile_url = tutor_utils()->profile_url( $authordata->ID, true );
?>



<div class="list-item-meta tutor-text-medium-caption tutor-color-text-primary tutor-bs-d-flex tutor-mt-10">
    <?php
        $course_duration = get_tutor_course_duration_context( get_the_ID(), true );
        $course_students = tutor_utils()->count_enrolled_users_by_course();
    ?>
    <?php
        if(!empty($course_duration)) { 
    ?>
    <div class="tutor-bs-d-flex tutor-bs-align-items-center">
        <span class="meta-icon ttr-clock-filled tutor-color-text-hints"></span>
        <span><?php echo wp_kses_post( $course_duration ); ?></span>
    </div>
    <?php } ?>
    <?php if ( tutor_utils()->get_option( 'enable_course_total_enrolled' ) ) : ?>
    <div class="tutor-bs-d-flex tutor-bs-align-items-center">
        <span class="meta-icon ttr-user-filled tutor-color-text-hints"></span>
        <span><?php echo esc_html( $course_students ); ?></span>
    </div>
    <?php endif; ?>
</div>

<div class="list-item-author tutor-bs-d-flex tutor-bs-align-items-center tutor-mt-30">
	<div class="tutor-avatar">
		<a href="<?php echo $profile_url; ?>"> <?php echo tutor_utils()->get_tutor_avatar($post->post_author); ?></a>
	</div>
	<div class="tutor-course-meta text-regular-caption tutor-color-text-subsued">
        <span class="tutor-course-meta-name">
            <?php esc_html_e('By', 'tutor') ?>
            <span class="text-medium-caption tutor-color-text-primary">
                <?php esc_html_e(get_the_author()); ?>
            </span>
        </span>
        <span class="tutor-course-meta-cat">
            <?php
                $course_categories = get_tutor_course_categories();
                if(!empty($course_categories) && is_array($course_categories ) && count($course_categories)){
            ?>
            <?php esc_html_e('In', 'tutor') ?>
            <span class="text-medium-caption course-category tutor-color-text-primary">
                <?php
                    foreach ($course_categories as $course_category){
                        $category_name = $course_category->name;
                        $category_link = get_term_link($course_category->term_id);
                        echo wp_kses_post("<a href='$category_link'>$category_name </a>");
                    }
                }
                ?>
            </span>
        </span>
	</div>
</div>
