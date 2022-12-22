<?php
/**
 * Load more ajax button template
 * ?for usage checkout: tutor/templates/single/lesson/comment.php:30
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Elements
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.6
 */

$current_url = tutor()->current_url;

$default_class = 'tutor-btn tutor-btn-outline-primary page-numbers tutor-mr-16';
$merge_class   = isset( $data['layout']['class'] ) ? $default_class . ' ' . $data['layout']['class'] : $default_class;

?>
<div data-tutor_pagination_ajax="<?php echo esc_attr( json_encode( $data['ajax'] ) ); ?>" data-tutor_pagination_layout="<?php echo esc_attr( json_encode( $data['layout'] ) ); ?>">
	<a href="<?php echo esc_url( add_query_arg( array( 'current_page' => $data['ajax']['current_page_num'] + 1 ), $current_url ) ); ?>" class="<?php echo esc_attr( $merge_class ); ?>">
		<?php echo esc_html( $data['layout']['load_more_text'] ); ?>
	</a>
</div>
