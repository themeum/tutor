<?php
/**
 * Tutor dashboard preferences.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use TUTOR\UserPreference;
use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;

// Theme options.
$theme_options = UserPreference::get_theme_options();

$font_scale_options = UserPreference::get_font_scale_options();

// Load current user preferences to seed the form.
$user_preferences = ( new UserPreference( false ) )->get_preferences();

$default_values = array(
	'auto_play_next' => isset( $user_preferences['auto_play_next'] ) ? (bool) $user_preferences['auto_play_next'] : false,
	'theme'          => isset( $user_preferences['theme'] ) ? $user_preferences['theme'] : UserPreference::DEFAULT_THEME,
	'font_scale'     => isset( $user_preferences['font_scale'] ) ? (int) $user_preferences['font_scale'] : UserPreference::DEFAULT_FONT_SCALE,
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
		@submit="handleSubmit((data) => { savePreferencesMutation?.mutate(data); })($event)"
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
						->attr( 'x-effect', 'TutorCore.preference.applyTheme(watch("theme"))' )
						->attr( 'style', 'min-width: 140px;' )
						->render();
					?>
				</div>
			</div>
		</div>

		<h5 class="tutor-preferences-section-header tutor-color-black tutor-mb-7"><?php esc_html_e( 'Accessibility', 'tutor' ); ?></h5>
		<div class="tutor-card tutor-card-rounded-2xl tutor-mb-6">
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
						->name( 'font_scale' )
						->options( $font_scale_options )
						->placeholder( __( 'Select font size...', 'tutor' ) )
						->attr( 'x-bind', "register('font_scale')" )
						->attr( 'x-effect', 'TutorCore.preference.applyFontScale(watch("font_scale"))' )
						->attr( 'style', 'min-width: 140px;' )
						->render();
					?>
				</div>
			</div>
		</div>
	</form>
</section>
