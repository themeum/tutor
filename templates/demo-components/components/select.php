<?php
/**
 * Advanced Select Component Demo
 *
 * @package TutorLMS\Templates
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;

defined( 'ABSPATH' ) || exit;

// Sample data.
$countries = array(
	array(
		'label' => 'United States',
		'value' => 'us',
		'icon'  => Icon::GLOBE,
	),
	array(
		'label' => 'United Kingdom',
		'value' => 'uk',
		'icon'  => Icon::GLOBE,
	),
	array(
		'label' => 'Canada',
		'value' => 'ca',
		'icon'  => Icon::GLOBE,
	),
	array(
		'label' => 'Australia',
		'value' => 'au',
		'icon'  => Icon::GLOBE,
	),
	array(
		'label' => 'Germany',
		'value' => 'de',
		'icon'  => Icon::GLOBE,
	),
	array(
		'label' => 'France',
		'value' => 'fr',
		'icon'  => Icon::GLOBE,
	),
	array(
		'label' => 'Japan',
		'value' => 'jp',
		'icon'  => Icon::GLOBE,
	),
	array(
		'label' => 'China',
		'value' => 'cn',
		'icon'  => Icon::GLOBE,
	),
);

$categories = array(
	array(
		'label'       => 'Development',
		'value'       => 'dev',
		'icon'        => Icon::DELETE_2,
		'description' => 'Web & Mobile Development',
	),
	array(
		'label'       => 'Design',
		'value'       => 'design',
		'icon'        => Icon::BOOK_2,
		'description' => 'UI/UX & Graphic Design',
	),
	array(
		'label'       => 'Marketing',
		'value'       => 'marketing',
		'icon'        => Icon::DELETE_2,
		'description' => 'Digital Marketing',
	),
	array(
		'label'       => 'Business',
		'value'       => 'business',
		'icon'        => Icon::NOTIFICATION_2,
		'description' => 'Business & Management',
	),
);

$grouped_options = array(
	array(
		'label'   => 'Popular',
		'options' => array(
			array(
				'label' => 'JavaScript',
				'value' => 'js',
			),
			array(
				'label' => 'Python',
				'value' => 'py',
			),
			array(
				'label' => 'PHP',
				'value' => 'php',
			),
		),
	),
	array(
		'label'   => 'Other Languages',
		'options' => array(
			array(
				'label' => 'Ruby',
				'value' => 'rb',
			),
			array(
				'label' => 'Go',
				'value' => 'go',
			),
			array(
				'label' => 'Rust',
				'value' => 'rust',
			),
		),
	),
);

$timezone_options = array();
foreach ( tutor_global_timezone_lists() as $key => $value ) {
	$timezone_options[] = array(
		'label' => $value,
		'value' => $key,
	);
}

?>

<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<div class="tutor-p-6 tutor-flex tutor-flex-column tutor-gap-8">
		<div>
			<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-2">
				<?php esc_html_e( 'Advanced Select Component', 'tutor' ); ?>
			</h1>
			<p class="tutor-text-gray-600">
				<?php esc_html_e( 'Production-grade select with auto form integration, accessibility, and advanced features.', 'tutor' ); ?>
			</p>
		</div>

		<!-- Basic Example -->
		<div>
			<h2 class="tutor-text-xl tutor-font-semibold"><?php esc_html_e( 'Basic Select', 'tutor' ); ?></h2>
			<div class="tutor-max-w-md">
				<?php
				tutor_load_template(
					'core-components.select',
					array(
						'options'     => $countries,
						'placeholder' => __( 'Select a country', 'tutor' ),
						'name'        => 'country',
					)
				);
				?>
			</div>
		</div>

		<!-- Searchable -->
		<div>
			<h2 class="tutor-text-xl tutor-font-semibold"><?php esc_html_e( 'Searchable Select', 'tutor' ); ?></h2>
			<div class="tutor-max-w-md">
				<?php
				tutor_load_template(
					'core-components.select',
					array(
						'options'     => $timezone_options,
						'placeholder' => __( 'Select timezone', 'tutor' ),
						'searchable'  => true,
						'name'        => 'timezone',
					)
				);
				?>
			</div>
		</div>

		<!-- With Icons & Descriptions -->
		<div>
			<h2 class="tutor-text-xl tutor-font-semibold"><?php esc_html_e( 'With Icons & Descriptions', 'tutor' ); ?></h2>
			<div class="tutor-max-w-md">
				<?php
				tutor_load_template(
					'core-components.select',
					array(
						'options'     => $categories,
						'placeholder' => __( 'Select category', 'tutor' ),
						'searchable'  => true,
						'name'        => 'category',
					)
				);
				?>
			</div>
		</div>

		<!-- Multi-Select -->
		<div>
			<h2 class="tutor-text-xl tutor-font-semibold"><?php esc_html_e( 'Multi-Select', 'tutor' ); ?></h2>
			<div class="tutor-max-w-md">
				<?php
				tutor_load_template(
					'core-components.select',
					array(
						'options'     => $countries,
						'placeholder' => __( 'Select countries', 'tutor' ),
						'multiple'    => true,
						'searchable'  => true,
						'clearable'   => true,
						'name'        => 'countries',
					)
				);
				?>
			</div>
		</div>

		<!-- Grouped Options -->
		<div>
			<h2 class="tutor-text-xl tutor-font-semibold"><?php esc_html_e( 'Grouped Options', 'tutor' ); ?></h2>
			<div class="tutor-max-w-md">
				<?php
				tutor_load_template(
					'core-components.select',
					array(
						'groups'      => $grouped_options,
						'placeholder' => __( 'Select language', 'tutor' ),
						'searchable'  => true,
						'name'        => 'language',
					)
				);
				?>
			</div>
		</div>

		<!-- Form Integration Example -->
		<div>
			<h2 class="tutor-text-xl tutor-font-semibold"><?php esc_html_e( 'Form Integration', 'tutor' ); ?></h2>
			<p class="tutor-text-gray-600 tutor-mb-4">
				<?php esc_html_e( 'Select automatically integrates with form validation. Try submitting without selecting.', 'tutor' ); ?>
			</p>

			<form
				x-data="tutorForm({ id: 'select-form', mode: 'onBlur', shouldFocusError: true })"
				x-bind="getFormBindings()"
				@submit="handleSubmit(
					(data) => { 
						alert('Form submitted!\\n' + JSON.stringify(data, null, 2)); 
					},
					(errors) => { 
						console.log('Validation errors:', errors); 
					}
				)($event)"
				class="tutor-max-w-md tutor-space-y-5"
			>
				<?php
				// Example: Filter by status using href options.
				$current_status = sanitize_text_field( wp_unslash( $_GET['status'] ?? '' ) );
				$options        = array(
					array(
						'label' => 'All Statuses',
						'value' => '',
						'href'  => remove_query_arg( 'status' ),
					),
					array(
						'label' => 'Published',
						'value' => 'published',
						'href'  => add_query_arg( 'status', 'published' ),
					),
					array(
						'label' => 'Draft',
						'value' => 'draft',
						'href'  => add_query_arg( 'status', 'draft' ),
					),
					array(
						'label' => 'Pending',
						'value' => 'pending',
						'href'  => add_query_arg( 'status', 'pending' ),
					),
				);

				InputField::make()
					->type( InputType::SELECT )
					->name( 'status' )
					->label( __( 'Select Course', 'tutor' ) )
					->placeholder( __( 'Select a course', 'tutor' ) )
					->value( $current_status )
					->options( $options )
					->searchable()
					->render();
				?>

				<!-- Using form-select wrapper -->
				<?php
				tutor_load_template(
					'core-components.form-select',
					array(
						'name'        => 'user_country',
						'label'       => __( 'Country', 'tutor' ),
						'options'     => $countries,
						'placeholder' => __( 'Select your country', 'tutor' ),
						'searchable'  => true,
						'required'    => __( 'Please select a country', 'tutor' ),
						'help_text'   => __( 'Select the country where you reside', 'tutor' ),
					)
				);
				?>

				<?php
				tutor_load_template(
					'core-components.form-select',
					array(
						'name'        => 'user_timezone',
						'label'       => __( 'Timezone', 'tutor' ),
						'options'     => $timezone_options,
						'placeholder' => __( 'Select your timezone', 'tutor' ),
						'searchable'  => true,
						'required'    => true,
						'help_text'   => __( 'Select your local timezone', 'tutor' ),
					)
				);
				?>

				<?php
				tutor_load_template(
					'core-components.form-select',
					array(
						'name'           => 'interests',
						'label'          => __( 'Interests', 'tutor' ),
						'options'        => $categories,
						'placeholder'    => __( 'Select your interests', 'tutor' ),
						'multiple'       => true,
						'searchable'     => true,
						'clearable'      => true,
						'max_selections' => 3,
						'help_text'      => __( 'Select up to 3 interests', 'tutor' ),
					)
				);
				?>

				<div class="tutor-flex tutor-gap-3">
					<button type="submit" class="tutor-btn tutor-btn-primary">
						<?php esc_html_e( 'Submit', 'tutor' ); ?>
					</button>
					<button type="button" @click="reset()" class="tutor-btn tutor-btn-outline">
						<?php esc_html_e( 'Reset', 'tutor' ); ?>
					</button>
				</div>

				<!-- Debug Form State -->
				<div class="tutor-mt-6 tutor-p-4 tutor-bg-gray-100 tutor-rounded-lg tutor-text-sm">
					<h4 class="tutor-font-semibold tutor-mb-2"><?php esc_html_e( 'Form State', 'tutor' ); ?></h4>
					<div><strong><?php esc_html_e( 'Values:', 'tutor' ); ?></strong> <span x-text="JSON.stringify(values, null, 2)"></span></div>
					<div><strong><?php esc_html_e( 'Errors:', 'tutor' ); ?></strong> <span x-text="JSON.stringify(errors, null, 2)"></span></div>
					<div><strong><?php esc_html_e( 'Valid:', 'tutor' ); ?></strong> <span x-text="isValid"></span></div>
				</div>
			</form>
		</div>

		<!-- Size Variants -->
		<div>
			<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-4"><?php esc_html_e( 'Size Variants', 'tutor' ); ?></h2>
			<div class="tutor-flex tutor-flex-column tutor-gap-6">
				<div>
					<p class="tutor-text-sm tutor-text-gray-600 tutor-mb-2"><?php esc_html_e( 'Small', 'tutor' ); ?></p>
					<div class="tutor-max-w-md">
						<?php
						tutor_load_template(
							'core-components.select',
							array(
								'options'     => $countries,
								'placeholder' => __( 'Small select', 'tutor' ),
								'size'        => 'sm',
							)
						);
						?>
					</div>
				</div>
				<div>
					<p class="tutor-text-sm tutor-text-gray-600 tutor-mb-2"><?php esc_html_e( 'Default', 'tutor' ); ?></p>
					<div class="tutor-max-w-md">
						<?php
						tutor_load_template(
							'core-components.select',
							array(
								'options'     => $countries,
								'placeholder' => __( 'Default select', 'tutor' ),
							)
						);
						?>
					</div>
				</div>
				<div>
					<p class="tutor-text-sm tutor-text-gray-600 tutor-mb-2"><?php esc_html_e( 'Large', 'tutor' ); ?></p>
					<div class="tutor-max-w-md">
						<?php
						tutor_load_template(
							'core-components.select',
							array(
								'options'     => $countries,
								'placeholder' => __( 'Large select', 'tutor' ),
								'size'        => 'lg',
							)
						);
						?>
					</div>
				</div>
			</div>
		</div>

		<!-- States -->
		<div>
			<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-4"><?php esc_html_e( 'States', 'tutor' ); ?></h2>
			<div class="tutor-flex tutor-flex-column tutor-gap-6">
				<div>
					<p class="tutor-text-sm tutor-text-gray-600 tutor-mb-2"><?php esc_html_e( 'Disabled', 'tutor' ); ?></p>
					<?php
					tutor_load_template(
						'core-components.select',
						array(
							'options'     => $countries,
							'placeholder' => __( 'Disabled select', 'tutor' ),
							'disabled'    => true,
						)
					);
					?>
				</div>
				<div>
					<p class="tutor-text-sm tutor-text-gray-600 tutor-mb-2"><?php esc_html_e( 'Loading', 'tutor' ); ?></p>
					<?php
					tutor_load_template(
						'core-components.select',
						array(
							'options'     => $countries,
							'placeholder' => __( 'Loading...', 'tutor' ),
							'loading'     => true,
						)
					);
					?>
				</div>
			</div>
		</div>
	</div>
</section>
