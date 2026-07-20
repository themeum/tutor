<?php
/**
 * Tutor dashboard preferences.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://www.themeum.com/
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use TUTOR\UserPreference;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\Color;
use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use Tutor\Helpers\UrlHelper;
use Tutor\Options_V2;
use TUTOR\User;

$theme_options          = UserPreference::get_theme_options();
$vision_options         = UserPreference::get_vision_options();
$motion_effects_options = UserPreference::get_motion_effects_options();
$learning_mood_options  = UserPreference::get_learning_mood_options();
$font_scale_options     = UserPreference::get_font_scale_options();

// Load current user preferences to seed the form.
$user_preferences = UserPreference::get_preferences();

// Confirmation modal id for resetting user preferences.
$reset_modal_id = 'tutor-preferences-reset-modal';

?>

<section class="tutor-preferences-section">
	<form
		id="<?php echo esc_attr( $form_id ); ?>"
		x-data='tutorForm({ 
			id: "<?php echo esc_attr( $form_id ); ?>", 
			mode: "onChange", 
			shouldFocusError: true,
			defaultValues: <?php echo wp_json_encode( $user_preferences ); ?>
		})'
		x-bind="getFormBindings()"
		@submit="handleSubmit((data) => { savePreferencesMutation?.mutate({...data, formId: '<?php echo esc_attr( $form_id ); ?>'}); })($event)"
	>
		<!-- Course Content Section -->
		<div class="tutor-flex tutor-justify-between tutor-mb-4">
			<h5 class="tutor-h5 tutor-font-semibold tutor-my-none">
				<?php esc_html_e( 'Course Content', 'tutor' ); ?>
			</h5>
			<div class="tutor-preferences-reset-default" @click="TutorCore.modal.showModal('<?php echo esc_js( $reset_modal_id ); ?>')">
				<?php SvgIcon::make()->name( Icon::RELOAD_3 )->color( Color::SUBDUED )->render(); ?>
				<span class="tutor-text-small tutor-text-subdued"><?php esc_html_e( 'Reset to Default', 'tutor' ); ?></span>
			</div>
		</div>
		<?php
		ConfirmationModal::make()
			->id( $reset_modal_id )
			->title( __( 'Reset your Preferences?', 'tutor' ) )
			->message( __( 'This will reset your learning preferences to the default settings. Your progress and account data won’t be affected.', 'tutor' ) )
			->cancel_text( __( 'Cancel', 'tutor' ) )
			->confirm_text( __( 'Reset Preferences', 'tutor' ) )
			->icon( tutor_utils()->get_themed_svg( 'images/illustrations/reset-preference.svg' ), 80, 80, ConfirmationModal::ICON_TYPE_HTML )
			->confirm_handler( "handleResetPreferences('" . esc_js( $form_id ) . "','" . esc_js( $reset_modal_id ) . "')" )
			->mutation_state( 'resetPreferencesMutation' )
			->render();
		?>

		<div class="tutor-card tutor-card-rounded-2xl tutor-mb-7">
			<div class="tutor-preferences-setting-item">
				<div class="tutor-preferences-setting-content">
					<div class="tutor-preferences-setting-icon">
						<?php SvgIcon::make()->name( Icon::PLAY_LINE )->size( 20 )->render(); ?>
					</div>
					<div class="tutor-preferences-setting-title">
						<?php esc_html_e( 'Auto-play next lesson', 'tutor' ); ?>
					</div>
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

		<h5 class="tutor-h5 tutor-font-semibold tutor-mb-4">
			<?php esc_html_e( 'Interactive Effects', 'tutor' ); ?>
		</h5>
		<div class="tutor-card tutor-card-rounded-2xl tutor-mb-7">
			<div class="tutor-preferences-setting-item">
				<div class="tutor-preferences-setting-content">
					<div class="tutor-preferences-setting-icon">
						<?php SvgIcon::make()->name( Icon::ANIMATION )->size( 20 )->render(); ?>
					</div>
					<div>
						<div class="tutor-preferences-setting-title">
							<?php esc_html_e( 'Motion Effects', 'tutor' ); ?>
						</div>
						<div class="tutor-preferences-setting-subtitle">
							<?php esc_html_e( 'Limit animations and motion effects to reduce visual strain.', 'tutor' ); ?>
						</div>
					</div>
				</div>
				<div class="tutor-preferences-setting-action">
					<?php
					InputField::make()
						->type( InputType::SELECT )
						->size( Size::SM )
						->name( 'motion_effects' )
						->options( $motion_effects_options )
						->attr( 'x-bind', "register('motion_effects')" )
						->attr( 'x-effect', 'TutorCore.preference.applyMotionEffects(watch("motion_effects"))' )
						->attr( 'style', 'min-width: 140px;' )
						->render();
					?>
				</div>
			</div>
		</div>

		<h5 class="tutor-h5 tutor-font-semibold tutor-mb-4">
			<?php esc_html_e( 'Appearance', 'tutor' ); ?>
		</h5>
		<div class="tutor-card tutor-card-rounded-2xl tutor-mb-7">
			<div class="tutor-preferences-setting-item">
				<div class="tutor-preferences-setting-content">
					<div class="tutor-preferences-setting-icon">
						<?php SvgIcon::make()->name( Icon::LIGHT )->size( 20 )->render(); ?>
					</div>
					<div class="tutor-preferences-setting-title">
						<?php esc_html_e( 'Theme', 'tutor' ); ?>
					</div>
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
			<?php if ( User::is_student_view() ) : ?>
			<div class="tutor-preferences-setting-item">
				<div class="tutor-preferences-setting-content">
					<div class="tutor-preferences-setting-icon">
						<?php SvgIcon::make()->name( Icon::INTERFACE )->size( 20 )->render(); ?>
					</div>
					<div class="tutor-preferences-setting-title">
						<?php esc_html_e( 'Learning Mode', 'tutor' ); ?>
					</div>
				</div>
				<div class="tutor-preferences-setting-action">
					<?php
						InputField::make()
						->type( InputType::SELECT )
						->size( Size::SM )
						->name( 'learning_mood' )
						->options( $learning_mood_options )
						->value( $user_preferences['learning_mood'] ?? Options_V2::LEARNING_MODE_MODERN )
						->placeholder( __( 'Select mode', 'tutor' ) )
						->attr( 'x-bind', "register('learning_mood')" )
						->attr( 'style', 'min-width: 140px;' )
						->render();
					?>
				</div>
			</div>
			<?php endif ?>
		</div>

		<h5 class="tutor-h5 tutor-font-semibold tutor-mb-4">
			<?php esc_html_e( 'Accessibility', 'tutor' ); ?>
		</h5>
		<div class="tutor-card tutor-card-rounded-2xl">
			<div class="tutor-preferences-setting-item">
				<div class="tutor-preferences-setting-content">
					<div class="tutor-preferences-setting-icon">
						<?php SvgIcon::make()->name( Icon::FONT )->size( 20 )->render(); ?>
					</div>
					<div>
						<div class="tutor-preferences-setting-title">
							<?php esc_html_e( 'Font Size', 'tutor' ); ?>
						</div>
						<div class="tutor-preferences-setting-subtitle">
							<?php esc_html_e( 'Adjust the text size for better readability.', 'tutor' ); ?>
						</div>
					</div>
				</div>
				<div class="tutor-preferences-setting-action">
					<?php
					InputField::make()
						->type( InputType::SELECT )
						->size( Size::SM )
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
			<div class="tutor-preferences-setting-item">
				<div class="tutor-preferences-setting-content">
					<div class="tutor-preferences-setting-icon">
						<?php SvgIcon::make()->name( Icon::CONTRAST )->size( 20 )->render(); ?>
					</div>
					<div>
						<div class="tutor-preferences-setting-title">
							<?php esc_html_e( 'High Contrast', 'tutor' ); ?>
						</div>
						<div class="tutor-preferences-setting-subtitle">
							<?php esc_html_e( 'Increase contrast to improve text and element visibility.', 'tutor' ); ?>
						</div>
					</div>
				</div>
				<div class="tutor-preferences-setting-action">
					<?php
					InputField::make()
						->type( InputType::SWITCH )
						->size( Size::SM )
						->name( 'contrast' )
						->value( 'high' )
						->checked( isset( $user_preferences['contrast'] ) && 'high' === $user_preferences['contrast'] )
						->attr( 'x-bind', "register('contrast')" )
						->attr( 'x-effect', 'TutorCore.preference.applyContrast(watch("contrast") ? "high" : "")' )
						->render();
					?>
				</div>
			</div>
			<div class="tutor-preferences-vision-preview">
				<img :src="'<?php echo esc_attr( UrlHelper::asset( 'images/vision/' ) ); ?>' + (watch('vision') ?? 'normal') + '.webp'" />
			</div>
			<div class="tutor-preferences-setting-item">
				<div class="tutor-preferences-setting-content">
					<div class="tutor-preferences-setting-icon">
						<?php SvgIcon::make()->name( Icon::VISION )->size( 20 )->render(); ?>
					</div>
					<div>
						<div class="tutor-preferences-setting-title">
							<?php esc_html_e( 'Vision', 'tutor' ); ?>
						</div>
						<div class="tutor-preferences-setting-subtitle">
							<?php esc_html_e( 'Choose a color filter based on your visual needs.', 'tutor' ); ?>
						</div>
					</div>
				</div>
				<div class="tutor-preferences-setting-action">
					<?php
					InputField::make()
						->type( InputType::SELECT )
						->size( Size::SM )
						->name( 'vision' )
						->options( $vision_options )
						->placeholder( __( 'Select vision...', 'tutor' ) )
						->attr( 'x-bind', "register('vision')" )
						->attr( 'x-effect', 'TutorCore.preference.applyVision(watch("vision"))' )
						->attr( 'style', 'min-width: 140px;' )
						->render();
					?>
				</div>
			</div>
		</div>
	</form>
</section>
