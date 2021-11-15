<div class="tutor-wp-dashboard-filter tutor-bs-d-flex tutor-bs-flex-xl-nowrap tutor-bs-flex-wrap tutor-bs-align-items-center tutor-bs-justify-content-between tutor-pb-40">
    <?php 
        $active     = isset( $_GET['period'] ) ? sanitize_text_field( $_GET['period'] ) : '';
        $start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
        $end_date   = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';
    ?>

    <?php if ( count( $data['filter_period'] ) ) : ?>

        <div class="tutor-bs-d-flex tutor-bs-align-items-center tutor-bs-justify-content-between">
            <?php foreach ( $data['filter_period'] as $key => $value ) : ?>
                <?php 
                    $active_class = $active === $value['type'] ? 'tutor-bg-primary color-text-white tutor-py-6' : 'tutor-py-5 tutor-border color-text-subsued';    
                ?>
                <a href="<?php echo esc_url( $value['url'] ); ?>" class="tutor-radius-6 tutor-px-20 tutor-mr-15 <?php esc_attr_e( $active_class ); ?>">
                    <?php esc_html_e( $value['title'] ); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ( $data['filter_calendar'] ) : ?>
        <div class="tutor-v2-date-range-picker " style="flex-basis:40%;"></div>
    <?php endif; ?>
</div>