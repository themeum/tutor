<?php
/**
 * Purchase history
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

defined( 'ABSPATH' ) || exit;

//global variables
$user_id     = get_current_user_id();
$time_period = $active = isset( $_GET['period'] ) ? $_GET['period'] : '';
$start_date  = isset( $_GET['start_date']) ? sanitize_text_field( $_GET['start_date'] ) : '';
$end_date    = isset( $_GET['end_date']) ? sanitize_text_field( $_GET['end_date'] ) : '';

$paged       = ( isset( $_GET['current_page'] ) && is_numeric( $_GET['current_page'] ) && $_GET['current_page'] >= 1 ) ? $_GET['current_page'] : 1;
$per_page    = tutor_utils()->get_option( 'pagination_per_page', 10 );
$offset      = ( $per_page * $paged ) - $per_page;

    if ( '' !== $start_date ) {
        $start_date = tutor_get_formated_date( 'Y-m-d', $start_date );
    }
    if ( '' !== $end_date ) {
        $end_date = tutor_get_formated_date( 'Y-m-d', $end_date );
    }
?>

<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24"><?php esc_html_e( 'Order History', 'tutor' ); ?></div>
<div class="tutor-purchase-history">
    <!--filter buttons tabs-->
    <?php
        /**
         * Prepare filter period buttons
         *
         * Array structure is required as below
         *
         * @since 2.0.0
         */
        $filter_period = array(
            array(
                'url'   => esc_url( tutor_utils()->tutor_dashboard_url() . 'purchase_history?period=today' ),
                'title' => __( 'Today', 'tutor' ),
                'type'  => 'today'
            ),
            array(
                'url'   => esc_url( tutor_utils()->tutor_dashboard_url() . 'purchase_history?period=monthly' ),
                'title' => __( 'Monthly', 'tutor' ),
                'type'  => 'monthly'
            ),
            array(
                'url'   => esc_url( tutor_utils()->tutor_dashboard_url() . 'purchase_history?period=yearly' ),
                'title' => __( 'Yearly', 'tutor' ),
                'type'  => 'yearly'
            ),
        );

        /**
         * Calendar date buttons
         *
         * Array structure is required as below
         *
         * @since 2.0.0
         */

        $filter_period_calendar = array(
            'filter_period'   => $filter_period,
            'filter_calendar' => true
        );

        $filter_period_calendar_template = tutor()->path . 'views/elements/purchase-history-filter.php';
        tutor_load_template_from_custom_path( $filter_period_calendar_template, $filter_period_calendar );

        $orders       = tutor_utils()->get_orders_by_user_id( $user_id, $time_period, $start_date, $end_date, $offset, $per_page );
        $total_orders = tutor_utils()->get_total_orders_by_user_id( $user_id, $time_period, $start_date, $end_date );
        $monetize_by  = tutor_utils()->get_option( 'monetize_by' );

    ?>
    <!--filter button tabs end-->
</div>

