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
                    $active_class = $active === $value['type'] ? 'tutor-bg-primary color-text-white tutor-py-6' : 'tutor-py-5 tutor-border';    
                ?>
                <a href="<?php echo esc_url( $value['url'] ); ?>" class="tutor-radius-6 tutor-px-20 tutor-mr-15 <?php esc_attr_e( $active_class ); ?>">
                    <?php esc_html_e( $value['title'] ); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ( $data['filter_calendar'] ) : ?>
        <form action="" type="get">
            <?php if ( isset( $_GET['course_id']) && '' !== $_GET['course_id'] ): ?>
                <input type="hidden" name="course_id" value="<?php esc_html_e( $_GET['course_id']); ?>">
            <?php endif; ?>    
            <div class="calendar-filter tutor-bs-d-lg-flex tutor-bs-align-items-center tutor-bs-justify-content-between">
                <div class="tutor-input-group tutor-form-control-sm tutor-mr-5">
                    <input
                    type="date"
                    id="tutor-backend-filter-date"
                    name="start_date"
                    class="tutor-form-control"
                    placeholder="<?php echo esc_attr( get_option( 'date_format' ) ); ?>"
                    value="<?php esc_attr_e( $start_date ); ?>"
                    required
                    />
                </div>
                <span class="tutor-mr-5">-</span>
                <div class="tutor-input-group tutor-form-control-sm tutor-mr-10">
                    <input
                    type="date"
                    id="tutor-backend-filter-date"
                    name="end_date"
                    class="tutor-form-control"
                    placeholder="<?php echo esc_attr( get_option( 'date_format' ) ); ?>"
                    value="<?php esc_attr_e( $end_date ); ?>"
                    required
                    />
                </div>  
                <button type="submit" class="tutor-border tutor-radius-6 tutor-p-4 tutor-bg-white tutor-bs-mt-3 tutor-bs-mt-lg-0">
                    <span style="vertical-align:middle" class="ttr-search-filled color-primary-main text-regular-h4"></span>
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>