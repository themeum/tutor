<?php
/**
 * Settings Account
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use TUTOR\User;
use Tutor\Components\Button;
use Tutor\Components\InputField;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\Constants\InputType;

$user          = wp_get_current_user();
$settings_data = User::get_profile_settings_data( $user->ID );

$display_name_options = array();
foreach ( $settings_data['public_display'] as $_id => $item ) {
	$display_name_options[] = array(
		'label' => $item,
		'value' => $item,
	);
}

$timezone_options = array();
foreach ( tutor_global_timezone_lists() as $key => $value ) {
	$timezone_options[] = array(
		'label' => $value,
		'value' => $key,
	);
}

$default_values = array(
	'first_name'    => $user->first_name,
	'last_name'     => $user->last_name,
	'username'      => $user->user_login,
	'phone_number'  => $settings_data['phone_number'],
	'timezone'      => $settings_data['timezone'],
	'occupation'    => $settings_data['job_title'],
	'bio'           => $settings_data['profile_bio'],
	'display_name'  => $user->display_name,
	'profile_photo' => $settings_data['profile_photo_src'],
	'cover_photo'   => $settings_data['cover_photo_src'],
);

$default_values = (array) apply_filters( 'tutor_profile_default_values', $default_values, $user );

?>

<div class="tutor-account-section">
	<?php do_action( 'tutor_profile_edit_form_before' ); ?>

	<form
		id="<?php echo esc_attr( $form_id ); ?>"
		x-data='tutorForm({ 
			id: "<?php echo esc_attr( $form_id ); ?>",
			mode: "onChange",
			defaultValues: <?php echo wp_json_encode( $default_values ); ?>,
		})'
		x-bind="getFormBindings()"
		@submit="handleSubmit(handleUpdateProfile)($event)"
		class="tutor-flex tutor-flex-column tutor-gap-6"
	>
		<div class="tutor-flex tutor-flex-column tutor-gap-4">
			<h5 class="tutor-h5 tutor-sm-hidden"><?php echo esc_html__( 'Account', 'tutor' ); ?></h5>
			<div class="tutor-card tutor-flex tutor-flex-column tutor-gap-5">
				<?php do_action( 'tutor_profile_edit_input_before' ); ?>

				<div class="tutor-account-avatar-wrapper">
					<div x-data="tutorPopover({
						placement: 'bottom',
						offset: 8,
					})">
						<div
							x-data="tutorFileUploader({
								value: [getValue('profile_photo')],
								variant: 'image-uploader',
								accept: '.png,.jpg,.jpeg',
								onFileSelect: handleUploadProfilePhoto,
								imagePreviewPlaceholder: '<?php esc_attr( $settings_data['profile_placeholder'] ); ?>',
							})"
							class="tutor-account-avatar" 
							:class="open ? 'active' : ''"
						>
							<input
								class="tutor-hidden"
								type="file"
								name="profile_photo"
								x-ref="fileInput"
								:multiple="multiple"
								:accept="accept"
								@change="handleFileSelect($event)"
							/>
							<img 
								:src="imagePreview ? imagePreview : '<?php echo esc_url( $default_values['profile_photo'] ); ?>'"
								class="tutor-avatar-image"
								alt="<?php esc_attr_e( 'User Avatar', 'tutor' ); ?>"
							>

							<?php
								Button::make()
									->label( __( 'Upload Photo', 'tutor' ) )
									->variant( Variant::PRIMARY )
									->size( Size::X_SMALL )
									->icon( Icon::EDIT_2, 'left', 24, 24 )
									->icon_only()
									->attr( 'type', 'button' )
									->attr( 'class', 'tutor-account-avatar-edit' )
									->attr( 'x-ref', 'trigger' )
									->attr( '@click', 'toggle()' )
									->render();
							?>

							<div 
								x-ref="content"
								x-show="open"
								x-cloak
								@click.outside="handleClickOutside()"
								class="tutor-popover"
							>
								<div class="tutor-flex tutor-flex-column tutor-gap-3 tutor-p-5">
									<?php
										Button::make()
											->label( __( 'Upload Photo', 'tutor' ) )
											->variant( Variant::PRIMARY )
											->size( Size::X_SMALL )
											->attr( 'type', 'button' )
											->attr( 'x-ref', 'upload' )
											->attr( '@click', 'openFileDialog()' )
											->render();

										Button::make()
											->label( __( 'Remove Photo', 'tutor' ) )
											->variant( Variant::SECONDARY )
											->size( Size::X_SMALL )
											->attr( 'type', 'button' )
											->attr( '@click', 'removeFile(), hide(), handleRemoveProfilePhoto()' )
											->render();
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="tutor-grid tutor-md-grid-cols-1 tutor-grid-cols-2 tutor-gap-5">
					<?php
						InputField::make()
							->type( InputType::TEXT )
							->label( __( 'First Name', 'tutor' ) )
							->name( 'first_name' )
							->clearable()
							->id( 'first_name' )
							->placeholder( __( 'Enter your first name', 'tutor' ) )
							->attr( 'x-bind', "register('first_name')" )
							->render();

						InputField::make()
							->type( InputType::TEXT )
							->label( __( 'Last Name', 'tutor' ) )
							->name( 'last_name' )
							->clearable()
							->id( 'last_name' )
							->placeholder( __( 'Enter your last name', 'tutor' ) )
							->attr( 'x-bind', "register('last_name')" )
							->render();
					?>
				</div>

				<?php
					InputField::make()
						->type( InputType::TEXT )
						->label( __( 'Username', 'tutor' ) )
						->name( 'username' )
						->disabled()
						->clearable()
						->id( 'username' )
						->placeholder( __( 'Enter your username', 'tutor' ) )
						->attr( 'x-bind', "register('username')" )
						->render();

					InputField::make()
						->type( InputType::TEXT )
						->label( __( 'Phone Number', 'tutor' ) )
						->name( 'phone_number' )
						->clearable()
						->id( 'phone_number' )
						->placeholder( __( 'Enter your phone number', 'tutor' ) )
						->attr(
							'x-bind',
							"register('phone_number', { pattern: { value: /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im } } )"
						)
						->render();

					InputField::make()
						->type( InputType::TEXT )
						->label( __( 'Skill/Occupation', 'tutor' ) )
						->name( 'occupation' )
						->clearable()
						->id( 'occupation' )
						->placeholder( __( 'Enter your skill/occupation', 'tutor' ) )
						->attr( 'x-bind', "register('occupation')" )
						->render();

					if ( ! User::is_admin() ) {
						Inputfield::make()
						->type( InputType::SELECT )
						->label( __( 'Timezone', 'tutor' ) )
						->name( 'timezone' )
						->options( $timezone_options )
						->searchable()
						->clearable()
						->id( 'timezone' )
						->placeholder( __( 'Select your timezone', 'tutor' ) )
						->attr( 'x-bind', "register('timezone')" )
						->render();
					}
					?>
			</div>
		</div>

		<div class="tutor-flex tutor-flex-column tutor-gap-4">
			<h5 class="tutor-h5"><?php echo esc_html__( 'Public Profile', 'tutor' ); ?></h5>
			<div class="tutor-card tutor-flex tutor-flex-column tutor-gap-5">
				<?php
					InputField::make()
						->type( InputType::SELECT )
						->label( __( 'Display Name', 'tutor' ) )
						->name( 'display_name' )
						->options( $display_name_options )
						->id( 'display_name' )
						->placeholder( __( 'Enter your display name', 'tutor' ) )
						->attr( 'x-bind', "register('display_name')" )
						->render();

					InputField::make()
						->type( InputType::TEXTAREA )
						->label( __( 'Bio', 'tutor' ) )
						->name( 'bio' )
						->clearable()
						->id( 'bio' )
						->placeholder( __( 'Enter your bio', 'tutor' ) )
						->attr( 'x-bind', "register('bio')" )
						->render();
				?>
			</div>
		</div>

		<?php do_action( 'tutor_profile_edit_input_after', $user ); ?>
	</form>

	<?php do_action( 'tutor_profile_edit_form_after' ); ?>
</div>
