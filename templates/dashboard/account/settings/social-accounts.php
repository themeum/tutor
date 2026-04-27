<?php
/**
 * Settings Social Profile Link
 *
 * @package Tutor\Templates
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;
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
	<h5 class="tutor-h5 tutor-md-hidden tutor-my-none"><?php echo esc_html__( 'Social Profile Link', 'tutor' ); ?></h5>
	<form
		id="<?php echo esc_attr( $form_id ); ?>"
		x-data='tutorForm({ 
			id: "<?php echo esc_attr( $form_id ); ?>",
			mode: "onChange",
			shouldFocusError: true,
			defaultValues: <?php echo esc_attr( wp_json_encode( $social_links ) ); ?>
		})'
		x-bind="getFormBindings()"
		@submit="handleSubmit((data) => handleSaveSocialProfile(data, '<?php echo esc_attr( $form_id ); ?>'))($event)"
		class="tutor-card tutor-social-form"
	>
		<?php do_action( 'tutor_profile_edit_before_social_media', $user ); ?>

		<?php foreach ( $social_fields as $key => $field ) : ?>
			<div class='tutor-social-field'>
				<!-- Social icon -->
				<div class="tutor-social-icon">
					<?php SvgIcon::make()->name( $field['svg_icon'] )->size( 20 )->render(); ?>
				</div>
				<?php
					$message = sprintf(
						/* translators: field label */
						__( 'Please enter a valid %s URL', 'tutor' ),
						$field['label']
					);

					InputField::make()
						->type( InputType::TEXT )
						->id( $key )
						->name( $key )
						->label( $field['label'] )
						->clearable()
						->placeholder( $field['placeholder'] )
						->attr( 'x-bind', "register('$key', { pattern: { value: /$field[pattern]/i, message: '" . esc_js( $message ) . "' } })" )
						->render();
				?>
			</div>
		<?php endforeach; ?>
	</form>
</section>
