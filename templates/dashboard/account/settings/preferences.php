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
use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;

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

$font_size_options = array(
	array(
		'label' => esc_html__( '70%', 'tutor' ),
		'value' => 70,
	),
	array(
		'label' => esc_html__( '80%', 'tutor' ),
		'value' => 80,
	),
	array(
		'label' => esc_html__( '90%', 'tutor' ),
		'value' => 90,
	),
	array(
		'label' => esc_html__( '100%', 'tutor' ),
		'value' => 100,
	),
	array(
		'label' => esc_html__( '110%', 'tutor' ),
		'value' => 110,
	),
	array(
		'label' => esc_html__( '120%', 'tutor' ),
		'value' => 120,
	),
	array(
		'label' => esc_html__( '130%', 'tutor' ),
		'value' => 130,
	),
	array(
		'label' => esc_html__( '140%', 'tutor' ),
		'value' => 140,
	),
	array(
		'label' => esc_html__( '150%', 'tutor' ),
		'value' => 150,
	),
);

$default_values = array(
	'auto_play_next'     => true,
	'download_wifi_only' => true,
	'video_quality'      => '720p',
	'sound_effects'      => true,
	'animations'         => true,
	'font_size'          => 120,
	'theme'              => 'light',
);

?>

<section class="tutor-preferences-section">
	<form
		id="<?php echo esc_attr( $form_id ); ?>"
		x-data='tutorForm({ 
			id: "<?php echo esc_attr( $form_id ); ?>", 
			mode: "onChange", 
			shouldFocusError: true,
			defaultValues: <?php echo wp_json_encode( $default_values ); ?>
		})'
		x-bind="getFormBindings()"
		@submit="handleSubmit(
			(data) => { 
				console.log('Preferences saved:', data);
				alert('Preferences saved successfully!');
			},
			(errors) => { 
				console.log('Form validation errors:', errors);
			}
		)($event)"
	>
		<!-- Course Content Section -->
		<h5 class="tutor-preferences-section-header tutor-h5">
			<?php esc_html_e( 'Course Content', 'tutor' ); ?>
		</h5>
		<div class="tutor-card tutor-card-rounded-2xl tutor-mb-7">
			<div class="tutor-preferences-setting-item">
				<div class="tutor-preferences-setting-content">
					<div class="tutor-preferences-setting-icon">
						<?php tutor_utils()->render_svg_icon( Icon::PLAY_LINE, 20, 20 ); ?>
					</div>
					<span class="tutor-preferences-setting-title"><?php esc_html_e( 'Auto-play next lecture', 'tutor' ); ?></span>
				</div>
				<div class="tutor-preferences-setting-action">
					<?php
						InputField::make()
							->type( InputType::SWITCH )
							->size( Size::SM )
							->name( 'auto_play_next' )
							->attr( 'x-bind', "register('auto_play_next')" )
							->render();
					?>
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
					<?php
						InputField::make()
							->type( InputType::SWITCH )
							->size( Size::SM )
							->name( 'download_wifi_only' )
							->attr( 'x-bind', "register('download_wifi_only')" )
							->render();
					?>
				</div>
			</div>

			<div class="tutor-preferences-setting-item">
				<div class="tutor-preferences-setting-content">
					<div class="tutor-preferences-setting-icon">
						<?php tutor_utils()->render_svg_icon( Icon::DOWNLOAD_2, 20, 20 ); ?>
					</div>
					<div class="tutor-preferences-setting-text">
						<span class="tutor-preferences-setting-title">
							<?php esc_html_e( 'Downloads', 'tutor' ); ?>
						</span>
						<span class="tutor-preferences-setting-subtitle">
							<?php esc_html_e( 'Manage all of your offline content', 'tutor' ); ?>
						</span>
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
						InputField::make()
							->type( InputType::SELECT )
							->size( Size::SM )
							->name( 'video_quality' )
							->options( $video_quality_options )
							->placeholder( __( 'Select quality...', 'tutor' ) )
							->attr( 'x-bind', "register('video_quality')" )
							->attr( 'style', 'min-width: 160px;' )
							->render();
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
					<?php
						InputField::make()
							->type( InputType::SWITCH )
							->size( Size::SM )
							->name( 'sound_effects' )
							->attr( 'x-bind', "register('sound_effects')" )
							->render();
					?>
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
					<?php
						InputField::make()
							->type( InputType::SWITCH )
							->size( Size::SM )
							->name( 'animations' )
							->attr( 'x-bind', "register('animations')" )
							->render();
					?>
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
						InputField::make()
							->type( InputType::SELECT )
							->size( Size::SM )
							->name( 'theme' )
							->options( $theme_options )
							->placeholder( __( 'Select theme...', 'tutor' ) )
							->attr( 'x-bind', "register('theme')" )
							->attr( 'style', 'min-width: 140px;' )
							->render();
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
					<?php
						InputField::make()
							->type( InputType::SWITCH )
							->size( Size::SM )
							->name( 'high_contrast' )
							->attr( 'x-bind', "register('high_contrast')" )
							->render();
					?>
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
					<?php
						InputField::make()
							->type( InputType::SELECT )
							->name( 'font_size' )
							->options( $font_size_options )
							->placeholder( __( 'Select font size...', 'tutor' ) )
							->attr( 'x-bind', "register('font_size')" )
							->attr( 'style', 'min-width: 140px;' )
							->render();
					?>
				</div>
			</div>
		</div>
	</form>
</section>
