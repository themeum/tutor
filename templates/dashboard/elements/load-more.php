<?php
/**
 * Load more ajax button template
 * ?for usage checkout: tutor/templates/single/lesson/comment.php:30
 *
 * @since v2.0.6
 *
 * @package Tutor\Template\Parts
 */

$current_url = tutor()->current_url;
?>
<div data-tutor_pagination_ajax="<?php echo esc_attr( json_encode($data['ajax'] ) ); ?>" data-tutor_pagination_layout="<?php echo esc_attr( json_encode( $data['layout'] ) ); ?>">
    <a href="<?php echo esc_url( add_query_arg( array( 'current_page' => $data['ajax']['current_page_num'] + 1 ), $current_url ) ); ?>" class="tutor-btn tutor-btn-outline-primary page-numbers tutor-mr-16">
        <?php echo esc_html( $data['layout']['load_more_text'] ); ?>
    </a>
</div>
