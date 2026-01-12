<?php
/**
 * Settings Social Profile Link
 *
 * @package Tutor\Templates
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;

$user = wp_get_current_user();

$social_fields = tutor_utils()->tutor_user_social_icons();

$social_links = array();
foreach ( $social_fields as $key => $field ) {
	$social_links[ $key ] = get_user_meta( $user->ID, $key, true );
}

?>

<section class="tutor-social-accounts">
	<div class="tutor-h5 tutor-sm-hidden"><?php echo esc_html__( 'Social Profile Link', 'tutor' ); ?></div>
	<form
		id="<?php echo esc_attr( $form_id ); ?>"
		x-data='tutorForm({ 
			id: "<?php echo esc_attr( $form_id ); ?>",
			mode: "onChange",
			shouldFocusError: true,
			defaultValues: <?php echo wp_json_encode( $social_links ); ?>
		})'
		x-bind="getFormBindings()"
		@submit="handleSubmit(handleSaveSocialProfile)($event)"
		class="tutor-social-form"
	>
		<?php do_action( 'tutor_profile_edit_before_social_media', $user ); ?>

		<?php foreach ( $social_fields as $key => $field ) : ?>
			<div class='tutor-social-field'>
				<!-- Social icon -->
				<div class="tutor-social-icon">
					<?php tutor_utils()->render_svg_icon( $field['svg_icon'], 20, 20 ); ?>
				</div>
				<?php
					$message = sprintf( /* translators: field label */ __( 'Please enter a valid %s URL', 'tutor' ), $field['label'] );

					InputField::make()
						->type( InputType::TEXT )
						->id( $key )
						->name( $key )
						->label( $field['label'] )
						->clearable()
						->required()
						->placeholder( $field['placeholder'] )
						->attr( 'x-bind', "register('$key', { pattern: { value: /$field[pattern]/i, message: '$message' } })" )
						->render();
				?>
			</div>
		<?php endforeach; ?>
	</form>
</section>