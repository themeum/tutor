<div class="tutor-instructor-filter" 
    <?php 
        foreach($attributes as $key => $value) {
            echo 'data-' . $key . '="' . $value . '" ';
        }
    ?>>
    <div class="tutor-instructor-filter-sidebar">
        <div class="course-filter">
            <h3>    
                <span class="expand-filter">
                    <img src="<?php echo tutor()->url; ?>/assets/images/icon-filter-blue.svg"/>
                    <?php _e('Filters', 'tutor'); ?>
                </span>
                <span class="clear-filter">
                    <i class="tutor-icon-line-cross"></i> <span><?php _e('Clear', 'tutor'); ?></span>
                </span>
            </h3>
        </div>
        <div class="course-categories">
            <h4><?php _e('Category', 'tutor'); ?></h4>
            <?php 
                foreach($categories as $category) {
                    ?>
                    <div>
                        <label>
                            <input type="checkbox" name="category" value="<?php echo $category->term_id; ?>"/> 
                            <?php echo $category->name; ?>
                        </label>
                    </div>
                    <?php
                }
            ?>
        </div>
    </div>
    <div class="tutor-instructor-filter-result">
        <div class="keyword-field">
            <i class="tutor-icon-magnifying-glass-1"></i>
            <input type="text" name="keyword" placeholder="<?php _e('Search any instructor...', 'tutor'); ?>"/>
        </div>
        <div class="filter-result-container">
            <?php echo $content; ?>
        </div>
    </div>
</div>