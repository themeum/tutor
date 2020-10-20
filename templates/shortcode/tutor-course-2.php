<div class="tutor-course-filter-wrapper">
    <div class="tutor-course-filter-container">
        <?php tutor_load_template('course-filter.filters'); ?>
    </div>
    <div>
        <div class="<?php tutor_container_classes() ?> tutor-course-filter-loop-container">
            <?php tutor_load_template('archive-course-init'); ?>
        </div><!-- .wrap -->
    </div>
</div>