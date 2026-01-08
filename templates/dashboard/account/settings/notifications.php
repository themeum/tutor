<?php
/**
 * Notifications settings
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

?>
<section class="tutor-profile-notification">
	<h5 class="tutor-mb-4 tutor-mt-4 tutor-md-mt-1 tutor-h5 tutor-sm-hidden">
		<?php esc_html_e( 'Notifications', 'tutor' ); ?>
	</h5>
	<form
		id="<?php echo esc_attr( $form_id ); ?>"
		x-data='tutorForm({ 
				id: "<?php echo esc_attr( $form_id ); ?>",
				mode: "onChange",
				shouldFocusError: true,
			})'
		x-bind="getFormBindings()"
		@submit="handleSubmit(
			(data) => { 
				console.log('Notifications saved:', data);
				alert('Notifications saved successfully!');
			},
			(errors) => { 
				console.log('Form validation errors:', errors);
			}
		)($event)"
	>
		<div x-data="{ expanded: false }" class="tutor-profile-notification-card tutor-card-rounded-2xl tutor-mt-5">
			<div class="tutor-flex tutor-items-center tutor-justify-between tutor-gap-8 tutor-p-6">
				<div class="tutor-flex tutor-items-center tutor-gap-5">
					<?php tutor_utils()->render_svg_icon( Icon::NOTIFICATION_2, 20, 20 ); ?>
					<div>
						<div class="tutor-text-small tutor-font-medium tutor-text-primary">
							<?php esc_html_e( 'Push Notifications', 'tutor' ); ?>
						</div>
						<div class="tutor-text-small tutor-text-secondary">
							<?php esc_html_e( 'Configure custom notifications settings for Push.', 'tutor' ); ?>
						</div>
					</div>
				</div>
				<div class="tutor-flex tutor-gap-4">
					<div class="tutor-profile-notification-toggle tutor-text-subdued" :class="{ 'is-expanded': expanded }" @click="expanded = ! expanded">
						<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_UP, 20, 20 ); ?>
					</div>
					<?php
						InputField::make()
							->type( InputType::SWITCH )
							->name( 'push_notifications' )
							->attr( 'x-bind', "register('push_notifications')" )
							->size( Size::SM )
							->render();
					?>
				</div>
			</div>
			<div class="tutor-profile-notification-content tutor-p-6" x-show="expanded" x-collapse.duration.200ms>
				<span class="tutor-text-small tutor-text-subdued">
					<?php esc_html_e( 'General', 'tutor' ); ?>
				</span>
				<div class="tutor-flex tutor-flex-column tutor-gap-4 tutor-pt-4">
					<?php
					InputField::make()
						->type( InputType::CHECKBOX )
						->name( 'push_notifications_1' )
						->label( __( 'Push Notifications 1', 'tutor' ) )
						->attr( 'x-bind', "register('push_notifications_1')" )
						->size( Size::SM )
						->render();

					InputField::make()
						->type( InputType::CHECKBOX )
						->name( 'push_notifications_2' )
						->label( __( 'Push Notifications 2', 'tutor' ) )
						->attr( 'x-bind', "register('push_notifications_2')" )
						->size( Size::SM )
						->render();
					?>
				</div>
			</div>
		</div>
		<div class="tutor-profile-notification-card tutor-card-rounded-2xl tutor-mt-5" x-data="{ expanded: false }">
			<div class="tutor-flex tutor-items-center tutor-justify-between tutor-gap-8 tutor-p-6">
				<div class="tutor-flex tutor-items-center tutor-gap-5">
					<?php tutor_utils()->render_svg_icon( Icon::NOTIFICATION_2, 20, 20 ); ?>
					<div>
						<div class="tutor-text-small tutor-font-medium tutor-text-primary">
							<?php esc_html_e( 'Email Notifications', 'tutor' ); ?>
						</div>
						<div class="tutor-text-small tutor-text-secondary">
							<?php esc_html_e( 'Configure custom notifications settings for Email.', 'tutor' ); ?>
						</div>
					</div>
				</div>
				<div class="tutor-flex tutor-gap-4">
					<div class="tutor-profile-notification-toggle tutor-text-subdued" :class="{ 'is-expanded': expanded }" @click="expanded = ! expanded">
						<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_UP, 20, 20 ); ?>
					</div>
					<?php
						InputField::make()
							->type( InputType::SWITCH )
							->name( 'email_notifications' )
							->attr( 'x-bind', "register('email_notifications')" )
							->size( Size::SM )
							->render();
					?>
				</div>
			</div>
			<div class="tutor-profile-notification-content tutor-p-6" x-show="expanded" x-collapse.duration.200ms>
				<span class="tutor-text-small tutor-text-subdued">General</span>
				<div class="tutor-flex tutor-flex-column tutor-gap-4 tutor-pt-4">
					<?php
					InputField::make()
						->type( InputType::CHECKBOX )
						->name( 'email_notifications_1' )
						->label( __( 'Email Notifications', 'tutor' ) )
						->attr( 'x-bind', "register('email_notifications_1')" )
						->size( Size::SM )
						->render();

					InputField::make()
						->type( InputType::CHECKBOX )
						->name( 'push_notifications' )
						->label( __( 'Push Notifications', 'tutor' ) )
						->size( Size::SM )
						->render();
					?>
				</div>
			</div>
		</div>
	</form>
</section>