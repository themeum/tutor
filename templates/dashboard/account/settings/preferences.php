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
use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;

$theme_options = UserPreference::get_theme_options();

$font_scale_options = UserPreference::get_font_scale_options();

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
		<div class="tutor-flex tutor-justify-between">
			<h5 class="tutor-preferences-section-header">
				<?php esc_html_e( 'Preferences', 'tutor' ); ?>
			</h5>
			<div class="tutor-preferences-section-reset tutor-flex tutor-items-center cursor-pointer" @click="TutorCore.modal.showModal('<?php echo esc_js( $reset_modal_id ); ?>')">
				<?php SvgIcon::make()->name( Icon::RELOAD_3 )->size( 16 )->render(); ?>
				<span class="tutor-text-small tutor-ml-2"><?php esc_html_e( 'Reset to Default', 'tutor' ); ?></span>
			</div>
		</div>

		<?php
		echo ConfirmationModal::make()
			->id( $reset_modal_id )
			->title( __( 'Reset Preferences?', 'tutor' ) )
			->message( __( 'This will reset your Tutor preferences back to the default values.', 'tutor' ) )
			->cancel_text( __( 'Cancel', 'tutor' ) )
			->confirm_text( __( 'Yes, Reset', 'tutor' ) )
			->icon( Icon::RELOAD_3, 80, 80 )
			->confirm_handler( "resetToDefault('" . esc_js( $form_id ) . "','" . esc_js( $reset_modal_id ) . "')" )
			->mutation_state( 'resetPreferencesMutation' )
			->render();
		?>

		<div class="tutor-card tutor-card-rounded-2xl tutor-mb-7">
			<div class="tutor-preferences-setting-item">
				<div class="tutor-preferences-setting-content">
					<div class="tutor-preferences-setting-icon">
						<?php SvgIcon::make()->name( Icon::PLAY_LINE )->size( 20 )->render(); ?>
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
						<?php SvgIcon::make()->name( Icon::LIGHT )->size( 20 )->render(); ?>
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
			<div class="tutor-preferences-setting-item">
				<div class="tutor-preferences-setting-content">
					<div class="tutor-preferences-setting-icon">
						<?php SvgIcon::make()->name( Icon::FONT )->size( 20 )->render(); ?>
					</div>
					<span class="tutor-preferences-setting-title"><?php esc_html_e( 'Font size', 'tutor' ); ?></span>
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
						<?php SvgIcon::make()->name( Icon::LEARNING_MOOD )->size( 20 )->render(); ?>
					</div>
					<span class="tutor-preferences-setting-title"><?php esc_html_e( 'Learning Mode', 'tutor' ); ?></span>
				</div>
				<div class="tutor-preferences-setting-action">
					<?php
					InputField::make()
						->type( InputType::SWITCH )
						->size( Size::SM )
						->name( 'learning_mood' )
						->attr( 'x-bind', "register('learning_mood')" )
						->render();
					?>
				</div>
			</div>
		</div>
	</form>
</section>
