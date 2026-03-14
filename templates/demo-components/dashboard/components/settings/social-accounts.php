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

$social_fields = array(
	'facebook' => array(
		'label'       => __( 'Facebook', 'tutor' ),
		'placeholder' => __( 'Enter your Facebook profile URL', 'tutor' ),
		'icon'        => 'facebook',
		'pattern'     => '^https?:\/\/(www\.|m\.|web\.|mobile\.)?facebook\.com\/([A-Za-z0-9._-]+)\/?$',
	),
	'x'        => array(
		'label'       => __( 'X.com', 'tutor' ),
		'placeholder' => __( 'Enter your X.com profile URL', 'tutor' ),
		'icon'        => 'x',
		'pattern'     => '^https?:\/\/(www\.)?(x\.com|twitter\.com)\/([A-Za-z0-9_]+)\/?$',
	),
	'linkedin' => array(
		'label'       => __( 'LinkedIn', 'tutor' ),
		'placeholder' => __( 'Enter your LinkedIn profile URL', 'tutor' ),
		'icon'        => 'linkedin',
		'pattern'     => '^https?:\/\/(www\.)?linkedin\.com\/(in|company|school)\/([A-Za-z0-9_-]+)\/?$',
	),
	'github'   => array(
		'label'       => __( 'GitHub', 'tutor' ),
		'placeholder' => __( 'Enter your GitHub profile URL', 'tutor' ),
		'icon'        => 'github',
		'pattern'     => '^https?:\/\/(www\.)?github\.com\/([A-Za-z0-9_-]+)\/?$',
	),
	'website'  => array(
		'label'       => __( 'Website', 'tutor' ),
		'placeholder' => __( 'Enter your website URL', 'tutor' ),
		'icon'        => 'globe',
		'pattern'     => '^https?:\/\/(www\.)?[A-Za-z0-9]([A-Za-z0-9-]{0,61}[A-Za-z0-9])?(\.[A-Za-z0-9]([A-Za-z0-9-]{0,61}[A-Za-z0-9])?)*\.[A-Za-z]{2,}(\/.*)?$',
	),
);

?>

<section class="tutor-social-accounts">
	<div class="tutor-h5 tutor-sm-hidden"><?php echo esc_html__( 'Social Profile Link', 'tutor' ); ?></div>
	<form
		id="social-accounts-form"
		x-data="tutorForm({ id: 'social-accounts-form', mode: 'onChange', shouldFocusError: true })"
		x-bind="getFormBindings()"
		@submit="handleSubmit(
			(data) => { 
				console.log('Social profiles saved:', data);
				alert('Social profiles saved successfully!');
			},
			(errors) => { 
				console.log('Form validation errors:', errors);
			}
		)($event)"
		class="tutor-social-form"
	>
		<?php foreach ( $social_fields as $key => $field ) : ?>
			<div class='tutor-social-field'>
				<!-- Social icon -->
				<div class="tutor-social-icon">
					<?php tutor_utils()->render_svg_icon( $field['icon'], 20, 20 ); ?>
				</div>
				<div class="tutor-input-field" :class="{
					'tutor-input-field-error': errors.<?php echo esc_attr( $key ); ?>,
				}">
					<label for="<?php echo esc_attr( $key ); ?>" class="tutor-label">
						<?php echo esc_html( $field['label'] ); ?>
					</label>

					<div class="tutor-input-wrapper">
						<input
							type="url"
							id="<?php echo esc_attr( $key ); ?>"
							class="tutor-input tutor-input-content-clear"
							placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
							x-bind="register('<?php echo esc_attr( $key ); ?>', { 
								pattern: { 
									value: /<?php echo esc_html( $field['pattern'] ); ?>/i,
									message: '<?php echo esc_html( sprintf( /* translators: field label */ __( 'Please enter a valid %s URL', 'tutor' ), $field['label'] ) ); ?>',

								}
							})"
						>
						<button 
							type="button"
							class="tutor-input-clear-button"
							x-show="values.<?php echo esc_attr( $key ); ?> && String(values.<?php echo esc_attr( $key ); ?>).length > 0"
							x-cloak
							@click="setValue('<?php echo esc_attr( $key ); ?>', '')"
							aria-label="Clear input"
						>
							<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
						</button>
					</div>

					<div class="tutor-error-text" x-cloak x-show="errors.<?php echo esc_attr( $key ); ?>" x-text="errors?.<?php echo esc_attr( $key ); ?>?.message" role="alert" aria-live="polite"></div>
					
					<?php if ( ! empty( $field['help_text'] ) ) : ?>
						<div class="tutor-help-text" x-show="!errors?.<?php echo esc_attr( $key ); ?>?.message">
							<?php echo esc_html( $field['help_text'] ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</form>
</section>