<!-- Purchase history table -->
<div class="tutor-ui-table-wrapper">
    <table class="tutor-ui-table tutor-ui-table-responsive tutor-ui-table-purchase-history">
        <?php if ( tutor_utils()->count ( $orders ) ) { ?>
        <thead class="tutor-fs-7 tutor-fw-medium">
            <th>
                <div class="tutor-fs-7 tutor-color-black-60">
                    <?php esc_html_e( 'Order ID', 'tutor' ); ?>
                </div>
            </th>
            <th>
                <div class="tutor-fs-7 tutor-color-black-60">
                    <?php esc_html_e( 'Course Name', 'tutor' ); ?>
                </div>
            </th>
            <th>
                <div class="tutor-fs-7 tutor-color-black-60">
                    <?php esc_html_e( 'Date', 'tutor' ); ?>
                </div>
            </th>
            <th>
                <div class="tutor-fs-7 tutor-color-black-60">
                    <?php esc_html_e( 'Price', 'tutor' ); ?>
                </div>
            </th>
            <th>
                <div class="tutor-fs-7 tutor-color-black-60">
                    <?php esc_html_e( 'Status', 'tutor' ); ?>
                </div>
            </th>
            <th class="tutor-shrink"></th>
        </thead>
        <?php } ?>
        <tbody>
            <?php
                if ( tutor_utils()->count ( $orders ) ) {
                    foreach ( $orders as $order ) {
                        if ( $monetize_by === 'wc' ) {
                            $wc_order   = wc_get_order( $order->ID );
                            $price      = tutor_utils()->tutor_price( $wc_order->get_total() );
                            $raw_price  = $wc_order->get_total();
                            $status = $order->post_status;
                            $order_status = '';
                            $order_status_text = '';
                            switch ( $status ) {
                                case 'wc-completed' ===  $status:
                                    $order_status = 'success';
                                    $order_status_text = __( 'Completed', 'tutor' );
                                    break;
                                case 'wc-processing' ===  $status:
                                    $order_status = 'processing';
                                    $order_status_text = __( 'Processing', 'tutor' );
                                    break;
                                case 'wc-on-hold' ===  $status:
                                    $order_status = 'onhold';
                                    $order_status_text = __( 'On Hold', 'tutor' );
                                    break;
                                case 'wc-refunded' ===  $status:
                                    $order_status = 'refund';
                                    $order_status_text = __( 'Processing', 'tutor' );
                                    break;
                                case 'wc-cancelled' ===  $status:
                                    $order_status = '';
                                    $order_status_text = __( 'Cancelled', 'tutor' );
                                    break;
                                case 'wc-pending' ===  $status:
                                    $order_status = '';
                                    $order_status_text = __( 'Pending', 'tutor' );
                                    break;
                            }
                        } else if ( $monetize_by === 'edd' ) {
                            $edd_order          = edd_get_payment( $order->ID );
                            $price              = edd_currency_filter( edd_format_amount( $edd_order->total ), edd_get_payment_currency_code( $order->ID ) );
                            $raw_price          = $edd_order->total;
                            $status             = $edd_order->status_nicename;
                            $order_status       = '';
                            $order_status_text  = $status;
                        }

            ?>
                <tr>
                    <td data-th="Order ID" class="v-align-top">
                        <div class="td-course tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mt-4" style="font-weight: 600;">
                            #<?php esc_html_e( $order->ID ); ?>
                        </div>
                    </td>
                    <td data-th="Course Name">
                        <?php
                            $courses = tutor_utils()->get_course_enrolled_ids_by_order_id( $order->ID );
                            if ( tutor_utils()->count( $courses ) ) {
                                foreach ( $courses as $course ) {
                                    echo '<div class="tutor-fs-7 tutor-fw-medium tutor-color-black">' . esc_html( get_the_title( $course['course_id'] ) ) . '</div>';
                                }
                            }
                        ?>
                    </td>
                    <td data-th="Date">
                        <span class="tutor-fs-7 tutor-fw-medium tutor-color-black"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->post_date ) ); ?></span>
                    </td>
                    <td data-th="Price">
                        <span class="tutor-fs-7 tutor-fw-medium tutor-color-black"><?php echo wp_kses_post( $price ); ?></span>
                    </td>
                    <td data-th="Status">
                        <span class="tutor-badge-label label-<?php esc_attr_e( $order_status ); ?> tutor-m-4"><?php esc_html_e( $order_status_text ); ?></span>
                    </td>
                    <td data-th="Download" class="tutor-export-purchase-history" data-order="<?php echo esc_attr( $order->ID ); ?>" data-course-name="<?php echo esc_attr( get_the_title( $course['course_id'] ) ); ?>" data-price="<?php echo esc_attr( $raw_price ); ?>" data-date="<?php echo esc_attr( date_i18n( get_option( 'date_format' ), strtotime( $order->post_date ) ) ); ?>" data-status="<?php echo esc_attr( $order_status_text ); ?>">
                        <a><span class="tutor-icon-receipt-line tutor-color-black-70" style="font-size:24px"></span></a>
                    </td>
                </tr>
                <?php } ?>
            <?php } else { ?>
            <tr>
                <td colspan="100%">
                    <div class="td-empty-state">
                        <?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php
        /**
         * Prepare pagination data & load template
         */
        $pagination_data = array(
            'total_items' => ! empty( $total_orders ) ? count( $total_orders ) : 0,
            'per_page'    => $per_page,
            'paged'       => $paged,
        );
        $total_page = ceil($pagination_data['total_items'] / $pagination_data['per_page']);
        if($total_page > 1) {
            $pagination_template = tutor()->path . 'templates/dashboard/elements/pagination.php';
            tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
        }
    ?>
</div>

