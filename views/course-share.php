<?php
/**
 * Course share
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.5.8
 */

$tutor_social_share_icons = tutor_utils()->tutor_social_share_icons();
if ( ! tutor_utils()->count( $tutor_social_share_icons ) ) {
	return;
}

$share_config = array(
	'title' => get_the_title(),
	'text'  => get_the_excerpt(),
	'image' => get_tutor_course_thumbnail( 'post-thumbnail', true ),
);
?>

<a data-tutor-modal-target="tutor-course-share-opener" href="#" class="tutor-btn tutor-btn-ghost tutor-course-share-btn">
	<span class="tutor-icon-share tutor-mr-8"></span> <?php esc_html_e( 'Share', 'tutor' ); ?>
</a>
<div id="tutor-course-share-opener" class="tutor-modal">
	<span class="tutor-modal-overlay"></span>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content tutor-modal-content-white">
			<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
				<span class="tutor-icon-times" area-hidden="true"></span>
			</button>
			<div class="tutor-modal-body">
				<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16">
					<?php esc_html_e( 'Share Course', 'tutor' ); ?>
				</div>
				<div class="tutor-fs-7 tutor-color-secondary tutor-mb-12">
					<?php esc_html_e( 'Page Link', 'tutor' ); ?>
				</div>
				<div class="tutor-mb-32">
					<input class="tutor-form-control" value="<?php echo esc_attr( get_permalink( get_the_ID() ) ); ?>" />
				</div>
				<div>
					<div class="tutor-color-black tutor-fs-6 tutor-fw-medium tutor-mb-16">
						<?php esc_html_e( 'Share On Social Media', 'tutor' ); ?>
					</div>
					<div class="tutor-social-share-wrap" data-social-share-config="<?php echo esc_attr( wp_json_encode( $share_config ) ); ?>">
						<?php foreach ( $tutor_social_share_icons as $icon ) : ?>
							<button class="tutor_share <?php echo esc_attr( $icon['share_class'] ); ?>" style="background: <?php echo esc_attr( $icon['color'] ); ?>">
								<?php echo wp_kses( $icon['icon_html'], tutor_utils()->allowed_icon_tags() ); ?>
								<span>
									<?php echo esc_html( $icon['text'] ); ?>
								</span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
