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
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Button;
use Tutor\Components\InputField;

$settings_data = User::get_profile_settings_data( $user->ID );
$user          = $settings_data['user'];

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

?>

<div class="tutor-account-section">
	<form
		id="<?php echo esc_attr( $form_id ); ?>"
		x-data='tutorForm({ 
			id: "<?php echo esc_attr( $form_id ); ?>",
			mode: "onChange",
			defaultValues: <?php echo wp_json_encode( $default_values ); ?>,
		})'
		x-bind="getFormBindings()"
		@submit="handleSubmit(
			(data) => { 
				// @TODO: Handle form submission here
				console.log('Billing address saved:', data);
				alert('Billing address saved successfully!');
			},
			(errors) => { 
				// @TODO: Handle form validation errors
				console.log('Form validation errors:', errors); 
			}
		)($event)"
		class="tutor-flex tutor-flex-column tutor-gap-6"
	>
		<div class="tutor-flex tutor-flex-column tutor-gap-4">
			<h5 class="tutor-h5 tutor-sm-hidden"><?php echo esc_html__( 'Account', 'tutor' ); ?></h5>
			<div class="tutor-card tutor-flex tutor-flex-column tutor-gap-5">
				<div class="tutor-account-avatar-wrapper">
					<div x-data="tutorPopover({
						placement: 'bottom',
						offset: 8,
					})">
						<div class="tutor-account-avatar" :class="open ? 'active' : ''">
							<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
							<button type="button" class="tutor-account-avatar-edit" x-ref="trigger" @click="toggle()">
								<?php tutor_utils()->render_svg_icon( Icon::EDIT_2, 24, 24 ); ?>
							</button>
						</div>

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
										->render();
								?>
								<?php
									Button::make()
										->label( __( 'Remove Photo', 'tutor' ) )
										->variant( Variant::SECONDARY )
										->size( Size::X_SMALL )
										->attr( 'type', 'button' )
										->render();
								?>
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
					?>
					<?php
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
						->clearable()
						->id( 'username' )
						->placeholder( __( 'Enter your username', 'tutor' ) )
						->attr( 'x-bind', "register('username')" )
						->render();
				?>

				<?php
					InputField::make()
						->type( InputType::TEXT )
						->label( __( 'Phone Number', 'tutor' ) )
						->name( 'phone_number' )
						->clearable()
						->id( 'phone_number' )
						->placeholder( __( 'Enter your phone number', 'tutor' ) )
						->attr( 'x-bind', "register('phone_number')" )
						->render();
				?>

				<?php
					InputField::make()
						->type( InputType::TEXT )
						->label( __( 'Skill/Occupation', 'tutor' ) )
						->name( 'occupation' )
						->clearable()
						->id( 'occupation' )
						->placeholder( __( 'Enter your skill/occupation', 'tutor' ) )
						->attr( 'x-bind', "register('occupation')" )
						->render();
				?>

				<?php
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
				?>

				<?php
					InputField::make()
						->type( InputType::TEXT )
						->label( __( 'Birthdate', 'tutor' ) )
						->name( 'birthdate' )
						->clearable()
						->id( 'birthdate' )
						->placeholder( __( 'Enter your birthdate', 'tutor' ) )
						->attr( 'x-bind', "register('birthdate')" )
						->render();
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
						->clearable()
						->id( 'display_name' )
						->placeholder( __( 'Enter your display name', 'tutor' ) )
						->attr( 'x-bind', "register('display_name')" )
						->render();
				?>

				<?php
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

		<!-- Certificate Signature will be served from certificate addon -->
		<div class="tutor-flex tutor-flex-column tutor-gap-4">
			<h5 class="tutor-h5"><?php echo esc_html__( 'Certificate Signature', 'tutor' ); ?></h5>
			<div class="tutor-card tutor-card-rounded-2xl">
				<?php
					InputField::make()
						->type( InputType::FILE )
						->variant( Variant::IMAGE_UPLOADER )
						->name( 'signature' )
						->accept( '.jpg,.jpeg,.png' )
						->uploader_icon( Icon::SIGNATURE_UPLOAD )
						->attr( 'x-bind', "register('signature')" )
						->uploader_subtitle( __( 'JPG, JPEG, GIF OR PNG Formats (700x430 Pixels)', 'tutor' ) )
						->uploader_button_text( __( 'Upload Image', 'tutor' ) )
						->render();
				?>
			</div>
		</div>
	</form>
</div>
