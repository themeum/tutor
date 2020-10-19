<?php
    $filter_object = new \TUTOR\Course_Filter();
    $filter_levels = array(
        'beginner'=>'Beginner',
        'intermediate'=>'Intermediate',
        'expert'=>'Expert'
    );
    $filter_prices=array(
        'free'=>'Free',
        'paid'=>'Paid'
    );
?>
<div>
    <div class="tutor-course-search-field">
        <input type="text" name="tutor-course-filter-keyword" placeholder="<?php _e('Search...'); ?>"/>
        <i class="tutor-icon-magnifying-glass-1"></i>
    </div>
    <div>
        <div>
            <h4><?php _e('Category', 'tutor'); ?></h4>
            <?php $filter_object->render_terms('category'); ?>
        </div>
        <div>
            <h4><?php _e('Tag', 'tutor'); ?></h4>
            <?php $filter_object->render_terms('tag'); ?>
        </div>
    </div>
    <div>
        <div>
            <h4><?php _e('Level', 'tutor'); ?></h4>
            <?php 
                foreach($filter_levels as $value=>$title){
                    ?>
                        <label>
                            <input type="checkbox" name="tutor-course-filter-level" value="<?php echo $value; ?>"/>&nbsp;
                            <?php echo $title; ?>
                        </label>
                    <?php
                }
            ?>
        </div>
        <div>
            <h4><?php _e('Price', 'tutor'); ?></h4>
            <?php 
                foreach($filter_prices as $value=>$title){
                    ?>
                        <label>
                            <input type="checkbox" name="tutor-course-filter-price" value="<?php echo $value; ?>"/>&nbsp;
                            <?php echo $title; ?>
                        </label>
                    <?php
                }
            ?>
        </div>
    </div>
</div>