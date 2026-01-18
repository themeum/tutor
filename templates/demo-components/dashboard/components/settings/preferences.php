<?php
/**
 * Tutor dashboard preferences.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

// Video quality options.
$video_quality_options = array(
	array(
		'label' => esc_html__( 'Low (360p)', 'tutor' ),
		'value' => '360p',
	),
	array(
		'label' => esc_html__( 'Medium (480p)', 'tutor' ),
		'value' => '480p',
	),
	array(
		'label' => esc_html__( 'High (720p)', 'tutor' ),
		'value' => '720p',
	),
	array(
		'label' => esc_html__( 'Very High (1080p)', 'tutor' ),
		'value' => '1080p',
	),
);

// Theme options.
$theme_options = array(
	array(
		'label' => esc_html__( 'Light', 'tutor' ),
		'value' => 'light',
	),
	array(
		'label' => esc_html__( 'Dark', 'tutor' ),
		'value' => 'dark',
	),
	array(
		'label' => esc_html__( 'System Default', 'tutor' ),
		'value' => 'system',
	),
);

?>

<section class="tutor-preferences-section">
	<!-- Course Content Section -->
	<h5 class="tutor-preferences-section-header tutor-color-black tutor-h5 tutor-sm-hidden"><?php esc_html_e( 'Course Content', 'tutor' ); ?></h5>
	<div class="tutor-card tutor-card-rounded-2xl tutor-mb-7">
		<div class="tutor-preferences-setting-item">
			<div class="tutor-preferences-setting-content">
				<div class="tutor-preferences-setting-icon">
					<?php tutor_utils()->render_svg_icon( Icon::PLAY_LINE, 20, 20 ); ?>
				</div>
				<span class="tutor-preferences-setting-title"><?php esc_html_e( 'Auto-play next lecture', 'tutor' ); ?></span>
			</div>
			<div class="tutor-preferences-setting-action">
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="checkbox"
							id="auto-play-next"
							name="auto_play_next"
							class="tutor-switch tutor-switch-md"
							aria-label="<?php esc_attr_e( 'Auto-play next lecture', 'tutor' ); ?>"
						>
					</div>
				</div>
			</div>
		</div>

		<div class="tutor-preferences-setting-item">
			<div class="tutor-preferences-setting-content">
				<div class="tutor-preferences-setting-icon">
					<?php tutor_utils()->render_svg_icon( Icon::WIFI, 20, 20 ); ?>
				</div>
				<span class="tutor-preferences-setting-title"><?php esc_html_e( 'Download over Wi-Fi only', 'tutor' ); ?></span>
			</div>
			<div class="tutor-preferences-setting-action">
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="checkbox"
							id="download-wifi-only"
							name="download_wifi_only"
							class="tutor-switch tutor-switch-md"
							aria-label="<?php esc_attr_e( 'Download over Wi-Fi only', 'tutor' ); ?>"
						>
					</div>
				</div>
			</div>
		</div>

		<div class="tutor-preferences-setting-item">
			<div class="tutor-preferences-setting-content">
				<div class="tutor-preferences-setting-icon">
					<?php tutor_utils()->render_svg_icon( Icon::DOWNLOAD_2, 20, 20 ); ?>
				</div>
				<div class="tutor-preferences-setting-text">
					<span class="tutor-preferences-setting-title"><?php esc_html_e( 'Downloads', 'tutor' ); ?></span>
					<span class="tutor-preferences-setting-subtitle"><?php esc_html_e( 'Manage all of your offline content', 'tutor' ); ?></span>
				</div>
			</div>
			<div class="tutor-preferences-setting-action">
				<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_RIGHT, 20, 20 ); ?>
			</div>
		</div>

		<div class="tutor-preferences-setting-item">
			<div class="tutor-preferences-setting-content">
				<div class="tutor-preferences-setting-icon">
					<?php tutor_utils()->render_svg_icon( Icon::VIDEO_QUALITY, 20, 20 ); ?>
				</div>
				<span class="tutor-preferences-setting-title"><?php esc_html_e( 'Video Quality', 'tutor' ); ?></span>
			</div>
			<div class="tutor-preferences-setting-action">
				<?php
				tutor_load_template(
					'core-components.stepper-dropdown',
					array(
						'options'     => $video_quality_options,
						'placeholder' => esc_html__( 'Select quality...', 'tutor' ),
						'value'       => '720p',
						'name'        => 'video_quality',
					)
				);
				?>
			</div>
		</div>
	</div>

	<!-- Lesson Experience Section -->
	<h5 class="tutor-preferences-section-header tutor-color-black tutor-mb-7"><?php esc_html_e( 'Lesson experience', 'tutor' ); ?></h5>
	<div class="tutor-card tutor-card-rounded-2xl tutor-mb-6">
		<div class="tutor-preferences-setting-item">
			<div class="tutor-preferences-setting-content">
				<div class="tutor-preferences-setting-icon">
					<?php tutor_utils()->render_svg_icon( Icon::MUSIC, 20, 20 ); ?>
				</div>
				<span class="tutor-preferences-setting-title"><?php esc_html_e( 'Sound effects', 'tutor' ); ?></span>
			</div>
			<div class="tutor-preferences-setting-action">
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="checkbox"
							id="sound-effects"
							name="sound_effects"
							class="tutor-switch tutor-switch-md"
							aria-label="<?php esc_attr_e( 'Sound effects', 'tutor' ); ?>"
						>
					</div>
				</div>
			</div>
		</div>

		<div class="tutor-preferences-setting-item">
			<div class="tutor-preferences-setting-content">
				<div class="tutor-preferences-setting-icon">
					<?php tutor_utils()->render_svg_icon( Icon::ANIMATION, 20, 20 ); ?>
				</div>
				<span class="tutor-preferences-setting-title"><?php esc_html_e( 'Animations', 'tutor' ); ?></span>
			</div>
			<div class="tutor-preferences-setting-action">
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="checkbox"
							id="animations"
							name="animations"
							class="tutor-switch tutor-switch-md"
							aria-label="<?php esc_attr_e( 'Animations', 'tutor' ); ?>"
						>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Appearance Section -->
	<h5 class="tutor-preferences-section-header tutor-color-black tutor-mb-7"><?php esc_html_e( 'Appearance', 'tutor' ); ?></h5>
	<div class="tutor-card tutor-card-rounded-2xl tutor-mb-6">
		<div class="tutor-preferences-setting-item">
			<div class="tutor-preferences-setting-content">
				<div class="tutor-preferences-setting-icon">
					<?php tutor_utils()->render_svg_icon( Icon::LIGHT, 20, 20 ); ?>
				</div>
				<span class="tutor-preferences-setting-title"><?php esc_html_e( 'Theme', 'tutor' ); ?></span>
			</div>
			<div class="tutor-preferences-setting-action">
				<?php
				tutor_load_template(
					'core-components.stepper-dropdown',
					array(
						'options'     => $theme_options,
						'placeholder' => esc_html__( 'Select theme...', 'tutor' ),
						'value'       => 'system',
						'name'        => 'theme',
					)
				);
				?>
			</div>
		</div>
	</div>

	<!-- Accessibility Section -->
	<h5 class="tutor-preferences-section-header tutor-color-black tutor-mb-7"><?php esc_html_e( 'Accessibility', 'tutor' ); ?></h5>
	<div class="tutor-card tutor-card-rounded-2xl tutor-mb-6">
		<div class="tutor-preferences-setting-item">
			<div class="tutor-preferences-setting-content">
				<div class="tutor-preferences-setting-icon">
					<?php tutor_utils()->render_svg_icon( Icon::CONTRAST, 20, 20 ); ?>
				</div>
				<span class="tutor-preferences-setting-title"><?php esc_html_e( 'High Contrast Mode', 'tutor' ); ?></span>
			</div>
			<div class="tutor-preferences-setting-action">
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="checkbox"
							id="high-contrast"
							name="high_contrast"
							class="tutor-switch tutor-switch-md"
							aria-label="<?php esc_attr_e( 'High Contrast Mode', 'tutor' ); ?>"
						>
					</div>
				</div>
			</div>
		</div>

		<div class="tutor-preferences-setting-item">
			<div class="tutor-preferences-setting-content">
				<div class="tutor-preferences-setting-icon">
					<?php tutor_utils()->render_svg_icon( Icon::FONT, 20, 20 ); ?>
				</div>
				<span class="tutor-preferences-setting-title"><?php esc_html_e( 'Font size', 'tutor' ); ?></span>
			</div>
			<div class="tutor-preferences-setting-action">
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="text"
							id="font-size"
							name="font_size"
							value="120%"
							class="tutor-input"
							aria-label="<?php esc_attr_e( 'Font size', 'tutor' ); ?>"
						>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
