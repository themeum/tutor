<?php 
    ob_start();
    $category_id        = '';
    $total_categories   = isset( $categories ) && is_array( $categories ) && count($categories) ? : 0; 
    foreach($categories as $category) {
        $category_id    = $category->term_id;
        ?>
        <div class="tutor-form-check tutor-mb-25">
            <input
                id="item-a"
                type="checkbox"
                class="tutor-form-check-input tutor-form-check-square"
                name="category"
                value="<?php esc_attr_e( $category->term_id );?>"/>
            <label for="item-a text-title text-medium-caption">
                 <?php esc_html_e( $category->name );?>
            </label>
        </div>
        <?php
    }

    $category_list = ob_get_clean();
?>

<div class="tutor-instructor-filter" 
    <?php 
        foreach($attributes as $key => $value) {
            echo 'data-' . $key . '="' . $value . '" ';
        }
    ?>>
    <div class="tutor-instructor-filter-sidebar">
        <div class="tutor-instructor-customize-wrapper">
            <div class="tutor-instructor-filters">
                <i class="ttr ttr-customize-filled"></i>
                <span class="text-medium-h5 text-primary">
                    <?php _e( 'Filters', 'tutor' );?>
                </span>
            </div>
            <div class="tutor-instructor-customize-clear clear-instructor-filter">
                <i class="tutor-icon-line-cross"></i>
                <span className="text-thin-body">
                    <?php _e( 'Clear', 'tutor' );?>
                </span>
            </div>
        </div>
        <div class="tutor-instructor-categories-wrapper">
            <div>
                <div class="tutor-category-text">
                    <span>
                        <?php _e( 'Category', 'tutor' );?>
                    </span>
                </div>
                <br/>
            </div>
            <div class="course-category-filter">
                <?php echo $category_list; ?>
            </div>
            <?php if ( $total_categories ): ?>
                <div class="tutor-instructor-category-show-more">
                    <div class="text-medium-caption" data-id="<?php esc_attr_e( $category_id ); ?>">
                        <i class="ttr ttr-plus-bold-filled"></i>
                        <span class="text-subsued text-medium-caption">
                            <?php _e( 'Show More', 'tutor' );?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="tutor-instructor-ratings-wrapper">
            <div class="tutor-instructor-rating-title">
                <span>
                    <?php _e( 'Ratings', 'tutor' ); ?>
                </span>
            </div>
            <div class="tutor-instructor-rating-range-wrapper">
                <div class="tutor-instructor-ratings">
                    <?php for ($i = 1; $i < 6; $i++): ?>
                        <i class="ttr ttr-star-line-filled" data-value="<?php echo $i;?>"></i>
                    <?php endfor;?> 
                </div>
                <span class="text-subsued text-medium-body tutor-instructor-rating-filter"></span>   
            </div>
        </div>
    </div>
    <div class="tutor-instructor-filter-result">
        <div class="filter-pc">
            <div class="keyword-field">
                <i class="tutor-icon-magnifying-glass-1"></i>
                <input type="text" name="keyword" placeholder="<?php _e('Search any instructor...', 'tutor'); ?>"/>
            </div>
        </div>
        <div class="tutor-instructor-short-relevant">

        </div>
        <div class="filter-mobile">
            <div class="mobile-filter-container">
                <div class="keyword-field mobile-screen">
                    <i class="tutor-icon-magnifying-glass-1"></i>
                    <input type="text" name="keyword" placeholder="<?php _e('Search any instructor...', 'tutor'); ?>"/>
                </div>
                <i class="tutor-icon-filter-tool-black-shape"></i>
            </div>
            <div class="mobile-filter-popup">
                <div>
                    <div class="tutor-category-text">
                        <div class="expand-instructor-filter"></div>
                        <span>Category</span>
                        <span class="clear-instructor-filter">
                            <i class="tutor-icon-line-cross"></i> <span><?php _e('Clear All', 'tutor'); ?></span>
                        </span>
                    </div>
                    <div>
                        <?php echo $category_list; ?>
                    </div>
                    <div>
                        <button class="tutor-btn btn-sm">
                            <?php _e('Apply Filter', 'tutor'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <div class="selected-cate-list">

            </div>
        </div>
        <div class="filter-result-container">
            <?php echo $content; ?>
        </div>
    </div>
</div>