<?php
/**
 * Course List Template
 *
 * @package Course List
 * @since v2.0.0
 */

/**
 * Prepare sub pages
 *
 * @var $sub_pages page title & value.
 */
$sub_pages = array(
	array(
		'title' => __( 'All', 'tutor' ),
		'value' => 100,
	),
	array(
		'title' => __( 'Mine', 'tutor' ),
		'value' => 100,
	),
	array(
		'title' => __( 'Published', 'tutor' ),
		'value' => 100,
	),
	array(
		'title' => __( 'Draft', 'tutor' ),
		'value' => 100,
	),
	array(
		'title' => __( 'Pending', 'tutor' ),
		'value' => 100,
	),
);
?>
<div class="tutor-admin-page-wrapper">
	<div class="tutor-admin-page-navbar">
		<div class="tutor-admin-page-title">
			<span class="text-medium-h5">
				<?php esc_html_e( 'Courses', 'tutor' ); ?>
			</span>
		</div>
		<div class="tutor-admin-sub-pages">
			<?php foreach ( $sub_pages as $key => $value ) : ?>
				<li>
					<a href="">
						<?php esc_html_e( $value['title'] ) ; ?>
						(<?php esc_html_e( $value['value'] ); ?>)
					</a>
				</li>
			<?php endforeach; ?>    
		</div>
	</div>
</div>
