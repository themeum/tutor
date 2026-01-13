<?php
/**
 * Tutor learning area resources.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $tutor_course_id;

$attachments    = tutor_utils()->get_attachments( $tutor_course_id );
$open_mode_view = apply_filters( 'tutor_pro_attachment_open_mode', null ) === 'view' ? ' target="_blank" ' : null;

$tabs_data = array(
	array(
		'id'    => 'downloads',
		'label' => 'Downloads',
		'icon'  => Icon::DOWNLOAD_2,
	),
);

?>

<div class="tutor-learning-area-resources">
	<h4 class="tutor-h4 tutor-mb-4 tutor-sm-hidden"><?php esc_html_e( 'Course Resources', 'tutor' ); ?></h4>

	<div class="tutor-resources-wrapper">
		<div x-data='tutorTabs({
				tabs: <?php echo wp_json_encode( $tabs_data ); ?>,
				defaultTab: "downloads",
				urlParams: {
					paramName: "tab",
				}
			})'
		>
			<div x-ref="tablist" class="tutor-tabs-nav" role="tablist" aria-orientation="horizontal">
				<template x-for="tab in tabs" :key="tab.id">
					<button
					type="button"
					role="tab"
					:class='getTabClass(tab)'
					x-bind:aria-selected="isActive(tab.id)"
					:disabled="tab.disabled ? true : false"
					@click="selectTab(tab.id)"
					>
						<span x-data="TutorCore.icon({ name: tab.icon, width: 20, height: 20})"></span>
						<span x-text="tab.label"></span>
					</button>
				</template>
			</div>

			<div class="tutor-tabs-content">
				<div x-show="activeTab === 'downloads'" x-cloak class="tutor-tab-panel" role="tabpanel">
					<div class="tutor-resources-list">
						<?php if ( is_array( $attachments ) && count( $attachments ) ) : ?>
							<?php foreach ( $attachments as $attachment ) : ?>
								<div class="tutor-card tutor-attachment-card">
									<div class="tutor-attachment-card-icon" aria-hidden="true">
										<?php tutor_utils()->render_svg_icon( Icon::RESOURCES, 24, 24 ); ?>
									</div>

									<div class="tutor-attachment-card-body">
										<div class="tutor-attachment-card-title">
											<?php echo esc_html( $attachment->title ); ?>
										</div>
										<span class="tutor-attachment-card-meta">
											<?php
											/* translators: %s: file size */
											printf( esc_html__( 'Size: %s', 'tutor' ), esc_html( $attachment->size ) );
											?>
										</span>
									</div>

									<div class="tutor-attachment-card-actions">
										<?php
										$download_attr = $open_mode_view ? $open_mode_view : 'download="' . esc_attr( $attachment->name ) . '"';
										?>
										<a href="<?php echo esc_url( $attachment->url ); ?>" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon" <?php echo esc_attr( $download_attr ); ?>>
											<?php tutor_utils()->render_svg_icon( Icon::DOWNLOAD_2, 16, 16 ); ?>
										</a>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
