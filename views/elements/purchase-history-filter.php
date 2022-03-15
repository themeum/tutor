<div class="tutor-wp-dashboard-filter tutor-d-flex tutor-flex-xl-nowrap tutor-flex-wrap tutor-align-items-center tutor-justify-content-between tutor-pb-40">
    <?php 
        $active     = isset( $_GET['period'] ) ? sanitize_text_field( $_GET['period'] ) : '';
        $start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
        $end_date   = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';
    ?>

    <?php if ( count( $data['filter_period'] ) ) : ?>

        <div class="tutor-d-flex tutor-align-items-center tutor-justify-content-between">
            <?php foreach ( $data['filter_period'] as $key => $value ) : ?>
                <?php 
                    $active_class = $active === $value['type'] ? 'tutor-bg-primary tutor-color-white tutor-py-8' : 'tutor-py-4 tutor-border tutor-color-black-60';    
                ?>
                <a href="<?php echo esc_url( $value['url'] ); ?>" class="tutor-radius-6 tutor-px-20 tutor-mr-16 <?php esc_attr_e( $active_class ); ?>">
                    <?php esc_html_e( $value['title'] ); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ( $data['filter_calendar'] ) : ?>
        <div class="tutor-v2-date-range-picker " style="flex-basis:40%;"></div>
    <?php endif; ?>
</div>