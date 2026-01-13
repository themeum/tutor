<?php
/**
 * Withdrawal Method Settings
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Models\WithdrawModel;
use Tutor\Components\EmptyState;
use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;

$get_field_key = fn( $method_id, $field_name ) => $method_id . '_' . $field_name;

$map_field_type = fn( $field_type ) => match ( $field_type ) {
	'email' => InputType::EMAIL,
	'number' => InputType::NUMBER,
	'textarea' => InputType::TEXTAREA,
	default => InputType::TEXT,
};

$get_saved_method_values = function ( $method_id, $user_id ) {
	$meta_key = '_tutor_withdraw_method_data_' . $method_id;
	$values   = get_user_meta( $user_id, $meta_key, true );
	$values   = maybe_unserialize( $values );
	return is_array( $values ) ? $values : array();
};

$get_field_value = function ( $method_id, $field_name, $old_method_key, $saved_account, $method_values ) {
	$saved_value = '';

	if ( $old_method_key === $method_id ) {
		$saved_value = tutor_utils()->avalue_dot( $field_name . '.value', $saved_account );
	}

	if ( ! $saved_value && isset( $method_values[ $field_name ]['value'] ) ) {
		$saved_value = $method_values[ $field_name ]['value'];
	}

	return $saved_value ? $saved_value : '';
};

$user_id            = get_current_user_id();
$withdrawal_methods = apply_filters( 'tutor_withdrawal_methods_available', array() );
$saved_account      = WithdrawModel::get_user_withdraw_method();

if ( empty( $withdrawal_methods ) ) {
	?>
	<section class="tutor-flex tutor-flex-column tutor-gap-4">
		<h5 class="tutor-h5 tutor-sm-hidden"><?php echo esc_html__( 'Withdraw Method', 'tutor' ); ?></h5>
		<?php
			EmptyState::make()
				->title( 'No Withdraw Method Selected' )
				->render();
		?>
	</section>
	<?php
	return;
}

$method_options = array_map(
	fn( $method_id, $method ) => array(
		'label' => tutor_utils()->avalue_dot( 'method_name', $method ),
		'value' => $method_id,
	),
	array_keys( $withdrawal_methods ),
	$withdrawal_methods
);

$old_method_key = tutor_utils()->avalue_dot( 'withdraw_method_key', $saved_account );
$default_values = array( 'withdraw_method' => $old_method_key ? $old_method_key : '' );

foreach ( $withdrawal_methods as $method_id => $method ) {
	$method_values = $get_saved_method_values( $method_id, $user_id );
	$form_fields   = tutor_utils()->avalue_dot( 'form_fields', $method );

	if ( is_array( $form_fields ) ) {
		foreach ( $form_fields as $field_name => $field ) {
			$field_key                    = $get_field_key( $method_id, $field_name );
			$default_values[ $field_key ] = $get_field_value(
				$method_id,
				$field_name,
				$old_method_key,
				$saved_account,
				$method_values
			);
		}
	}
}

?>

<section class="tutor-flex tutor-flex-column tutor-gap-4">
	<h5 class="tutor-h5 tutor-sm-hidden"><?php echo esc_html__( 'Withdraw Method', 'tutor' ); ?></h5>

	<form
		id="<?php echo esc_attr( $form_id ); ?>"
		x-data='tutorForm({ 
			id: "<?php echo esc_attr( $form_id ); ?>",
			mode: "onChange",
			defaultValues: <?php echo wp_json_encode( $default_values ); ?>,
		})'
		x-bind="getFormBindings()"
		@submit="handleSubmit(handleSaveWithdrawMethod)($event)"
		class="tutor-card tutor-card-rounded-2xl tutor-flex tutor-flex-column tutor-gap-5"
	>
		<?php
			$min_withdraw_amount = tutor_utils()->get_option( 'min_withdraw_amount' );
			$formatted_min       = tutor_utils()->tutor_price( $min_withdraw_amount );

			InputField::make()
				->type( InputType::SELECT )
				->label( __( 'Select Method', 'tutor' ) )
				->name( 'withdraw_method' )
				->id( 'withdraw_method' )
				->options( $method_options )
				->placeholder( __( 'Select a withdrawal method', 'tutor' ) )
				->required()
				->attr( 'x-bind', "register('withdraw_method', { required: true })" )
				->help_text(
					sprintf(
						/* translators: %s: minimum withdraw amount */
						__( 'Minimum withdraw amount is %s', 'tutor' ),
						$formatted_min
					)
				)
				->render();
			?>

		<?php foreach ( $withdrawal_methods as $method_id => $method ) : ?>
			<?php
			$form_fields = tutor_utils()->avalue_dot( 'form_fields', $method );
			if ( ! is_array( $form_fields ) || empty( $form_fields ) ) {
				continue;
			}
			?>
			<div 
				x-show="values.withdraw_method === '<?php echo esc_js( $method_id ); ?>'" 
				x-cloak
				class="tutor-flex tutor-flex-column tutor-gap-5"
			>
				<?php foreach ( $form_fields as $field_name => $field ) : ?>
					<?php
					$field_key  = $get_field_key( $method_id, $field_name );
					$input_type = $map_field_type( $field['type'] ?? 'text' );

					$input_field = InputField::make()
						->type( $input_type )
						->name( $field_key )
						->id( $field_key )
						->attr( 'x-bind', "register('{$field_key}')" );

					if ( ! empty( $field['label'] ) ) {
						$input_field->label( $field['label'] );
					}

					if ( ! empty( $field['desc'] ) ) {
						$input_field->help_text( $field['desc'] );
					}

					$input_field->render();
					?>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</form>
</section>